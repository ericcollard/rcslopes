<?php
// config/config.php
define('PIOUPIOU_API_URL', 'http://api.pioupiou.fr/v1/live-with-meta/all');
define('CACHE_DURATION', 300); // Duration in seconds (toutes les 5 minutes)


// CORS headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
