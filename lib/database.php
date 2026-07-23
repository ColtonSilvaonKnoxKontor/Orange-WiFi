<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Core Database Manager
 */

class Database extends SQLite3 {
  private $sid;
  private $devid;
  private $ip_addr;
  private $mac_addr;
  private $hostname;
  private $offset = 0;
  private $limit  = 25;
  private $mb_limit = 0;
  private $mb_used  = 0;
  private $time_limit = 0;
  private $piso_count = 0;

  public function __construct() {
    $dbf = '/home/pi/orange-wifi/conf/orange-wifi.db';
    $this->open($dbf);
    $this->exec("PRAGMA busy_timeout=10000; PRAGMA journal_mode=WAL;");
    $this->create_table();
  }

  public function __destruct() { $this->close(); }

  // --- SETTERS ---
  public function set_mac($s) { if(filter_var($s, FILTER_VALIDATE_MAC)) $this->mac_addr = strtoupper($s); }
  public function set_ip($s) { if(filter_var($s, FILTER_VALIDATE_IP)) $this->ip_addr = $s; }
  public function set_host($s) { $this->hostname = $s; }
  public function set_limit($n) { if(filter_var($n, FILTER_VALIDATE_INT) !== false) $this->limit = $n; }
  public function set_offset($n) { if(filter_var($n, FILTER_VALIDATE_INT) !== false) $this->offset = $n; }
  public function set_mb_limit($f) { if(is_numeric($f)) $this->mb_limit = $f; }
  public function set_mb_used($f) { if(is_numeric($f)) $this->mb_used = $f; }
  public function set_time_limit($n) { if(is_numeric($n)) $this->time_limit = $n; }
  public function set_amount($f) { if(is_numeric($f)) $this->piso_count = $f; }
  public function set_did($n) { $this->devid = intval($n); }
  public function set_sid($n) { $this->sid = intval($n); }

  public function get_did() { return $this->devid; }
  public function get_sid() { return $this->sid; }
  public function get_device_mac() { $r = $this->query("SELECT mac_addr FROM devices WHERE id={$this->devid}")->fetchArray(SQLITE3_NUM); return $r[0] ?? '00:00:00:00:00:00'; }
  public function get_device_ip() { $r = $this->query("SELECT ip_addr FROM devices WHERE id={$this->devid}")->fetchArray(SQLITE3_NUM); return $r[0] ?? '0.0.0.0'; }

  public function create_table() {
    $this->exec("CREATE TABLE IF NOT EXISTS devices (id INTEGER PRIMARY KEY AUTOINCREMENT, mac_addr TEXT NOT NULL UNIQUE, ip_addr DEFAULT '127.0.0.1', hostname DEFAULT '-NA-', topup_count DEFAULT 0, created_at INTEGER, updated_at INTEGER, topup_at INTEGER);");
    $this->exec("CREATE TABLE IF NOT EXISTS session (id INTEGER PRIMARY KEY AUTOINCREMENT, device_id INTEGER, piso_count DEFAULT 0, mb_limit DEFAULT 0, mb_used DEFAULT 0, time_limit_min DEFAULT 0, created_at INTEGER, updated_at INTEGER);");
    $this->exec("CREATE TABLE IF NOT EXISTS sharetx (id INTEGER PRIMARY KEY AUTOINCREMENT, device_id INTEGER, token TEXT, created_at INTEGER);");
    $this->exec("CREATE TABLE IF NOT EXISTS vouchers (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT UNIQUE, prefix TEXT, price INTEGER, duration_min INTEGER, data_limit_mb INTEGER, down_limit_kbps INTEGER, up_limit_kbps INTEGER, status TEXT DEFAULT 'UNUSED', used_by_mac TEXT, used_at INTEGER, created_at INTEGER);");
    // NEW: RATES TABLE
    $this->exec("CREATE TABLE IF NOT EXISTS rates (id INTEGER PRIMARY KEY AUTOINCREMENT, amount INTEGER UNIQUE, mb_size INTEGER, time_duration TEXT, created_at INTEGER);");
  }

