<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head(__('Favourites'), __('Ammu, the flatmate, the friend who always forgets. One tap from home.'), ['account.php', __('Home')],
                  '<a class="btn" href="#addFav">' . icon('plus') . e(__('Add someone')) . '</a>', badge: ['star', 5]) ?>
    <?= error_note($error) ?>

    <?php if ($suggestions): ?>
      <section class="card tight rise mb-5" style="--n:1">
        <span class="label"><?= __('You send to these folks a lot. Add them?') ?></span>
        <div class="chips">
          <?php foreach ($suggestions as $s): ?>
            <form method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="saved_phone" value="<?= e($s['phone']) ?>">
              <input type="hidden" name="label" value="<?= e($s['firstname'] . ' ' . $s['lastname']) ?>">
              <button class="chip" type="submit">+ <?= e($s['firstname'] . ' ' . $s['lastname']) ?>
                <span class="sr-only"><?= e(__(', sent to {n} times', ['n' => (int) $s['n']])) ?></span></button>
            </form>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if (!$favourites): ?>
      <div class="card reveal">
        <?= empty_state('star', __('No favourites yet'),
              __('Save the people you send to most and they show up right on your home screen.'),
              '<a class="btn" href="#addFav">' . icon('plus') . e(__('Add someone')) . '</a>', 3, 5) ?>
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
              <?= e($f['sends'] == 1 ? __('1 transfer') : __('{n} transfers', ['n' => (int) $f['sends']])) ?> · <span class="num"><?= e(money($f['total'])) ?></span> <?= __('in all') ?></p>
            <div class="row">
              <a class="btn btn-sm" href="send_money.php?to=<?= e($f['saved_phone']) ?>"><?= icon('send') ?><?= __('Send') ?></a>
              <details class="inline">
                <summary class="btn btn-quiet btn-sm"><?= __('Rename') ?></summary>
                <form method="post" class="panel">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="rename">
                  <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                  <label for="lbl<?= (int) $f['id'] ?>"><?= __('Name') ?></label>
                  <div class="row" style="flex-wrap:nowrap">
                    <input id="lbl<?= (int) $f['id'] ?>" name="label" maxlength="40" required value="<?= e($f['label']) ?>">
                    <button class="btn btn-sm" type="submit"><?= __('Save') ?></button>
                  </div>
                </form>
              </details>
              <form method="post" class="push" data-confirm="<?= e(__('Remove {name} from favourites?', ['name' => $f['label'] ?: $f['saved_phone']])) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                <button class="tool" type="submit" aria-label="<?= e(__('Remove {name}', ['name' => $f['label'] ?: $f['saved_phone']])) ?>"><?= icon('trash') ?></button>
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
    <a class="tool x" href="#" data-close aria-label="<?= e(__('Close')) ?>"><?= icon('x') ?></a>
    <h2 id="addFavTitle"><?= __('Add someone') ?></h2>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <div class="field">
        <label for="saved_phone"><?= __('Their number') ?></label>
        <input id="saved_phone" name="saved_phone" type="tel" inputmode="numeric" autocomplete="off"
               maxlength="11" pattern="01[3-9][0-9]{8}" required placeholder="<?= e(__('01XXXXXXXXX')) ?>">
      </div>
      <div class="field">
        <label for="label"><?= __('What do you call them?') ?> <span class="muted"><?= __('(optional)') ?></span></label>
        <input id="label" name="label" maxlength="40" placeholder="<?= e(__('Ammu, Rafid, landlord')) ?>">
      </div>
      <div class="row">
        <a class="btn btn-quiet" href="#" data-close><?= __('Cancel') ?></a>
        <button class="btn push" type="submit"><?= __('Save') ?></button>
      </div>
    </form>
  </div>
</dialog>
<?php include __DIR__ . '/partials/foot.php'; ?>
