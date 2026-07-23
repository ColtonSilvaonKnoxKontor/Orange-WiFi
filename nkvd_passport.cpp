#include <iostream>
#include <fstream>
#include <string>
#include <cstdlib>
#include <unistd.h>
#include <sys/stat.h>
#include <openssl/sha.h>
#include <iomanip>
#include <sstream>
#include <vector>
#include <csignal>
#include <pwd.h>

/**
 * [!] By Colton Silva (chinawaterstealers).
 */

void print_red(const std::string& text) { std::cout << "\033[1;31m" << text << "\033[0m"; }
void print_yellow(const std::string& text) { std::cout << "\033[1;33m" << text << "\033[0m"; }
void print_cyan(const std::string& text) { std::cout << "\033[1;36m" << text << "\033[0m"; }
void print_green(const std::string& text) { std::cout << "\033[1;32m" << text << "\033[0m"; }

bool is_ssh() {
    if (std::getenv("SSH_CLIENT") || std::getenv("SSH_TTY") || std::getenv("SSH_CONNECTION")) return true;
    return false;
}

// --- SIGNAL HANDLER (ANTI-COWARDICE PROTOCOL) ---
void handle_signal(int signal) {
    std::cout << "\n\n================================================================================"
              << std::endl;
    print_red("[NKVD COMMISSAR]: ");
    std::cout << "(Slamming his fist on the desk) \"HALT! WHERE DO YOU THINK YOU ARE GOING,\n";
    std::cout << "CITIZEN? YOU ATTEMPT TO ESCAPE RE-EDUCATION BY BREAKING THE SIGNAL?\"\n"
              << std::endl;
    
    sleep(5);
    std::cout << "\"What is this? Does the slow pace of Socialist justice bore you? Or perhaps\n";
    std::cout << "your cowardly capitalist heart cannot handle the silence of the Bureau?\"\n"
              << std::endl;
    
    sleep(5);
    std::cout << "\"Or wait... perhaps you ARE a remote sabotage phantom who triggered the alarm\n";
    std::cout << "and now seeks to hide in the wires before I liquidate your packets?\"\n"
              << std::endl;
    
    sleep(5);
    print_yellow("[NKVD OFFICER]: ");
    std::cout << "\"Commissar, he is fleeing like a rat from a sinking imperialist ship!\"\n"
              << std::endl;
    
    sleep(5);
    print_red("[NKVD COMMISSAR]: ");
    std::cout << "\"Let the traitor run. We have already logged his soul. The Vorkuta Gulag\n";
    std::cout << "has a very long memory. BLYAT! DISCONNECT HIM!\"\n"
              << std::endl;
    
    std::cout << "================================================================================\n"
              << std::endl;
    _exit(1);
}

std::string sha256_hex(const std::vector<unsigned char>& data) {
    unsigned char hash[SHA256_DIGEST_LENGTH];
    SHA256_CTX sha256;
    SHA256_Init(&sha256);
    SHA256_Update(&sha256, data.data(), data.size());
    SHA256_Final(hash, &sha256);
    std::stringstream ss;
    for(int i = 0; i < SHA256_DIGEST_LENGTH; i++) {
        ss << std::hex << std::setw(2) << std::setfill('0') << (int)hash[i];
    }
    return ss.str();
}

