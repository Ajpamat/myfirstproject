<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$userId = (int)$user['id'];
$storeId = (int)$user['store_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'profile') {
        $name = trim((string)($_POST['name'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));

        if ($name === '' || strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
            flash('error', 'Enter a name (up to 120 characters) and a valid email address (up to 190 characters).');
            redirect('profile.php');
        }

        try {
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ? AND store_id = ?');
            $stmt->execute([$name, $email, $userId, $storeId]);
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            flash('success', 'Your profile details have been updated.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                flash('error', 'That email address is already registered.');
            } else {
                error_log('TindaHub profile update failed: ' . $e->getMessage());
                flash('error', 'Could not update your profile. Please try again.');
            }
        }
        redirect('profile.php');
    }

    if ($action === 'password') {
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ? AND store_id = ?');
        $stmt->execute([$userId, $storeId]);
        $passwordHash = $stmt->fetchColumn();

        if (!is_string($passwordHash) || !password_verify($currentPassword, $passwordHash)) {
            flash('error', 'Your current password is incorrect.');
        } elseif (strlen($newPassword) < 8) {
            flash('error', 'Your new password must be at least 8 characters.');
        } elseif (!hash_equals($newPassword, $confirmPassword)) {
            flash('error', 'The new password and confirmation do not match.');
        } else {
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ? AND store_id = ?');
            $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId, $storeId]);
            flash('success', 'Your password has been changed.');
        }
        redirect('profile.php');
    }

    flash('error', 'That settings action is not recognized.');
    redirect('profile.php');
}

$stmt = $pdo->prepare('SELECT created_at FROM users WHERE id = ? AND store_id = ?');
$stmt->execute([$userId, $storeId]);
$joinedAt = $stmt->fetchColumn();
require __DIR__ . '/includes/layout.php';
page_header('Profile & settings', 'profile');

$initial = strtoupper(substr((string)($user['name'] ?? 'U'), 0, 1));
?>
<div class="page-head">
  <div>
    <div class="eyebrow">Your account</div>
    <h1>Profile &amp; settings</h1>
    <p class="subtitle">Manage your personal details, password, and workspace appearance.</p>
  </div>
</div>

<section class="profile-summary panel">
  <div class="profile-avatar" aria-hidden="true"><?= e($initial) ?></div>
  <div class="profile-summary-copy">
    <h2><?= e($user['name'] ?? '') ?></h2>
    <p><?= e($user['email'] ?? '') ?></p>
  </div>
  <span class="badge active"><?= e(ucfirst((string)($user['role'] ?? 'member'))) ?></span>
  <div class="profile-meta">
    <span>Store</span><strong><?= e($user['store_name'] ?? 'Your store') ?></strong>
    <?php if ($joinedAt): ?><span>Member since</span><strong><?= e(date('F Y', strtotime((string)$joinedAt))) ?></strong><?php endif; ?>
  </div>
</section>

