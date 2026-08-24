<?php
/* =====================================================================
 *  Small view helpers shared by the dashboard, the history page and
 *  the receipt, so a transaction looks the same everywhere.
 * =====================================================================*/

require_once __DIR__ . '/../config/helpers.php';

/** Emoji used for each transaction type. */
function trx_icon(string $type): string
{
    static $map = [
        'Send Money'      => "\u{1F4B8}",
        'Add Money'       => "\u{1F3E6}",
        'Cash In'         => "\u{1F4B5}",
        'Cash Out'        => "\u{1F3E7}",
        'Mobile Recharge' => "\u{1F4F1}",
        'Payment'         => "\u{1F6CD}",
        'Bill Pay'        => "\u{1F4A1}",
        'Education Fee'   => "\u{1F393}",
        'Savings'         => "\u{1F3AF}",
        'Savings Release' => "\u{1F513}",
        'Request Payment' => "\u{1F91D}",
        'Refund'          => "\u{21A9}",
    ];
    return $map[$type] ?? "\u{1F504}";
}

/** true when the money moved away from this user. */
function trx_is_out(array $t, string $phone): bool
{
    return $t['transaction_from'] === $phone;
}

/** The other side of a transaction, ready to display. */
function trx_counterparty(array $t, string $phone): string
{
    $other = trx_is_out($t, $phone) ? $t['transaction_to'] : $t['transaction_from'];
    return valid_phone($other) ? name_of($other) . ' (' . $other . ')' : $other;
}

/** One row of the activity list. */
function trx_entry(array $t, string $phone): string
{
    $out   = trx_is_out($t, $phone);
    $delta = $out ? -((float) $t['amount'] + (float) $t['fee']) : (float) $t['amount'];
    $sign  = $out ? '-' : '+';

    $html  = '<a class="entry" href="receipt.php?id=' . urlencode($t['trx_id']) . '" style="color:inherit">';
    $html .= '<span class="ico">' . trx_icon($t['transaction_type']) . '</span>';
    $html .= '<span class="body"><span class="t">' . e($t['transaction_type']) . '</span>'
           . '<span class="s">' . e(trx_counterparty($t, $phone)) . ' &middot; '
           . e(time_ago($t['transaction_time'])) . '</span></span>';
    $html .= '<span class="amt ' . ($out ? 'neg' : 'pos') . '">'
           . $sign . ' ' . money(abs($delta)) . '</span>';
    $html .= '</a>';
    return $html;
}

/** Colour for a category, used by the analytics page. */
function category_colour(string $c): string
{
    return TRX_CATEGORIES[$c] ?? '#ADB5BD';
}

/** Builds a pie slice path for the donut chart. */
function donut_slices(array $data, int $size = 160, int $thickness = 26): string
{
    $total = array_sum(array_column($data, 'value'));
    if ($total <= 0) {
        return '';
    }
    $r      = $size / 2 - $thickness / 2;
    $centre = $size / 2;
    $circ   = 2 * M_PI * $r;
    $offset = 0.0;
    $svg    = '<svg class="donut" width="' . $size . '" height="' . $size . '" viewBox="0 0 '
            . $size . ' ' . $size . '">';

    foreach ($data as $d) {
        $share  = $d['value'] / $total;
        $len    = $share * $circ;
        $svg   .= '<circle cx="' . $centre . '" cy="' . $centre . '" r="' . $r . '"'
                . ' fill="none" stroke="' . e($d['colour']) . '" stroke-width="' . $thickness . '"'
                . ' stroke-dasharray="' . round($len, 2) . ' ' . round($circ - $len, 2) . '"'
                . ' stroke-dashoffset="' . round(-$offset, 2) . '"'
                . ' transform="rotate(-90 ' . $centre . ' ' . $centre . ')"></circle>';
        $offset += $len;
    }
    $svg .= '</svg>';
    return $svg;
}
