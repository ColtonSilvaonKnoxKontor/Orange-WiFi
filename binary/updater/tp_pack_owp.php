<?php // [!] By Colton Silva (chinawaterstealers).
/**
 * SilvaSystems Third-Party OWP Package Generator
 * Authoritative Signing & Key Generation
 */

// --- COMMAND: KEY GENERATION ---
if ($argc == 2 && $argv[1] == "--keygen") {
    echo "[TP-PACKER] Generating authoritative 4096-bit RSA Key Pair...\n";
    
    $config = ["private_key_bits" => 4096, "private_key_type" => OPENSSL_KEYTYPE_RSA];
    $res = openssl_pkey_new($config);
    
    // 1. Export Private Key (PEM)
    openssl_pkey_export($res, $privKey);
    file_put_contents("tp_private.pem", $privKey);
    
    // 2. Export Public Key (DER)
    $pubKey = openssl_pkey_get_details($res);
    $pubKeyData = $pubKey["key"];
    // Convert PEM to DER for gen_owk compatibility
    $derKey = base64_decode(preg_replace('/\-+BEGIN PUBLIC KEY\-+|\-+END PUBLIC KEY\-+|\s+/', '', $pubKeyData));
    file_put_contents("tp_public.der", $derKey);
    
    echo "[SUCCESS] Keys generated:\n";
    echo " > Private Key: tp_private.pem (KEEP SECRET - used for signing)\n";
    echo " > Public Key:  tp_public.der  (SHARE - used for gen_owk)\n";
    exit;
}

// --- COMMAND: PACKAGE SIGNING ---
if ($argc < 4) {
    die("Usage:\n" .
        "  Generate Keys:  php tp_pack_owp.php --keygen\n" .
        "  Pack Update:    php tp_pack_owp.php <manifest.json> <private_key.pem> <output_file.owp>\n");
}

$manifestPath = $argv[1];
$privateKeyPath = $argv[2];
$outputFile = $argv[3];
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

echo "[TP-PACKER] Initializing third-party package generation...\n";

$stagingDir = "/tmp/tp_owp_stage_" . uniqid();
mkdir($stagingDir, 0755, true);

foreach ($manifest as $sysPath => $localPath) {
    if (!file_exists($localPath)) {
        echo "[!] WARNING: Skipping $localPath (not found)\n";
        continue;
    }
    $targetStage = $stagingDir . $sysPath;
    @mkdir(dirname($targetStage), 0755, true);
    copy($localPath, $targetStage);
    echo " > Staged: $sysPath\n";
}

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
shell_exec("rm -rf " . escapeshellarg($stagingDir));

echo "[TP-PACKER] Signing with provided private key...\n";
$privKey = openssl_get_privatekey(file_get_contents($privateKeyPath));
openssl_sign($encryptedData, $signature, $privKey, OPENSSL_ALGO_SHA256);

$out = fopen($outputFile, "wb");
fwrite($out, "OWP!");
fwrite($out, $signature);
fwrite($out, $encryptedData);
fclose($out);

echo "[SUCCESS] Third-party package generated: $outputFile ($totalBytes bytes)\n";
?>