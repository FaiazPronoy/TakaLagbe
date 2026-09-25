<footer class="foot">
  <div class="wrap">
    <span><strong><?= e(APP_NAME) ?></strong> v<?= e(APP_VERSION) ?> · made by second years for CSE370 at BRAC University, fuelled by cha.
      Not a real bank, so no real money moves.</span>
    <?php if (($bodyClass ?? '') !== 'admin'): ?>
      <nav aria-label="Footer">
        <a href="about.php">About</a>
        <a href="supports.php">Support</a>
        <a href="currency.php">Currency converter</a>
      </nav>
    <?php endif; ?>
  </div>
</footer>
</body>
</html>