  private function sql_ts($col) {
    return "(CASE WHEN typeof($col) = 'integer' THEN $col ELSE strftime('%s', $col) END)";
  }

  private function parse_ts($val) {
    if (is_numeric($val)) return (int)$val;
    $ts = strtotime($val);
    return $ts ? $ts : 0;
  }

  // --- RATES MANAGEMENT ---
  public function get_rates() {
    $res = $this->query("SELECT amount, mb_size, time_duration FROM rates ORDER BY amount ASC");
    $rates = [];
    while($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $rates[$row['amount']] = [
            'mb' => $row['mb_size'],
            'time' => $row['time_duration'] ?? '0m'
        ];
    }
    return $rates;
  }

  public function set_rates($new_rates) {
    $this->exec("BEGIN TRANSACTION;");
    $this->exec("DELETE FROM rates;");
    $now = time();
    $stmt = $this->prepare("INSERT INTO rates (amount, mb_size, time_duration, created_at) VALUES (:amt, :mb, :time, $now)");
    foreach($new_rates as $amt => $data) {
        $stmt->bindValue(':amt', $amt, SQLITE3_INTEGER);
        $stmt->bindValue(':mb', $data['mb'] ?? 0, SQLITE3_INTEGER);
        $stmt->bindValue(':time', $data['time'] ?? '0m', SQLITE3_TEXT);
        $stmt->execute();
    }
    $this->exec("COMMIT;");
    return true;
  }

  public function add_device() {
    $now = time();
    $this->exec("INSERT OR IGNORE INTO devices(mac_addr,ip_addr,hostname,created_at,updated_at) VALUES('{$this->mac_addr}','{$this->ip_addr}','{$this->hostname}', $now, $now)");
    $this->devid = $this->lastInsertRowID();
  }

  public function update_device() {
    $now = time();
    $this->exec("UPDATE devices SET hostname='{$this->hostname}',ip_addr='{$this->ip_addr}',topup_count=0,updated_at=$now WHERE id='{$this->devid}'");
  }

  public function add_session() {
    $now = time();
    $this->exec("INSERT INTO session(device_id,piso_count,mb_limit,time_limit_min,created_at,updated_at) VALUES({$this->devid},{$this->piso_count},{$this->mb_limit},{$this->time_limit}, $now, $now)");
    $this->sid = $this->lastInsertRowID();
  }

  public function get_data_usage() {
    $now = time();
    $cmd = $this->query("SELECT SUM(mb_limit) AS mb_limit, SUM(mb_used) AS mb_used, SUM(time_limit_min) AS time_limit FROM session WHERE device_id={$this->devid}");
    $row = $cmd->fetchArray(SQLITE3_ASSOC);
    $q = $this->query("SELECT created_at, time_limit_min FROM session WHERE device_id={$this->devid} AND time_limit_min > 0");
    $total_used_min = 0;
    while($s = $q->fetchArray(SQLITE3_ASSOC)) {
        $created = $this->parse_ts($s['created_at']);
        $elapsed = floor(($now - $created) / 60);
        $total_used_min += max(0, min($elapsed, (int)$s['time_limit_min']));
    }
    return [(float)($row['mb_limit'] ?? 0), (float)($row['mb_used'] ?? 0), (int)($row['time_limit'] ?? 0), (int)$total_used_min];
  }

  public function update_mb_used() {
    $now = time();
    $this->exec("UPDATE session SET mb_used=mb_used+{$this->mb_used},updated_at=$now WHERE device_id={$this->devid} AND mb_limit > mb_used LIMIT 1");
  }

  public function get_device_id() {
    $res = $this->query("SELECT id FROM devices WHERE mac_addr='{$this->mac_addr}'");
    $row = $res->fetchArray(SQLITE3_ASSOC);
    return $this->devid = $row['id'] ?? false;
  }

  public function get_device_id_by_ip() {
    $res = $this->query("SELECT id FROM devices WHERE ip_addr='{$this->ip_addr}' ORDER BY id DESC LIMIT 1");
    $row = $res->fetchArray(SQLITE3_NUM);
    return $this->devid = $row[0] ?? false;
  }

