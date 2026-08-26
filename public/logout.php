<?php

declare(strict_types=1);

session_start();
require __DIR__ . '/../src/Auth.php';

Auth::logout();
header('Location: /index.php');
exit;
