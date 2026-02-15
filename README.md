# Orange WiFi (alpha and beta release)

> Work in Progress

<i>**A Free Piso WiFi Software made for Paranoids**</i>

Orange WiFi is a single, non-centralized vendo software for Piso WiFi, suitable for inexpensive builds.  As for now,  it only supports Orange Pi One unit. This is free to use, no **fucking license** needed as I am loyal to free and open source software.

This software enforces security measures, warnings and mandatory lockdown mechanism for potential attacks at runtime.

<img width="720" height="1600" alt="portal" src="https://github.com/user-attachments/assets/ad1be687-cf47-49f2-bb3f-c6c2135779ff" /> This is a preview of incomplete alpha build, it may change or remove features that are still existing, mostly from foswvs artifacts.

# Features
- Latest Armbian version, minimal build.

- Modern portal UI. Can add background image, and change custom font styles.

- Supports radio-like system for music playback as amusement for customer.

- It has HDMI display working so that you will know if the system is alive, or does "Kernel Panic" instead of seeing kernel logs to serial UART, and to see or to recover the Orange Star ID. It uses OpenBox as ultra lightweight alternative to known desktop environment like LXDE, XFCE, or LXQT.

- Supports basic logs within orange wifi operation, security level log to see sudo session and ssh logging attempts and very advanced logs for low-level system (eg. kernel log) to see what's wrong with the system.

- Uses XXXXXXXX, and SXXXXS for password hashing and it enforced very strict security lockdown on admin dashboard if the password file is removed 

- You can enable SSH restriction so that only whitelisted MACs can get SSH access, and all connected device that are not listed in whitelist cannot get SSH access.

- You can restrict admin access by defining whitelist MAC address, so no one can dare to guess password on login page and only whitelisted device can get access to dashboard. It can redirect unauthorized user to access denied page with random sarcastic quotes if you activate admin restriction.

- File integrity checker to ensure that there is no patches in each source files.

> At this time, it only support inexpensive build (cheap routers), no VLAN or bridge interface support.

# At First Time Use
- Make sure that you copy-pasted the **Orange Star ID** to a safe place, because this ID will be used if your system got fucked up by the attacker. There is a "Burn It!" button that if you pressed it, you will never see the Orange Star ID again. This Orange Star ID is not hardcoded on your internal server because it is done by calculations. But if the attacker somehow managed to obtain Orange Star ID, you can reshuffle it only by doing it inside physical terminal; and accessing this via ssh remote and unauthorized user will be automatically rejected.

- You must have fan + heatsink because this software might fry the processor if it is poorly ventilated

# FAQ
- **Why we can't customized the captive portal page?**
Uploading a custom Captive Portal is one of the methods of RCE and Backdoors. I do not implement this for a while until I sanitized all code that is related to direct shell executions or sql commands.

- **Is this the perfect alternative to commercial brands?**
No, it only includes coin and voucher for Wi-Fi vending needs. No PPPoE, e-Wallet, e-Load, charging, PC rental, or anything you think. So if you are not satisfied in Orange WiFi's features, you may use your favorite commercialized brands.

# Recruiting Help

- Need to add charging, e-wallet and e-load support (if you want it).

- Need to add PPPoE

- Need to test this on bunch of Orange Pi One units.

- Need to add option for vlan connection (I don't have router with VLAN support despite having 6 routers)

- Pentesters to see if there is a vulnerability existed inside this system (eg. RCE which triggers either fail-open, IDOR, backdoors, etc.)

# Security ToDo

Since this is based on Armbian image, the chance on getting attacked by using RCE is high (based on my previous vulnerability investigation with commercial brand, in Armbian-based system), unlike the OpenWRT-based which the system is definitely read only and then only configs, datas and states are on a separate read-write partition. Another problem is that obfuscating php is useless here as anyone can create the deobfuscator easily, or they can download to github, so I leave the php file as is except for critical ones. I will strengthen the software security just to block RCE entrypoint.

# But where is the Source Code:

I do not publish the source code in public, only on another private repository to avoid potential modification and exploitation. But for a general idea 'bout this system, you may refer to: https://github.com/foswvs/foswvs in which this was the starting point on improving the codebase. To access private repository, just tell me.
