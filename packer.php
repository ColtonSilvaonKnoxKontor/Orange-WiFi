<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Logic Packer (UPLOAD & METHOD AWARE)
 */

$key_str = "silvasystemschinawaterstealers01"; 
$iv_str  = "chinawatersteale"; 
$signature = "SECURITYBYSILVASYSTEMS";
$outputDir = __DIR__ . "/processed";

if ($argc < 2) {
    echo "Usage: php packer.php <file.php>\n";
    exit(1);
}

$inputFile = $argv[1];
if (!file_exists($inputFile)) {
    echo "Error: File '$inputFile' not found.\n";
    exit(1);
}

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$baseFileName = basename($inputFile, '.php');
$encryptedFile = $outputDir . "/" . $baseFileName . ".titemongmaliit";
$bridgeFile = $outputDir . "/" . $baseFileName . ".php";

$data = file_get_contents($inputFile);
$encrypted = openssl_encrypt($data, 'aes-256-cbc', $key_str, OPENSSL_RAW_DATA, $iv_str);
if ($encrypted === false) { exit("Error: Encryption failed.\n"); }

file_put_contents($encryptedFile, $signature . $encrypted);
echo "[+] Encrypted: $encryptedFile\n";

// SECURE BRIDGE: V11 - Upload & Method Aware
$bridgeCode = <<<'CODE'
<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure Logic Bridge
 */
ob_start();
if (file_exists('/home/pi/orange-wifi/admin/pass.php')) {
    require_once '/home/pi/orange-wifi/admin/pass.php';
}
$status = http_response_code();
ob_end_clean();

if ($status !== 200) {
    http_response_code($status);
    exit;
}

$cmd = basename($_SERVER['SCRIPT_FILENAME'], '.php');

// Package Context for Binary (Including Method and Files)
$context = [
    "method" => $_SERVER['REQUEST_METHOD'],
    "get" => $_GET,
    "post" => $_POST,
    "files" => $_FILES,
    "input" => file_get_contents('php://input')
];
$dataArg = " " . escapeshellarg(base64_encode(json_encode($context)));

passthru("/usr/bin/silvasystems " . escapeshellarg($cmd) . $dataArg);
?>
CODE;

file_put_contents($bridgeFile, $bridgeCode);
echo "[+] Generated Secure Bridge: $bridgeFile\n";
?>