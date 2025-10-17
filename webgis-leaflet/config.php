<?php
// config.php - default settings for XAMPP
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'webgis');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP default
define('BASE_URL', 'http://localhost/webgis-leaflet/public');
ini_set('display_errors', 0);
error_reporting(E_ALL);
session_start();
