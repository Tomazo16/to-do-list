<?php
// Symulacja prostego routingu
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/home') {
    http_response_code(200);
    echo "<html><body><h1>Welcome to Home Page</h1></body></html>";
} else {
    http_response_code(404);
    echo "Page not found";
}