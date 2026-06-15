<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Academic Roadmap';
$extraCSS = ['roadmap.css'];

$userId = $_SESSION['user_id'];

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS academic_roadmaps (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        roadmap_title VARCHAR(255) NOT NULL,
        total_nodes INT DEFAULT 0,
        completed_nodes INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS roadmap_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        roadmap_id INT NOT NULL,
        item_title VARCHAR(255) NOT NULL,
        is_completed TINYINT(1) DEFAULT 0,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (roadmap_id) REFERENCES academic_roadmaps(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}

$stmt = $pdo->prepare("SELECT * FROM academic_roadmaps WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$roadmaps = $stmt->fetchAll();

// Fetch items for each roadmap
$itemsByRoadmap = [];
foreach ($roadmaps as $rm) {
    $si = $pdo->prepare("SELECT * FROM roadmap_items WHERE roadmap_id = ? ORDER BY sort_order ASC, id ASC");
    $si->execute([$rm['id']]);
    $itemsByRoadmap[$rm['id']] = $si->fetchAll();
}

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>
<div class="app-content">
  <div class="page-header">
    <div>
      <h2><i class="fas fa-road" style="color:#8a2be2;"></i> Academic Roadmap</h2>
      <p>Track your learning journey with interactive roadmaps and progress bars</p>
    </div>
  </div>
  <div class="roadmap-wrapper">
    <!-- Create Roadmap Form -->
    <div class="roadmap-form-card">
      <h3><i class="fas fa-plus-circle"></i> Create New Roadmap</h3>
      <form id="roadmapForm" method="POST" action="roadmap_handler.php">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add_roadmap">
        <div class="roadmap-form-row">
          <input type="text" name="title" id="roadmapTitleInput" placeholder="e.g. Full-Stack Web Development, BCA 1st Sem Roadmap" required>
          <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Create</button>
        </div>
      </form>
    </div>

    <!-- Roadmap Grid -->
    <?php if (empty($roadmaps)): ?>
      <div class="roadmap-empty">
        <i class="fas fa-road"></i>
        <h5>No roadmaps yet</h5>
        <p>Create your first roadmap to start tracking your learning progress.</p>
      </div>
    <?php else: ?>
      <div class="section-search-container">
          <i class="fas fa-search section-search-icon"></i>
          <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#roadmapGrid">
      </div>
      <div class="roadmap-grid" id="roadmapGrid">
        <?php foreach ($roadmaps as $rm):
          $items = $itemsByRoadmap[$rm['id']] ?? [];
          $total = (int)$rm['total_nodes'];
          $completed = (int)$rm['completed_nodes'];
          $pct = $total > 0 ? round(($completed / $total) * 100) : 0;
        ?>
        <div class="roadmap-card" data-id="<?= $rm['id'] ?>">
          <div class="roadmap-card-header">
            <h4 class="roadmap-card-title"><?= htmlspecialchars($rm['roadmap_title']) ?></h4>
            <div class="roadmap-card-actions">
              <button class="planner-action-btn action-edit" onclick="showAddItem(<?= $rm['id'] ?>)" title="Add Item"><i class="fas fa-plus"></i></button>
              <button class="planner-action-btn action-edit" onclick="editRoadmap(<?= $rm['id'] ?>)" title="Edit Roadmap"><i class="fas fa-pen"></i></button>
              <button class="planner-action-btn action-delete" onclick="deleteRoadmap(<?= $rm['id'] ?>)" title="Delete Roadmap"><i class="fas fa-trash-alt"></i></button>
            </div>
          </div>
          <div class="roadmap-progress-wrap">
            <div class="roadmap-progress-bar-wrap">
              <div class="roadmap-progress-fill" style="width:<?= $pct ?>%;"></div>
            </div>
            <div class="roadmap-progress-label"><span class="roadmap-pct"><?= $pct ?>%</span> (<span class="roadmap-completed"><?= $completed ?></span>/<span class="roadmap-total"><?= $total ?></span>)</div>
          </div>
          <div class="roadmap-checklist" id="checklist-<?= $rm['id'] ?>">
            <?php if (empty($items)): ?>
              <div style="text-align:center;padding:10px;font-size:0.78rem;color:#8f94a8;">No items yet. Click + to add.</div>
            <?php else: ?>
              <?php foreach ($items as $item): ?>
              <div class="roadmap-checklist-item <?= $item['is_completed'] ? 'completed' : '' ?>" data-item-id="<?= $item['id'] ?>">
                <input type="checkbox" <?= $item['is_completed'] ? 'checked' : '' ?> onchange="toggleItem(this, <?= $item['id'] ?>)">
                <span class="roadmap-item-title"><?= htmlspecialchars($item['item_title']) ?></span>
                <div class="roadmap-item-actions">
                  <button class="roadmap-item-btn action-edit" onclick="editItem(<?= $item['id'] ?>)" title="Edit"><i class="fas fa-pen"></i></button>
                  <button class="roadmap-item-btn action-delete" onclick="deleteItem(<?= $item['id'] ?>)" title="Delete"><i class="fas fa-trash-alt"></i></button>
                </div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Add Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="POST" action="roadmap_handler.php" id="addItemForm">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add_item">
        <input type="hidden" name="roadmap_id" id="addItemRoadmapId" value="">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Item Title</label>
            <input type="text" class="form-control" name="item_title" required placeholder="e.g. Learn HTML & CSS">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Edit Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="POST" action="roadmap_handler.php" id="editItemForm">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="edit_item">
        <input type="hidden" name="item_id" id="editItemId" value="">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Item Title</label>
            <input type="text" class="form-control" name="item_title" id="editItemTitle" required placeholder="e.g. Learn HTML & CSS">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Edit Roadmap Modal -->
<div class="modal fade" id="editRoadmapModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Edit Roadmap</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="POST" action="roadmap_handler.php" id="editRoadmapForm">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="edit_roadmap">
        <input type="hidden" name="id" id="editRoadmapId" value="">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Roadmap Title</label>
            <input type="text" class="form-control" name="title" id="editRoadmapTitle" required placeholder="e.g. Semester 1 Goals">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
(function () {
  'use strict';
  function post(url, data) {
    return fetch(url,{method:'POST',body:data}).then(function(r){if(!r.ok)throw new Error('Server error');return r.json();});
  }
  // Create roadmap
  document.getElementById('roadmapForm').addEventListener('submit',function(e){
    e.preventDefault();
    post('roadmap_handler.php',new FormData(this)).then(function(res){
      if(res.status==='success'){Swal.fire({icon:'success',title:'Created',text:res.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}}).then(function(){location.reload();});}
      else{Swal.fire({icon:'error',title:'Error',text:res.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}});}
    }).catch(function(err){Swal.fire({icon:'error',title:'Request Failed',text:err.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}});});
  });
  // Add item modal
  window.showAddItem=function(rid){document.getElementById('addItemRoadmapId').value=rid;new bootstrap.Modal(document.getElementById('addItemModal')).show();};
  document.getElementById('addItemForm').addEventListener('submit',function(e){
    e.preventDefault();
    post('roadmap_handler.php',new FormData(this)).then(function(res){
      if(res.status==='success'){var m=bootstrap.Modal.getInstance(document.getElementById('addItemModal'));if(m)m.hide();Swal.fire({icon:'success',title:'Added',text:res.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}}).then(function(){location.reload();});}
      else{Swal.fire({icon:'error',title:'Error',text:res.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}});}
    }).catch(function(err){Swal.fire({icon:'error',title:'Request Failed',text:err.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}});});
  });
  // Edit item
  window.editItem=function(id){
    var d=document.querySelector('.roadmap-checklist-item[data-item-id="'+id+'"]');if(!d)return;
    var t=d.querySelector('.roadmap-item-title');if(!t)return;
    document.getElementById('editItemId').value=id;document.getElementById('editItemTitle').value=t.textContent.trim();
    new bootstrap.Modal(document.getElementById('editItemModal')).show();
  };
  document.getElementById('editItemForm').addEventListener('submit',function(e){
    e.preventDefault();
    post('roadmap_handler.php',new FormData(this)).then(function(res){
      if(res.status==='success'){var m=bootstrap.Modal.getInstance(document.getElementById('editItemModal'));if(m)m.hide();Swal.fire({icon:'success',title:'Updated',text:res.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}}).then(function(){location.reload();});}
      else{Swal.fire({icon:'error',title:'Error',text:res.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}});}
    }).catch(function(err){Swal.fire({icon:'error',title:'Request Failed',text:err.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}});});
  });
  // Toggle item
  window.toggleItem=function(cb,id){
    var fd=new FormData();fd.append('action','toggle_item');fd.append('item_id',id);fd.append('completed',cb.checked?1:0);
    post('roadmap_handler.php',fd).then(function(res){
      if(res.status==='success'){
        var item=cb.closest('.roadmap-checklist-item');if(cb.checked)item.classList.add('completed');else item.classList.remove('completed');
        var card=cb.closest('.roadmap-card');var fill=card.querySelector('.roadmap-progress-fill');var pctL=card.querySelector('.roadmap-pct');var cL=card.querySelector('.roadmap-completed');var tL=card.querySelector('.roadmap-total');
        var tot=parseInt(res.total_nodes),com=parseInt(res.completed_nodes),pct=tot>0?Math.round(com/tot*100):0;
        if(fill)fill.style.width=pct+'%';if(pctL)pctL.textContent=pct+'%';if(cL)cL.textContent=com;if(tL)tL.textContent=tot;
        Swal.fire({icon:'success',title:'Updated',timer:1200,showConfirmButton:false,background:'#191c24',color:'#ffffff'});
      }else{cb.checked=!cb.checked;Swal.fire({icon:'error',title:'Error',text:res.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}});}
    }).catch(function(err){cb.checked=!cb.checked;Swal.fire({icon:'error',title:'Request Failed',text:err.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}});});
  };
  // Delete item
  window.deleteItem=function(id){
    Swal.fire({title:'Delete Item?',text:'This item will be removed.',icon:'warning',showCancelButton:true,confirmButtonText:'Delete',cancelButtonText:'Cancel',background:'#191c24',color:'#ffffff',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm-delete',cancelButton:'swal2-cancel'}}).then(function(r){
      if(!r.isConfirmed)return;
      var d=document.querySelector('.roadmap-checklist-item[data-item-id="'+id+'"]');if(!d)return;
      var card=d.closest('.roadmap-card');d.remove();
      var fd=new FormData();fd.append('action','delete_item');fd.append('item_id',id);
      post('roadmap_handler.php',fd).then(function(res){
        if(res.status==='success'){
          var fill=card.querySelector('.roadmap-progress-fill'),pctL=card.querySelector('.roadmap-pct'),cL=card.querySelector('.roadmap-completed'),tL=card.querySelector('.roadmap-total');
          var tot=parseInt(res.total_nodes),com=parseInt(res.completed_nodes),pct=tot>0?Math.round(com/tot*100):0;
          if(fill)fill.style.width=pct+'%';if(pctL)pctL.textContent=pct+'%';if(cL)cL.textContent=com;if(tL)tL.textContent=tot;
          if(card.querySelectorAll('.roadmap-checklist-item').length===0){var cl=card.querySelector('.roadmap-checklist');if(cl)cl.innerHTML='<div style="text-align:center;padding:10px;font-size:0.78rem;color:#8f94a8;">No items yet. Click + to add.</div>';}
          Swal.fire({icon:'success',title:'Deleted',timer:1200,showConfirmButton:false,background:'#191c24',color:'#ffffff'});
        }else{Swal.fire({icon:'error',title:'Error',text:res.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}});}
      }).catch(function(err){Swal.fire({icon:'error',title:'Request Failed',text:err.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}});});
    });
  };
  // Edit roadmap
  window.editRoadmap=function(id){
    var card=document.querySelector('.roadmap-card[data-id="'+id+'"]');if(!card)return;
    var titleEl=card.querySelector('.roadmap-card-title');if(!titleEl)return;
    document.getElementById('editRoadmapId').value=id;document.getElementById('editRoadmapTitle').value=titleEl.textContent.trim();
    new bootstrap.Modal(document.getElementById('editRoadmapModal')).show();
  };
  document.getElementById('editRoadmapForm').addEventListener('submit',function(e){
    e.preventDefault();
    post('roadmap_handler.php',new FormData(this)).then(function(res){
      if(res.status==='success'){var m=bootstrap.Modal.getInstance(document.getElementById('editRoadmapModal'));if(m)m.hide();Swal.fire({icon:'success',title:'Updated',text:res.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}}).then(function(){location.reload();});}
      else{Swal.fire({icon:'error',title:'Error',text:res.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}});}
    }).catch(function(err){Swal.fire({icon:'error',title:'Request Failed',text:err.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}});});
  });
  // Delete roadmap
  window.deleteRoadmap=function(id){
    Swal.fire({title:'Delete Roadmap?',text:'All items inside will also be removed.',icon:'warning',showCancelButton:true,confirmButtonText:'Delete',cancelButtonText:'Cancel',background:'#191c24',color:'#ffffff',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm-delete',cancelButton:'swal2-cancel'}}).then(function(r){
      if(!r.isConfirmed)return;
      var fd=new FormData();fd.append('action','delete_roadmap');fd.append('id',id);
      post('roadmap_handler.php',fd).then(function(res){
        if(res.status==='success'){Swal.fire({icon:'success',title:'Deleted',text:res.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}}).then(function(){location.reload();});}
        else{Swal.fire({icon:'error',title:'Error',text:res.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}});}
      }).catch(function(err){Swal.fire({icon:'error',title:'Request Failed',text:err.message,background:'#191c24',color:'#ffffff',confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}});});
    });
  };
})();
</script>
<?php require 'includes/footer.php'; ?>