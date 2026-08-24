<?php
require_once __DIR__ . '/config/helpers.php';
redirect(is_logged_in() ? 'account.php' : 'login.php');
