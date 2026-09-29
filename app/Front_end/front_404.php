<?php
include __DIR__ . '/partials/head.php';
if (is_logged_in()) {
    include __DIR__ . '/partials/nav.php';
}
?>
<main id="main" class="page">
  <div class="wrap narrow">
    <div class="card rise">
      <?= empty_state('search', 'Nothing here',
            'That page or record does not exist, or it belongs to someone else.',
            '<a class="btn" href="' . (is_logged_in() ? 'account.php' : 'login.php') . '">'
            . (is_logged_in() ? 'Back to home' : 'Go to sign in') . '</a>', 1) ?>
    </div>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
