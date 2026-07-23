<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Speedtest Authoritative API
 * Fixed: Permission isolation & Multi-unit support
 */
require_once '/home/pi/orange-wifi/admin/critical/gatekeeper.php';

$speedtest_bin = '/home/pi/orange-wifi/admin/bin/speedtest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: text/plain');
    header('Cache-Control: no-cache');
    
    if (function_exists('apache_setenv')) {
        @apache_setenv('no-gzip', 1);
    }
    @ini_set('zlib.output_compression', 0);
    @ini_set('implicit_flush', 1);
    ob_implicit_flush(1);

    if (!file_exists($speedtest_bin)) {
        die("[ERROR] Speedtest binary not found.");
    }

    // Capture Unit Preference (Mbps default or MB/s)
    $input = json_decode(file_get_contents('php://input'), true);
    $unit = ($input['unit'] === 'MB/s') ? 'MB/s' : 'Mbps';

    $descriptorspec = [
        0 => ["pipe", "r"], // stdin
        1 => ["pipe", "w"], // stdout
        2 => ["pipe", "w"]  // stderr
    ];

    // FIX: Set HOME to /tmp to prevent "Permission denied" on /var/www/.config
    $env = array_merge($_ENV, ['HOME' => '/tmp']);

    // RCE PROTECTION: Unit is strictly validated against whitelist.
    $command = sprintf("%s --accept-license --accept-gdpr -u %s", 
        $speedtest_bin, 
        escapeshellarg($unit)
    );

    $process = proc_open($command, $descriptorspec, $pipes, null, $env);

    if (is_resource($process)) {
        while ($s = fgets($pipes[1])) {
            echo $s;
            @flush();
            @ob_flush();
        }
        while ($s = fgets($pipes[2])) {
            // Filter out the "Failed to save settings" noise if it persists
            if (strpos($s, "Failed to save settings") === false) {
                echo "[ERR] " . $s;
            }
            @flush();
            @ob_flush();
        }
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
    } else {
        echo "[ERROR] Process execution failed.";
    }
    exit;
}
?>