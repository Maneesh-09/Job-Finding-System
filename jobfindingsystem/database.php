<?php
// Override these values with environment variables for your local installation.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $con = mysqli_connect(
        getenv('DB_HOST') ?: '127.0.0.1',
        getenv('DB_USER') ?: 'root',
        getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '',
        getenv('DB_NAME') ?: 'jfc',
        (int) (getenv('DB_PORT') ?: 3306)
    );
    mysqli_set_charset($con, 'utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log('Database connection failed: ' . $exception->getMessage());
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "Database unavailable. Check the DB_* environment variables and start MySQL.\n");
        exit(1);
    }
    http_response_code(503);
    exit('Database unavailable. Check DB_HOST, DB_PORT, DB_USER, DB_PASSWORD and DB_NAME, and start MySQL.');
}
