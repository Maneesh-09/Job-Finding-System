<?php
require_once __DIR__ . '/auth.php';
require_role('company');
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/application_decision.php';
decide_application($con, true);
