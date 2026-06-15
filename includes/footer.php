    <div class="app-footer">
        &copy; <?php echo date('Y'); ?> <a href="<?php echo SITE_URL; ?>"><?php echo SITE_NAME; ?></a>. All rights reserved.
    </div>
</div><!-- /.app-wrapper -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($extraJS)): ?>
        <?php foreach ($extraJS as $js): ?>
            <?php if ($js !== 'main.js'): ?>
                <script src="js/<?php echo $js; ?>"></script>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <div id="confirmDeleteOverlay" class="confirm-overlay">
      <div class="confirm-modal">
        <div class="confirm-icon-wrap">
          <div class="confirm-icon"><i class="fas fa-trash-alt"></i></div>
        </div>
        <h3 id="confirmTitle">Delete this?</h3>
        <p class="confirm-msg" id="confirmMsg">Are you sure? This action cannot be undone.</p>
        <div class="confirm-actions">
          <button class="confirm-btn-cancel" id="confirmCancelBtn">Cancel</button>
          <button class="confirm-btn-delete" id="confirmDeleteBtn">Delete</button>
        </div>
      </div>
    </div>
</body>
</html>
