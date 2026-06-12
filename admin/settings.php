<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/../api/settings.php';
requireAdmin();

$pdo = getDb();
$settings = getAllSettings($pdo);
$groups = siteSettingsGroups();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = (string) ($_POST['action'] ?? 'save');

    if ($action === 'reset_group') {
        $groupId = (string) ($_POST['group_id'] ?? '');
        if (resetSettingsGroup($pdo, $groupId)) {
            $group = findSettingsGroup($groupId);
            flashSet('success', 'Reset “' . ($group['title'] ?? $groupId) . '” to defaults.');
        } else {
            flashSet('error', 'Could not reset that section.');
        }
        header('Location: ./settings.php#' . rawurlencode($groupId));
        exit;
    }

    if ($action === 'reset_all') {
        resetAllSiteSettings($pdo);
        flashSet('success', 'All site settings restored to defaults.');
        header('Location: ./settings.php');
        exit;
    }

    saveSettings($pdo, $_POST);
    flashSet('success', 'Site settings saved. Open the homepage to review your changes.');
    $hash = (string) ($_POST['return_section'] ?? '');
    header('Location: ./settings.php' . ($hash !== '' ? '#' . rawurlencode($hash) : ''));
    exit;
}

function renderSettingField(array $field, array $settings): void
{
    $key = (string) $field['key'];
    $type = (string) ($field['type'] ?? 'text');
    $value = (string) ($settings[$key] ?? '');
    $placeholder = (string) ($field['placeholder'] ?? '');
    ?>
    <div class="admin-setting-field">
      <label for="setting-<?= e($key) ?>">
        <span class="admin-setting-label"><?= e((string) $field['label']) ?></span>
        <?php if ($type === 'textarea'): ?>
          <textarea
            id="setting-<?= e($key) ?>"
            name="<?= e($key) ?>"
            placeholder="<?= e($placeholder) ?>"
            rows="3"
          ><?= e($value) ?></textarea>
        <?php else: ?>
          <input
            id="setting-<?= e($key) ?>"
            type="text"
            name="<?= e($key) ?>"
            value="<?= e($value) ?>"
            placeholder="<?= e($placeholder) ?>"
            <?= $type === 'url' ? 'spellcheck="false"' : '' ?>
          />
        <?php endif; ?>
      </label>
      <p class="admin-setting-help"><?= e((string) $field['help']) ?></p>
      <p class="admin-setting-affects"><strong>Changes:</strong> <?= e((string) $field['affects']) ?></p>
    </div>
    <?php
}

renderAdminHeader('Site settings', 'settings.php');
?>
<div class="admin-settings-intro admin-panel">
  <div class="admin-toolbar">
    <div>
      <h2>Homepage text &amp; links</h2>
      <p class="admin-meta">
        These settings control headings, descriptions, buttons, and official links on
        <code>index.html</code>. Save, then preview the live homepage.
      </p>
    </div>
    <div class="admin-settings-intro-actions">
      <a class="admin-btn primary" href="../index.html" target="_blank" rel="noopener">Preview homepage</a>
    </div>
  </div>

  <div class="admin-settings-notice">
    <p><strong>Not controlled here</strong> — use the other admin pages for dynamic content:</p>
    <ul class="admin-settings-notice-list">
      <li><a href="./posts.php">Posts</a> — news &amp; story cards (#updates)</li>
      <li><a href="./spotlight.php">Recent events</a> — homepage cards, hero image, and highlights gallery</li>
      <li><a href="./appeals.php">Appeal requests</a> — donation/appeal form submissions</li>
      <li><a href="./messages.php">Messages</a> — contact form inbox</li>
      <li><a href="./subscribers.php">Subscribers</a> — email list</li>
    </ul>
  </div>

  <nav class="admin-settings-jump" aria-label="Settings sections">
    <?php foreach ($groups as $group): ?>
      <a href="#<?= e((string) $group['id']) ?>"><?= e((string) $group['title']) ?></a>
    <?php endforeach; ?>
  </nav>
</div>

<form class="admin-form admin-settings-form" method="post">
  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />
  <input type="hidden" name="action" value="save" />
  <input type="hidden" id="returnSection" name="return_section" value="" />

  <?php foreach ($groups as $group): ?>
    <?php $groupId = (string) $group['id']; ?>
    <section class="admin-panel admin-settings-group" id="<?= e($groupId) ?>">
      <div class="admin-settings-group-head">
        <div>
          <h2><?= e((string) $group['title']) ?></h2>
          <p class="admin-meta"><?= e((string) $group['description']) ?></p>
        </div>
        <div class="admin-settings-group-actions">
          <?php if (!empty($group['preview'])): ?>
            <a class="admin-btn" href="<?= e((string) $group['preview']) ?>" target="_blank" rel="noopener">Preview section</a>
          <?php endif; ?>
        </div>
      </div>

      <?php if (!empty($group['managed_elsewhere'])): ?>
        <p class="admin-settings-managed"><?= e((string) $group['managed_elsewhere']) ?></p>
      <?php endif; ?>

      <?php foreach ($group['fields'] as $item): ?>
        <?php if (isset($item['row']) && is_array($item['row'])): ?>
          <div class="admin-form-row admin-settings-row">
            <?php foreach ($item['row'] as $rowField): ?>
              <?php renderSettingField($rowField, $settings); ?>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <?php renderSettingField($item, $settings); ?>
        <?php endif; ?>
      <?php endforeach; ?>

      <div class="admin-settings-group-foot">
        <button class="admin-btn" type="submit" data-return-section="<?= e($groupId) ?>">Save &amp; stay in this section</button>
      </div>
    </section>
  <?php endforeach; ?>

  <div class="admin-settings-savebar admin-panel">
    <p class="admin-meta">Saving updates the homepage immediately after you refresh or open the preview.</p>
    <button class="primary" type="submit">Save all settings</button>
  </div>
</form>

<section class="admin-panel admin-settings-danger-zone">
  <h2>Restore defaults</h2>
  <p class="admin-meta">Reset stored values to the project defaults. This does not delete posts, recent events, or messages.</p>
  <div class="admin-settings-reset-grid">
    <?php foreach ($groups as $group): ?>
      <form method="post" onsubmit="return confirm('Reset “<?= e((string) $group['title']) ?>” to defaults?');">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />
        <input type="hidden" name="action" value="reset_group" />
        <input type="hidden" name="group_id" value="<?= e((string) $group['id']) ?>" />
        <button class="admin-btn" type="submit">Reset <?= e((string) $group['title']) ?></button>
      </form>
    <?php endforeach; ?>
  </div>
  <form method="post" style="margin-top:14px" onsubmit="return confirm('Reset ALL site settings to defaults?');">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />
    <input type="hidden" name="action" value="reset_all" />
    <button class="admin-btn danger" type="submit">Reset everything</button>
  </form>
</section>

<script>
  (function () {
    const returnInput = document.getElementById("returnSection");
    document.querySelectorAll("[data-return-section]").forEach((btn) => {
      btn.addEventListener("click", () => {
        if (returnInput) returnInput.value = btn.dataset.returnSection || "";
      });
    });
    const mainSave = document.querySelector(".admin-settings-savebar button[type='submit']");
    if (mainSave && returnInput) {
      mainSave.addEventListener("click", () => {
        returnInput.value = "";
      });
    }
  })();
</script>
<?php renderAdminFooter(); ?>
