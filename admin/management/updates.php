<?php // [!] By Colton Silva (chinawaterstealers).
/**
 * SilvaSystems Software Update & Trust API
 */
require_once '/home/pi/orange-wifi/admin/critical/gatekeeper.php';
require_once '/home/pi/orange-wifi/lib/autoload.php';

$action = $_GET['action'] ?? '';
$keyStore = "/home/pi/orange-wifi/conf/keys/";

if ($action === 'list_keys') {
    $keys = [];
    if (is_dir($keyStore)) {
        $files = scandir($keyStore);
        foreach ($files as $file) {
            if (strpos($file, 'tp_') === 0 && strpos($file, '.der') !== false) {
                $filePath = $keyStore . $file;
                $metaPath = str_replace('.der', '.json', $filePath);
                $content = file_get_contents($filePath);
                $meta = file_exists($metaPath) ? json_decode(file_get_contents($metaPath), true) : [];
                $keys[] = [
                    'filename' => $file,
                    'developer' => $meta['developer'] ?? str_replace(['tp_', '.der'], '', $file),
                    'organization' => $meta['organization'] ?? 'Independent',
                    'website' => $meta['website'] ?? '',
                    'social_name' => $meta['social_name'] ?? '',
                    'social_url' => $meta['social_url'] ?? '',
                    'fingerprint' => base64_encode(hash('sha256', $content, true)),
                    'key_content' => base64_encode($content),
                    'added_at' => date("Y-m-d H:i:s", filemtime($filePath))
                ];
            }
        }
    }
    echo json_encode($keys);
    exit;
}

if ($action === 'process_key_file') {
    if (!isset($_FILES['key_file'])) {
        echo json_encode(['error' => 'No file uploaded']); exit;
    }

    $content = file_get_contents($_FILES['key_file']['tmp_name']);
    
    if (substr($content, 0, 4) !== "OWK!") {
        echo json_encode(['error' => 'Invalid Header. Not a valid SilvaSystems Key Package.']); exit;
    }

    $owk_key = [
        0x4c, 0x3d, 0xf4, 0x57, 0x64, 0xc4, 0xbc, 0x5e,
        0xd0, 0x63, 0x68, 0x62, 0x35, 0x50, 0xa6, 0xe5,
        0x7b, 0x5d, 0xfb, 0x18, 0x86, 0xfb, 0x29, 0x61, 
        0xc7, 0xd1, 0xd1, 0xc9, 0xca, 0x77, 0xa8, 0x8a
    ];

    $payload = substr($content, 4);
    $decrypted = "";
    for ($i = 0; $i < strlen($payload); $i++) {
        $decrypted .= chr(ord($payload[$i]) ^ $owk_key[$i % 32]);
    }

    $data = json_decode($decrypted, true);
    if (!$data || !isset($data['public_key']) || !isset($data['developer'])) {
        echo json_encode(['error' => 'Cryptographic Mismatch or Corrupted Metadata.']); exit;
    }

    // Geolocation verification for audit
    $ip = $data['public_ip'] ?? '0.0.0.0';
    if ($ip !== '0.0.0.0') {
        $geo = @json_decode(file_get_contents("http://ip-api.com/json/{$ip}"), true);
        $data['location'] = ($geo && $geo['status'] === 'success') 
            ? "{$geo['city']}, {$geo['country']} ({$geo['isp']})"
            : "Unknown Location";
    }

    echo json_encode($data);
    exit;
}

if ($action === 'authorize_key') {
    $postData = json_decode(file_get_contents('php://input'), true);
    $b64Key = $postData['public_key'] ?? '';
    $devName = preg_replace("/[^a-zA-Z0-9]/", "_", $postData['developer'] ?? 'unknown');

    if (empty($b64Key)) {
        echo json_encode(['error' => 'Missing public key']); exit;
    }

    $derKey = base64_decode($b64Key);
    if (!$derKey) {
        echo json_encode(['error' => 'Invalid key encoding']); exit;
    }

    if (!is_dir($keyStore)) mkdir($keyStore, 0755, true);

    $baseName = "tp_" . $devName . "_" . time();
    file_put_contents($keyStore . $baseName . ".der", $derKey);
    file_put_contents($keyStore . $baseName . ".json", json_encode($postData));

    echo json_encode(['status' => 'SUCCESS', 'message' => "Developer '$devName' authorized with full metadata."]);
    exit;
}

if ($action === 'delete_key') {
    // SECURITY: Prevent Path Traversal
    $filename = basename($_GET['filename'] ?? '');
    
    if (strpos($filename, 'tp_') === 0 && file_exists($keyStore . $filename)) {
        unlink($keyStore . $filename);
        @unlink($keyStore . str_replace('.der', '.json', $filename));
        echo json_encode(['status' => 'SUCCESS']);
    } else {
        echo json_encode(['error' => 'Invalid key file']);
    }
    exit;
}
?>