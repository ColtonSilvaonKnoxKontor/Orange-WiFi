<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 */
require_once __DIR__ . '/database.php';

function get_mb_reward($amt) {
  $db = new Database();
  $rates = $db->get_rates();
  
  if( isset($rates[$amt]) ) {
    return $rates[$amt];
  }

  return 0;
}
?>