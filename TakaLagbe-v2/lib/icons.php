<?php
/* The icon set. 24px grid, 1.6 stroke, drawn with currentColor so an icon
   takes the colour of the text around it. */

const ICONS = [
    'home'      => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20h14V9.5"/><path d="M10 20v-6h4v6"/>',
    'history'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    'chart'     => '<path d="M4 4v16h16"/><path d="M8 16v-4"/><path d="M12 16V8"/><path d="M16 16v-6"/>',
    'target'    => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.2"/>',
    'exchange'  => '<path d="M4 8h14l-3.5-3.5"/><path d="M20 16H6l3.5 3.5"/>',
    'bell'      => '<path d="M6 16v-5a6 6 0 0 1 12 0v5l1.5 2h-15z"/><path d="M10 20.5a2 2 0 0 0 4 0"/>',
    'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 20.5c1.5-3.5 4.4-5 8-5s6.5 1.5 8 5"/>',
    'sun'       => '<circle cx="12" cy="12" r="4"/><path d="M12 2.5v2M12 19.5v2M4.6 4.6 6 6M18 18l1.4 1.4M2.5 12h2M19.5 12h2M4.6 19.4 6 18M18 6l1.4-1.4"/>',
    'moon'      => '<path d="M20 14.5A8 8 0 0 1 9.5 4 8 8 0 1 0 20 14.5z"/>',
    'send'      => '<path d="m4 11.5 16-7.5-6.5 16-2.5-6.5z"/><path d="M11 13.5 20 4"/>',
    'plus'      => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
    'cash-in'   => '<path d="M12 3.5V15"/><path d="m7 10 5 5 5-5"/><path d="M4.5 20h15"/>',
    'cash-out'  => '<path d="M12 15.5V4"/><path d="m7 9 5-5 5 5"/><path d="M4.5 20h15"/>',
    'phone'     => '<rect x="7" y="2.5" width="10" height="19" rx="2.2"/><path d="M11 18h2"/>',
    'bolt'      => '<path d="M13 2.5 5 13.5h6l-1 8 8-11h-6z"/>',
    'flame'     => '<path d="M12 21c-3.6 0-6-2.4-6-5.6 0-3.2 2.4-4.9 3.4-7.6.5 1.5 1.2 2.4 2.2 3 .2-2.8 1.3-5.2 3.4-7.3-.2 3 .9 4.8 2 6.4.9 1.4 1.9 2.9 1.9 5.3 0 3.4-3 5.8-6.9 5.8z"/>',
    'droplet'   => '<path d="M12 3c3.2 4.2 6 7.4 6 10.6a6 6 0 0 1-12 0C6 10.4 8.8 7.2 12 3z"/>',
    'wifi'      => '<path d="M3 9.5a13 13 0 0 1 18 0"/><path d="M6.2 13a8.4 8.4 0 0 1 11.6 0"/><path d="M9.3 16.4a4 4 0 0 1 5.4 0"/><path d="M12 20h.01"/>',
    'cap'       => '<path d="m2 9 10-5 10 5-10 5z"/><path d="M6 11v5c0 1.4 2.7 3 6 3s6-1.6 6-3v-5"/><path d="M22 9v5"/>',
    'bag'       => '<path d="M5 8h14l-1 12.5H6z"/><path d="M9 10V6.5a3 3 0 0 1 6 0V10"/>',
    'qr'        => '<rect x="3.5" y="3.5" width="6.5" height="6.5" rx="1"/><rect x="14" y="3.5" width="6.5" height="6.5" rx="1"/><rect x="3.5" y="14" width="6.5" height="6.5" rx="1"/><path d="M14 14h3v3h-3zM20.5 14v.01M17 20.5h3.5M20.5 17.5v3"/>',
    'star'      => '<path d="m12 3.5 2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.2-4.1 5.8-.8z"/>',
    'eye'       => '<path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
    'eye-off'   => '<path d="m3 3 18 18"/><path d="M10.6 5.6A10 10 0 0 1 12 5.5c6.4 0 10 6.5 10 6.5a17 17 0 0 1-3.2 3.8"/><path d="M6.6 6.7C3.8 8.5 2 12 2 12s3.6 6.5 10 6.5c1.7 0 3.2-.4 4.5-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>',
    'file'      => '<path d="M6 2.5h8.5l4 4v15H6z"/><path d="M14 2.5V7h4.5"/><path d="M9 12h6M9 16h6"/>',
    'search'    => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
    'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
    'x'         => '<path d="m6 6 12 12M18 6 6 18"/>',
    'check'     => '<path d="m5 12.5 4.5 4.5L19 7"/>',
    'alert'     => '<path d="M12 3.5 2.5 20h19z"/><path d="M12 10v4M12 17v.01"/>',
    'info'      => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8v.01"/>',
    'back'      => '<path d="M19 12H5"/><path d="m11 6-6 6 6 6"/>',
    'copy'      => '<rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5.5A2.5 2.5 0 0 1 7.5 3H15"/>',
    'print'     => '<path d="M7 9V3h10v6"/><path d="M7 17H4v-6.5A1.5 1.5 0 0 1 5.5 9h13a1.5 1.5 0 0 1 1.5 1.5V17h-3"/><path d="M7 14h10v7H7z"/>',
    'download'  => '<path d="M12 3.5V16"/><path d="m7 11 5 5 5-5"/><path d="M4 20.5h16"/>',
    'logout'    => '<path d="M15 4h4v16h-4"/><path d="m10 8-4 4 4 4"/><path d="M6 12h10"/>',
    'lock'      => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
    'gift'      => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M5 12v8.5h14V12M12 8v12.5"/><path d="M12 8C10 5 6.5 4.6 6.5 6.6 6.5 8 9 8 12 8zm0 0c2-3 5.5-3.4 5.5-1.4C17.5 8 15 8 12 8z"/>',
    'shield'    => '<path d="M12 3 20 6v6c0 4.8-3.4 8-8 9-4.6-1-8-4.2-8-9V6z"/><path d="m9 12 2 2 4-4"/>',
    'receipt'   => '<path d="M6 2.5h12v19l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6"/>',
    'calendar'  => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
    'users'     => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c1-3.5 3.5-5 6.5-5s5.5 1.5 6.5 5"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18 15.2c2 .6 3.3 2.2 3.8 4.8"/>',
    'building'  => '<path d="M4 21V5l8-2.5V21"/><path d="M12 8h8v13"/><path d="M7 8h2M7 12h2M7 16h2M15 12h2M15 16h2M2.5 21h19"/>',
    'store'     => '<path d="M3 9h18"/><path d="M5 9v11h14V9"/><path d="m3 9 2-5h14l2 5"/><path d="M10 20v-5h4v5"/>',
    'lifebuoy'  => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><path d="m5.6 5.6 3.6 3.6M14.8 14.8l3.6 3.6M18.4 5.6l-3.6 3.6M9.2 14.8l-3.6 3.6"/>',
    'globe'     => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9S9.5 5.7 12 3z"/>',
    'wallet'    => '<path d="M3.5 7.5h15a2.5 2.5 0 0 1 2.5 2.5v8a2.5 2.5 0 0 1-2.5 2.5h-12A3 3 0 0 1 3.5 17.5z"/><path d="M3.5 7.5 15 3.5v4"/><path d="M16 14h2"/>',
    'split'     => '<circle cx="12" cy="6" r="1.5"/><circle cx="12" cy="18" r="1.5"/><path d="M5 12h14"/>',
    'arrow-in'  => '<path d="M17 7 7 17"/><path d="M7 9v8h8"/>',
    'arrow-out' => '<path d="M7 17 17 7"/><path d="M9 7h8v8"/>',
    'trash'     => '<path d="M4 7h16M9.5 7V4h5v3"/><path d="m6 7 1 13.5h10L18 7"/>',
    'pencil'    => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="m13 7 4 4"/>',
    'grid'      => '<rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="5" rx="1.5"/><rect x="13" y="10" width="8" height="11" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/>',
    'list'      => '<path d="M9 6h11M9 12h11M9 18h11"/><path d="m3.5 6 1 1 2-2M3.5 12l1 1 2-2M3.5 18l1 1 2-2"/>',
    'camera'    => '<path d="M4 8h3l2-3h6l2 3h3v11.5H4z"/><circle cx="12" cy="13" r="3.5"/>',
    'hand'      => '<path d="M7 11V6.5a1.5 1.5 0 0 1 3 0V11"/><path d="M10 10V4.5a1.5 1.5 0 0 1 3 0V10"/><path d="M13 10V5.5a1.5 1.5 0 0 1 3 0V12"/><path d="M16 9.5a1.5 1.5 0 0 1 3 0V14a7 7 0 0 1-7 7h-.5a6.5 6.5 0 0 1-5.3-2.8L3.5 14.5a1.6 1.6 0 0 1 2.4-2.1L7 13.5"/>',
];

/** An inline SVG icon. Decorative unless a label is given. */
function icon(string $name, string $label = ''): string
{
    $body = ICONS[$name] ?? ICONS['info'];
    $a11y = $label === ''
        ? ' aria-hidden="true"'
        : ' role="img" aria-label="' . e($label) . '"';
    return '<svg class="i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"'
         . ' stroke-linecap="round" stroke-linejoin="round"' . $a11y . '>' . $body . '</svg>';
}

/**
 * The engraved rosette behind the balance card, the kind printed on a
 * banknote. A circle rolled around inside a ring, drawn once per petal.
 */
function guilloche(int $petals = 28): string
{
    $paths = '';
    for ($i = 0; $i < $petals; $i++) {
        $deg = round(360 / $petals * $i, 2);
        $paths .= '<ellipse cx="200" cy="200" rx="150" ry="58" transform="rotate(' . $deg . ' 200 200)"/>';
    }
    return '<svg class="rosette" viewBox="0 0 400 400" aria-hidden="true" fill="none"'
         . ' stroke="currentColor" stroke-width=".6">' . $paths
         . '<circle cx="200" cy="200" r="178"/><circle cx="200" cy="200" r="186"/></svg>';
}
