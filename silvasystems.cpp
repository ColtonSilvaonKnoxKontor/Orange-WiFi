#include <iostream>
#include <fstream>
#include <string>
#include <vector>
#include <unistd.h>
#include <sys/wait.h>
#include <openssl/evp.h>
#include <openssl/err.h>
#include <openssl/sha.h>
#include <cstring>
#include <fcntl.h>
#include <sys/ptrace.h>
#include <iomanip>
#include <sstream>
#include <sys/socket.h>
#include <sys/un.h>
#include <limits.h>
#include <stdlib.h>
#include <sys/reboot.h>

/**
 * [!] By Colton Silva (chinawaterstealers).
 */

const std::string SIGNATURE = "SECURITYBYSILVASYSTEMS";
const std::string BASE_DIR = "/home/pi/orange-wifi/";
const std::string LOGIC_DIR = "/home/pi/orange-wifi/logic/";
const std::string RAW_STATE = "/etc/security/gost.d/integrity.state";
const std::string SIG_STATE = "/usr/lib/astra/security/parsec_auth"; 

unsigned char K[32];
unsigned char I[16];
unsigned char S[64];
char P[65]; 

bool is_ssh() {
    // 1. Check environment (Fast path)
    if (std::getenv("SSH_CLIENT") || std::getenv("SSH_TTY") || std::getenv("SSH_CONNECTION")) return true;

    // 2. UNBREAKABLE: Audit the entire process tree for sshd
    // This ignores environment manipulation (like env -u)
    pid_t parent_pid = getppid();
    while (parent_pid > 1) {
        std::string comm_path = "/proc/" + std::to_string(parent_pid) + "/comm";
        std::ifstream comm_file(comm_path);
        if (comm_file) {
            std::string comm;
            std::getline(comm_file, comm);
            // Physically detect the sshd daemon in the lineage
            if (comm.find("sshd") != std::string::npos) return true;
        }
        
        // Resolve grandparent PID
        std::string status_path = "/proc/" + std::to_string(parent_pid) + "/status";
        std::ifstream status_file(status_path);
        if (!status_file) break;
        std::string line;
        pid_t next_ppid = 0;
        while (std::getline(status_file, line)) {
            if (line.substr(0, 6) == "PPid:\t") {
                next_ppid = (pid_t)std::stoll(line.substr(6));
                break;
            }
        }
        if (next_ppid == parent_pid || next_ppid == 0) break;
        parent_pid = next_ppid;
    }
    return false;
}

// --- SECURE PATH VALIDATION ---
bool is_path_authorized(const std::string& path) {
    char resolved_path[PATH_MAX];
    // realpath resolves all symlinks, extra / and ../ traversals
    if (realpath(path.c_str(), resolved_path) == NULL) {
        // If file doesn't exist, we still treat it as unauthorized for safety
        return false;
    }
    
    std::string rpath(resolved_path);
    
    // Define Absolute Whitelist Prefixes
    const char* whitelist[] = {
        "/home/pi/orange-wifi/",
        "/etc/security/gost.d/",
        "/usr/lib/astra/security/",
        "/etc/hosts"
    };
    
    for (const char* prefix : whitelist) {
        if (rpath.compare(0, strlen(prefix), prefix) == 0) return true;
    }
    
    return false;
}

void prepare_secrets() {
    K[0]='s';K[1]='i';K[2]='l';K[3]='v';K[4]='a';K[5]='s';K[6]='y';K[7]='s';K[8]='t';K[9]='e';
    K[10]='m';K[11]='s';K[12]='c';K[13]='h';K[14]='i';K[15]='n';K[16]='a';K[17]='w';K[18]='a';K[19]='t';
    K[20]='e';K[21]='r';K[22]='s';K[23]='t';K[24]='e';K[25]='a';K[26]='l';K[27]='e';K[28]='r';K[29]='s';
    K[30]='0';K[31]='1';

    I[0]='c';I[1]='h';I[2]='i';I[3]='n';I[4]='a';I[5]='w';I[6]='a';I[7]='t';I[8]='e';I[9]='r';
    I[10]='s';I[11]='t';I[12]='e';I[13]='a';I[14]='l';I[15]='e';

    const char* s_raw = "SILVASYSTEMS_ORANGESTAR_RECOVERY_KEY_2026";
    std::memcpy(S, s_raw, 41); S[41] = '\0';

    // Verify system seed paths before loading
    if (is_path_authorized(SIG_STATE)) {
        std::ifstream pfile(SIG_STATE, std::ios::binary);
        if (pfile) {
            pfile.read(P, 64);
            P[pfile.gcount()] = '\0';
        } else {
            std::strcpy(P, "X9f#m2@Lp!zQ8v$R");
        }
    } else {
        std::strcpy(P, "X9f#m2@Lp!zQ8v$R");
    }
}

