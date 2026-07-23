#include <iostream>
#include <string>
#include <unistd.h>

/**
 * [!] By Colton Silva (chinawaterstealers).
 * SiSH Applet: Bash Decoy (Access Restricted)
 */

int main(int argc, char* argv[]) {
    // 1. Magic Byte Verification
    if (argc < 2 || std::string(argv[1]) != "SiSH_restr_interf") {
        std::cerr << "[SECURITY] Direct execution forbidden." << std::endl;
        return 1;
    }

    std::cout << "\nSilvaSystems Security Policy: Restricted Shell Access" << std::endl;
    std::cout << "--------------------------------------------------------------------------------" << std::endl;
    std::cout << "Notice: Direct access to the internal Linux system is prohibited over SSH." << std::endl;
    sleep(1);
    std::cout << "This attempt to initiate an interactive shell has been intercepted." << std::endl;
    sleep(1);
    std::cout << "\n[ACTION]: Administrative shell access is only available via a local connection." << std::endl;
    std::cout << "Please utilize a physical terminal (Monitor + Keyboard) to access the system core." << std::endl;
    std::cout << "--------------------------------------------------------------------------------\n" << std::endl;

    return 0;
}