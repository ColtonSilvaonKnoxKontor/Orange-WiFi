<?php // [!] By Colton Silva (chinawaterstealers).
class Iptables {
  public $ip;
  public $mac;

  function __construct($ip_addr) {
    $this->ip = filter_var($ip_addr, FILTER_VALIDATE_IP);
    $this->mac = $this->resolve_mac($this->ip);
  }

  private function resolve_mac($ip) {
    if (!$ip) return false;
    if (file_exists('/proc/net/arp')) {
        $arp = file('/proc/net/arp');
        foreach ($arp as $line) {
            $cols = preg_split('/\s+/', $line);
            if (count($cols) > 3 && $cols[0] === $ip) return strtoupper($cols[3]);
        }
    }
    return false;
  }

  public function add_client() {
    if (!$this->ip || !$this->mac || $this->mac === '00:00:00:00:00:00') return false;
    $this->rem_client();
    
    // Authoritatively INSERT rules at the TOP (-I) to bypass global blocks
    shell_exec("sudo iptables -t nat -I PREROUTING -m mac --mac-source {$this->mac} -j ACCEPT");
    shell_exec("sudo iptables -I FORWARD -i eth1 -s {$this->ip} -m mac --mac-source {$this->mac} -o eth0 -j ACCEPT");
    shell_exec("sudo iptables -I FORWARD -i eth0 -d {$this->ip} -o eth1 -m state --state ESTABLISHED,RELATED -j ACCEPT");
    return true;
  }

  public function rem_client() {
    if (!$this->ip && !$this->mac) return false;

    $identifiers = array_filter([$this->ip, $this->mac]);
    foreach ($identifiers as $id) {
        if (empty($id)) continue;
        foreach (['filter', 'nat', 'mangle', 'raw'] as $table) {
            while (true) {
                $rules = shell_exec("sudo iptables -t $table -S 2>/dev/null");
                if (!$rules) break;
                $found = false;
                foreach (explode("\n", trim($rules)) as $line) {
                    if (strpos($line, $id) !== false) {
                        $cmd = str_replace("-A", "-D", $line);
                        shell_exec("sudo iptables -t $table $cmd 2>/dev/null");
                        $found = true; break;
                    }
                }
                if (!$found) break;
            }
        }
    }
    if ($this->ip) $this->kick_via_daemon($this->ip);
    return true;
  }

  private function kick_via_daemon($ip) {
    $socket_path = "/run/silvasystems_conntrack.sock";
    if (!file_exists($socket_path)) return false;
    $socket = @socket_create(AF_UNIX, SOCK_STREAM, 0);
    if ($socket && @socket_connect($socket, $socket_path)) {
        @socket_write($socket, $ip, strlen($ip));
    }
    if ($socket) @socket_close($socket);
    return true;
  }

  public function connected() {
    if (!$this->mac) return false;
    $res = shell_exec("sudo iptables -t nat -C PREROUTING -m mac --mac-source {$this->mac} -j ACCEPT 2>&1");
    return (empty($res)); 
  }

  public static function block_mac($mac) {
    if (!$mac || !filter_var($mac, FILTER_VALIDATE_MAC)) return false;
    $mac = strtoupper($mac);
    // Physically Drop Internet AND Portal Access
    shell_exec("sudo iptables -I FORWARD -m mac --mac-source $mac -j DROP");
    shell_exec("sudo iptables -I INPUT -m mac --mac-source $mac -j DROP");
    return true;
  }

  public static function unblock_mac($mac) {
    if (!$mac || !filter_var($mac, FILTER_VALIDATE_MAC)) return false;
    $mac = strtoupper($mac);
    // Physically Remove DROP rules from both chains
    shell_exec("sudo iptables -D FORWARD -m mac --mac-source $mac -j DROP 2>/dev/null");
    shell_exec("sudo iptables -D INPUT -m mac --mac-source $mac -j DROP 2>/dev/null");
    return true;
  }
}
?>