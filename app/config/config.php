<?php

$local = is_file(__DIR__ . '/local.php') ? require __DIR__ . '/local.php' : [];
$setting = static function (string $key, $default) use ($local) {
    if (array_key_exists($key, $local)) {
        return $local[$key];
    }
    $env = getenv('TKL_' . $key);
    return $env !== false ? $env : $default;
};

define('DB_HOST', $setting('DB_HOST', 'localhost'));
define('DB_NAME', $setting('DB_NAME', 'takalagbe'));
define('DB_USER', $setting('DB_USER', 'root'));
define('DB_PASS', $setting('DB_PASS', ''));
define('DB_PORT', $setting('DB_PORT', '3306'));

define('APP_DEBUG', (bool) $setting('DEBUG', $local === []));
define('APP_HOSTS', (array) $setting('HOSTS', []));

ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

define('APP_NAME',     'TakaLagbe!');
define('APP_VERSION',  '2.0');
define('CURRENCY_SIGN', "\u{09F3}");

define('MIN_AMOUNT',            10.00);
define('SEND_MONEY_MAX',        25000.00);
define('ADD_MONEY_MAX',         25000.00);
define('RECHARGE_MAX',          10000.00);
define('CASH_OUT_MAX',          25000.00);
define('DAILY_LIMIT_DEFAULT',   25000.00);

define('FEE_SEND_MONEY_PCT',  0.00);
define('FEE_CASH_OUT_PCT',    1.85);
define('FEE_BILL_FLAT',       5.00);

define('CASHBACK_RECHARGE_PCT', 1.00);

define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCK_MINUTES', 15);
define('VELOCITY_MAX_TRX',   10);
define('VELOCITY_WINDOW_MIN', 5);

define('ROWS_PER_PAGE', 12);

const TRX_CATEGORIES = [
    'Transfer'  => '#4A73B5',
    'Bills'     => '#B8862B',
    'Recharge'  => '#5B9BB5',
    'Shopping'  => '#9A6A4A',
    'Groceries' => '#C9A45C',
    'Food'      => '#B5543C',
    'Transport' => '#6C7FC4',
    'Health'    => '#3F8F68',
    'Books'     => '#8A6FA8',
    'Education' => '#6A5AA8',
    'Savings'   => '#4F9A82',
    'Cash'      => '#7C869A',
    'Other'     => '#A6ADBB',
];

const OPERATOR_PREFIX = [
    '017' => 'Grameenphone', '013' => 'Grameenphone',
    '019' => 'Banglalink',   '014' => 'Banglalink',
    '018' => 'Robi',         '016' => 'Airtel',
    '015' => 'Teletalk',
];

date_default_timezone_set('Asia/Dhaka');
