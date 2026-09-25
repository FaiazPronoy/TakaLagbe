<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head('QR code', 'Show yours to get paid. Scan theirs to pay. Easy.', ['account.php', 'Home'], badge: ['qr', 3]) ?>

    <div class="grid cols-2">
      <section class="card center rise" style="--n:1" aria-labelledby="myQr">
        <?= avatar($user['firstname'], $user['lastname'], $user['avatar_color'], 'mx') ?>
        <h2 class="m0" id="myQr"><?= e($user['firstname'] . ' ' . $user['lastname']) ?></h2>
        <p class="mono muted"><?= e($phone) ?></p>

        <div class="qr qr-lg settle" role="img" aria-label="QR code for <?= e($phone) ?>"><?= QrCode::svg($payload, 5) ?></div>

        <?php if (valid_amount($amount)): ?>
          <p class="big-amt" style="font-size:var(--step-2)">Asking for <?= e(money($amount)) ?></p>
        <?php endif; ?>
        <p class="small muted">Made right here by the app, pixel by pixel.</p>

        <form method="get" class="no-print mt-4" style="text-align:left">
          <div class="field">
            <label for="amount">Ask for a set amount <span class="muted">(optional)</span></label>
            <div class="money"><span aria-hidden="true">৳</span>
              <input id="amount" name="amount" type="number" step="0.01" inputmode="decimal"
                     min="<?= MIN_AMOUNT ?>" value="<?= e($amount) ?>"></div>
          </div>
          <div class="row">
            <button class="btn" type="submit">Update code</button>
            <button class="btn btn-quiet js-only" type="button" onclick="window.print()"><?= icon('print') ?>Print</button>
          </div>
        </form>
      </section>

      <section class="card rise no-print" style="--n:2" id="scan" aria-labelledby="scanTitle">
        <div class="card-h"><h2 id="scanTitle"><?= icon('camera') ?>Scan to pay</h2></div>
        <p class="note" id="scanUnsupported" hidden><?= icon('info') ?>
          <span>This browser cannot read QR codes from the camera. Type the number on the send money page instead.</span></p>
        <video id="scanVideo" playsinline muted hidden style="width:100%;border-radius:var(--r2);background:var(--ink)"></video>
        <p id="scanResult" class="small" aria-live="polite"></p>
        <div class="row">
          <button class="btn js-only" id="scanStart" type="button"><?= icon('camera') ?>Start camera</button>
          <a class="btn btn-quiet" href="sendmoney.php">Type a number instead</a>
        </div>
      </section>
    </div>
  </div>
</main>

<script>
/* The browser's own barcode reader where there is one. The picture never
   leaves the phone. */
(function () {
  var btn = document.getElementById('scanStart'), video = document.getElementById('scanVideo');
  var out = document.getElementById('scanResult'), stream = null, timer = null;
  if (!btn) return;
  if (!('BarcodeDetector' in window) || !navigator.mediaDevices) {
    document.getElementById('scanUnsupported').hidden = false;
    btn.hidden = true;
    return;
  }
  var detector = new BarcodeDetector({ formats: ['qr_code'] });
  function stop() {
    clearInterval(timer); timer = null;
    if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
    video.hidden = true;
    btn.lastChild.textContent = 'Start camera';
  }
  btn.addEventListener('click', function () {
    if (stream) { stop(); return; }
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }).then(function (s) {
      stream = s; video.srcObject = s; video.hidden = false; video.play();
      btn.lastChild.textContent = 'Stop camera';
      timer = setInterval(function () {
        detector.detect(video).then(function (codes) {
          if (!codes.length) return;
          // the text came off a code somebody else printed, so it only ever goes into textContent
          var parts = codes[0].rawValue.split('|');
          if (parts[0] !== 'TKL' || !/^01[3-9]\d{8}$/.test(parts[1])) {
            out.textContent = 'That is not a <?= e(APP_NAME) ?> code.';
            return;
          }
          stop();
          out.textContent = 'Found ' + (parts[2] || parts[1]) + '. Opening…';
          location.href = 'sendmoney.php?to=' + encodeURIComponent(parts[1])
                        + (parts[3] ? '&amount=' + encodeURIComponent(parts[3]) : '');
        }).catch(function () {});
      }, 350);
    }).catch(function () { out.textContent = 'The camera was not allowed.'; });
  });
})();
</script>
<?php include __DIR__ . '/partials/foot.php'; ?>
