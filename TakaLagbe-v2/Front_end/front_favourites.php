<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="wrap section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Favourites</h1>
    <button class="btn btn-primary btn-sm" onclick="openModal('addFav')">Add a number</button>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <?php if ($suggestions): ?>
    <div class="card" style="margin-bottom:1rem">
      <div class="card-title">People you send to often</div>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <?php foreach ($suggestions as $s): ?>
          <form method="post" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="saved_phone" value="<?= e($s['phone']) ?>">
            <input type="hidden" name="label" value="<?= e($s['firstname'] . ' ' . $s['lastname']) ?>">
            <button class="chip" type="submit">
              + <?= e($s['firstname'] . ' ' . $s['lastname']) ?>
              <span class="muted">(<?= (int) $s['n'] ?>)</span>
            </button>
          </form>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!$favourites): ?>
    <div class="card empty">
      <div class="big">&#11088;</div>
      <h3>No favourites yet</h3>
      <p class="muted">Save the numbers you send to most and they appear on your home screen.</p>
      <button class="btn btn-primary" onclick="openModal('addFav')">Add your first one</button>
    </div>
  <?php else: ?>
    <div class="grid grid-3">
      <?php foreach ($favourites as $f): ?>
        <div class="card">
          <div style="display:flex;gap:.8rem;align-items:center">
            <span class="avatar" style="background:<?= e($f['avatar_color'] ?: '#1E3A63') ?>">
              <?= e(initials($f['firstname'], $f['lastname'])) ?>
            </span>
            <div style="min-width:0;flex:1">
              <div style="font-weight:800"><?= e($f['label'] ?: $f['saved_phone']) ?></div>
              <div class="tiny muted mono"><?= e($f['saved_phone']) ?></div>
            </div>
          </div>
          <div class="between tiny muted" style="margin:.8rem 0">
            <span><?= (int) $f['sends'] ?> transfer(s)</span>
            <strong><?= e(money($f['total'])) ?></strong>
          </div>
          <div style="display:flex;gap:.4rem">
            <a class="btn btn-primary btn-sm btn-block"
               href="sendmoney.php?to=<?= e($f['saved_phone']) ?>">Send</a>
            <button class="btn btn-ghost btn-sm" type="button"
                    onclick="renameFav(<?= (int) $f['id'] ?>, <?= json_encode($f['label']) ?>)">Rename</button>
            <form method="post" data-confirm="Remove this favourite?">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
              <button class="btn btn-ghost btn-sm" type="submit">&#10005;</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<div class="modal" id="addFav">
  <div class="modal-box">
    <h2>Add a favourite</h2>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <div class="field">
        <label for="saved_phone">Mobile number</label>
        <input id="saved_phone" name="saved_phone" type="tel" inputmode="numeric"
               maxlength="11" pattern="01[3-9][0-9]{8}" required placeholder="01XXXXXXXXX">
      </div>
      <div class="field">
        <label for="label">Name it <span class="muted tiny">(optional)</span></label>
        <input id="label" name="label" maxlength="40" placeholder="Amma, Rafid, landlord">
      </div>
      <div style="display:flex;gap:.5rem">
        <button class="btn btn-ghost btn-block" type="button" onclick="closeModal('addFav')">Cancel</button>
        <button class="btn btn-primary btn-block" type="submit">Save</button>
      </div>
    </form>
  </div>
</div>

<div class="modal" id="renameFav">
  <div class="modal-box">
    <h2>Rename</h2>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="rename">
      <input type="hidden" name="id" id="renameId">
      <div class="field">
        <label for="renameLabel">Name</label>
        <input id="renameLabel" name="label" maxlength="40" required>
      </div>
      <div style="display:flex;gap:.5rem">
        <button class="btn btn-ghost btn-block" type="button" onclick="closeModal('renameFav')">Cancel</button>
        <button class="btn btn-primary btn-block" type="submit">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
function renameFav(id, label) {
  document.getElementById('renameId').value = id;
  document.getElementById('renameLabel').value = label || '';
  openModal('renameFav');
}
</script>
<?php include __DIR__ . '/partials/foot.php'; ?>
