<?php
/**
 * Database Configuration and Connection
 * 
 * Provides a PDO connection to the MySQL database.
 * Uses prepared statements for security against SQL injection.
 */

function getDatabaseConfig() {
    $dbUrl = getenv('DATABASE_URL') ?: getenv('DB_URL');

    if (!empty($dbUrl)) {
        $parsed = parse_url($dbUrl);

        if ($parsed !== false) {
            $host = $parsed['host'] ?? '';
            $port = $parsed['port'] ?? '3306';
            $user = isset($parsed['user']) ? urldecode($parsed['user']) : '';
            $pass = isset($parsed['pass']) ? urldecode($parsed['pass']) : '';
            $name = isset($parsed['path']) ? ltrim($parsed['path'], '/') : '';

            if (!empty($host) && !empty($name)) {
                return [
                    'host' => $host,
                    'port' => $port,
                    'name' => $name,
                    'user' => $user,
                    'pass' => $pass,
                    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
                ];
            }
        }
    }

    return [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'ecommerce_db',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
    ];
}

$config = getDatabaseConfig();
define('DB_HOST', $config['host']);
define('DB_NAME', $config['name']);
define('DB_USER', $config['user']);
define('DB_PASS', $config['pass']);
define('DB_PORT', $config['port']);
define('DB_CHARSET', $config['charset']);

/**
 * Get PDO database connection
 * 
 * @return PDO Database connection object
 */
function getConnection() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    return $pdo;
}
