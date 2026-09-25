<?php
/* Small pieces of markup the pages share. */

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/icons.php';

/** Icon for each transaction type. */
function trx_icon(string $type): string
{
    static $map = [
        'Send Money'      => 'send',
        'Add Money'       => 'wallet',
        'Cash In'         => 'cash-in',
        'Cash Out'        => 'cash-out',
        'Mobile Recharge' => 'phone',
        'Payment'         => 'bag',
        'Bill Pay'        => 'receipt',
        'Education Fee'   => 'cap',
        'Savings'         => 'target',
        'Savings Release' => 'target',
    ];
    return icon($map[$type] ?? 'exchange');
}

/** true when the money moved away from this user. */
function trx_is_out(array $t, string $phone): bool
{
    return $t['transaction_from'] === $phone;
}

/* Joins the names of both sides onto a ledger query, so a list of rows does
   not go back to the user table once per row. */
const TRX_WITH_NAMES = "SELECT t.*,
        CONCAT(uf.firstname, ' ', uf.lastname) AS from_name,
        CONCAT(ut.firstname, ' ', ut.lastname) AS to_name
   FROM transactions t
   LEFT JOIN user uf ON uf.phone = t.transaction_from
   LEFT JOIN user ut ON ut.phone = t.transaction_to";

/** The other side of a transaction, ready to display. */
function trx_counterparty(array $t, string $phone): string
{
    $out   = trx_is_out($t, $phone);
    $other = $out ? $t['transaction_to'] : $t['transaction_from'];
    if (!valid_phone($other)) {
        return $other;
    }
    $key  = $out ? 'to_name' : 'from_name';
    $name = array_key_exists($key, $t) ? $t[$key] : name_of($other);
    return ($name ?: $other) . ' (' . $other . ')';
}

/** One row of the activity list, as an <li>. */
function trx_entry(array $t, string $phone): string
{
    $out    = trx_is_out($t, $phone);
    $failed = $t['status'] !== 'success';
    $delta  = $out ? (float) $t['amount'] + (float) $t['fee'] : (float) $t['amount'];

    return '<li><a class="entry" href="receipt.php?id=' . urlencode($t['trx_id']) . '">'
         . '<span class="ico ' . ($out ? 'out' : 'in') . '">' . trx_icon($t['transaction_type']) . '</span>'
         . '<span class="body"><span class="t">' . e($t['transaction_type']) . '</span>'
         . '<span class="s">' . e(trx_counterparty($t, $phone)) . ' · ' . e(time_ago($t['transaction_time']))
         . '</span></span>'
         . ($failed
             ? '<span class="badge bad">declined</span>'
             : '<span class="amt ' . ($out ? 'neg' : 'pos') . '">' . ($out ? '−' : '+') . ' '
               . money($delta) . '</span>')
         . '</a></li>';
}

/**
 * A round badge with someone's initials. Their colour is whatever they picked,
 * so the letters go dark on the light ones (brass, gold) to stay readable.
 */
function avatar(?string $first, ?string $last, ?string $colour, string $class = ''): string
{
    $colour = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $colour) ? $colour : '#4A73B5';
    [$r, $g, $b] = array_map(static function (string $h): float {
        $v = hexdec($h) / 255;
        return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
    }, str_split(substr($colour, 1), 2));
    $lum = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    // white on it would fall under 4.5:1 once the background is lighter than this
    $ink = $lum > 0.183 ? ' dark' : '';
    return '<span class="avatar' . $ink . ($class ? ' ' . $class : '') . '" style="background:' . $colour . '"'
         . ' aria-hidden="true">' . e(initials($first, $last)) . '</span>';
}

/** The six digit PIN, as one ordinary field so it posts without any script. */
function pin_field(string $id = 'pin', string $label = 'Transaction PIN'): string
{
    return '<div class="field"><label for="' . $id . '">' . e($label) . '</label>'
         . '<input class="pin" type="password" id="' . $id . '" name="pin" inputmode="numeric"'
         . ' pattern="\d{6}" minlength="6" maxlength="6" required autocomplete="off"'
         . ' aria-describedby="' . $id . '-hint">'
         . '<span class="hint center" id="' . $id . '-hint">The six digits you chose when you signed up.</span></div>';
}

/** A refused form, said plainly at the top of it. */
function error_note(?string $error): string
{
    if ($error === null || $error === '') {
        return '';
    }
    return '<div class="note bad" role="alert">' . icon('alert') . '<span>' . e($error) . '</span></div>';
}

/** What to show instead of an empty list: what it is for, and how to fill it. */
function empty_state(string $iconName, string $title, string $text, string $action = '', int $level = 3): string
{
    return '<div class="empty">' . icon($iconName) . "<h$level>" . e($title) . "</h$level><p>" . e($text) . '</p>'
         . $action . '</div>';
}

/** The page title block. $back is [href, label]. */
function page_head(string $title, string $lede = '', ?array $back = null, string $actions = '',
                   string $eyebrow = ''): string
{
    $html = '<div class="head rise"><div>';
    if ($back) {
        $html .= '<a class="back" href="' . e($back[0]) . '">' . icon('back') . e($back[1]) . '</a>';
    }
    if ($eyebrow !== '') {
        $html .= '<span class="eyebrow">' . e($eyebrow) . '</span>';
    }
    $html .= '<h1>' . e($title) . '</h1>';
    if ($lede !== '') {
        $html .= '<p class="lede">' . e($lede) . '</p>';
    }
    $html .= '</div>';
    if ($actions !== '') {
        $html .= '<div class="row">' . $actions . '</div>';
    }
    return $html . '</div>';
}

/** Amount chips that fill a field. They need script, so they only appear with it. */
function amount_chips(string $target, array $values): string
{
    $html = '<div class="chips js-only">';
    foreach ($values as $v) {
        $html .= '<button type="button" class="chip" aria-pressed="false" data-fill="' . e($target)
               . '" data-value="' . (int) $v . '">' . CURRENCY_SIGN . ' ' . number_format($v) . '</button>';
    }
    return $html . '</div>';
}

/** Colour for a category, used by the charts. */
function category_colour(string $c): string
{
    return TRX_CATEGORIES[$c] ?? TRX_CATEGORIES['Other'];
}

/** Builds the donut chart as stroked circle segments. */
function donut_slices(array $data, int $size = 170, int $thickness = 24, string $label = ''): string
{
    $total = array_sum(array_column($data, 'value'));
    if ($total <= 0) {
        return '';
    }
    $r      = $size / 2 - $thickness / 2;
    $centre = $size / 2;
    $circ   = 2 * M_PI * $r;
    $offset = 0.0;
    $svg    = '<svg class="donut settle" width="' . $size . '" height="' . $size . '" viewBox="0 0 '
            . $size . ' ' . $size . '" role="img" aria-label="' . e($label) . '">';

    foreach ($data as $d) {
        $len  = $d['value'] / $total * $circ;
        // a hair of space between slices, so neighbours stay apart
        $gap  = count($data) > 1 ? min(2.0, $len / 3) : 0;
        $svg .= '<circle cx="' . $centre . '" cy="' . $centre . '" r="' . $r . '"'
              . ' fill="none" stroke="' . e($d['colour']) . '" stroke-width="' . $thickness . '"'
              . ' stroke-dasharray="' . round($len - $gap, 2) . ' ' . round($circ - $len + $gap, 2) . '"'
              . ' stroke-dashoffset="' . round(-$offset, 2) . '"'
              . ' transform="rotate(-90 ' . $centre . ' ' . $centre . ')"></circle>';
        $offset += $len;
    }
    return $svg . '</svg>';
}
