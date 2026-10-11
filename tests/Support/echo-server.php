<?php

// Test-only HTTP server: echoes what the client sent so tests can assert real requests.
header('Content-Type: application/json');
echo json_encode([
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'authorization' => $_SERVER['HTTP_AUTHORIZATION'] ?? null,
    'beta' => $_SERVER['HTTP_OPENAI_BETA'] ?? null,
    'accept' => $_SERVER['HTTP_ACCEPT'] ?? null,
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? null,
    'body' => file_get_contents('php://input'),
]);
