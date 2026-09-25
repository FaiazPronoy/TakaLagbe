<footer class="foot">
  <div class="wrap">
    <span>© <?= date('Y') ?> <strong><?= e(APP_NAME) ?></strong> · Send. Pay. Save.</span>
    <?php if (($bodyClass ?? '') !== 'admin'): ?>
      <nav aria-label="Footer">
        <a href="about.php">About</a>
        <a href="supports.php">Help</a>
        <a href="currency.php">Currency converter</a>
      </nav>
    <?php endif; ?>
  </div>
</footer>
</body>
</html>
