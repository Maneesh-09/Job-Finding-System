<?php
// CLI runner for the same migration file that can be imported in phpMyAdmin.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/database.php';
$path = __DIR__ . '/update_existing_database.sql';
$sql = file_get_contents($path);
if ($sql === false) {
    fwrite(STDERR, "Unable to read update_existing_database.sql.\n");
    exit(1);
}
// DELIMITER is a mysql/phpMyAdmin client directive, not server SQL. The chosen
// $$ separator appears only at statement boundaries in this migration file.
$sql = preg_replace('/^DELIMITER[^\r\n]*\r?$/m', '', $sql);
$sql = str_replace('$$', ';', $sql);
try {
    $con->multi_query($sql);
    do {
        $result = $con->store_result();
        if ($result instanceof mysqli_result) $result->free();
    } while ($con->more_results() && $con->next_result());
    echo "Database migration completed. Existing records have been preserved.\n";
} catch (mysqli_sql_exception $exception) {
    fwrite(STDERR, 'Database migration failed: ' . $exception->getMessage() . "\n");
    fwrite(STDERR, "Resolve the reported issue, then rerun php migrate.php.\n");
    exit(1);
}
