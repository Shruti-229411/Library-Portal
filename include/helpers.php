<?php
if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        // adjust if you changed Apache port
        $base = 'http://localhost/Library-Management-System-with-Barcode/';
        return $base . ltrim($path, '/');
    }
}
