<?php
$file = '/var/log/nginx/error.log';
if (file_exists($file)) {
    echo shell_exec('tail -n 200 ' . escapeshellarg($file));
} else {
    echo "Log file not found or not readable.";
}
?>