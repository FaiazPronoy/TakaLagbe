<footer class="foot">
  <div class="wrap">
    <span>© <?= date('Y') ?> <strong><?= e(APP_NAME) ?></strong> <?= __('· Send. Pay. Save.') ?></span>
    <?php if (($bodyClass ?? '') !== 'admin'): ?>
      <nav aria-label="<?= e(__('Footer')) ?>">
        <a href="about.php"><?= __('About') ?></a>
        <a href="supports.php"><?= __('Help') ?></a>
        <a href="currency.php"><?= __('Currency converter') ?></a>
      </nav>
    <?php endif; ?>
    <span class="made-by"><?= __('Made by Faiaz Abrar Zaman Pronoy and Sinthia Khandoker. All rights reserved.') ?></span>
  </div>
</footer>
</body>
</html>
