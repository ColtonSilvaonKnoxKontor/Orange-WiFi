#include <iostream>
#include <fstream>
#include <string>
#include <vector>
#include <unistd.h>
#include <openssl/sha.h>
#include <cstring>
#include <iomanip>
#include <sstream>
#include <sys/socket.h>
#include <sys/un.h>
#include <sys/stat.h>
#include <algorithm>

/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Identity Streamer - Memory-Mapped Hardware Salts
 */

const std::string SOCK_PATH = "/run/silva_ident.sock";
const std::string RAW_STATE = "/etc/security/gost.d/integrity.state";

std::string sha256_hex(const std::string& data) {
    unsigned char hash[SHA256_DIGEST_LENGTH];
    SHA256_CTX sha256;
    SHA256_Init(&sha256);
    SHA256_Update(&sha256, data.c_str(), data.size());
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
    if (!file) return "UNKNOWN";
    std::string content;
    if (file >> content) return trim(content);
    return "UNKNOWN";
}

std::string get_bin_file(const std::string& path) {
    std::ifstream file(path, std::ios::binary);
    if (!file) return "";
    return std::string((std::istreambuf_iterator<char>(file)), std::istreambuf_iterator<char>());
}

int main() {
    // 1. Calculate Machine Identities Once
    const char* S = "SILVASYSTEMS_ORANGESTAR_RECOVERY_KEY_2026";
    const char* P = "X9f#m2@Lp!zQ8v$R";
    std::string seed = get_bin_file(RAW_STATE);
    std::string os_id = sha256_hex(get_cpu_serial() + get_sd_cid() + S + seed);

    // 2. Setup Unix Socket
    unlink(SOCK_PATH.c_str());
    int server_fd = socket(AF_UNIX, SOCK_STREAM, 0);
    if (server_fd == -1) return 1;

    struct sockaddr_un addr;
    memset(&addr, 0, sizeof(addr));
    addr.sun_family = AF_UNIX;
    strncpy(addr.sun_path, SOCK_PATH.c_str(), sizeof(addr.sun_path) - 1);

    if (bind(server_fd, (struct sockaddr*)&addr, sizeof(addr)) == -1) return 1;
    listen(server_fd, 10);
    chmod(SOCK_PATH.c_str(), 0666); // Open for web context

    // 3. Service Loop
    while (true) {
        int client_fd = accept(server_fd, NULL, NULL);
        if (client_fd == -1) continue;
        std::string response = "{\"id\":\"" + os_id + "\",\"pepper\":\"" + P + "\"}";
        send(client_fd, response.c_str(), response.size(), 0);
        close(client_fd);
    }
    return 0;
}