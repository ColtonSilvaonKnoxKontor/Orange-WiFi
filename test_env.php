<?php
$data = getenv('SECURE_DATA');
if ($data === false) {
    echo "DEBUG: SECURE_DATA NOT FOUND\n";
} else {
    echo "DEBUG: SECURE_DATA FOUND: " . $data . "\n";
    echo "DECODED: " . base64_decode($data) . "\n";
}
?>