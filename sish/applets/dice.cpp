#include <iostream>
#include <string>
#include <vector>
#include <random>

/**
 * [!] By Colton Silva (chinawaterstealers).
 * SiSH Applet: Dice Roller
 */

int main(int argc, char* argv[]) {
    // 1. Magic Byte Verification
    if (argc < 2 || std::string(argv[1]) != "SiSH_restr_interf") {
        std::cerr << "[SECURITY] Direct execution forbidden." << std::endl;
        return 1;
    }

    int quantity = 1;
    for (int i = 2; i < argc; ++i) {
        std::string arg = argv[i];
        if ((arg == "-q" || arg == "--quantity") && i + 1 < argc) {
            try {
                quantity = std::stoi(argv[i+1]);
            } catch (...) {
                quantity = 1;
            }
            break;
        }
    }

    if (quantity < 1) quantity = 1;
    if (quantity > 10) quantity = 10;

    std::random_device rd;
    std::mt19937 gen(rd());
    std::uniform_int_distribution<> dis(1, 6);

    std::cout << "[DICE] Rolling " << quantity << " dice..." << std::endl;
    for (int i = 0; i < quantity; ++i) {
        std::cout << "  Dice " << (i + 1) << ": " << dis(gen) << std::endl;
    }

    return 0;
}