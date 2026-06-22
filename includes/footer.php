    <div class="app-footer">
        &copy; <?php echo date('Y'); ?> <a href="<?php echo SITE_URL; ?>"><?php echo SITE_NAME; ?></a>. All rights reserved.
    </div>
</div><!-- /.app-wrapper -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($extraJS)): ?>
        <?php foreach ($extraJS as $js): ?>
            <?php if ($js !== 'main.js'):
                $jsPath = __DIR__ . '/../js/' . $js;
                $v = file_exists($jsPath) ? filemtime($jsPath) : time(); ?>
                <script src="<?php echo SITE_URL; ?>/js/<?php echo $js; ?>?v=<?php echo $v; ?>"></script>
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
