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
 * Check if request is AJAX
 */
function isAjaxRequest(\Psr\Http\Message\ServerRequestInterface $request): bool
{
    $headers = $request->getHeader('X-Requested-With');

    return ! empty($headers) && strtolower($headers[0]) === 'xmlhttprequest';
}

/**
 * Render a view with layout
 *
 * @param  string  $view  - View name (e.g., 'items.index')
 * @param  array  $data  - Data to pass to view
 * @param  string|null  $layout  - Layout name (default: 'main')
 * @return string - Rendered HTML
 */
function view(string $view, array $data = [], ?string $layout = 'main'): string
{
    // Extract data to variables for view
    extract($data);

    // Start output buffering
    ob_start();

    // Load view file from view/ directory (lowercase)
    $viewPath = BASE.'view'.DIRECTORY_SEPARATOR.str_replace('.', DIRECTORY_SEPARATOR, $view).'.php';

    if (! file_exists($viewPath)) {
        throw new \Exception("View not found: {$view}");
    }

    require $viewPath;

    // Get view content
    $content = ob_get_clean();

    // If layout is specified, wrap content in layout
    if ($layout) {
        $layoutPath = BASE.'view'.DIRECTORY_SEPARATOR.'layouts'.DIRECTORY_SEPARATOR.$layout.'.php';

        if (! file_exists($layoutPath)) {
            throw new \Exception("Layout not found: {$layout}");
        }

        // Extract data again for layout
        extract($data);

        ob_start();
        require $layoutPath;

        return ob_get_clean();
    }

    return $content;
}

/**
 * Include a partial view
 *
 * @param  string  $partial  - Partial view name
 * @param  array  $data  - Data to pass to partial
 */
function partial(string $partial, array $data = []): void
{
    extract($data);
    $partialPath = BASE.'view'.DIRECTORY_SEPARATOR.'partials'.DIRECTORY_SEPARATOR.$partial.'.php';

    if (! file_exists($partialPath)) {
        throw new \Exception("Partial not found: {$partial}");
    }

    require $partialPath;
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
 * Generate URL
 */
function url(string $path = ''): string
{
    $baseUrl = rtrim($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']), '/');

    return $baseUrl.'/'.ltrim($path, '/');
}

/**
 * Generate asset URL
 */
function asset(string $path): string
{
    return url($path);
}

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
