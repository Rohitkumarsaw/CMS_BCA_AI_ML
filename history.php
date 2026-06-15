<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Activity History';
$extraCSS = ['history.css'];
$extraJS = ['history.js'];

$userId = $_SESSION['user_id'];

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>
<div class="app-content">
  <div class="page-header">
    <div>
      <h2><i class="fas fa-history" style="color:#0090e7;"></i> Activity History</h2>
      <p>Track all changes and actions across the system</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-primary btn-sm" onclick="loadMore()" id="loadMoreBtn" style="display:none;"><i class="fas fa-sync-alt"></i> Load More</button>
      <a href="history_handler.php?action=csv" class="btn btn-primary btn-sm"><i class="fas fa-download"></i> Download CSV</a>
    </div>
  </div>
  <div class="history-wrapper">
            <div class="section-search-container">
                <i class="fas fa-search section-search-icon"></i>
                <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#historyList">
            </div>
    <div class="history-timeline">
      <div class="history-timeline-header">
        <h3><i class="fas fa-stream"></i> Recent Activity</h3>
      </div>
      <div id="historyList">
        <div class="history-empty">
          <i class="fas fa-history"></i>
          <h5>No activity yet</h5>
          <p>Actions you perform across the portal will be logged here.</p>
        </div>
      </div>
      <div id="historyLoader" style="text-align:center;padding:20px;display:none;">
        <i class="fas fa-spinner fa-spin" style="color:#8f94a8;font-size:1.2rem;"></i>
      </div>
    </div>
  </div>
</div>
<?php require 'includes/footer.php'; ?>