<div class="profile-settings-grid">
  <section class="panel profile-settings-panel">
    <div class="panel-head"><div><h2>Personal details</h2><p>Update the name and email used for your account.</p></div></div>
    <form method="post" class="profile-settings-form">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="profile">
      <div class="field">
        <label for="profile-name">Full name</label>
        <input id="profile-name" name="name" value="<?= e($user['name'] ?? '') ?>" maxlength="120" autocomplete="name" required>
      </div>
      <div class="field">
        <label for="profile-email">Email address</label>
        <input id="profile-email" type="email" name="email" value="<?= e($user['email'] ?? '') ?>" maxlength="190" autocomplete="email" required>
        <span class="small-muted">You will use this email the next time you sign in.</span>
      </div>
      <button class="btn btn-primary" type="submit">Save profile</button>
    </form>
  </section>

  <section class="panel profile-settings-panel">
    <div class="panel-head"><div><h2>Password &amp; security</h2><p>Confirm your current password before choosing a new one.</p></div></div>
    <form method="post" class="profile-settings-form">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="password">
      <div class="field">
        <label for="current-password">Current password</label>
        <input id="current-password" type="password" name="current_password" autocomplete="current-password" required>
      </div>
      <div class="field">
        <label for="new-password">New password</label>
        <input id="new-password" type="password" name="new_password" minlength="8" autocomplete="new-password" required>
        <span class="small-muted">Use at least 8 characters.</span>
      </div>
      <div class="field">
        <label for="confirm-password">Confirm new password</label>
        <input id="confirm-password" type="password" name="confirm_password" minlength="8" autocomplete="new-password" required>
      </div>
      <button class="btn btn-primary" type="submit">Change password</button>
    </form>
  </section>

  <section class="panel profile-settings-panel profile-appearance-panel">
    <div class="panel-head"><div><h2>Appearance</h2><p>Choose how TindaHub looks on this browser.</p></div></div>
    <div class="profile-appearance-options" role="group" aria-label="Color theme">
      <button type="button" class="profile-theme-option" data-theme-choice="light" aria-pressed="false">
        <span class="profile-theme-preview profile-theme-preview-light" aria-hidden="true"></span>
        <span><strong>Light</strong><small>Bright and clear</small></span>
      </button>
      <button type="button" class="profile-theme-option" data-theme-choice="dark" aria-pressed="false">
        <span class="profile-theme-preview profile-theme-preview-dark" aria-hidden="true"></span>
        <span><strong>Dark</strong><small>Easy on the eyes</small></span>
      </button>
    </div>
    <div class="profile-accent-section">
      <h3>Accent color</h3>
      <p>Choose the color used for buttons, highlights, and active navigation.</p>
      <div class="profile-accent-options" role="group" aria-label="Accent color">
        <button type="button" class="profile-accent-option" data-accent-choice="emerald" aria-label="Emerald accent" aria-pressed="false">
          <span class="profile-accent-swatch accent-emerald" aria-hidden="true"></span><span>Emerald</span>
        </button>
        <button type="button" class="profile-accent-option" data-accent-choice="ocean" aria-label="Ocean blue accent" aria-pressed="false">
          <span class="profile-accent-swatch accent-ocean" aria-hidden="true"></span><span>Ocean</span>
        </button>
        <button type="button" class="profile-accent-option" data-accent-choice="sunrise" aria-label="Sunrise coral accent" aria-pressed="false">
          <span class="profile-accent-swatch accent-sunrise" aria-hidden="true"></span><span>Sunrise</span>
        </button>
        <button type="button" class="profile-accent-option" data-accent-choice="violet" aria-label="Violet accent" aria-pressed="false">
          <span class="profile-accent-swatch accent-violet" aria-hidden="true"></span><span>Violet</span>
        </button>
      </div>
    </div>
    <p class="profile-settings-note">Your appearance preferences are saved in this browser and apply across TindaHub pages.</p>
  </section>
</div>

<script>
(function () {
  var options = document.querySelectorAll('[data-theme-choice]');
  var accentOptions = document.querySelectorAll('[data-accent-choice]');

  function setTheme(theme) {
    var isDark = theme === 'dark';
    document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
    try {
      localStorage.setItem('tindahub_theme', isDark ? 'dark' : 'light');
    } catch (error) {
      console.warn('Could not save the theme preference.', error);
    }
    options.forEach(function (option) {
      option.setAttribute('aria-pressed', option.getAttribute('data-theme-choice') === theme ? 'true' : 'false');
    });
    var toggle = document.getElementById('themeToggle');
    if (toggle) toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
  }

  options.forEach(function (option) {
    option.addEventListener('click', function () {
      setTheme(option.getAttribute('data-theme-choice'));
    });
  });

  function setAccent(accent) {
    var allowedAccents = ['emerald', 'ocean', 'sunrise', 'violet'];
    if (allowedAccents.indexOf(accent) === -1) accent = 'emerald';
    document.documentElement.setAttribute('data-accent', accent);
    try {
      localStorage.setItem('tindahub_accent', accent);
    } catch (error) {
      console.warn('Could not save the accent color preference.', error);
    }
    accentOptions.forEach(function (option) {
      option.setAttribute('aria-pressed', option.getAttribute('data-accent-choice') === accent ? 'true' : 'false');
    });
  }

  accentOptions.forEach(function (option) {
    option.addEventListener('click', function () {
      setAccent(option.getAttribute('data-accent-choice'));
    });
  });

  setTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light');
  setAccent(document.documentElement.getAttribute('data-accent') || 'emerald');
})();
</script>
<?php page_footer(); ?>
