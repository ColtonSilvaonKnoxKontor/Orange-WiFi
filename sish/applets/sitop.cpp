#include <iostream>
#include <string>
#include <vector>
#include <unistd.h>
#include <fstream>
#include <iomanip>
#include <csignal>
#include <algorithm>

/**
 * [!] By Colton Silva (chinawaterstealers).
 * SiSH Applet: System Resource Monitor (sitop)
 * Full Process View Implementation
 */

bool keep_running = true;
void handle_sigint(int sig) { keep_running = false; }

struct SysStats {
    long idle, total;
};

SysStats get_cpu_stats() {
    std::ifstream file("/proc/stat");
    std::string cpu;
    long user, nice, system, idle, iowait, irq, softirq, steal;
    file >> cpu >> user >> nice >> system >> idle >> iowait >> irq >> softirq >> steal;
    return {idle, user + nice + system + idle + iowait + irq + softirq + steal};
}

int main(int argc, char* argv[]) {
    if (argc < 2 || std::string(argv[1]) != "SiSH_restr_interf") {
        std::cerr << "[SECURITY] Direct access denied." << std::endl;
        return 1;
    }

    signal(SIGINT, handle_sigint);
    int num_cores = sysconf(_SC_NPROCESSORS_ONLN);

    SysStats prev = get_cpu_stats();
    std::cout << "\033[2J\033[H";

    while (keep_running) {
        usleep(1000000);
        SysStats curr = get_cpu_stats();
        
        long idle_delta = curr.idle - prev.idle;
        long total_delta = curr.total - prev.total;
        double cpu_usage = (total_delta > 0) ? 100.0 * (1.0 - (double)idle_delta / total_delta) : 0.0;
        prev = curr;

        // Memory Stats
        std::ifstream meminfo("/proc/meminfo");
        long total_mem = 0, free_mem = 0, buffers = 0, cached = 0;
        std::string line;
        while (std::getline(meminfo, line)) {
            if (line.find("MemTotal:") == 0) sscanf(line.c_str(), "MemTotal: %ld kB", &total_mem);
            if (line.find("MemFree:") == 0) sscanf(line.c_str(), "MemFree: %ld kB", &free_mem);
            if (line.find("Buffers:") == 0) sscanf(line.c_str(), "Buffers: %ld kB", &buffers);
            if (line.find("Cached:") == 0 && line.find("SwapCached:") != 0) sscanf(line.c_str(), "Cached: %ld kB", &cached);
        }
        long used_mem = (total_mem - free_mem - buffers - cached) / 1024;

        std::cout << "\033[H";
        std::cout << "SilvaSystems Resource Monitor [H3-SOC]          " << std::endl;
        std::cout << "------------------------------------------------" << std::endl;
        std::cout << "CPU Utilization: " << std::fixed << std::setprecision(1) << cpu_usage << "%" << "    " << std::endl;
        std::cout << "Memory Usage:    " << used_mem << " MB / " << total_mem/1024 << " MB" << "    " << std::endl;
        std::cout << "------------------------------------------------" << std::endl;
        std::cout << "Active System Processes:                        " << std::endl;
        std::cout << "PID   USER     %CPU  %MEM  COMMAND              " << std::endl;
        std::cout << "------------------------------------------------" << std::endl;
        fflush(stdout);
        
        // Professional Formatted Process List (PID, USER, %CPU, %MEM, COMMAND)
        // We skip the first 7 lines of top output and parse specifically for our columns
        std::string top_cmd = "top -b -n 1 | tail -n +8 | head -n 30 | awk '{printf \"%-6s %-10s %-5s %-5s %-16s\\n\", $1, $2, $9, $10, $12}'";
        system(top_cmd.c_str());
        
        std::cout << "\n[INFO] Press Ctrl+C to return to SiSH." << std::endl;
    }

    std::cout << "\nClosing monitor..." << std::endl;
    return 0;
}