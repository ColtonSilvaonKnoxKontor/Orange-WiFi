<?php // [!] By Colton Silva (chinawaterstealers).
/**
 * SilvaSystems Orange WiFi Key (OWK) Generator
 */

if ($argc < 3) {
    die("Usage: php gen_owk.php <developer_name> <public_key.der>
");
}

$developer = $argv[1];
$publicKeyPath = $argv[2];

if (!file_exists($publicKeyPath)) {
    die("[!] ERROR: Public key file not found: $publicKeyPath
");
}

echo "[OWK-GEN] Initializing cryptographic metadata gathering...
";

// 1. Fetch Public IP via authoritative service
$ip = trim(@file_get_contents('https://icanhazip.com'));
if (!$ip) $ip = "0.0.0.0";

// 2. Fetch Approximate Location via GeoIP
$geo = @json_decode(file_get_contents("http://ip-api.com/json/{$ip}"), true);
$location = ($geo && $geo['status'] === 'success') 
    ? "{$geo['city']}, {$geo['country']} ({$geo['isp']})"
    : "Unknown Location";

// 3. Construct Authoritative Metadata Package
$metadata = [
    'developer'  => $developer,
    'public_key' => base64_encode(file_get_contents($publicKeyPath)),
    'public_ip'  => $ip,
    'location'   => $location,
    'date'       => date('Y-m-d H:i:s'),
    'timestamp'  => time()
];

$safeName = preg_replace("/[^a-zA-Z0-9]/", "_", $developer);
$outputFile = $safeName . ".owk";

file_put_contents($outputFile, json_encode($metadata, JSON_PRETTY_PRINT));

echo "[SUCCESS] Authoritative OWK package generated: $outputFile
";
echo "--------------------------------------------------
";
echo " Developer:  $developer
";
echo " Public IP:  $ip
";
echo " Location:   $location
";
echo " Timestamp:  " . $metadata['date'] . "
";
echo "--------------------------------------------------
";
?>