#include <iostream>
#include <iomanip>
#include <sstream>
#include <string>
#include <vector>
#include <cstring>
#include <cstdint>
#include <fstream>

/**
 * [!] By Colton Silva (chinawaterstealers).
 * SHIT: Silvasystems Hash Integrity Transformer
 * SilvaSystems Proprietary 512-bit Hashing Engine
 * File Support
 */

class SHIT {
private:
    uint64_t state[8];
    const uint64_t MAGIC_PRIMES[8] = {
        0x9e3779b97f4a7c15ULL, 0xbf58476d1ce4e5b9ULL,
        0x94d049bb133111ebULL, 0xff51afd7ed558ccdULL,
        0x1337c0debabefeedULL, 0xdeadbeefcafefaceULL,
        0x8badf00dcafebabeULL, 0xFEEDFACEDEADCAFEULL
    };

    void churn(uint64_t& a, uint64_t& b, uint64_t val, uint64_t salt) {
        a ^= (val ^ salt);
        b += (a << 19) | (a >> 45);
        a *= MAGIC_PRIMES[0];
        b ^= (a >> 13);
        a += b;
    }

    void process_block(const uint8_t* block) {
        for (size_t i = 0; i < 64; i += 8) {
            uint64_t b;
            std::memcpy(&b, block + i, 8);
            for(int r=0; r<4; ++r) {
                churn(state[r], state[7-r], b, MAGIC_PRIMES[r+4]);
                state[r] ^= state[(r+1)%8] + MAGIC_PRIMES[r];
            }
        }
    }

public:
    SHIT() {
        for(int i=0; i<8; ++i) state[i] = MAGIC_PRIMES[i];
    }

    void finalize() {
        for(int i=0; i<8; ++i) {
            state[i] ^= state[(i+3)%8] * MAGIC_PRIMES[7-i];
            state[i] = (state[i] << 31) | (state[i] >> 33);
        }
    }

    std::string get_hex() {
        std::stringstream ss;
        ss << std::hex << std::setfill('0');
        for(int i = 0; i < 8; i++) ss << std::setw(16) << state[i];
        return ss.str();
    }

    void hash_string(const std::string& input) {
        std::string buffer = input;
        buffer += (char)0x80;
        while (buffer.length() % 64 != 0) buffer += (char)0x13;
        for (size_t i = 0; i < buffer.length(); i += 64) {
            process_block((const uint8_t*)buffer.data() + i);
        }
        finalize();
    }

    bool hash_file(const std::string& path) {
        std::ifstream file(path, std::ios::binary);
        if (!file) return false;

        uint8_t buffer[64];
        while (file.read((char*)buffer, 64) || file.gcount() > 0) {
            std::streamsize bytes = file.gcount();
            if (bytes < 64) {
                buffer[bytes] = 0x80;
                for (int i = (int)bytes + 1; i < 64; ++i) buffer[i] = 0x13;
            }
            process_block(buffer);
            if (bytes < 64) break;
        }
        finalize();
        return true;
    }
};

int main(int argc, char* argv[]) {
    if (argc < 2) {
        std::cout << "Usage:\n";
        std::cout << "  shit <string>      Hash a string\n";
        std::cout << "  shit -f <file>     Hash a file\n";
        return 1;
    }

    SHIT engine;
    std::string arg1 = argv[1];

    if (arg1 == "-f" && argc > 2) {
        std::string path = argv[2];
        if (!engine.hash_file(path)) {
            std::cerr << "[ERROR] Cannot open file: " << path << std::endl;
            return 2;
        }
        std::cout << engine.get_hex() << std::endl;
    } else {
        engine.hash_string(arg1);
        std::cout << engine.get_hex() << std::endl;
    }

    return 0;
}
