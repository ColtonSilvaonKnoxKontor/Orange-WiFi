<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Portal Design API
 */
require_once '/home/pi/orange-wifi/admin/critical/gatekeeper.php';
require_once '/home/pi/orange-wifi/lib/autoload.php';

header('Content-Type: application/json');

$designFile = '/home/pi/orange-wifi/css/img/design.json';
$fontDir = '/home/pi/orange-wifi/fonts/';

$action = $_GET['action'] ?? '';

// 1. LIST FONTS & STATUS
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $fonts = [];
    if (is_dir($fontDir)) {
        $files = scandir($fontDir);
        foreach ($files as $f) {
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            if ($ext === 'ttf' || $ext === 'otf') {
                $fullPath = $fontDir . $f;
                $fonts[] = [
                    'file' => $f,
                    'name' => str_replace(['.ttf', '.otf'], '', $f),
                    'size' => round(filesize($fullPath) / 1024 / 1024, 2) . ' MB'
                ];
            }
        }
    }
    
    $current = file_exists($designFile) ? json_decode(file_get_contents($designFile), true) : [];
    echo json_encode([
        'status' => 'SUCCESS',
        'fonts' => $fonts,
        'current' => $current
    ]);
    exit;
}

// 2. SAVE FONT PREFERENCE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_font') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    
    // Support nested data from the frontend
    $font = $data['post']['font'] ?? $data['font'] ?? null;
    
    if ($font === null) { echo json_encode(['error' => 'No font data received.']); exit; }

    $current = file_exists($designFile) ? json_decode(file_get_contents($designFile), true) : [];
    $current['font'] = $font;
    
    if (file_put_contents($designFile, json_encode($current))) {
        echo json_encode(['status' => 'SUCCESS', 'msg' => 'Typography updated!']);
    } else {
        echo json_encode(['error' => 'Failed to save preference.']);
    }
    exit;
}

// 3. HANDLE BACKGROUND UPLOAD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['background'])) {
    $file = $_FILES['background'];
    $targetDir = '/home/pi/orange-wifi/css/img/';
    
    $ext = (mime_content_type($file['tmp_name']) === 'image/png') ? '.png' : '.jpg';
    $targetFile = $targetDir . 'bg' . $ext;

    @unlink($targetDir . 'bg.jpg');
    @unlink($targetDir . 'bg.png');

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $current = file_exists($designFile) ? json_decode(file_get_contents($designFile), true) : [];
        $current['background'] = '/css/img/bg' . $ext;
        file_put_contents($designFile, json_encode($current));
        echo json_encode(['status' => 'SUCCESS', 'msg' => 'Background updated!', 'path' => $current['background']]);
    } else {
        echo json_encode(['error' => 'Upload failed.']);
    }
    exit;
}
?>