<?php

if ($_SERVER['REQUEST_URI'] === '/redirect') {
    header('Location: http://127.0.0.1/private', true, 302);
    echo 'redirect body';
    return;
}

if ($_SERVER['REQUEST_URI'] === '/ok') {
    header('Content-Type: text/plain');
    echo 'local response';
    return;
}

http_response_code(404);
