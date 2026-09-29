<?php
require_once __DIR__ . '/config/helpers.php';

require_login();

/* how many taka one unit of each currency is worth */
const RATES = [
    'USD' => ['US Dollar',        122.50, "\u{1F1FA}\u{1F1F8}"],
    'EUR' => ['Euro',             132.80, "\u{1F1EA}\u{1F1FA}"],
    'GBP' => ['British Pound',    155.40, "\u{1F1EC}\u{1F1E7}"],
    'INR' => ['Indian Rupee',       1.44, "\u{1F1EE}\u{1F1F3}"],
    'SAR' => ['Saudi Riyal',       32.65, "\u{1F1F8}\u{1F1E6}"],
    'AED' => ['UAE Dirham',        33.35, "\u{1F1E6}\u{1F1EA}"],
    'MYR' => ['Malaysian Ringgit', 27.60, "\u{1F1F2}\u{1F1FE}"],
    'JPY' => ['Japanese Yen',       0.79, "\u{1F1EF}\u{1F1F5}"],
    'AUD' => ['Australian Dollar', 79.90, "\u{1F1E6}\u{1F1FA}"],
    'CAD' => ['Canadian Dollar',   88.20, "\u{1F1E8}\u{1F1E6}"],
];

$from   = isset(RATES[get('from')]) || get('from') === 'BDT' ? get('from') : 'USD';
$to     = isset(RATES[get('to')])   || get('to')   === 'BDT' ? get('to')   : 'BDT';
$amount = is_numeric(get('amount')) && (float) get('amount') >= 0 ? (float) get('amount') : 100.0;

$rateOf = static fn(string $c): float => $c === 'BDT' ? 1.0 : RATES[$c][1];
$result = $amount * $rateOf($from) / $rateOf($to);
$single = $rateOf($from) / $rateOf($to);

$pageTitle = 'Currency converter';
$activeNav = '';
include('Front_end/front_currency.php');
