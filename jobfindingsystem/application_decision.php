<?php
function decide_application(mysqli $con, bool $accepted): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: companyrecivedrequest.php');
        exit;
    }
    $id = filter_var($_POST['application_id'] ?? null, FILTER_VALIDATE_INT);
    $jobId = filter_var($_POST['job_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || !$jobId || $id < 1 || $jobId < 1) {
        http_response_code(400);
        exit('Invalid application ID.');
    }
    $cid = $_SESSION['cid'];
    $table = $accepted ? 'accepted_application' : 'declined_application';
    $date = $accepted ? 'accepted_at' : 'rejected_at';
    $con->begin_transaction();
    try {
        // Lock the pending row and verify ownership before moving it.
        $stmt = $con->prepare('SELECT ja.id FROM jobapplication ja JOIN jobs j ON j.id = ja.job_id WHERE ja.id = ? AND ja.job_id = ? AND j.company_id = ? FOR UPDATE');
        $stmt->bind_param('iii', $id, $jobId, $cid);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            $con->rollback();
            http_response_code(404);
            exit('Application not found.');
        }
        $stmt = $con->prepare("INSERT INTO `$table` (application_id, job_id, fullname, email, phone, address, skills, experiences, photo, cv, `$date`, username, cid) SELECT id, job_id, fullname, email, phone, address, skills, experiences, photo, cv, NOW(), username, ? FROM jobapplication WHERE id = ?");
        $stmt->bind_param('ii', $cid, $id);
        $stmt->execute();
        $stmt = $con->prepare('DELETE FROM jobapplication WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $con->commit();
    } catch (Throwable $exception) {
        $con->rollback();
        throw $exception;
    }
    header('Location: companyrecivedrequest.php');
    exit;
}
