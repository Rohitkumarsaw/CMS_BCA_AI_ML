<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Academic Inventory & Planner';
$extraCSS = ['planner.css'];
$extraJS = ['planner.js'];

$userId = $_SESSION['user_id'];

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS shopping_planner (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        item_name VARCHAR(255) NOT NULL,
        quantity VARCHAR(100),
        estimated_cost DECIMAL(10,2),
        status ENUM('pending','purchased') DEFAULT 'pending',
        target_date DATE,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS current_inventory (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        item_name VARCHAR(255) NOT NULL,
        quantity VARCHAR(100),
        category VARCHAR(100),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}

// Fetch shopping items
$stmt = $pdo->prepare("SELECT * FROM shopping_planner WHERE user_id = ? ORDER BY FIELD(status,'pending','purchased'), target_date ASC");
$stmt->execute([$userId]);
$shoppingItems = $stmt->fetchAll();

// Fetch inventory items
$stmt = $pdo->prepare("SELECT * FROM current_inventory WHERE user_id = ? ORDER BY item_name ASC");
$stmt->execute([$userId]);
$inventoryItems = $stmt->fetchAll();

// Count stats
$pendingCount = 0;
$urgentCount = 0;
foreach ($shoppingItems as $s) {
    if ($s['status'] === 'pending') $pendingCount++;
    if ($s['status'] === 'pending' && $s['target_date']) {
        $daysLeft = (strtotime($s['target_date']) - time()) / 86400;
        if ($daysLeft >= 0 && $daysLeft <= 7) $urgentCount++;
    }
}

$availableCount = 0;
$lowCount = 0;
$outCount = 0;
foreach ($inventoryItems as $inv) {
    if ($inv['availability_status'] === 'Available') $availableCount++;
    elseif ($inv['availability_status'] === 'Running Low') $lowCount++;
    elseif ($inv['availability_status'] === 'Out of Stock') $outCount++;
}

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>

<div class="app-content">
  <div class="page-header">
    <div>
      <h2><i class="fas fa-clipboard-list"></i> Academic Inventory & Shopping Planner</h2>
      <p>Track what you need to buy and what you already have</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <span style="font-size:0.75rem;color:#8f94a8;"><?php echo date('l, d M Y'); ?></span>
    </div>
  </div>

  <!-- Quick Stats -->
  <div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
      <div class="planner-card text-center" style="padding:14px;">
        <div style="font-size:1.3rem;font-weight:800;color:#fbbf24;"><?= $pendingCount ?></div>
        <div style="font-size:0.7rem;color:#8f94a8;text-transform:uppercase;letter-spacing:0.5px;">Pending</div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="planner-card text-center" style="padding:14px;">
        <div style="font-size:1.3rem;font-weight:800;color:#fc424a;"><?= $urgentCount ?></div>
        <div style="font-size:0.7rem;color:#8f94a8;text-transform:uppercase;letter-spacing:0.5px;">Urgent</div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="planner-card text-center" style="padding:14px;">
        <div style="font-size:1.3rem;font-weight:800;color:#00d25b;"><?= $availableCount ?></div>
        <div style="font-size:0.7rem;color:#8f94a8;text-transform:uppercase;letter-spacing:0.5px;">Available</div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="planner-card text-center" style="padding:14px;">
        <div style="font-size:1.3rem;font-weight:800;color:#fca5a5;"><?= $outCount + $lowCount ?></div>
        <div style="font-size:0.7rem;color:#8f94a8;text-transform:uppercase;letter-spacing:0.5px;">Low / Out</div>
      </div>
    </div>
  </div>

  <div class="planner-wrapper">

    <!-- LEFT COLUMN: Shopping List -->
    <div class="planner-left">
      <div class="planner-card">
        <div class="planner-card-header">
          <h3><i class="fas fa-cart-plus"></i> To-Buy List</h3>
          <span style="font-size:0.72rem;color:#8f94a8;"><?= count($shoppingItems) ?> items</span>
        </div>
        <div class="planner-list">
          <?php if (empty($shoppingItems)): ?>
            <div class="planner-empty">
              <i class="fas fa-cart-plus"></i>
              <h5>No items in shopping list</h5>
              <p>Add items you need to purchase</p>
            </div>
          <?php else: ?>
            <?php foreach ($shoppingItems as $item):
              $isPurchased = $item['status'] === 'purchased';
              $isApproaching = false;
              if (!$isPurchased && $item['target_date']) {
                  $daysLeft = (strtotime($item['target_date']) - time()) / 86400;
                  $isApproaching = ($daysLeft >= 0 && $daysLeft <= 7);
              }
              $tileClass = $isPurchased ? 'purchased' : ($isApproaching ? 'approaching' : '');
              $toggleIcon = $isPurchased ? 'fa-undo' : 'fa-check-circle';
              $toggleTitle = $isPurchased ? 'Mark pending' : 'Mark purchased';
            ?>
              <div class="planner-tile <?= $tileClass ?>" data-id="<?= $item['id'] ?>">
                <div class="planner-tile-top">
                  <h4 class="planner-tile-title"><?= htmlspecialchars($item['item_name']) ?></h4>
                  <div class="planner-tile-tags">
                    <?php if ($isPurchased): ?>
                      <span class="planner-tag planner-tag-purchased">Purchased</span>
                    <?php elseif ($isApproaching): ?>
                      <span class="planner-tag planner-tag-urgent">Urgent</span>
                    <?php else: ?>
                      <span class="planner-tag planner-tag-pending">Pending</span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="planner-tile-meta">
                  <?php if (!empty($item['reason_why'])): ?>
                    <div class="planner-tile-reason"><strong>Reason:</strong> <?= htmlspecialchars($item['reason_why']) ?></div>
                  <?php endif; ?>
                  <?php if (!empty($item['purpose_work'])): ?>
                    <div class="planner-tile-purpose"><strong>Purpose:</strong> <?= htmlspecialchars($item['purpose_work']) ?></div>
                  <?php endif; ?>
                </div>
                <div class="planner-tile-footer">
                  <div class="planner-tile-date">
                    <i class="far fa-calendar-alt"></i>
                    <span class="planner-tile-date-text"><?= $item['target_date'] ? date('d M Y', strtotime($item['target_date'])) : 'No date set' ?></span>
                  </div>
                  <div class="planner-tile-actions">
                    <button class="planner-action-btn action-toggle" onclick="togglePurchased(<?= $item['id'] ?>)" title="<?= $toggleTitle ?>">
                      <i class="fas <?= $toggleIcon ?>"></i>
                    </button>
                    <button class="planner-action-btn action-edit" onclick="editShopping(<?= $item['id'] ?>)" title="Edit">
                      <i class="fas fa-pen"></i>
                    </button>
                    <button class="planner-action-btn action-delete" onclick="confirmDeleteShopping(<?= $item['id'] ?>)" title="Delete">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- RIGHT COLUMN: Forms + Inventory -->
    <div class="planner-right">

      <!-- Tabbed Form -->
      <div class="planner-card">
        <div class="planner-card-header">
          <h3><i class="fas fa-pen"></i> <span id="formTitle">Add Shopping Item</span></h3>
        </div>

        <!-- Tab buttons -->
        <div class="planner-tabs">
          <button class="planner-tab active" data-target="shoppingFormSection"><i class="fas fa-cart-plus"></i> Shopping</button>
          <button class="planner-tab" data-target="inventoryFormSection"><i class="fas fa-boxes"></i> Inventory</button>
        </div>

        <!-- Shopping Form -->
        <div class="planner-form-section active" id="shoppingFormSection">
          <form class="planner-form" method="POST" action="planner_handler.php" id="shoppingForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="add_shopping" id="shop_action">
            <input type="hidden" name="id" id="shop_id" value="">
            <div class="form-group">
              <label for="shop_item_name">Item Name</label>
              <input type="text" id="shop_item_name" name="item_name" placeholder="e.g. Physics Notebook" required>
            </div>
            <div class="form-group">
              <label for="shop_reason_why">Reason (Why buy?)</label>
              <textarea id="shop_reason_why" name="reason_why" rows="2" placeholder="e.g. Old notebook is full"></textarea>
            </div>
            <div class="form-group">
              <label for="shop_purpose_work">Purpose / Use Case</label>
              <textarea id="shop_purpose_work" name="purpose_work" rows="2" placeholder="e.g. For Physics practical records"></textarea>
            </div>
            <div class="form-group">
              <label for="shop_target_date">Target Date</label>
              <input type="date" id="shop_target_date" name="target_date">
            </div>
            <div class="planner-btn-group">
              <button type="submit" class="planner-btn" id="shop_submit_btn"><i class="fas fa-plus"></i> Add</button>
              <button type="button" class="planner-btn planner-btn-secondary" id="shop_cancel_btn" style="display:none;" onclick="cancelShoppingEdit()"><i class="fas fa-times"></i> Cancel</button>
            </div>
          </form>
        </div>

        <!-- Inventory Form -->
        <div class="planner-form-section" id="inventoryFormSection">
          <form class="planner-form" method="POST" action="planner_handler.php" id="inventoryForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="add_inventory" id="inv_action">
            <input type="hidden" name="id" id="inv_id" value="">
            <div class="form-group">
              <label for="inv_item_name">Item Name</label>
              <input type="text" id="inv_item_name" name="item_name" placeholder="e.g. Ballpoint Pens" required>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="inv_quantity">Quantity</label>
                <input type="number" id="inv_quantity" name="quantity" value="1" min="0">
              </div>
              <div class="form-group">
                <label for="inv_availability_status">Status</label>
                <select id="inv_availability_status" name="availability_status">
                  <option value="Available">Available</option>
                  <option value="Running Low">Running Low</option>
                  <option value="Out of Stock">Out of Stock</option>
                </select>
              </div>
            </div>
            <div class="planner-btn-group">
              <button type="submit" class="planner-btn" id="inv_submit_btn"><i class="fas fa-plus"></i> Add</button>
              <button type="button" class="planner-btn planner-btn-secondary" id="inv_cancel_btn" style="display:none;" onclick="cancelInventoryEdit()"><i class="fas fa-times"></i> Cancel</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Inventory Grid -->
      <div class="planner-card">
        <div class="planner-card-header">
          <h3><i class="fas fa-boxes"></i> Current Stock</h3>
          <span style="font-size:0.72rem;color:#8f94a8;"><?= count($inventoryItems) ?> items</span>
        </div>
        <?php if (empty($inventoryItems)): ?>
          <div class="planner-empty">
            <i class="fas fa-box-open"></i>
            <h5>No inventory items</h5>
            <p>Add items you currently have</p>
          </div>
        <?php else: ?>
          <div class="inventory-grid">
            <?php foreach ($inventoryItems as $inv):
              $iconMap = ['Available' => 'fa-check-circle text-success', 'Running Low' => 'fa-exclamation-triangle text-warning', 'Out of Stock' => 'fa-times-circle text-danger'];
              $badgeMap = ['Available' => 'inventory-badge-available', 'Running Low' => 'inventory-badge-low', 'Out of Stock' => 'inventory-badge-out'];
              $icon = $iconMap[$inv['availability_status']] ?? 'fa-box text-muted';
              $badge = $badgeMap[$inv['availability_status']] ?? 'inventory-badge-available';
            ?>
              <div class="inventory-item" data-id="<?= $inv['id'] ?>">
                <div class="inventory-item-icon"><i class="fas <?= $icon ?>"></i></div>
                <div class="inventory-item-name"><?= htmlspecialchars($inv['item_name']) ?></div>
                <div class="inventory-item-qty">Qty: <?= (int)$inv['quantity'] ?></div>
                <span class="inventory-badge <?= $badge ?>"><?= $inv['availability_status'] ?></span>
                <div class="inventory-item-actions">
                  <button class="planner-action-btn action-edit" onclick="editInventoryItem(<?= $inv['id'] ?>)" title="Edit"><i class="fas fa-pen"></i></button>
                  <button class="planner-action-btn action-delete" onclick="confirmDeleteInventory(<?= $inv['id'] ?>)" title="Delete"><i class="fas fa-trash-alt"></i></button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<?php require 'includes/footer.php'; ?>
