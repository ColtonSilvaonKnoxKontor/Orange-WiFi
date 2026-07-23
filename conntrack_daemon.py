#!/usr/bin/env python3
import socket
import os
import subprocess
import signal
import sys

# [!] By Colton Silva (chinawaterstealers).
# SilvaSystems Secure Conntrack Daemon
# Privileged service to sever connections without elevating web server permissions

SOCKET_PATH = "/run/silvasystems_conntrack.sock"

def handle_request(ip):
    ip = ip.strip()
    if not ip:
        return
    
    # Security: Basic IP validation
    try:
        socket.inet_aton(ip)
    except socket.error:
        print(f"Invalid IP received: {ip}")
        return

    print(f"Severing connections for: {ip}")
    # Delete entries where IP is source or destination
    subprocess.run(["/usr/sbin/conntrack", "-D", "-s", ip], stderr=subprocess.DEVNULL)
    subprocess.run(["/usr/sbin/conntrack", "-D", "-d", ip], stderr=subprocess.DEVNULL)

def cleanup(signum, frame):
    if os.path.exists(SOCKET_PATH):
        os.remove(SOCKET_PATH)
    sys.exit(0)

def main():
    if os.path.exists(SOCKET_PATH):
        os.remove(SOCKET_PATH)

    signal.signal(signal.SIGTERM, cleanup)
    signal.signal(signal.SIGINT, cleanup)

    with socket.socket(socket.AF_UNIX, socket.SOCK_STREAM) as s:
        s.bind(SOCKET_PATH)
        # Allow www-data to write to the socket
        os.chmod(SOCKET_PATH, 0o666)
        s.listen(5)
        print(f"Daemon listening on {SOCKET_PATH}...")

        while True:
            conn, addr = s.accept()
            with conn:
                data = conn.recv(1024)
                if not data:
                    break
                ip = data.decode('utf-8')
                handle_request(ip)

if __name__ == "__main__":
    main()