  public function get_active_devices() {
    $now = time();
    $threshold = $now - 300; 
    $cmd = $this->query("SELECT d.mac_addr AS mac, d.ip_addr AS ip, IFNULL(d.hostname,'-NA-') AS host, s.updated_at, s.created_at, s.mb_limit, s.mb_used, s.time_limit_min FROM session s JOIN devices d ON d.id=s.device_id ORDER BY s.id DESC");
    $res = []; $seen = [];
    while($row = $cmd->fetchArray(SQLITE3_ASSOC)) {
        if (in_array($row['mac'], $seen)) continue;
        $ts_upd = $this->parse_ts($row['updated_at']);
        $ts_cre = $this->parse_ts($row['created_at']);
        if ($ts_upd < $threshold) continue;
        $data_ok = ($row['mb_limit'] > 0 && floatval($row['mb_used']) < floatval($row['mb_limit']));
        $elapsed = floor(($now - $ts_cre) / 60);
        $time_ok = ($row['time_limit_min'] > 0 && $elapsed < intval($row['time_limit_min']));
        if ($data_ok || $time_ok) {
            $seen[] = $row['mac'];
            $res[] = ['mac' => $row['mac'], 'ip' => $row['ip'], 'host' => $row['host'], 'updated_at' => $ts_upd * 1000, 'type' => ($row['time_limit_min'] > 0) ? 'VOUCHER' : 'COINS'];
        }
    }
    return $res;
  }

  public function get_devices() {
    $cmd = $this->query("SELECT mac_addr AS mac, IFNULL(ip_addr,'-NA-') AS ip, IFNULL(hostname,'-NA-') AS host, updated_at FROM devices WHERE mac_addr!='' ORDER BY id DESC");
    $res = [];
    while( $row = $cmd->fetchArray(SQLITE3_ASSOC) ) {
        $row['updated_at'] = $this->parse_ts($row['updated_at']) * 1000;
        $res[] = $row;
    }
    return $res;
  }

  public function get_all_txn() {
    $cmd = $this->query("SELECT s.piso_count AS amt, s.mb_limit, s.time_limit_min, s.created_at, d.mac_addr AS mac, d.ip_addr AS ip, d.hostname AS host FROM session s LEFT JOIN devices d ON s.device_id=d.id ORDER BY s.id DESC LIMIT {$this->offset},{$this->limit}");
    $res = [];
    while($row = $cmd->fetchArray(SQLITE3_ASSOC)) {
        $row['ts'] = $this->parse_ts($row['created_at']) * 1000;
        $res[] = $row;
    }
    return $res;
  }

  public function get_device_sessions() {
    $cmd = $this->query("SELECT id, piso_count AS amt, mb_limit, mb_used, time_limit_min, created_at, updated_at FROM session WHERE device_id={$this->devid} ORDER BY id DESC");
    $res = [];
    while($row = $cmd->fetchArray(SQLITE3_ASSOC)) {
        $row['ts'] = $this->parse_ts($row['created_at']) * 1000;
        $row['te'] = $this->parse_ts($row['updated_at']) * 1000;
        $res[] = $row;
    }
    return $res;
  }

  public function get_device_info() {
    $cmd = $this->query("SELECT mac_addr AS mac, IFNULL(ip_addr,'-NA-') AS ip, IFNULL(hostname,'-NA-') AS host FROM devices WHERE id='{$this->devid}'");
    return $cmd->fetchArray(SQLITE3_ASSOC);
  }

  public function get_active_at() {
    $cmd = $this->query("SELECT updated_at FROM session WHERE device_id={$this->devid} ORDER BY id DESC LIMIT 1");
    $res = $cmd->fetchArray(SQLITE3_NUM);
    return $this->parse_ts($res[0] ?? 0) * 1000;
  }

  public function rem_session() { $this->exec("DELETE FROM session WHERE id={$this->sid}"); }
  public function clear_mb() { $this->exec("DELETE FROM session WHERE device_id={$this->devid}"); }
  public function clear_sharetx() { $threshold = time() - 60; $this->exec("DELETE FROM sharetx WHERE created_at < $threshold"); }
}
?>