std::string sha256(const std::string str) {
    unsigned char hash[SHA256_DIGEST_LENGTH];
    SHA256_CTX sha256;
    SHA256_Init(&sha256);
    SHA256_Update(&sha256, str.c_str(), str.size());
    SHA256_Final(hash, &sha256);
    std::stringstream ss;
    for(int i = 0; i < SHA256_DIGEST_LENGTH; i++) {
        ss << std::hex << std::setw(2) << std::setfill('0') << (int)hash[i];
    }
    return ss.str();
}

std::string trim(const std::string& str) {
    size_t first = str.find_first_not_of(" \t\n\r");
    if (std::string::npos == first) return "";
    size_t last = str.find_last_not_of(" \t\n\r");
    return str.substr(first, (last - first + 1));
}

std::string get_sys_file(const std::string& path) {
    if (!is_path_authorized(path)) return "UNAUTHORIZED";
    std::ifstream file(path);
    std::string content;
    if (file >> content) return trim(content);
    return "UNKNOWN";
}

std::string get_bin_file(const std::string& path) {
    if (!is_path_authorized(path)) return "";
    std::ifstream file(path, std::ios::binary);
    if (!file) return "";
    return std::string((std::istreambuf_iterator<char>(file)), std::istreambuf_iterator<char>());
}

std::string get_cpu_serial() {
    std::ifstream file("/proc/cpuinfo");
    std::string line;
    while (std::getline(file, line)) {
        if (line.find("Serial") != std::string::npos) {
            size_t pos = line.find(":");
            if (pos != std::string::npos) return trim(line.substr(pos + 1));
        }
    }
    return "UNKNOWN";
}

std::string get_sd_cid() {
    std::ifstream file("/sys/block/mmcblk0/device/cid");
    std::string content;
    if (file >> content) return trim(content);
    return "UNKNOWN";
}

void decrypt_and_run(const std::string& encrypted_file, const char* extra_data, const char* socket_path) {
    if (!is_path_authorized(encrypted_file)) {
        std::cerr << "Unauthorized Logic Path" << std::endl;
        return;
    }

    std::ifstream ifile(encrypted_file, std::ios::binary);
    if (!ifile) return;

    char file_sig[22]; 
    ifile.read(file_sig, 22);
    if (std::memcmp(file_sig, SIGNATURE.c_str(), 22) != 0) return;

    std::vector<unsigned char> ciphertext((std::istreambuf_iterator<char>(ifile)), std::istreambuf_iterator<char>());
    ifile.close();

    std::vector<unsigned char> decryptedtext(ciphertext.size() + 128); 
    EVP_CIPHER_CTX *ctx = EVP_CIPHER_CTX_new();
    EVP_DecryptInit_ex(ctx, EVP_aes_256_cbc(), NULL, K, I);
    int len;
    EVP_DecryptUpdate(ctx, decryptedtext.data(), &len, ciphertext.data(), ciphertext.size());
    int decryptedtext_len = len;
    EVP_DecryptFinal_ex(ctx, decryptedtext.data() + len, &len);
    decryptedtext_len += len;
    EVP_CIPHER_CTX_free(ctx);

    int pipe_fd[2];
    pipe(pipe_fd);
    if (fork() == 0) {
        close(pipe_fd[1]);
        dup2(pipe_fd[0], STDIN_FILENO);
        
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
        } else {
            int devnull = open("/dev/null", O_WRONLY);
            if (devnull != -1) dup2(devnull, STDERR_FILENO);
        }

        std::string cpu = get_cpu_serial();
        std::string sd = get_sd_cid();
        std::string seed = get_bin_file(RAW_STATE); 
        
        std::string os_id = sha256(cpu + sd + (char*)S + seed);
        std::string data = (extra_data != nullptr) ? std::string(extra_data) : "";

        execlp("php", "php", "--", os_id.c_str(), P, data.c_str(), NULL);
        _exit(1);
    } else {
        close(pipe_fd[0]);
        write(pipe_fd[1], decryptedtext.data(), decryptedtext_len);
        close(pipe_fd[1]);
        wait(NULL);
    }
}

