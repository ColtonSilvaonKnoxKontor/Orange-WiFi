<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Portal Identity API
 */
header('Content-Type: application/json');

$confFile = '/home/pi/orange-wifi/conf/general_settings.json';
$data = [
    'vendo_name' => 'SILVASYSTEMS',
    'custom_msg' => ''
];

if (file_exists($confFile)) {
    $json = json_decode(file_get_contents($confFile), true);
    if ($json) {
        $data['vendo_name'] = $json['vendo_name'] ?? 'SILVASYSTEMS';
        $data['custom_msg'] = $json['custom_msg'] ?? '';
    }
}

echo json_encode($data);
?>