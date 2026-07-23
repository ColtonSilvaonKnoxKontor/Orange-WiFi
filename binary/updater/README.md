# What is this?

This section is responsible for packing third party public key and
packed update file for distribution

# Using the tp_pack_owp.php

This option let's you to pack the modified file to push updates to the system.

# How to define the target location of file using manifest.json

The manifest.json file is a strict mapping between the files on your developer 
machine and their final destination on the Orange Pi's filesystem.

The JSON object uses the Target System Path as the key and the Local File Path as the value.

"""
   1 {
   2   "/usr/bin/silvasystems": "bin/silvasystems",
   3   "/home/pi/orange-wifi/lib/iptables.php": "src/iptables.php",
   4   "/etc/dnsmasq.conf": "configs/dnsmasq.conf"
   5 }
"""