int main(int argc, char* argv[]) {
    if (is_ssh()) {
        std::cerr << "Execution Forbidden" << std::endl;
        return 1;
    }

    // Elevate Real UID to Root to maintain authority in spawned shells/php
    setuid(0);

    const char* socket_path = nullptr;
    std::string cmd = "";
    const char* payload = nullptr;

    for (int i = 1; i < argc; ++i) {
        std::string arg = argv[i];
        if (arg == "--socket" && i + 1 < argc) {
            socket_path = argv[i+1];
            i++; 
        } else if (cmd == "") {
            cmd = arg;
        } else if (payload == nullptr) {
            payload = argv[i];
        }
    }

    if (cmd == "") return 1;

    prepare_secrets();
    
    if (cmd == "id") {
        std::string cpu = get_cpu_serial();
        std::string sd = get_sd_cid();
        std::string seed = get_bin_file(RAW_STATE);
        std::cout << sha256(cpu + sd + (char*)S + seed);
        return 0;
    }
    if (cmd == "cpu") {
        std::cout << get_cpu_serial();
        return 0;
    }
    if (cmd == "reboot") {
        std::cout << "SUCCESS: SYSTEM REBOOT INITIATED";
        sync();
        reboot(RB_AUTOBOOT);
        return 0;
    }
    if (cmd == "shutdown") {
        std::cout << "SUCCESS: SYSTEM SHUTDOWN INITIATED";
        sync();
        reboot(RB_POWER_OFF);
        return 0;
    }
    if (cmd == "pepper") {
        std::cout << P;
        return 0;
    }

    if (cmd == "syn") {
        if (argc < 3) return 1;
        std::string state = argv[2];
        
        // 1. Kernel Level (Passive)
        std::ofstream f("/proc/sys/net/ipv4/tcp_syncookies");
        if (f) {
            f << ((state == "1") ? "1" : "0");
            f.close();
        }

        // 2. Firewall Level (Active)
        if (state == "1") {
            system("iptables -I INPUT -p tcp --syn -m limit --limit 20/s --limit-burst 50 -j ACCEPT > /dev/null 2>&1");
            system("iptables -A INPUT -p tcp --syn -j DROP > /dev/null 2>&1");
            std::cout << "[SUCCESS] SYN Defense ACTIVE";
        } else {
            system("iptables -D INPUT -p tcp --syn -m limit --limit 20/s --limit-burst 50 -j ACCEPT > /dev/null 2>&1");
            system("iptables -D INPUT -p tcp --syn -j DROP > /dev/null 2>&1");
            std::cout << "[SUCCESS] SYN Defense DISABLED";
        }
        return 0;
    }

    if (cmd == "http_flood") {
        if (argc < 3) return 1;
        std::string state = argv[2];
        std::string ka_val = (argc >= 4) ? argv[3] : "5";
        const char* limit_file = "/etc/nginx/conf.d/http_limit.conf";
        
        // 1. Purge old rules (both raw and filter)
        system("iptables -t raw -D PREROUTING -p tcp --dport 80 -m state --state NEW -m recent --update --seconds 1 --hitcount 3 --name HTTP_RAW -j DROP > /dev/null 2>&1");
        system("iptables -D INPUT -p tcp --dport 80 -m state --state NEW -m recent --update --seconds 1 --hitcount 2 --name HTTP_FIXED -j DROP > /dev/null 2>&1");
        system("iptables -D INPUT -p tcp --dport 80 -m state --state NEW -m recent --set --name HTTP_FIXED > /dev/null 2>&1");

        if (state == "1") {
            // 2. Apply Dynamic Layer 4 Throttling (Hitcount matches Keep-Alive val)
            std::string set_cmd = "iptables -I INPUT -p tcp --dport 80 -m state --state NEW -m recent --set --name HTTP_FIXED > /dev/null 2>&1";
            std::string drop_cmd = "iptables -I INPUT -p tcp --dport 80 -m state --state NEW -m recent --update --seconds 1 --hitcount " + ka_val + " --name HTTP_FIXED -j DROP > /dev/null 2>&1";
            system(set_cmd.c_str());
            system(drop_cmd.c_str());
            
            // 3. LAYER 7: Keepalive Throttling
            std::ofstream f(limit_file);
            if (f) {
                f << "keepalive_requests " << ka_val << ";\n";
                f.close();
            }
            std::cout << "[SUCCESS] HTTP Defense ACTIVE (L4 + L7 Threshold: " << ka_val << ")";
        } else {
            unlink(limit_file);
            std::cout << "[SUCCESS] HTTP Defense DISABLED";
        }
        system("systemctl reload nginx > /dev/null 2>&1");
        return 0;
    }

    if (cmd == "icmp") {
        if (argc < 3) return 1;
        std::string state = argv[2];
        if (state == "1") {
            // ICMP Limit: 1/sec with burst 5
            system("iptables -I INPUT -p icmp --icmp-type echo-request -m limit --limit 1/s -j LOG --log-prefix '[ORANGE-WIFI-ICMP-BLOCK] ' > /dev/null 2>&1");
            system("iptables -I INPUT -p icmp --icmp-type echo-request -m limit --limit 1/s --limit-burst 5 -j ACCEPT > /dev/null 2>&1");
            system("iptables -A INPUT -p icmp --icmp-type echo-request -j DROP > /dev/null 2>&1");
            std::cout << "[SUCCESS] ICMP Defense ACTIVE";
        } else {
            system("iptables -D INPUT -p icmp --icmp-type echo-request -m limit --limit 1/s -j LOG --log-prefix '[ORANGE-WIFI-ICMP-BLOCK] ' > /dev/null 2>&1");
            system("iptables -D INPUT -p icmp --icmp-type echo-request -m limit --limit 1/s --limit-burst 5 -j ACCEPT > /dev/null 2>&1");
            system("iptables -D INPUT -p icmp --icmp-type echo-request -j DROP > /dev/null 2>&1");
            std::cout << "[SUCCESS] ICMP Defense DISABLED";
        }
        return 0;
    }

    if (cmd == "arp_set") {
        if (argc < 4) return 1;
        std::string ip = argv[2];
        std::string mac = argv[3];
        std::string command = "arp -s " + ip + " " + mac;
        system(command.c_str());
        std::cout << "[SUCCESS] ARP Binding Active";
        return 0;
    }

    if (cmd == "arp_del") {
        if (argc < 3) return 1;
        std::string ip = argv[2];
        std::string command = "arp -d " + ip;
        system(command.c_str());
        std::cout << "[SUCCESS] ARP Binding Purged";
        return 0;
    }

    // AUTHORITATIVE: Network Traffic Shaping (Positional Args)
    if (cmd == "shaper") {
        if (argc < 6) { std::cerr << "[ERROR] Insufficient arguments for shaper." << std::endl; return 1; }
        std::string iface = argv[2];
        std::string status = argv[3];
        std::string down = argv[4];
        std::string up = argv[5];

        std::string base_cmd = "sudo /home/pi/orange-wifi/admin/bin/wondershaper ";
        if (status == "disabled") {
            system((base_cmd + "-c -a " + iface + " > /dev/null 2>&1").c_str());
            std::cout << "[SUCCESS] Throttling physically removed from " << iface << std::endl;
        } else {
            // First clear then apply to ensure absolute state
            system((base_cmd + "-c -a " + iface + " > /dev/null 2>&1").c_str());
            std::string apply = base_cmd + "-a " + iface + " -d " + down + " -u " + up;
            int res = system(apply.c_str());
            if (res == 0) std::cout << "[SUCCESS] Constraints active on " << iface << ": " << down << "/" << up << " Kbps" << std::endl;
            else std::cout << "[ERROR] Physical constraint failure." << std::endl;
        }
        return 0;
    }

    // Securely construct logic path
    std::string full_path = LOGIC_DIR + cmd + ".titemongmaliit";
    decrypt_and_run(full_path, payload, socket_path);
    
    std::memset(K, 0, 32);
    std::memset(I, 0, 16);
    std::memset(S, 0, 64);
    std::memset(P, 65, 0);
    return 0;
}
