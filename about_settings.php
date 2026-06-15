<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'About & Partner Settings';
$extraCSS = ['about_settings.css'];
$extraJS = ['about_settings.js'];

// Fetch settings
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        site_name VARCHAR(255),
        site_description TEXT,
        partner_details TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}
$stmt = $pdo->prepare("SELECT * FROM system_settings LIMIT 1");
$stmt->execute();
$settings = $stmt->fetch();

$aboutText = $settings['about_text'] ?? '';
$partnerName = $settings['partner_name'] ?? 'SITM College (Sanskaram Institute of Technology & Management)';
$partnerDetails = $settings['partner_details'] ?? '';
$lastUpdated = $settings['last_updated'] ?? '';

// Parse partner details into list items
$detailLines = [];
if (!empty($partnerDetails)) {
    $parts = array_map('trim', explode('|', $partnerDetails));
    foreach ($parts as $part) {
        if (strpos($part, ':') !== false) {
            list($key, $val) = array_map('trim', explode(':', $part, 2));
            $detailLines[] = ['icon' => getDetailIcon($key), 'label' => $key, 'value' => $val];
        } else {
            $detailLines[] = ['icon' => 'fa-circle', 'label' => '', 'value' => $part];
        }
    }
}

function getDetailIcon($key) {
    $k = strtolower($key);
    if (strpos($k, 'affiliated') !== false) return 'fa-university';
    if (strpos($k, 'program') !== false || strpos($k, 'course') !== false) return 'fa-graduation-cap';
    if (strpos($k, 'location') !== false) return 'fa-map-marker-alt';
    if (strpos($k, 'established') !== false) return 'fa-calendar-alt';
    if (strpos($k, 'accredit') !== false) return 'fa-certificate';
    return 'fa-circle';
}

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>

<div class="app-content">
  <div class="page-header">
    <div>
      <h2><i class="fas fa-info-circle"></i> About & Partner Settings</h2>
      <p>Manage system information and official partner details</p>
    </div>
  </div>

  <div class="about-wrapper">

    <!-- LEFT: About Section -->
    <div class="about-card">
      <div class="about-card-header">
        <h3><i class="fas fa-info-circle"></i> About This System</h3>
        <button class="btn-edit-about" id="editAboutBtn"><i class="fas fa-pen"></i> Edit</button>
      </div>

      <!-- Display -->
      <div class="about-content" id="aboutDisplay">
        <?php if (!empty($aboutText)): ?>
          <p><?= nl2br(htmlspecialchars($aboutText)) ?></p>
        <?php else: ?>
          <p style="color:#6c7293;">No description set. Click Edit to add information about this system.</p>
        <?php endif; ?>
      </div>

      <!-- Edit Form -->
      <div class="about-edit-form" id="aboutEditForm">
        <form method="POST" action="about_settings_handler.php">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="update_about">
          <div class="form-group">
            <label for="aboutText">Description</label>
            <textarea id="aboutText" name="about_text" rows="8" placeholder="Describe what this application is built for..."><?= htmlspecialchars($aboutText) ?></textarea>
          </div>
          <div class="btn-group-about">
            <button type="submit" class="btn-save-about"><i class="fas fa-save"></i> Save Changes</button>
            <button type="button" class="btn-cancel-about" id="cancelAboutBtn"><i class="fas fa-times"></i> Cancel</button>
          </div>
        </form>
      </div>

      <div class="last-updated">
        <i class="far fa-clock"></i> Last updated: <?= $lastUpdated ? date('d M Y, h:i A', strtotime($lastUpdated)) : 'Never' ?>
      </div>
    </div>

    <!-- RIGHT: Partner Section -->
    <div class="about-card">
      <div class="about-card-header">
        <h3><i class="fas fa-handshake"></i> Official Partner</h3>
        <button class="btn-edit-about" id="editPartnerBtn"><i class="fas fa-pen"></i> Edit</button>
      </div>

      <!-- Display -->
      <div class="partner-display" id="partnerDisplay">
        <div class="partner-logo-frame">
          <i class="fas fa-graduation-cap"></i>
        </div>
        <h4 class="partner-name"><?= htmlspecialchars($partnerName) ?></h4>
        <?php if (!empty($detailLines)): ?>
          <ul class="partner-detail-list">
            <?php foreach ($detailLines as $line): ?>
              <li><i class="fas <?= $line['icon'] ?>"></i> <strong><?= htmlspecialchars($line['label']) ?>:</strong> <?= htmlspecialchars($line['value']) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p style="color:#6c7293;font-size:0.82rem;">No partner details configured.</p>
        <?php endif; ?>
      </div>

      <!-- Edit Form -->
      <div class="partner-edit-form" id="partnerEditForm">
        <form method="POST" action="about_settings_handler.php">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="update_partner">
          <div class="form-group">
            <label for="partnerName">Institute Name</label>
            <input type="text" id="partnerName" name="partner_name" value="<?= htmlspecialchars($partnerName) ?>" placeholder="e.g. SITM College">
          </div>
          <div class="form-group">
            <label for="partnerDetails">Details <span style="color:#6c7293;font-weight:400;">(one per line, use <strong>Label:</strong> Value format)</span></label>
            <textarea id="partnerDetails" name="partner_details" rows="6" placeholder="Affiliated to: RTU, Kota&#10;Program: BCA AI/ML&#10;Location: Jhunjhunu, Rajasthan"><?php
              // Convert pipe-separated back to newlines for editing
              if (!empty($partnerDetails)) {
                  echo htmlspecialchars(str_replace('|', PHP_EOL, $partnerDetails));
              }
            ?></textarea>
          </div>
          <div class="btn-group-partner">
            <button type="submit" class="btn-save-partner"><i class="fas fa-save"></i> Save Changes</button>
            <button type="button" class="btn-cancel-partner" id="cancelPartnerBtn"><i class="fas fa-times"></i> Cancel</button>
          </div>
        </form>
      </div>

      <div class="last-updated">
        <i class="far fa-clock"></i> Last updated: <?= $lastUpdated ? date('d M Y, h:i A', strtotime($lastUpdated)) : 'Never' ?>
      </div>
    </div>

  </div>
</div>

<?php require 'includes/footer.php'; ?>
