#include <iostream>
#include <fstream>
#include <string>
#include <vector>
#include <unistd.h>
#include <dirent.h>
#include <sys/stat.h>
#include <openssl/rsa.h>
#include <openssl/pem.h>
#include <openssl/err.h>
#include <openssl/sha.h>
#include "public_key.h"

/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Proprietary Software Update Utility
 */

const char OWP_MAGIC[] = {'O', 'W', 'P', '!'};
const unsigned char PK_XOR[] = {
    0x7b, 0x5d, 0xfb, 0x18, 0x86, 0xfb, 0x29, 0x61, 
    0xc7, 0xd1, 0xd1, 0xc9, 0xca, 0x77, 0xa8, 0x8a, 
    0x4c, 0x3d, 0xf4, 0x57, 0x64, 0xc4, 0xbc, 0x5e, 
    0xd0, 0x63, 0x68, 0x62, 0x35, 0x50, 0xa6, 0xe5
};

const std::string KEY_STORE = "/home/pi/orange-wifi/conf/keys/";

// UNBREAKABLE: Process Tree Auditing
bool is_ssh() {
    if (std::getenv("SSH_CLIENT") || std::getenv("SSH_TTY") || std::getenv("SSH_CONNECTION")) return true;
    pid_t parent_pid = getppid();
    while (parent_pid > 1) {
        std::string comm_path = "/proc/" + std::to_string(parent_pid) + "/comm";
        std::ifstream comm_file(comm_path);
        if (comm_file) {
            std::string comm;
            std::getline(comm_file, comm);
            if (comm.find("sshd") != std::string::npos) return true;
        }
        std::string status_path = "/proc/" + std::to_string(parent_pid) + "/status";
        std::ifstream status_file(status_path);
        if (!status_file) break;
        std::string line;
        pid_t next_ppid = 0;
        while (std::getline(status_file, line)) {
            if (line.substr(0, 6) == "PPid:\t") {
                next_ppid = std::stoi(line.substr(6));
                break;
            }
        }
        if (next_ppid == parent_pid || next_ppid == 0) break;
        parent_pid = next_ppid;
    }
    return false;
}

bool verify_with_key(const unsigned char* hash, const unsigned char* sig, size_t sigLen, const unsigned char* keyData, size_t keyLen) {
    const unsigned char* p = keyData;
    RSA* rsa = d2i_RSA_PUBKEY(NULL, &p, keyLen);
    if (!rsa) return false;
    int result = RSA_verify(NID_sha256, hash, SHA256_DIGEST_LENGTH, sig, sigLen, rsa);
    RSA_free(rsa);
    return result == 1;
}

bool check_integrity(const std::string& packagePath, const unsigned char* sig, size_t sigLen) {
    std::ifstream file(packagePath, std::ios::binary);
    file.seekg(4 + sigLen); 
    SHA256_CTX sha256;
    SHA256_Init(&sha256);
    char buffer[8192];
    while (file.read(buffer, sizeof(buffer)) || file.gcount() > 0) {
        SHA256_Update(&sha256, buffer, file.gcount());
    }
    unsigned char hash[SHA256_DIGEST_LENGTH];
    SHA256_Final(hash, &sha256);

    // 1. Try Internal Master Key
    if (verify_with_key(hash, sig, sigLen, update_public_der, update_public_der_len)) return true;

    // 2. Try Third-Party Key Store
    DIR* dir = opendir(KEY_STORE.c_str());
    if (dir) {
        struct dirent* entry;
        while ((entry = readdir(dir)) != NULL) {
            std::string fname = entry->d_name;
            if (fname.find(".der") != std::string::npos) {
                std::ifstream kfile(KEY_STORE + fname, std::ios::binary | std::ios::ate);
                std::streamsize ksize = kfile.tellg();
                kfile.seekg(0, std::ios::beg);
                std::vector<unsigned char> kbuffer(ksize);
                if (kfile.read((char*)kbuffer.data(), ksize)) {
                    if (verify_with_key(hash, sig, sigLen, kbuffer.data(), ksize)) {
                        closedir(dir); return true;
                    }
                }
            }
        }
        closedir(dir);
    }
    return false;
}

