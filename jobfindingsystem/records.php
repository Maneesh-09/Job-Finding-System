<?php
function delete_job_records(mysqli $con, int $id): void
{
    foreach (['jobapplication', 'accepted_application', 'declined_application'] as $table) {
        $con->query("DELETE FROM `$table` WHERE job_id = $id");
    }
    $con->query("DELETE FROM jobs WHERE id = $id");
}

function delete_account_records(mysqli $con, string $role, int $id): void
{
    if (!in_array($role, ['user', 'company'], true)) throw new InvalidArgumentException('Invalid account role');
    $key = $role === 'user' ? 'uid' : 'cid';
    $account = $con->query("SELECT username FROM `$role` WHERE `$key` = $id")->fetch_assoc();
    if (!$account) return;
    if ($role === 'company') {
        $jobs = $con->query("SELECT id FROM jobs WHERE company_id = $id");
        while ($job = $jobs->fetch_assoc()) delete_job_records($con, (int) $job['id']);
    } else {
        foreach (['jobapplication', 'accepted_application', 'declined_application'] as $table) {
            $stmt = $con->prepare("DELETE FROM `$table` WHERE username = ?");
            $stmt->bind_param('s', $account['username']);
            $stmt->execute();
        }
    }
    $con->query("DELETE FROM `$role` WHERE `$key` = $id");
}

function rename_account_records(mysqli $con, string $role, string $oldName, string $newName): void
{
    $tables = $role === 'user' ? ['jobapplication', 'accepted_application', 'declined_application'] : ['jobs'];
    foreach ($tables as $table) {
        $stmt = $con->prepare("UPDATE `$table` SET username = ? WHERE username = ?");
        $stmt->bind_param('ss', $newName, $oldName);
        $stmt->execute();
    }
}
