<?php
require_once __DIR__ . '/auth.php';
require_role('company');
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/records.php';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(400); exit('Invalid job ID.'); }
$con->begin_transaction();
try {
    $stmt = $con->prepare('SELECT id FROM jobs WHERE id = ? AND company_id = ? FOR UPDATE');
    $stmt->bind_param('ii', $id, $_SESSION['cid']);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        $con->rollback();
        http_response_code(404);
        exit('Job not found.');
    }
    delete_job_records($con, $id);
    $con->commit();
} catch (Throwable $exception) {
    $con->rollback();
    throw $exception;
}
header('Location: companyhomepage.php');
exit;