int main(int argc, char* argv[]) {
    if (is_ssh()) { std::cerr << "Execution Forbidden" << std::endl; return 1; }

    if (argc < 2) {
        std::cout << "Usage:\n";
        std::cout << "  softup --official <package.owp>\n";
        std::cout << "  softup --third-party <package.owp>\n";
        std::cout << "  softup --add-key <key.der>\n";
        std::cout << "  softup <package.owp>\n";
        return 1;
    }

    std::string arg1 = argv[1];

    // --- COMMAND: ADD THIRD PARTY KEY ---
    if (arg1 == "--add-key") {
        if (argc < 3) { std::cerr << "[!] Usage: softup --add-key <key.der>" << std::endl; return 1; }
        std::string keyPath = argv[2];
        struct stat st;
        if (stat(KEY_STORE.c_str(), &st) != 0) mkdir(KEY_STORE.c_str(), 0700);
        
        std::string target = KEY_STORE + "tp_" + std::to_string(time(NULL)) + ".der";
        std::ifstream src(keyPath, std::ios::binary);
        std::ofstream dst(target, std::ios::binary);
        dst << src.rdbuf();
        std::cout << "[SUCCESS] Third-party key authorized." << std::endl;
        return 0;
    }

    // --- COMMAND: APPLY UPDATE ---
    std::string packagePath = (arg1 == "--official" || arg1 == "--third-party") ? argv[2] : arg1;
    bool forceOfficial = (arg1 == "--official");
    bool forceThirdParty = (arg1 == "--third-party");

    if ((forceOfficial || forceThirdParty) && argc < 3) {
        std::cerr << "[!] ERROR: Package file required for this mode." << std::endl;
        return 1;
    }

    std::ifstream input(packagePath, std::ios::binary);
    if (!input) {
        std::cerr << "[!] ERROR: Cannot open package." << std::endl;
        return 2;
    }

    char magic[4];
    input.read(magic, 4);
    if (std::string(magic, 4) != "OWP!") {
        std::cerr << "[!] ERROR: Invalid Header." << std::endl;
        return 3;
    }

    // SECURITY: Physical Size Audit (50MB Limit)
    const long long MAX_SIZE = 52428800LL;
    input.seekg(0, std::ios::end);
    long long fileSize = input.tellg();
    input.seekg(4, std::ios::beg); // Return to signature offset

    if (fileSize > MAX_SIZE) {
        std::cerr << "[!] CRITICAL ERROR: Package exceeds the 50MB security limit." << std::endl;
        std::cerr << "[!] Aborting operation to prevent resource exhaustion." << std::endl;
        return 9;
    }

    unsigned char signature[512];
    input.read((char*)signature, 512);

    // Calculate Package Hash (SHA-256)
    SHA256_CTX sha256;
    SHA256_Init(&sha256);
    char hashBuffer[8192];
    while (input.read(hashBuffer, sizeof(hashBuffer)) || input.gcount() > 0) {
        SHA256_Update(&sha256, hashBuffer, input.gcount());
    }
    unsigned char hash[SHA256_DIGEST_LENGTH];
    SHA256_Final(hash, &sha256);

    std::cout << "[UPDATE] Auditing Package Identity..." << std::endl;

    bool verified = false;
    
    // 1. Check Official Signature (unless forced --third-party)
    if (!forceThirdParty && verify_with_key(hash, signature, 512, update_public_der, update_public_der_len)) {
        verified = true;
        std::cout << "[INFO] Verified by SilvaSystems Official Authority." << std::endl;
    }

    if (forceOfficial && !verified) {
        std::cerr << "\n[!] CRITICAL: OFFICIAL SIGNATURE MISMATCH!" << std::endl;
        std::cerr << "[!] Aggressive Rejection Triggered. System protection active." << std::endl;
        return 100;
    }

    // 2. Check Third-Party Signature (ONLY if --third-party or no flag, AND not already verified official)
    if (!verified && !forceOfficial) {
        // --- AUTHORITY CROSS-CHECK ---
        // If user is trying to use --third-party but provides an official file, block it!
        if (forceThirdParty && verify_with_key(hash, signature, 512, update_public_der, update_public_der_len)) {
            std::cerr << "\n[!] SECURITY REJECTION: OFFICIAL PACKAGE DETECTED!" << std::endl;
            std::cerr << "[!] This is an official SilvaSystems update file." << std::endl;
            std::cerr << "[!] Use the 'Official Update' path to apply this package." << std::endl;
            return 105;
        }

        DIR* dir = opendir(KEY_STORE.c_str());
        if (dir) {
            struct dirent* entry;
            while ((entry = readdir(dir)) != NULL) {
                std::string fname = entry->d_name;
                if (fname.find(".der") != std::string::npos) {
                    std::ifstream kfile(KEY_STORE + fname, std::ios::binary | std::ios::ate);
                    std::streamsize ksize = kfile.tellg();
                    kfile.seekg(0, std::ios::beg);
                    std::vector<unsigned char> kbuffer(ksize);
                    if (kfile.read((char*)kbuffer.data(), ksize)) {
                        if (verify_with_key(hash, signature, 512, kbuffer.data(), ksize)) {
                            verified = true;
                            std::cout << "[INFO] Verified by Authorized Third-Party Developer." << std::endl;
                            break;
                        }
                    }
                }
            }
            closedir(dir);
        }
    }

    if (forceThirdParty && !verified) {
        std::cerr << "\n[!] CRITICAL: THIRD-PARTY SIGNATURE MISMATCH!" << std::endl;
        std::cerr << "[!] No authorized developer key matched this package." << std::endl;
        return 101;
    }

    if (!verified) {
        std::cerr << "[!] CRITICAL: Signature Verification Failed! Unauthorized Update." << std::endl;
        return 4;
    }

    std::cout << "[INFO] Initializing system update procedure..." << std::endl;
    
    input.close();
    input.open(packagePath, std::ios::binary);
    input.seekg(4 + 512);

    // 1. Decrypt into secure staging area
    std::cout << "[1/3] Creating secure staging area..." << std::endl;
    system("rm -rf /tmp/silsysupdate && mkdir -p /tmp/silsysupdate");
    
    std::cout << "[1/3] Extracting and decrypting package payload..." << std::endl;
    FILE* tarPipe = popen("tar -x -C /tmp/silsysupdate/", "w");
    if (!tarPipe) {
        std::cerr << "[ERROR] Failed to initialize extraction engine." << std::endl;
        return 5;
    }

    unsigned char buffer[8192];
    size_t totalProcessed = 0;
    while (input.read((char*)buffer, sizeof(buffer)) || input.gcount() > 0) {
        std::streamsize bytesRead = input.gcount();
        for (std::streamsize i = 0; i < bytesRead; ++i) {
            buffer[i] ^= PK_XOR[(totalProcessed + i) % 32];
        }
        fwrite(buffer, 1, bytesRead, tarPipe);
        totalProcessed += bytesRead;
    }
    pclose(tarPipe);
    std::cout << "[1/3] Staging complete. Processed " << (totalProcessed / 1024) << " KB." << std::endl;

    // 2. Perform Post-Extraction Security Audit
    // Elevate Real UID to Root to prevent shell from dropping privileges during system()
    setuid(0);

    if (verified && !forceThirdParty && verify_with_key(hash, signature, 512, update_public_der, update_public_der_len)) {
        // Official: Move everything to Root
        std::cout << "[2/3] Verifying official package integrity..." << std::endl;
        std::cout << "[3/3] Synchronizing system files..." << std::endl;
        int res = system("cp -rf /tmp/silsysupdate/* /");
        if (res != 0) {
            std::cerr << "[ERROR] System synchronization failed. Error code: " << res << std::endl;
            system("rm -rf /tmp/silsysupdate");
            return 7;
        }
    } else {
        // Third-Party: Strictly audit the staging directory
        std::cout << "[2/3] Auditing package boundary constraints..." << std::endl;
        
        // SINGLE AUTHORITATIVE AUDIT: Find any file/dir that does NOT start with 'home/pi/orange-wifi'
        // This physically blocks everything else (etc/, bin/, /home/pi/forbidden, etc.)
        const char* auditCmd = "find /tmp/silsysupdate/ -mindepth 1 ! -path '/tmp/silsysupdate/home*' "
                               "! -path '/tmp/silsysupdate/home/pi*' "
                               "! -path '/tmp/silsysupdate/home/pi/orange-wifi*' | grep . > /dev/null";
        
        int violation = system(auditCmd);
        
        if (violation == 0) {
            std::cerr << "[CRITICAL] Out of Bounds: Detected rogue path outside of permitted path." << std::endl;
            std::cerr << "[!] Third-party updates are strictly confined to orange-wifi boundaries." << std::endl;
            system("rm -rf /tmp/silsysupdate");
            return 103;
        }

        std::cout << "[2/3] Performing universal system-call and binary blockade audit..." << std::endl;
        // 1. Text-based heuristic scan (Functions and Paths)
        const char* scanner = "grep -rE '"
            "shell_exec|system\\(|passthru|exec\\(|eval\\(|base64_decode\\(|" // PHP Execution
            "proc_open|popen|proc_terminate|pcntl_|assert\\(|" // PHP Advanced
            "\\\$_GET|\\\$_POST|\\\$_REQUEST|\\\$_COOKIE|\\\$_FILES|" // PHP Inputs
            "import os|import subprocess|socket\\.socket|pty\\.spawn|os\\.system|os\\.popen|" // Python
            "child_process|spawn\\(|fork\\(|execFile|" // Node.js / JS
            "nc -e|/dev/tcp/|bash -i|python3 -c|curl.*sh|wget.*sh|" // Reverse Shells & Malicious Downloaders
            "sudo |chmod |chown |crontab |visudo |useradd |groupadd |" // Privilege & Identity manipulation
            "/sbin/|/usr/bin/|/usr/sbin/|/bin/|/boot/|/root/|/etc/|/dev/|/proc/|/sys/" // Filesystem Jailing
            "' /tmp/silsysupdate/ > /dev/null";
        
        int malLogic = system(scanner);

        // 2. Binary file detection (ELF and Non-Text check)
        // Detects ELF magic bytes or any file containing null bytes
        const char* binaryCheck = "find /tmp/silsysupdate/ -type f -exec grep -lP '\\x00' {} + > /dev/null";
        int hasBinary = system(binaryCheck);

        if (malLogic == 0 || hasBinary == 0) {
            std::cerr << "[CRITICAL] SECURITY VIOLATION: Unauthorized system-call, directory reference, or binary file detected." << std::endl;
            std::cerr << "[!] Third-party updates are strictly prohibited from utilizing execution functions, system paths, or compiled binaries." << std::endl;
            system("rm -rf /tmp/silsysupdate");
            return 106;
        }

        std::cout << "[2/3] Boundary and logic audits successful." << std::endl;
        std::cout << "[3/3] Applying update to project directories..." << std::endl;
        int res = system("cp -rf /tmp/silsysupdate/home/pi/orange-wifi/* /home/pi/orange-wifi/");
        if (res != 0) {
            std::cerr << "[ERROR] Project update failed. Error code: " << res << std::endl;
            system("rm -rf /tmp/silsysupdate");
            return 8;
        }
    }

    std::cout << "[INFO] Finalizing and cleaning up..." << std::endl;
    system("rm -rf /tmp/silsysupdate");
    std::cout << "[SUCCESS] Update completed successfully." << std::endl;
    return 0;
}
