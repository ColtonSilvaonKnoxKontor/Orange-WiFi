<?php
function orangeWifiClasses($filename) {
  require_once __DIR__ . "/" . strtolower($filename) . ".php";
}

spl_autoload_register("orangeWifiClasses");