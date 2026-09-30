<?php
/* Opening markup for every page, signed in or not.
   Set $pageTitle (and $bodyClass if the page needs one) before including. */
require_once __DIR__ . '/../../lib/view.php';

$pageTitle = $pageTitle ?? APP_NAME;
$bodyClass = $bodyClass ?? '';
// the front doors say what the app is; inner pages say where you are
$tabTitle = !empty($frontDoor)
    ? APP_NAME . ' | ' . __('A Money Transaction System')
    : $pageTitle . ' · ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="<?= lang() ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light dark">
<meta name="description" content="TakaLagbe! is a mobile money wallet: send money, pay bills, save and split.">
<title><?= e($tabTitle) ?></title>
<script>
/* before anything paints: the saved theme, and a class that says script is on */
(function () {
  var d = document.documentElement;
  d.classList.add('js');
  var t = null;
  try { t = localStorage.getItem('tkl-theme'); } catch (e) {}
  if (!t) t = window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  d.setAttribute('data-theme', t);
})();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Nunito:wght@400;500;600;700;800&family=Poppins:wght@600;700&display=swap">
<link rel="stylesheet" href="Assets/css/app.css?v=<?= e(APP_VERSION) ?>">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><circle cx='16' cy='16' r='15' fill='%23FFB81C'/><circle cx='16' cy='16' r='12' fill='none' stroke='%23FFE39A' stroke-width='1.5'/><text x='16' y='22' font-size='16' font-family='Arial' font-weight='bold' fill='%230B6B4F' text-anchor='middle'>৳</text></svg>">
<?php
/* the words the script shows, in the reader's language */
$__jsWords = [
    'Switch to light theme',
    'Switch to dark theme',
    'Sending…',
    'Amount',
    'Charge',
    'Cashback',
    'Balance after',
    'That is more than you have.',
    'Operator: {name}',
    'Split {n} ways, {each} each, you included.',
    'Add the total and at least one number to see each share.',
    'Show balance',
    'Hide balance',
    'Show what you typed',
    'Hide it again',
    'Copied',
];
?>
<script>window.TKL_TEXT = <?= json_encode(array_combine($__jsWords, array_map('__', $__jsWords)),
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<script src="Assets/js/app.js?v=<?= e(APP_VERSION) ?>" defer></script>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip" href="#main"><?= __('Skip to content') ?></a>
<div class="veil" aria-hidden="true"></div>
