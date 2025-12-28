<?php

function dd(...$vars): void
{
    echo '<pre style="background:#000; color:green;font-weight:bold;font-size:16px;padding:10px; border:1px solid #ccc;">';
    foreach ($vars as $var) {
        var_dump($var);
        echo "\n";
    }
    echo '</pre>';
    exit();
}

function config() {}

/**
 * Helper function for JSON responses (AJAX-friendly)
 */
function jsonResponse(\Psr\Http\Message\ResponseInterface $response, $data, int $status = 200): \Psr\Http\Message\ResponseInterface
{
    $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));

    return $response
        ->withStatus($status)
        ->withHeader('Content-Type', 'application/json');
}
