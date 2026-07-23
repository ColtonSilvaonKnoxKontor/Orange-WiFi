<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Server Restarter Logic
 */

// We use systemctl to restart the web stack
// This will apply any changes to PHP files or Nginx config
$output = shell_exec("sudo systemctl restart nginx php8.4-fpm 2>&1");

if (empty($output)) {
    echo "SUCCESS";
} else {
    echo "ERROR: " . $output;
}
?>