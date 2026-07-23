<?php // [!] By Colton Silva (chinawaterstealers).
/**
 * SilvaSystems Encrypted Site Blocker Logic
 */

$confFile = '/home/pi/orange-wifi/conf/site_blocker.json';
$hostsFile = '/etc/hosts';
$listBaseDir = '/home/pi/orange-wifi/admin/thrdparty/site-list/porn/';

$config = [
    'porn_blocker' => false,
    'selected_list' => 'lite.txt'
];

if (file_exists($confFile)) {
    $config = array_merge($config, json_decode(file_get_contents($confFile), true));
}

// 1. Read existing hosts
$currentHosts = file_get_contents($hostsFile);

// 2. Remove previous blocks
$startMarker = "### SILVASYSTEMS BLOCK START ###";
$endMarker = "### SILVASYSTEMS BLOCK END ###";

$pattern = "/".preg_quote($startMarker).".*?".preg_quote($endMarker)."/s";
$newHosts = preg_replace($pattern, "", $currentHosts);
$newHosts = trim($newHosts) . "\n";

// 3. Add new blocks if enabled
if ($config['porn_blocker']) {
    $listPath = $listBaseDir . $config['selected_list'];
    if (file_exists($listPath)) {
        $domains = file($listPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $blockContent = "\n" . $startMarker . "\n";
        foreach ($domains as $domain) {
            $domain = trim($domain);
            if (empty($domain) || strpos($domain, '#') === 0) continue;
            // CRITICAL FIX: Point to Gateway IP so Nginx can catch the request
            $blockContent .= "10.0.0.1 " . $domain . "\n";
        }
        $blockContent .= $endMarker . "\n";
        $newHosts .= $blockContent;
    }
}

// 4. Save to /etc/hosts
file_put_contents($hostsFile, $newHosts);
echo "SUCCESS";
?>