#include <iostream>
#include <fstream>
#include <string>
#include <vector>
#include <algorithm>
#include <cstdlib>
#include <unistd.h>
#include <pwd.h>
#include <sstream>
#include <csignal>
#include <sys/wait.h>

/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystem's SiSHell (SiSH) - Secure Core Interface
 */

const std::string MAGIC_BYTE = "SiSH_restr_interf";
const std::string APPLETS_DIR = "/usr/libexec/applet/";

bool is_ssh() {
    // 1. Check environment
    if (std::getenv("SSH_CLIENT") || std::getenv("SSH_TTY") || std::getenv("SSH_CONNECTION")) return true;
    
    // 2. Authoritative Process Audit: Check if parent is sshd
    char path[256];
    snprintf(path, sizeof(path), "/proc/%d/comm", getppid());
    std::ifstream f(path);
    if (f.is_open()) {
        std::string comm;
        f >> comm;
        if (comm.find("sshd") != std::string::npos) return true;
    }
    return false;
}

std::string get_username() {
    struct passwd *pw = getpwuid(getuid());
    return (pw) ? pw->pw_name : "unknown";
}

bool is_excluded(const std::string& username) {
    std::ifstream f("/etc/sish.conf");
    if (!f.is_open()) return false;
    std::string line;
    while (std::getline(f, line)) {
        line.erase(std::remove(line.begin(), line.end(), '\n'), line.end());
        line.erase(std::remove(line.begin(), line.end(), '\r'), line.end());
        if (line == username) return true;
    }
    return false;
}

void execute_applet(const std::string& cmd, const std::vector<std::string>& args) {
    std::string path = APPLETS_DIR + cmd;
    if (access(path.c_str(), X_OK) != 0) {
        std::cout << "[ERROR] Command '" << cmd << "' not recognized." << std::endl;
        return;
    }

    pid_t pid = fork();
    if (pid == 0) {
        std::vector<char*> c_args;
        c_args.push_back((char*)path.c_str());
        c_args.push_back((char*)MAGIC_BYTE.c_str());
        for (const auto& arg : args) c_args.push_back((char*)arg.c_str());
        c_args.push_back(NULL);

        execv(path.c_str(), c_args.data());
        exit(1);
    } else {
        wait(NULL);
    }
}

int main(int argc, char* argv[]) {
    std::string username = get_username();

    // 1. Bypass Logic (Physical Terminal and Exclusions)
    if (!is_ssh() || is_excluded(username)) {
        char* shell_args[] = {(char*)"/bin/bash", NULL};
        execvp("/bin/bash", shell_args);
        return 0; 
    }

    // 2. Lock Signals
    signal(SIGINT, SIG_IGN);
    signal(SIGTSTP, SIG_IGN);
    signal(SIGQUIT, SIG_IGN);

    // 3. Welcome Screen
    std::cout << "SSH Session is not allowed!\n" << std::endl;
    std::cout << "SilvaSystem's SiSHell (SiSH) Interface" << std::endl;
    std::cout << "2026 by Colton Silva\n" << std::endl;

    std::string input;
    while (true) {
        std::cout << "(" << username << ")SiSH => ";
        if (!std::getline(std::cin, input)) {
            std::cout << std::endl;
            break;
        }
        if (input.empty()) continue;

        std::stringstream ss(input);
        std::string cmd;
        ss >> cmd;
        
        if (cmd == "exit" || cmd == "quit") break;
        if (cmd == "clear") {
            std::cout << "\033[2J\033[H" << std::flush;
            continue;
        }
        if (cmd == "help") {
            std::cout << "SiSH Commands:\n";
            std::cout << "  dice [-q|--quantity] - Roll system dice\n";
            std::cout << "  masturbation         - Silicon stimulation module\n";
            std::cout << "  bash                 - Access system shell\n";
            std::cout << "  sitop                - Resource monitor\n";
            std::cout << "  clear                - Clear terminal screen\n";
            std::cout << "  help                 - View this menu\n";
            std::cout << "  exit                 - Disconnect session\n" << std::endl;
            continue;
        }

        std::vector<std::string> args;
        std::string arg;
        while (ss >> arg) args.push_back(arg);

        execute_applet(cmd, args);
    }

    return 0;
}