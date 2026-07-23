<?php // [!] By Colton Silva (chinawaterstealers).
/**
 * SilvaSystems Orange WiFi Package Generator
 * RSA-4096 Secure Signing (Multi-Path Support)
 */

if ($argc < 3) {
    die("Usage: php pack_owp.php <manifest.json> <output_file.owp>\n\n" .
        "Manifest JSON format:\n" .
        "{\n" .
        "  \"/usr/bin/softup\": \"local_bin/softup\",\n" .
        "  \"/home/pi/orange-wifi/lib/iptables.php\": \"modified/iptables.php\"\n" .
        "}\n");
}

$manifestPath = $argv[1];
$outputFile = $argv[2];
$privateKeyPath = "update_private.pem";
$pk_xor = [
    0x7b, 0x5d, 0xfb, 0x18, 0x86, 0xfb, 0x29, 0x61, 
    0xc7, 0xd1, 0xd1, 0xc9, 0xca, 0x77, 0xa8, 0x8a, 
    0x4c, 0x3d, 0xf4, 0x57, 0x64, 0xc4, 0xbc, 0x5e, 
    0xd0, 0x63, 0x68, 0x62, 0x35, 0x50, 0xa6, 0xe5
];

if (!file_exists($manifestPath)) die("[!] ERROR: Manifest not found.\n");
if (!file_exists($privateKeyPath)) die("[!] ERROR: Private key not found.\n");

$manifest = json_decode(file_get_contents($manifestPath), true);
if (!$manifest) die("[!] ERROR: Invalid Manifest JSON.\n");

echo "[PACKER] Staging precision update...\n";

// 1. Create a temporary staging directory
$stagingDir = "/tmp/owp_stage_" . uniqid();
mkdir($stagingDir, 0755, true);

foreach ($manifest as $sysPath => $localPath) {
    if (!file_exists($localPath)) {
        echo "[!] WARNING: Local file not found: $localPath (Skipping)\n";
        continue;
    }
    
    // Create respective directory structure in stage
    $targetStage = $stagingDir . $sysPath;
    mkdir(dirname($targetStage), 0755, true);
    copy($localPath, $targetStage);
    echo " > Staged: $sysPath\n";
}

// 2. Generate Encrypted Payload from staging
echo "[PACKER] Encrypting staged payload...\n";
$tarCmd = "tar -c -C " . escapeshellarg($stagingDir) . " .";
$handle = popen($tarCmd, "r");
$encryptedData = "";
$totalBytes = 0;

while (!feof($handle)) {
    $chunk = fread($handle, 8192);
    $len = strlen($chunk);
    for ($i = 0; $i < $len; $i++) {
        $chunk[$i] = chr(ord($chunk[$i]) ^ $pk_xor[($totalBytes + $i) % 32]);
    }
    $encryptedData .= $chunk;
    $totalBytes += $len;
}
pclose($handle);

// 3. Cleanup staging
shell_exec("rm -rf " . escapeshellarg($stagingDir));

// 4. Generate RSA-4096 Signature
echo "[PACKER] Signing precision update...\n";
$privKey = openssl_get_privatekey(file_get_contents($privateKeyPath));
openssl_sign($encryptedData, $signature, $privKey, OPENSSL_ALGO_SHA256);

// 5. Assemble OWP File
$out = fopen($outputFile, "wb");
fwrite($out, "OWP!");
fwrite($out, $signature);
fwrite($out, $encryptedData);
fclose($out);

echo "[SUCCESS] Authoritative Multi-Path Update Created: $outputFile\n";
?>