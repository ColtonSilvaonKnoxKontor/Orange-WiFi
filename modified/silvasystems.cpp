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

/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure Logic Router
 * Features: Anti-Debug, Secret Obfuscation, Hardware-Locked ID
 */

const std::string SIGNATURE = "SECURITYBYSILVASYSTEMS";
const std::string LOGIC_DIR = "/home/pi/orange-wifi/logic/";

// Memory-only containers
unsigned char K[32];
unsigned char I[16];
unsigned char S[64];
unsigned char P[32];

// Byte-by-byte construction to hide from 'strings' and Ghidra
void prepare_secrets() {
    // Key: silvasystemschinawaterstealers01
    K[0]='s';K[1]='i';K[2]='l';K[3]='v';K[4]='a';K[5]='s';K[6]='y';K[7]='s';K[8]='t';K[9]='e';
    K[10]='m';K[11]='s';K[12]='c';K[13]='h';K[14]='i';K[15]='n';K[16]='a';K[17]='w';K[18]='a';K[19]='t';
    K[20]='e';K[21]='r';K[22]='s';K[23]='t';K[24]='e';K[25]='a';K[26]='l';K[27]='e';K[28]='r';K[29]='s';
    K[30]='0';K[31]='1';

    // IV: chinawatersteale
    I[0]='c';I[1]='h';I[2]='i';I[3]='n';I[4]='a';I[5]='w';I[6]='a';I[7]='t';I[8]='e';I[9]='r';
    I[10]='s';I[11]='t';I[12]='e';I[13]='a';I[14]='l';I[15]='e';

    // Salt: SILVASYSTEMS_ORANGESTAR_RECOVERY_KEY_2026
    const char* s_raw = "SILVASYSTEMS_ORANGESTAR_RECOVERY_KEY_2026";
    std::memcpy(S, s_raw, 41); S[41] = '\0';

    // Pepper: X9f#m2@Lp!zQ8v$R
    P[0]='X';P[1]='9';P[2]='f';P[3]='#';P[4]='m';P[5]='2';P[6]='@';P[7]='L';P[8]='p';P[9]='!';
    P[10]='z';P[11]='Q';P[12]='8';P[13]='v';P[14]='$';P[15]='R';P[16]='\0';
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

std::string get_sys_file(const std::string& path) {
    std::ifstream file(path);
    std::string content;
    if (file >> content) return content;
    return "UNKNOWN";
}

std::string get_cpu_serial() {
    std::ifstream file("/proc/cpuinfo");
    std::string line;
    while (std::getline(file, line)) {
        if (line.find("Serial") != std::string::npos) {
            size_t pos = line.find(":");
            if (pos != std::string::npos) {
                std::string s = line.substr(pos + 1);
                s.erase(0, s.find_first_not_of(" \t"));
                return s;
            }
        }
    }
    return "UNKNOWN";
}

void decrypt_and_run(const std::string& encrypted_file, const char* extra_data) {
    std::ifstream ifile(encrypted_file, std::ios::binary);
    if (!ifile) return;

    char file_sig[22]; 
    ifile.read(file_sig, 22);
    if (std::memcmp(file_sig, SIGNATURE.c_str(), 22) != 0) return;

    std::vector<unsigned char> ciphertext((std::istreambuf_iterator<char>(ifile)), std::istreambuf_iterator<char>());
    ifile.close();

    std::vector<unsigned char> decryptedtext(ciphertext.size() + 128); 
    int decryptedtext_len;
    EVP_CIPHER_CTX *ctx = EVP_CIPHER_CTX_new();
    EVP_DecryptInit_ex(ctx, EVP_aes_256_cbc(), NULL, K, I);
    int len;
    EVP_DecryptUpdate(ctx, decryptedtext.data(), &len, ciphertext.data(), ciphertext.size());
    decryptedtext_len = len;
    EVP_DecryptFinal_ex(ctx, decryptedtext.data() + len, &len);
    decryptedtext_len += len;
    EVP_CIPHER_CTX_free(ctx);

    int pipe_fd[2];
    pipe(pipe_fd);
    if (fork() == 0) {
        close(pipe_fd[1]);
        dup2(pipe_fd[0], STDIN_FILENO);
        int devnull = open("/dev/null", O_WRONLY);
        if (devnull != -1) dup2(devnull, STDERR_FILENO);

        std::string cpu = get_cpu_serial();
        std::string sd = get_sys_file("/sys/block/mmcblk0/device/cid");
        std::string os_id = sha256(cpu + sd + (char*)S);
        std::string pepper = std::string((char*)P);
        std::string data = (extra_data != nullptr) ? std::string(extra_data) : "";

        // Arguments: 1=ID, 2=Pepper, 3=Payload
        execlp("php", "php", "--", os_id.c_str(), pepper.c_str(), data.c_str(), NULL);
        _exit(1);
    } else {
        close(pipe_fd[0]);
        write(pipe_fd[1], decryptedtext.data(), decryptedtext_len);
        close(pipe_fd[1]);
        wait(NULL);
    }
}

int main(int argc, char* argv[]) {

    if (argc < 2) return 1;

    prepare_secrets();
    std::string cmd = argv[1];
    std::string full_path = LOGIC_DIR + cmd + ".titemongmaliit";
    decrypt_and_run(full_path, (argc >= 3) ? argv[2] : nullptr);
    
    std::memset(K, 0, 32);
    std::memset(I, 0, 16);
    std::memset(S, 0, 64);
    std::memset(P, 0, 32);
    return 0;
}
