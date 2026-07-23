#include <iostream>
#include <fstream>
#include <string>
#include <vector>
#include <ctime>
#include <algorithm>
#include <cstdint>
#include <curl/curl.h>

/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Key Package Generator
 * Binary Obfuscation
 */

const char OWK_MAGIC[] = {'O', 'W', 'K', '!'};
const unsigned char OWK_KEY[] = {
    0x4c, 0x3d, 0xf4, 0x57, 0x64, 0xc4, 0xbc, 0x5e,
    0xd0, 0x63, 0x68, 0x62, 0x35, 0x50, 0xa6, 0xe5,
    0x7b, 0x5d, 0xfb, 0x18, 0x86, 0xfb, 0x29, 0x61, 
    0xc7, 0xd1, 0xd1, 0xc9, 0xca, 0x77, 0xa8, 0x8a
};

size_t WriteCallback(void* contents, size_t size, size_t nmemb, void* userp) {
    ((std::string*)userp)->append((char*)contents, size * nmemb);
    return size * nmemb;
}

std::string fetch_url(const std::string& url) {
    CURL* curl;
    CURLcode res;
    std::string readBuffer;
    curl = curl_easy_init();
    if(curl) {
        curl_easy_setopt(curl, CURLOPT_URL, url.c_str());
        curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, WriteCallback);
        curl_easy_setopt(curl, CURLOPT_WRITEDATA, &readBuffer);
        curl_easy_setopt(curl, CURLOPT_TIMEOUT, 5L);
        res = curl_easy_perform(curl);
        curl_easy_cleanup(curl);
    }
    return readBuffer;
}

std::string base64_encode(const std::vector<unsigned char>& data) {
    static const char* alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    std::string res;
    for (size_t i = 0; i < data.size(); i += 3) {
        uint32_t val = (data[i] << 16) | 
                       ((i + 1 < data.size() ? data[i + 1] : 0) << 8) | 
                       (i + 2 < data.size() ? data[i + 2] : 0);
        res += alphabet[(val >> 18) & 0x3F];
        res += alphabet[(val >> 12) & 0x3F];
        res += (i + 1 < data.size()) ? alphabet[(val >> 6) & 0x3F] : '=';
        res += (i + 2 < data.size()) ? alphabet[val & 0x3F] : '=';
    }
    return res;
}

int main(int argc, char* argv[]) {
    if (argc < 7) {
        std::cout << "Usage: gen_owk <name> <org> <website> <soc_platform> <soc_url> <public_key.der>" << std::endl;
        return 1;
    }

    std::string name = argv[1];
    std::string org = argv[2];
    std::string web = argv[3];
    std::string socName = argv[4];
    std::string socUrl = argv[5];
    std::string keyPath = argv[6];

    std::ifstream kfile(keyPath, std::ios::binary | std::ios::ate);
    if (!kfile) {
        std::cerr << "[!] ERROR: Cannot open key file." << std::endl;
        return 2;
    }

    std::streamsize ksize = kfile.tellg();
    kfile.seekg(0, std::ios::beg);
    std::vector<unsigned char> kbuffer(ksize);
    if (!kfile.read((char*)kbuffer.data(), ksize)) return 3;

    std::cout << "[OWK-GEN] Gathering metadata..." << std::endl;

    std::string ip = fetch_url("https://icanhazip.com");
    ip.erase(std::remove(ip.begin(), ip.end(), '\n'), ip.end());
    if (ip.empty()) ip = "0.0.0.0";

    std::string geo = fetch_url("http://ip-api.com/line/" + ip + "?fields=city,country,isp");
    std::replace(geo.begin(), geo.end(), '\n', ' ');

    time_t now = time(0);
    char dt[20];
    strftime(dt, sizeof(dt), "%Y-%m-%d %H:%M:%S", localtime(&now));

    std::string json = "{\n";
    json += "  \"developer\": \"" + name + "\",\n";
    json += "  \"organization\": \"" + org + "\",\n";
    json += "  \"website\": \"" + web + "\",\n";
    json += "  \"social_name\": \"" + socName + "\",\n";
    json += "  \"social_url\": \"" + socUrl + "\",\n";
    json += "  \"public_key\": \"" + base64_encode(kbuffer) + "\",\n";
    json += "  \"public_ip\": \"" + ip + "\",\n";
    json += "  \"location\": \"" + geo + "\",\n";
    json += "  \"date\": \"" + std::string(dt) + "\",\n";
    json += "  \"timestamp\": " + std::to_string(now) + "\n";
    json += "}";

    std::string filename = name + ".owk";
    std::replace(filename.begin(), filename.end(), ' ', '_');

    std::ofstream out(filename, std::ios::binary);
    out.write(OWK_MAGIC, 4);
    for (size_t i = 0; i < json.length(); ++i) {
        unsigned char c = json[i] ^ OWK_KEY[i % 32];
        out.put(c);
    }

    std::cout << "[SUCCESS] Key Package Created: " << filename << std::endl;
    return 0;
}
