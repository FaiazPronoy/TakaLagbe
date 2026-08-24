<?php
/* Database and app settings. */

/* ---- database ---------------------------------------------------- */
define('DB_HOST', getenv('TKL_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('TKL_DB_NAME') ?: 'takalagbe');
define('DB_USER', getenv('TKL_DB_USER') ?: 'root');
define('DB_PASS', getenv('TKL_DB_PASS') !== false ? getenv('TKL_DB_PASS') : '');
define('DB_PORT', getenv('TKL_DB_PORT') ?: '3306');

/* ---- branding ---------------------------------------------------- */
define('APP_NAME',     'TakaLagbe!');
define('APP_TAGLINE',  'Send. Pay. Save. Simple.');
define('APP_VERSION',  '2.0');
define('CURRENCY',     'BDT');
define('CURRENCY_SIGN', "\u{09F3}");   // Bengali taka sign

/* ---- money rules ------------------------------------------------- */
define('MIN_AMOUNT',            10.00);
define('SEND_MONEY_MAX',        25000.00);
define('ADD_MONEY_MAX',         25000.00);
define('RECHARGE_MAX',          10000.00);
define('CASH_OUT_MAX',          25000.00);
define('DAILY_LIMIT_DEFAULT',   25000.00);

/* fees, as a percentage of the amount */
define('FEE_SEND_MONEY_PCT',  0.00);   // free between users
define('FEE_CASH_OUT_PCT',    1.85);   // matches the real market rate
define('FEE_BILL_FLAT',       5.00);   // flat convenience fee per bill

/* rewards */
define('CASHBACK_RECHARGE_PCT', 1.00);

/* ---- safety limits ----------------------------------------------- */
define('LOGIN_MAX_ATTEMPTS', 5);       // per phone number
define('LOGIN_LOCK_MINUTES', 15);
define('VELOCITY_MAX_TRX',   10);      // outgoing transfers ...
define('VELOCITY_WINDOW_MIN', 5);      // ... allowed in this many minutes

/* ---- pagination -------------------------------------------------- */
define('ROWS_PER_PAGE', 12);

/* ---- transaction categories used by the analytics page ----------- */
/* Category colours sit on the same navy-to-brass line as the rest of the
   app, so the donut on the analytics page does not look like a different
   product from the page around it. */
const TRX_CATEGORIES = [
    'Transfer'  => '#1E3A63',
    'Bills'     => '#A87A2B',
    'Recharge'  => '#4C7396',
    'Shopping'  => '#8C5A3C',
    'Groceries' => '#C09A4E',
    'Food'      => '#A9412C',
    'Transport' => '#39567F',
    'Health'    => '#2C6E4E',
    'Books'     => '#6E5A7D',
    'Education' => '#5A4A86',
    'Savings'   => '#3F7F68',
    'Cash'      => '#6B7385',
    'Other'     => '#A6ADBB',
];

/* mobile operator prefixes, used to auto detect the operator */
const OPERATOR_PREFIX = [
    '017' => 'Grameenphone', '013' => 'Grameenphone',
    '019' => 'Banglalink',   '014' => 'Banglalink',
    '018' => 'Robi',         '016' => 'Airtel',
    '015' => 'Teletalk',
];

date_default_timezone_set('Asia/Dhaka');
