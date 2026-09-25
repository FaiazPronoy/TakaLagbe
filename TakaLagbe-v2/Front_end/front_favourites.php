<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head('Favourites', 'The people you send to most, one tap from the home screen.', ['account.php', 'Home'],
                  '<a class="btn" href="#addFav">' . icon('plus') . 'Add someone</a>') ?>
    <?= error_note($error) ?>

    <?php if ($suggestions): ?>
      <section class="card tight rise mb-5" style="--n:1">
        <span class="label">You send to these people often</span>
        <div class="chips">
          <?php foreach ($suggestions as $s): ?>
            <form method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="saved_phone" value="<?= e($s['phone']) ?>">
              <input type="hidden" name="label" value="<?= e($s['firstname'] . ' ' . $s['lastname']) ?>">
              <button class="chip" type="submit">+ <?= e($s['firstname'] . ' ' . $s['lastname']) ?>
                <span class="sr-only">, sent to <?= (int) $s['n'] ?> times</span></button>
            </form>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if (!$favourites): ?>
      <div class="card reveal">
        <?= empty_state('star', 'No favourites yet',
              'Save the numbers you send to most. They appear on your home screen, and sending to them takes one tap.',
              '<a class="btn" href="#addFav">Add your first</a>') ?>
      </div>
    <?php else: ?>
      <div class="grid cols-3">
        <?php foreach ($favourites as $i => $f): ?>
          <section class="card lift reveal" style="--n:<?= $i % 3 ?>" aria-label="<?= e($f['label'] ?: $f['saved_phone']) ?>">
            <div class="row" style="flex-wrap:nowrap">
              <?= avatar($f['firstname'], $f['lastname'], $f['avatar_color']) ?>
              <span style="min-width:0">
                <strong style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($f['label'] ?: $f['saved_phone']) ?></strong>
                <span class="small muted mono"><?= e($f['saved_phone']) ?></span>
              </span>
            </div>
            <p class="small muted" style="margin:var(--s3) 0">
              <?= (int) $f['sends'] ?> transfer<?= $f['sends'] == 1 ? '' : 's' ?> · <span class="num"><?= e(money($f['total'])) ?></span> in all</p>
            <div class="row">
              <a class="btn btn-sm" href="sendmoney.php?to=<?= e($f['saved_phone']) ?>"><?= icon('send') ?>Send</a>
              <details class="inline">
                <summary class="btn btn-quiet btn-sm">Rename</summary>
                <form method="post" class="panel">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="rename">
                  <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                  <label for="lbl<?= (int) $f['id'] ?>">Name</label>
                  <div class="row" style="flex-wrap:nowrap">
                    <input id="lbl<?= (int) $f['id'] ?>" name="label" maxlength="40" required value="<?= e($f['label']) ?>">
                    <button class="btn btn-sm" type="submit">Save</button>
                  </div>
                </form>
              </details>
              <form method="post" class="push" data-confirm="Remove <?= e($f['label'] ?: $f['saved_phone']) ?> from favourites?">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                <button class="tool" type="submit" aria-label="Remove <?= e($f['label'] ?: $f['saved_phone']) ?>"><?= icon('trash') ?></button>
              </form>
            </div>
          </section>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<dialog id="addFav" aria-labelledby="addFavTitle">
  <div class="sheet">
    <a class="tool x" href="#" data-close aria-label="Close"><?= icon('x') ?></a>
    <h2 id="addFavTitle">Add a favourite</h2>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <div class="field">
        <label for="saved_phone">Their number</label>
        <input id="saved_phone" name="saved_phone" type="tel" inputmode="numeric" autocomplete="off"
               maxlength="11" pattern="01[3-9][0-9]{8}" required placeholder="01XXXXXXXXX">
      </div>
      <div class="field">
        <label for="label">Call them <span class="muted">(optional)</span></label>
        <input id="label" name="label" maxlength="40" placeholder="Ammu, Rafid, landlord">
      </div>
      <div class="row">
        <a class="btn btn-quiet" href="#" data-close>Cancel</a>
        <button class="btn push" type="submit">Save</button>
      </div>
    </form>
  </div>
</dialog>
<?php include __DIR__ . '/partials/foot.php'; ?>
