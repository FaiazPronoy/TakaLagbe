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
      <h1><?= e($artTitle ?? 'Money that moves when you do.') ?></h1>
      <p><?= e($artText ?? 'Send, pay, save and split, from one wallet, with every taka accounted for.') ?></p>
      <ul>
        <li><?= icon('shield') ?>Every transfer locks both accounts and lands in one transaction</li>
        <li><?= icon('lock') ?>A separate six digit PIN guards anything that moves money</li>
        <li><?= icon('receipt') ?>A receipt and a ledger row for every attempt, even the refused ones</li>
      </ul>
    </div>
  </aside>

  <main class="auth-form" id="main">
