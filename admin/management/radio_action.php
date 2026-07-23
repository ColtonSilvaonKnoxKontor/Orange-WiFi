<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Radio Settings Handler
 */
require_once __DIR__ . '/../critical/gatekeeper.php';

$confFile = '/home/pi/orange-wifi/conf/radio_settings.json';
$customDir = '/home/pi/orange-wifi/css/sfx/custom_radio/';

// Handle Mode Switch
if (isset($_POST['mode'])) {
    $mode = $_POST['mode'];
    if (!in_array($mode, ['off', 'preset', 'custom'])) {
        header('Location: radio.php?error=InvalidMode');
        exit;
    }
    
    file_put_contents($confFile, json_encode(['mode' => $mode]));
    header('Location: radio.php?success=ModeUpdated');
    exit;
}

// Handle File Deletion
if (isset($_POST['delete_file'])) {
    $fileToDelete = basename($_POST['delete_file']);
    $filePath = $customDir . $fileToDelete;
    
    // Safety check: ensure it's an mp3 and exists in our custom dir
    if (file_exists($filePath) && strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'mp3') {
        if (unlink($filePath)) {
            header('Location: radio.php?success=FileDeleted');
        } else {
            header('Location: radio.php?error=DeleteFailed');
        }
    } else {
        header('Location: radio.php?error=FileNotFound');
    }
    exit;
}

// Handle File Upload
if (isset($_FILES['music_file'])) {
    if (!is_dir($customDir)) mkdir($customDir, 0755, true);
    
    $file = $_FILES['music_file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if ($ext !== 'mp3') {
        header('Location: radio.php?error=OnlyMP3Allowed');
        exit;
    }
    
    // Sanitize filename
    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME)) . '.mp3';
    
    if (move_uploaded_file($file['tmp_name'], $customDir . $safeName)) {
        header('Location: radio.php?success=FileUploaded');
    } else {
        header('Location: radio.php?error=UploadFailed');
    }
    exit;
}

header('Location: radio.php');
?>