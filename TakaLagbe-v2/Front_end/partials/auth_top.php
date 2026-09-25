<?php
/* The two column layout the sign in pages share: the engraved panel on one
   side and the form on the other. Set $pageTitle, $artTitle, $artText. */
include __DIR__ . '/head.php';
?>
<div class="auth">
  <aside class="auth-art">
    <?= guilloche(32) ?>
    <a class="brand" href="login.php"><span class="mark">৳</span><?= e(APP_NAME) ?></a>
    <div class="rise">
      <h1><?= e($artTitle ?? 'Taka lagbe? Say less.') ?></h1>
      <p><?= e($artText ?? 'Send money to friends, pay the current bill, split the kacchi, stash some for Eid. All from one wallet.') ?></p>
      <ul>
        <li><?= icon('shield') ?>Money never goes missing halfway, even if two things happen at once</li>
        <li><?= icon('lock') ?>A separate PIN for anything that moves money</li>
        <li><?= icon('receipt') ?>A receipt for everything, even the ones that bounced</li>
      </ul>
    </div>
  </aside>

  <main class="auth-form" id="main">