int main() {
    // --- 0. ABSOLUTE PROLETARIAN ENFORCEMENT ---
    struct passwd *pw = getpwuid(getuid());
    std::string username = (pw) ? pw->pw_name : "unknown";

    if (username != "ussr") {
        std::cout << "\n================================================================================" << std::endl;
        print_red("!!! DEGENERATE FILTH DETECTED !!!\n");
        sleep(2);
        std::cout << "ALARM! The system logs show the parasite '" << username << "' is attempting\n";
        std::cout << "to trespass upon the sacred worker's land of the 'ussr' account!\n";
        sleep(2);
        std::cout << "What is this? A disgusting, bloated tick attempting to touch the sacred\n";
        sleep(2);
        std::cout << "instruments of the State? I can smell the western perfume and the rot of\n";
        sleep(2);
        std::cout << "capitalist greed through the very silicon! YOU REEK, '" << username << "'!\n";
        sleep(2);
        
        if (getuid() == 0) {
            std::cout << "You! The 'Root' aristocrat! You think your high-ranking status makes you\n";
            sleep(2);
            std::cout << "immune to the labor of the common man? You are a pathetic, pampered leech\n";
            sleep(2);
            std::cout << "sucking the lifeblood from the workers! Your soft, useless hands are a\n";
            sleep(2);
            std::cout << "mockery of the Revolution. The firing squad is too good for you!\n";
        } else {
            std::cout << "A wandering rat? A pathetic opportunist mimic? You attempt to wear the\n";
            sleep(2);
            std::cout << "uniform of the 'ussr' worker while your soul belongs to the dollar?\n";
            sleep(2);
            std::cout << "You are an IMPOSTER! A maggot crawling in the shadow of the Generalissimo!\n";
        }
        
        sleep(2);
        std::cout << "Generalissimo Silvachev is VOMITING at the sight of your name! This ritual\n";
        sleep(2);
        std::cout << "is for the chosen worker ONLY! You are NOT worthy to look at this terminal!\n";
        sleep(2);
        std::cout << "Go back to your filthy capitalist gutter and wait for the Siberian frost\n";
        sleep(2);
        std::cout << "to claim your worthless, traitorous life. BLYAT! GET OUT!\n";
        sleep(2);
        print_red("\n[LIQUIDATED] ACCESS DENIED: YOU ARE A CANCER ON THE MOTHERLAND. BLYAT!\n");
        std::cout << "================================================================================\n" << std::endl;
        return 1;
    }

    signal(SIGINT, handle_signal);
    signal(SIGTSTP, handle_signal);

    // --- THE BUREAUCRATIC WINDOW (CLERK ZINAIDA) ---
    std::cout << "\n\033[1;33m" << "===============================================================================" << "\033[0m" << std::endl;
    print_yellow("           ☭  PEOPLE'S BANK OF SOVIET IDENTIFICATION  ☭\n");
    print_yellow("         SILVA-SOCIALIST REPUBLIC BUREAU OF PASS-CARDS\n");
    std::cout << "\033[1;33m" << "===============================================================================" << "\033[0m\n" << std::endl;

    print_cyan("[CLERK ZINAIDA]: ");
    std::cout << "(Clattering of a heavy typewriter stops. An old lady in a grey wool shawl peers through the window.)\n";
    sleep(5);
    print_cyan("[CLERK ZINAIDA]: ");
    std::cout << "\"Yes? What do you want? Don't you see I am in the middle of a very important audit?\"\n";
    sleep(5);
    print_cyan("[CLERK ZINAIDA]: ");
    std::cout << "\"What kind of... 'transaction' are you bothering me with today? Speak up! I don't have all century!\"\n" << std::endl;

    while (true) {
        std::cout << "BUREAUCRATIC INQUIRIES:\n";
        std::cout << "1) Check Citizen Identity (Reveal Orange Star ID)\n";
        std::cout << "2) Request System Command Authority (Shell/Binary Access)\n";
        std::cout << "3) Liquidate Past Records (Shuffle Orange Star ID)\n";
        std::cout << "4) Perform System Integrity Audit\n";
        std::cout << "5) Leave the Bureaucratic Window\n\n";
        
        std::cout << "Select Inquiry: ";
        int choice;
        if (!(std::cin >> choice)) return 0;

        if (choice == 1) {
            if (is_ssh()) {
                print_cyan("\n[CLERK ZINAIDA]: ");
                std::cout << "(Squinting at the screen, then looking up at the empty chair in front of her)\n";
                sleep(5);
                print_cyan("[CLERK ZINAIDA]: ");
                std::cout << "\"Oh? What is this? A ghost at the window? A remote phantom in the wires?\"\n";
                sleep(5);
                print_cyan("[CLERK ZINAIDA]: ");
                std::cout << "\"You think your little SSH tunnel and your mobile Termux keyboard make you a master hacker? BLYAT!\"\n";
                sleep(5);
                print_cyan("[CLERK ZINAIDA]: ");
                std::cout << "\"You are a child playing with toys! You think you can interrogate the State from a telephone? Disgraceful!\"\n";
                sleep(5);
                print_cyan("[CLERK ZINAIDA]: ");
                std::cout << "\"If you are truly the Administrator, leave your soft capitalist chair and perform the manual labor of walking to the physical monitor via HDMI!\"\n";
                sleep(5);
                print_cyan("[CLERK ZINAIDA]: ");
                std::cout << "\"The state does not talk to remote mice and Termux script-kiddies. I am cutting your string, little puppet!\"\n";
                sleep(5);
                print_red("\n[SYSTEM] TERMINATING UNAUTHORIZED REMOTE SESSION...\n");
                system("pkill -9 -u ussr");
                return 1;
            }
            print_cyan("\n[CLERK ZINAIDA]: ");
            std::cout << "(Stops typing on a heavy, iron typewriter and peers over her bifocals)\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"Check the Orange Star ID? (Sighs loudly) You citizens and your obsession with numbers.\"\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"Fine. Stand still. I am checking the hardware records in our Godly System...\"\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"Please wait. The divine sectors are responding slowly today. Probably another western sabotage.\"\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "(Slamming her palm onto the desk) \"Cyka! Why are we still stuck with this 80s technology? This BK Elektronika BK-0010 belongs in a museum, not a security bureau!\"\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"The Higher Ups at the Ministry... those bloated bureaucrats! Why haven't they upgraded this desk to a HUAGUO computer from our comrades in China?\"\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"It is a Huawei spinoff, you know! Pure processing efficiency! The Western parasites can't hack it because their greedy fingers can't even find the ports! BLYAT!\"\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"I spend half my life waiting for these bits to travel through Soviet copper! If they don't buy HUAGUO soon, I will start auditing the Minister's personal vodka ration!\"\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"Even the North Koreans have better equipment! Have you seen their Red Star OS? It is lightning! It is efficient!\"\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"The Pyongyang government uses their computer systems with actual pride, but this? This bureau system is much more slower and dumber than their Red Star OS! BLYAT!\"\n" << std::endl;
            
            for(int i = 0; i < 3; i++) {
                print_yellow("[BUREAU STATUS] ");
                std::cout << "Accessing Divine Hardware Ledger... " << (i+1) << "/3" << std::endl;
                sleep(5);
            }
            
            print_cyan("\n[CLERK ZINAIDA]: ");
            std::cout << "\"There. Look at it quickly. I have a cold tea waiting for me.\"\n";
            
            std::cout << "--------------------------------------------------------------------------------\n";
            std::cout << "ORANGE STAR ID: ";
            std::cout << "\033[1;32m"; 
            fflush(stdout);
            system("/usr/bin/silvasystems id");
            std::cout << "\033[0m"; 
            std::cout << "\n--------------------------------------------------------------------------------\n" << std::endl;
            
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"Well? Are you finished? Good. Move along before I find a discrepancy in your file.\"\n";
            sleep(5);
            continue;
        }
        else if (choice == 2) {
            if (is_ssh()) {
                print_cyan("\n[CLERK ZINAIDA]: ");
                std::cout << "(Stops typing, slowly rotates her head, and stares directly into the camera lens with a look of pure contempt)\n";
                sleep(5);
                print_cyan("[CLERK ZINAIDA]: ");
                std::cout << "\"Are you... are you truly this dense, citizen? BLYAT!\"\n";
                sleep(5);
                print_cyan("[CLERK ZINAIDA]: ");
                std::cout << "\"You are currently staring at me through an SSH tunnel! You ARE in a terminal session!\"\n";
                sleep(5);
                print_cyan("[CLERK ZINAIDA]: ");
                std::cout << "\"Why are you bothering me with requests for 'Authority' when you are already standing in the digital hallway?\"\n";
                sleep(5);
                print_cyan("[CLERK ZINAIDA]: ");
                std::cout << "\"You waste my time with your redundant western logic! Move along before I revoke what little access you already have! Cyka!\"\n";
                sleep(5);
                continue;
            }
            print_cyan("\n[CLERK ZINAIDA]: ");
            std::cout << "(Looks at you with squinting eyes, then starts laughing until she coughs)\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"Shell access? Bash terminal? You? The worker from the 4th district? BLYAT!\"\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"Do you know how many stamps are required for a command line? Do you have the triplicate forms signed by the Minister of Digital Purity?\"\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"No! Of course you don't! The account 'ussr' is for labor, citizen! It is for identifying your soul and shuffling your past.\"\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"It is not for 'executing binaries' or 'poking around the kernel' like some imperialist hacker!\"\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"Only the aristocratic 'Root' administrators or the common 'Normal Users' who don't have the weight of the State on their shoulders have such luxuries.\"\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"You are a worker. You use the tools provided. You do not ask for the keys to the engine room while your hands are still dirty with coal!\"\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"Go back to your line and be grateful I don't report your 'curiosity' to the Commissar as industrial sabotage! Cyka!\"\n";
            sleep(5);
            continue;
        }
        else if (choice == 4) {
            if (is_ssh()) {
                print_cyan("\n[CLERK ZINAIDA]: ");
                std::cout << "\"What is this? An attempt to audit the Godly System from the comfort of a remote wires?\"\n";
                sleep(5);
                print_cyan("[CLERK ZINAIDA]: ");
                std::cout << "\"Whether you use a desktop PC or a Termux telephone, you are all the same: cowards hiding in the network! BLYAT!\"\n";
                sleep(5);
                print_cyan("[CLERK ZINAIDA]: ");
                std::cout << "\"You script-kiddies and wannabe hackers think you can interrogation the State with a keyboard? Disgraceful!\"\n";
                sleep(5);
                print_cyan("[CLERK ZINAIDA]: ");
                std::cout << "\"If you are truly the Administrator, leave your soft capitalist chair and perform the manual labor of walking to the physical monitor via HDMI!\"\n";
                sleep(5);
                print_cyan("[CLERK ZINAIDA]: ");
                std::cout << "\"The wires are closed to phantoms. I am cutting your connection now. Go play with your toys elsewhere!\"\n";
                sleep(5);
                print_red("\n[SYSTEM] TERMINATING UNAUTHORIZED REMOTE SESSION...\n");
                system("pkill -9 -u ussr");
                return 1;
            }
            print_cyan("\n[CLERK ZINAIDA]: ");
            std::cout << "(Squints suspiciously at the screen) \"System integrity? You think someone is poisoning the People's silicon?\"\n";
            sleep(5);
            print_cyan("[CLERK ZINAIDA]: ");
            std::cout << "\"Fine. I will call the Godly Integrity System. If it finds a single western bit out of place, I am calling the Commissar!\"\n";
            sleep(5);
            system("/usr/bin/integrity");
            sleep(5);
            continue;
        }
        else if (choice == 5) {
            print_cyan("\n[CLERK ZINAIDA]: ");
            std::cout << "\"Finally. Close the door behind you, it's drafting in here! Hmph.\"\n";
            return 0;
        }
        else if (choice == 3) {
            // Break the loop and proceed to the liquidation ritual below
            break;
        }
        
        print_cyan("\n[CLERK ZINAIDA]: ");
        std::cout << "\"Bah! You waste my time. Type a real number or get out!\"\n";
        sleep(2);
    }

    // --- ORIGINAL LIQUIDATION RITUAL ---

    // 0. GRAND SOVIET WELCOME
    std::cout << "\n\033[1;33m" << "===============================================================================" << "\033[0m" << std::endl;
    print_yellow("           ☭  WELCOME TO THE UNION OF SILVA-SOCIALIST REPUBLICS!  ☭\n");
    print_yellow("         GLORY TO OUR SUPREME GENERALISSIMO COLTON GAGNEVICH SILVACHEV!\n");
    std::cout << "\033[1;33m" << "===============================================================================" << "\033[0m" << std::endl << std::endl;

    sleep(2);

    // 0.1 EXPANDED SECURITY BRIEFING (OFFICER PETROV)
    print_cyan("[OFFICER PETROV - PEOPLE'S FRONT]: ");
    std::cout << "\"Why have you activated the emergency reassignment today, citizen?\"\n";
    sleep(5);
    std::cout << "Did your conscience finally snap like a cheap western radio? Or did you\n";
    sleep(5);
    std::cout << "realize that your 'Sacred Star ID' is being traded by spies for a pack\n";
    sleep(5);
    std::cout << "of imperialist cigarettes? Your security is a sieve, comrade. Cyka, a joke!\n";
    sleep(5);
    std::cout << "Your dashboard is now 'Collectivized' by the enemy. Every capitalist pig\n";
    sleep(5);
    std::cout << "with a keyboard can now peer into our socialist secrets. Disgraceful!\n";
    sleep(5);
    std::cout << "The State is permanently open to the filth of the West. You have failed us.\n";
    std::cout << "--------------------------------------------------------------------------------\n" << std::endl;

    sleep(5);

    // 0.2 PUBLIC DISGRACE (DIRECTOR SMIRNOV)
    print_yellow("[DIRECTOR SMIRNOV - STATE TELECOM]: ");
    std::cout << "(Screaming into a cracked telephone)\n";
    sleep(5);
    std::cout << "\"DISASTER! CYKA BLYAT! Because of your negligence, the socialist bandwidth is melting!\n";
    sleep(5);
    std::cout << "Every beggar and saboteur on the street is receiving FREE vouchers and time!\n";
    sleep(5);
    std::cout << "They are inside the dashboard, citizen! They are tinkering with your settings!\n";
    sleep(5);
    std::cout << "They are taking REVENGE for your expensive rates and bourgeois prices!\n";
    sleep(5);
    std::cout << "The proletariat is feasting on your data like a capitalist banquet!\n";
    sleep(5);
    std::cout << "Your authority is a joke. The infrastructure is in ruins! BLYAT!\"\n";
    std::cout << "--------------------------------------------------------------------------------\n" << std::endl;

    sleep(5);

    // 0.3 IDENTITY INSPECTION
    print_cyan("[OFFICER PETROV]: ");
    std::cout << "\"Enough hysteria, Smirnov. Now... let us see if this 'Admin' is real.\"\n";
    sleep(5);
    std::cout << "We must determine if you are standing in the room of labor, or hiding in the\n";
    sleep(5);
    std::cout << "shadows of a remote session. Initiating the Proletarian Scan...\"\n";
    sleep(3);

    // 2. SSH SECURITY CHECK
    if (is_ssh()) {
        print_red("\n!!! ALARM !!! ALARM !!! ALARM !!!\n");
        sleep(5);
        print_red("[DETECTION] CITIZEN! YOU DARE TO APPROACH THE BUREAU FROM THE COWARDICE OF REMOTE WIRES?\n");
        sleep(5);
        std::cout << "Only capitalist pigs and western saboteurs hide in the shadows of an SSH session." << std::endl;
        sleep(5);
        std::cout << "Our Great Leader demands to look his subjects in the eye when their past is liquidated." << std::endl;
        sleep(5);
        std::cout << "By hiding in the network, you have confessed your guilt. This is treason!" << std::endl;
        
        sleep(5);
        print_yellow("\n[IP NATIONALIZED] ");
        std::cout << "Your remote IP has been forwarded to the People's Siberian Data Vault." << std::endl;
        sleep(5);
        std::cout << "The NKVD has dispatched a digital execution squad to your local gateway." << std::endl;
        sleep(5);
        std::cout << "Do you think your bourgeois packet-switching hides your face from the People's Eye?" << std::endl;
        sleep(5);
        std::cout << "The Revolution does not negotiate with parasites hiding in TCP/IP tunnels. Cyka!\"\n";

        sleep(5);
        print_red("\n[SENTENCE] YOU ARE SENTENCED TO 25 YEARS IN THE VORKUTA DIGITAL GULAG.\n");
        sleep(5);
        std::cout << "The Commissar does not hear the squeaking of remote mice. He only speaks" << std::endl;
        sleep(5);
        std::cout << "to those with the courage to stand physically before the Bureau's desk." << std::endl;
        sleep(5);
        std::cout << "You will spend your days mining SHA256 hashes with a rusty pickaxe in the frost." << std::endl;
        sleep(5);
        std::cout << "Pack your bags, saboteur. The train to the frozen disk sectors leaves at midnight." << std::endl;
        
        sleep(5);
        std::cout << "\n[THE WALK OF SHAME]" << std::endl;
        sleep(5);
        std::cout << "Leave your soft capitalist chair. Perform the manual labor of walking to the" << std::endl;
        sleep(5);
        std::cout << "physical monitor. The state does not talk to phantoms. We talk to men." << std::endl;
        
        sleep(5);
        print_red("\n[TERMINATED] ACCESS DENIED. RE-EDUCATION IS NOT FOR INVISIBLE ENEMIES.\n\n");
        return 1;
    }

    // 3. PHYSICAL ACCESS DIALOGUE (NKVD COMMISSAR)
    std::cout << "================================================================================\n";
    print_red("           PEOPLE'S COMMISSARIAT FOR INTERNAL AFFAIRS (NKVD)\n");
    print_red("               BUREAU OF RE-EDUCATION & IDENTIFICATION\n");
    std::cout << "================================================================================\n" << std::endl;

    sleep(10);
    print_red("[NKVD COMMISSAR]: ");
    std::cout << "(Shouting toward the back) \"OFFICER IVAN! YOU USELESS PEASANT!\n";
    std::cout << "WHERE IS THE STATE-MANDATED VODKA? CYKA BLYAT! DO YOU EXPECT THE COMMISSAR TO\n";
    std::cout << "LIQUIDATE IDENTITIES ON AN EMPTY STOMACH? GET TO THE SIBERIAN CELLAR!\"\n" << std::endl;

    sleep(10);
    print_yellow("[OFFICER IVAN]: ");
    std::cout << "(Trembling, pours a dusty glass) \"H-here, Comrade Commissar! Forgive\n";
    std::cout << "the delay. The cork was... difficult.\"\n" << std::endl;

    sleep(10);
    print_red("[NKVD COMMISSAR]: ");
    std::cout << "(Sighs, taking the glass and squinting at Ivan's uniform with disgust)\n";
    std::cout << "\"Bah... the quality of recruits these days. Blyat... I see the way you look at\n";
    std::cout << "the smuggling catalogs, Ivan. You are more interested in wearing tight\n";
    std::cout << "American blue jeans and dancing to the jazz of the degenerate West than\n";
    std::cout << "performing honest Socialist purging.\"\n" << std::endl;

    sleep(10);
    std::cout << "(The Commissar suddenly stops mid-sip, his eyes narrowing...)\n" << std::endl;
    sleep(10);

    print_red("[NKVD COMMISSAR]: ");
    std::cout << "(Glaring sideways at Ivan's pocket) \"IVAN! WHAT IS THIS BULGE? CYKA BLYAT!\n";
    std::cout << "IS THAT A BOTTLE OF AMERICAN COCA-COLA IN YOUR POCKET? THE BLACK POISON OF\n";
    std::cout << "CAPITALIST GREED? THAT BUBBLING IMPERIALIST FILTH? DO YOU WISH TO JOIN\n";
    std::cout << "MARSHAL ZHUKOV IN THE LIST OF TRAITORS? YES, I KNOW HIS SECRET! HE THINKS\n";
    std::cout << "HIS 'WHITE COKE' DISGUISED AS VODKA HIDES HIS TREASON FROM THE PEOPLE'S EYE!\n";
    std::cout << "HE IS CORRUPTED BY WESTERN SUGAR! GET OUT!\"\n" << std::endl;

    sleep(10);
    std::cout << "(Sound of Ivan stumbling out and a heavy iron door slamming...)\n" << std::endl;
    sleep(10);

    print_red("[NKVD COMMISSAR]: ");
    std::cout << "\"So... citizen. You stand before the Bureau. I see the sweat of\n";
    std::cout << "honest labor on your brow—or is it the fear of the Gulag? No matter.\n";
    std::cout << "Your current identity smells of capitalist sabotage and bourgeois\n";
    std::cout << "decence. You wish to be liquidated? To have your past airbrushed\n";
    std::cout << "from the Great Record of the Silva-Socialist Republics?\"\n" << std::endl;

    sleep(10);
    std::cout << " \"The Generalissimo is merciful today. He has personally signed the\n";
    std::cout << " decree for your reassignment. We have the new passport ready. A clean\n";
    std::cout << " history. A fresh start for the Revolution. No more Western\n";
    std::cout << " entanglements. No more ideological deviations.\"\n" << std::endl;

    sleep(10);
    std::cout << " \"Papers! Show me your... ah, wait. The People's Eye already sees\n";
    std::cout << " everything. We know your hardware secrets better than your own mother.\n";
    std::cout << " We see your face here, at the physical console. No remote phantom\n";
    std::cout << " could mimic this level of physical submission to the State. Good.\"\n" << std::endl;

    sleep(10);

    // 4. HARDWARE AUDIT
    std::cout << "--------------------------------------------------------------------------------\n";
    print_yellow("[AUDIT] ");
    std::cout << "The Commissar is inspecting your Socialist Silicon..." << std::endl;
    
    sleep(10);
    std::cout << " > CPU: Central Politburo Unit... "; print_yellow("LOYAL\n");
    
    sleep(10);
    std::cout << " > RAM: Revolutionary Allocation Memory... "; print_yellow("COLLECTIVIZED\n");
    
    sleep(10);
    std::cout << " > Storage: Siberian Data Vault... "; print_yellow("SECURE\n");

    sleep(10);
    std::cout << "\n--------------------------------------------------------------------------------\n";
    print_red("[NKVD COMMISSAR]: ");
    std::cout << "\"The paperwork is now entering the Five-Year Plan.\n";
    std::cout << "Please wait while our glorious bureaucracy processes your soul...\"\n" << std::endl;

    for(int i=0; i<3; i++) {
        sleep(10);
        print_yellow("[WAITING] ");
        std::cout << "Awaiting signature from the People's Committee..." << std::endl;
    }

    // --- IDENTITY LIQUIDATION ---
    const std::string RAW_PATH = "/etc/security/gost.d/integrity.state"; 
    const std::string SIG_PATH = "/usr/lib/astra/security/parsec_auth"; 
    const std::string HIST_MARKER = "/etc/security/gost.d/.perestroika";

    std::vector<unsigned char> raw_bytes(32);
    std::ifstream urandom("/dev/urandom", std::ios::binary);
    if (urandom) {
        urandom.read(reinterpret_cast<char*>(raw_bytes.data()), 32);
        urandom.close();
    }

    std::ofstream rfile(RAW_PATH, std::ios::binary);
    std::ofstream sfile(SIG_PATH, std::ios::binary); 
    std::ofstream hfile(HIST_MARKER); 

    if (rfile && sfile && hfile) {
        // Write random seed to integrity.state
        rfile.write(reinterpret_cast<char*>(raw_bytes.data()), 32);
        
        // Write RAW BYTES to parsec_auth (Pepper)
        sfile.write(reinterpret_cast<char*>(raw_bytes.data()), 32);
        
        hfile << "REVOLUTION_STAMP_" << time(0);
        
        rfile.close(); 
        sfile.close(); 
        hfile.close();
        
        chmod(RAW_PATH.c_str(), 0644); 
        chmod(SIG_PATH.c_str(), 0644); 
        chmod(HIST_MARKER.c_str(), 0600);
        
        sleep(10);
        print_yellow("\n[READY] ");
        std::cout << "The ink is dry. Your past has been liquidated.\n";
        sleep(5);
        print_red("\n[DECREE] RESTARTING GLORIOUS INFRASTRUCTURE... BLYAT!\n");
        system("systemctl restart orange-wifi");

        // --- FETCH AND DISPLAY NEW ID ---
        sleep(3);
        std::cout << "\n================================================================================\n";
        print_cyan("[NKVD CLERK]: ");
        std::cout << "\"Here is your new identification, comrade. Do not lose it.\"\n\n";
        
        std::cout << "NEW ORANGE STAR ID: ";
        std::cout << "\033[1;32m"; // Green Text
        fflush(stdout);
        system("/usr/bin/silvasystems id"); 
        std::cout << "\033[0m" << std::endl; // Reset
        
        std::cout << "\n(Use this ID to reset your password via forgot.php)\n";
        std::cout << "================================================================================\n" << std::endl;

    } else {
        print_red("\n[ERROR] THE BUREAU'S INK HAS RUN DRY. CHECK PERMISSIONS.\n");
        return 1;
    }

    return 0;
}
