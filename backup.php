<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Backup & Restore';
$extraCSS = ['backup.css'];
$extraJS = [];

$userId = $_SESSION['user_id'];

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>

<div class="app-content">
  <div class="page-header">
    <div>
      <h2><i class="fas fa-database"></i> Backup & Restore</h2>
      <p>Export or import your complete system data</p>
    </div>
  </div>

  <div id="backupAlertContainer"></div>

  <div class="backup-wrapper">

    <!-- CARD 1: BACKUP -->
    <div class="backup-card">
      <div class="backup-card-icon export">
        <i class="fas fa-download"></i>
      </div>
      <h3>Backup Database</h3>
      <p>Download a full export copy of your system data, settings, and records as a single .sql file.</p>
      <a href="includes/backup_handler.php?action=export" class="backup-btn export">
        <i class="fas fa-file-export"></i> Generate & Download Backup
      </a>
      <div class="backup-meta">
        <span><i class="fas fa-table"></i> 31 tables</span>
        <span><i class="fas fa-weight"></i> Complete data snapshot</span>
      </div>
    </div>

    <!-- CARD 2: RESTORE -->
    <div class="backup-card">
      <div class="backup-card-icon import">
        <i class="fas fa-upload"></i>
      </div>
      <h3>Restore Database</h3>
      <p>Upload a previously exported .sql backup file to restore your entire database on this system.</p>
      <div class="backup-upload-area">
        <input type="file" id="sqlFileInput" accept=".sql">
      </div>
      <button class="backup-btn import" id="restoreBtn" onclick="restoreBackup()">
        <i class="fas fa-file-import"></i> Upload & Restore Data
      </button>
      <div class="backup-meta" style="margin-top:10px;">
        <span><i class="fas fa-info-circle"></i> Only .sql files accepted</span>
      </div>
    </div>

  </div>
</div>

<script>
function restoreBackup() {
  var input = document.getElementById('sqlFileInput');
  var btn = document.getElementById('restoreBtn');
  var container = document.getElementById('backupAlertContainer');

  if (!input.files || !input.files[0]) {
    showAlert('Please select a .sql file first.', 'error');
    return;
  }

  var file = input.files[0];
  if (!file.name.toLowerCase().endsWith('.sql')) {
    showAlert('Invalid file format. Only .sql files are allowed.', 'error');
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restoring...';

  var formData = new FormData();
  formData.append('sql_file', file);

  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'includes/backup_handler.php?action=import', true);

  xhr.onload = function () {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-file-import"></i> Upload & Restore Data';

    try {
      var res = JSON.parse(xhr.responseText);
      if (res.success) {
        showAlert(res.message, 'success');
        input.value = '';
      } else {
        showAlert(res.message, 'error');
      }
    } catch (e) {
      showAlert('Invalid server response. Please try again.', 'error');
    }
  };

  xhr.onerror = function () {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-file-import"></i> Upload & Restore Data';
    showAlert('Network error. Please try again.', 'error');
  };

  xhr.send(formData);
}

function showAlert(msg, type) {
  var container = document.getElementById('backupAlertContainer');
  var icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', info: 'fa-info-circle' };
  var html = '<div class="backup-alert ' + type + '">' +
    '<i class="fas ' + (icons[type] || icons.info) + '"></i>' +
    '<span>' + msg + '</span>' +
    '<button class="alert-close" onclick="this.parentElement.remove()">&times;</button>' +
    '</div>';
  container.innerHTML = html;
  setTimeout(function () {
    var el = container.querySelector('.backup-alert');
    if (el) el.remove();
  }, 8000);
}
</script>

<?php require 'includes/footer.php'; ?>
