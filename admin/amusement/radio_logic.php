<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Radio Logic - MP3 Precision Edition
 */

require_once '/home/pi/orange-wifi/admin/thrdparty/getid3/getid3.php';

$confFile = '/home/pi/orange-wifi/conf/radio_settings.json';
$mode = 'preset';

if (file_exists($confFile)) {
    $settings = json_decode(file_get_contents($confFile), true);
    $mode = $settings['mode'] ?? 'preset';
}

if ($mode === 'off') {
    echo json_encode(['status' => 'off']);
    exit;
}

$radioDir = ($mode === 'custom') 
    ? '/home/pi/orange-wifi/css/sfx/custom_radio/' 
    : '/home/pi/orange-wifi/css/sfx/radio/';

if (!is_dir($radioDir)) {
    echo json_encode(['error' => 'Directory not found']);
    exit;
}

// Now scanning for MP3
$files = glob($radioDir . '*.mp3');
if (empty($files)) {
    echo json_encode(['error' => 'No mp3 files found']);
    exit;
}

sort($files); 

$getID3 = new getID3;
$playlist = [];
$totalDuration = 0;

foreach ($files as $f) {
    $fileInfo = $getID3->analyze($f);
    $duration = (float)($fileInfo['playtime_seconds'] ?? 0);
    
    if ($duration < 1) $duration = 10; 
    
    $playlist[] = [
        'file' => basename($f),
        'duration' => $duration,
        'start_offset' => $totalDuration,
        'title' => $fileInfo['tags']['id3v2']['title'][0] ?? $fileInfo['tags']['id3v1']['title'][0] ?? basename($f, '.mp3'),
        'artist' => $fileInfo['tags']['id3v2']['artist'][0] ?? $fileInfo['tags']['id3v1']['artist'][0] ?? 'Live Broadcast'
    ];
    $totalDuration += $duration;
}

if ($totalDuration == 0) {
    echo json_encode(['error' => 'Playlist empty']);
    exit;
}

// Global Sync Timeline
$start = 1767225600; // 2026-01-01
$now = microtime(true);
$loopPosition = fmod(($now - $start), $totalDuration);

$currentTrack = null;
$seekTime = 0;

foreach ($playlist as $track) {
    if ($loopPosition >= $track['start_offset'] && $loopPosition < ($track['start_offset'] + $track['duration'])) {
        $currentTrack = $track;
        $seekTime = $loopPosition - $track['start_offset'];
        break;
    }
}

if ($currentTrack) {
    $webPath = ($mode === 'custom') ? '../custom_radio/' : '';
    echo json_encode([
        'status' => 'playing',
        'mode' => $mode,
        'track' => $webPath . $currentTrack['file'],
        'seek' => round($seekTime, 3),
        'duration' => round($currentTrack['duration'], 2),
        'title' => $currentTrack['title'],
        'artist' => $currentTrack['artist']
    ]);
} else {
    echo json_encode(['error' => 'Sync error']);
}
?>