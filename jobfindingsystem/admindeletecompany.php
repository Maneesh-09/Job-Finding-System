<?php
require_once __DIR__ . '/auth.php';
require_role('admin');
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/records.php';
$id = filter_var($_GET['cid'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit('Invalid record ID.');
}
$con->begin_transaction();
try {
    delete_account_records($con, 'company', $id);
    $con->commit();
} catch (Throwable $exception) {
    $con->rollback();
    throw $exception;
}
header('Location: adminviewcompany.php');
exit;
