<?php
class System {
  public static function uptime() {
    return exec("uptime -p");
  }

  public static function cpu_temp() {
    return floatval(file_get_contents('/sys/class/thermal/thermal_zone0/temp'))/1000;
  }

  public static function cpu_frequency() {
    return floatval(file_get_contents('/sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq'))/1000;
  }

  public static function mem_usage() {
    return exec("free -m | awk '/Mem:/ { total=$2 ; used=$3 } END { print used/total*100}'");
  }

  public static function interfaces() {
    exec("ls /sys/class/net | grep -v lo", $interfaces);

    return json_encode($interfaces);
  }

  public static function get_manufacturer($mac) {
    if (empty($mac)) return "Unknown";
    $oui = str_replace(':', '', substr($mac, 0, 8));
    $db = "/usr/share/ieee-data/oui.txt";
    if (!file_exists($db)) return "Unknown";
    
    // Fast grep for the organization name
    $cmd = "grep -i \"^$oui\" $db | head -n 1 | awk '{print substr($0, index($0,$3))}'";
    $vendor = trim((string)shell_exec($cmd));
    return !empty($vendor) ? $vendor : "Unknown Hardware";
  }
}