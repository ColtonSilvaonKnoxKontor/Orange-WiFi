#include <iostream>
#include <fstream>
#include <string>
#include <vector>
#include <unistd.h>
#include <openssl/sha.h>
#include <iomanip>
#include <sstream>
#include <sys/socket.h>
#include <sys/un.h>
#include <cstring>

/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems File Integrity Verification Utility
 */

// Embedded Golden Hashes
const std::string NKVD_HASH = "ae359b3d3b7e88939677ebb289580059b9b9ff293a56fb01c4c7e6faf847b2f0";
const std::string SILVA_HASH = "7b5dfb1886fb2961c7d1d1c9ca77a88a4c3df45764c4bc5ed06368623550a6e5";

bool is_ssh() {
    return (std::getenv("SSH_CLIENT") || std::getenv("SSH_TTY") || std::getenv("SSH_CONNECTION"));
}

std::string calculate_sha256(const std::string& path) {
    std::ifstream file(path, std::ios::binary);
    if (!file) return "FILE_NOT_FOUND";

    SHA256_CTX sha256;
    SHA256_Init(&sha256);
    char buffer[16384];
    while (file.read(buffer, sizeof(buffer))) {
        SHA256_Update(&sha256, buffer, file.gcount());
    }
    if (file.gcount() > 0) {
        SHA256_Update(&sha256, buffer, file.gcount());
    }

    unsigned char hash[SHA256_DIGEST_LENGTH];
    SHA256_Final(hash, &sha256);

    std::stringstream ss;
    for(int i = 0; i < SHA256_DIGEST_LENGTH; i++) {
        ss << std::hex << std::setw(2) << std::setfill('0') << (int)hash[i];
    }
    return ss.str();
}

int main(int argc, char* argv[]) {
    if (is_ssh()) {
        std::cerr << "Execution Forbidden" << std::endl;
        return 1;
    }

    const char* socket_path = nullptr;
    for (int i = 1; i < argc; ++i) {
        std::string arg = argv[i];
        if (arg == "--socket" && i + 1 < argc) {
            socket_path = argv[i+1];
            break;
        }
    }

    // Redirect to socket if provided
    if (socket_path != nullptr) {
        int sock = socket(AF_UNIX, SOCK_STREAM, 0);
        struct sockaddr_un addr;
        std::memset(&addr, 0, sizeof(addr));
        addr.sun_family = AF_UNIX;
        std::strncpy(addr.sun_path, socket_path, sizeof(addr.sun_path)-1);
        if (connect(sock, (struct sockaddr*)&addr, sizeof(addr)) == 0) {
            dup2(sock, STDOUT_FILENO);
            dup2(sock, STDERR_FILENO);
        }
        close(sock);
    }

    bool is_tty = isatty(STDOUT_FILENO);
    bool integrity_breached = false;

    if (is_tty) {
        std::cout << "\n[SYSTEM] Starting File Integrity Verification..." << std::endl;
        std::cout << "----------------------------------------------------" << std::endl;
    }
    
    // Audit nkvd_passport
    if (is_tty) std::cout << "Checking: /usr/bin/nkvd_passport ... ";
    if (calculate_sha256("/usr/bin/nkvd_passport") == NKVD_HASH) {
        if (is_tty) std::cout << "\033[1;32mOK\033[0m" << std::endl;
        else std::cout << "/usr/bin/nkvd_passport:OK" << std::endl;
    } else {
        if (is_tty) std::cout << "\033[1;31mFAILED\033[0m" << std::endl;
        else std::cout << "/usr/bin/nkvd_passport:TAMPERED" << std::endl;
        integrity_breached = true;
    }

    // Audit silvasystems
    if (is_tty) std::cout << "Checking: /usr/bin/silvasystems   ... ";
    if (calculate_sha256("/usr/bin/silvasystems") == SILVA_HASH) {
        if (is_tty) std::cout << "\033[1;32mOK\033[0m" << std::endl;
        else std::cout << "/usr/bin/silvasystems:OK" << std::endl;
    } else {
        if (is_tty) std::cout << "\033[1;31mFAILED\033[0m" << std::endl;
        else std::cout << "/usr/bin/silvasystems:TAMPERED" << std::endl;
        integrity_breached = true;
    }

    if (is_tty) {
        std::cout << "----------------------------------------------------" << std::endl;
        if (integrity_breached) {
            std::cout << "\033[1;31mCRITICAL: System integrity mismatch detected.\033[0m" << std::endl;
            std::cout << "One or more core binaries have been modified without authorization.\n" << std::endl;
        } else {
            std::cout << "\033[1;32mSUCCESS: System integrity verified. No modifications detected.\033[0m\n" << std::endl;
        }
    }

    return integrity_breached ? 2 : 0;
}
