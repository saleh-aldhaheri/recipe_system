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

function jsonResponse(\Psr\Http\Message\ResponseInterface $response, $data, int $status = 200): \Psr\Http\Message\ResponseInterface
{
    $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));

    return $response
        ->withStatus($status)
        ->withHeader('Content-Type', 'application/json');
}

function isAjaxRequest(\Psr\Http\Message\ServerRequestInterface $request): bool
{
    $headers = $request->getHeader('X-Requested-With');

    return ! empty($headers) && strtolower($headers[0]) === 'xmlhttprequest';
}

function view(string $view, array $data = [], ?string $layout = 'main'): string
{
    extract($data);

    ob_start();

    $viewPath = BASE.'view'.DIRECTORY_SEPARATOR.str_replace('.', DIRECTORY_SEPARATOR, $view).'.php';

    if (! file_exists($viewPath)) {
        throw new \Exception("View not found: {$view}");
    }

    require $viewPath;

    $content = ob_get_clean();

    if ($layout) {
        $layoutPath = BASE.'view'.DIRECTORY_SEPARATOR.'layouts'.DIRECTORY_SEPARATOR.$layout.'.php';

        if (! file_exists($layoutPath)) {
            throw new \Exception("Layout not found: {$layout}");
        }

        extract($data);

        ob_start();
        require $layoutPath;

        return ob_get_clean();
    }

    return $content;
}

/**
 * Escape HTML output
 *
 * @param  string  $string
 * @return string
 *                Note: Illuminate already provides e() function, so we only define it if it doesn't exist
 */
if (! function_exists('e')) {
    function e(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Generate URL (relative path for routes)
 */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    
    if (empty($path)) {
        return '/';
    }
    
    return '/'.$path;
}

/**
 * Generate asset URL (for CSS, JS, images in public folder)
 */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    
    // Assets are in public folder, so return relative path
    return '/'.$path;
}