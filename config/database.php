<?php
/**
 * Database Configuration and Connection
 * 
 * Provides a PDO connection to the MySQL database.
 * Uses prepared statements for security against SQL injection.
 */

function getEnvValue(array $keys, $default = null) {
    foreach ($keys as $key) {
        $value = getenv($key);
        if ($value !== false && trim((string) $value) !== '') {
            return $value;
        }
    }

    return $default;
}

function getDatabaseConfig() {
    $dbUrl = getEnvValue(['DATABASE_URL', 'DB_URL'], '');

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
                    'charset' => getEnvValue(['DB_CHARSET'], 'utf8mb4'),
                ];
            }
        }
    }

    return [
        'host' => getEnvValue(['DB_HOST', 'MYSQLHOST', 'MYSQL_HOST'], 'localhost'),
        'port' => getEnvValue(['DB_PORT', 'MYSQLPORT', 'MYSQL_PORT'], '3306'),
        'name' => getEnvValue(['DB_NAME', 'MYSQLDATABASE', 'MYSQL_DB', 'MYSQL_DATABASE'], 'ecommerce_db'),
        'user' => getEnvValue(['DB_USER', 'MYSQLUSER', 'MYSQL_USER'], 'root'),
        'pass' => getEnvValue(['DB_PASS', 'MYSQLPASSWORD', 'MYSQL_PASS', 'MYSQL_PWD'], ''),
        'charset' => getEnvValue(['DB_CHARSET'], 'utf8mb4'),
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
