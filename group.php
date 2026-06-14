<?php
$pageTitle = 'Groups';
$extraCSS = ['style.css', 'groups.css'];
$extraJS = ['main.js', 'groups.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT g.*, gm.added_at as member_since, u.name as creator_name,
    (SELECT COUNT(*) FROM group_members WHERE group_id = g.id) as member_count
    FROM groups g
    INNER JOIN group_members gm ON g.id = gm.group_id
    LEFT JOIN users u ON g.created_by = u.id
    WHERE gm.user_id = ?
    ORDER BY g.created_at DESC");
$stmt->execute([$userId]);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

$groupTypeColors = [
    'Attendance' => 'primary',
    'Homework' => 'warning',
    'Projects' => 'success',
    'General' => 'info'
];

$groupTypeIcons = [
    'Attendance' => 'check-circle',
    'Homework' => 'book',
    'Projects' => 'project-diagram',
    'General' => 'users'
];

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-users me-2 text-primary"></i>Groups</h4>
                    <p class="text-muted mb-0">Manage your study and project groups</p>
                </div>
                <a href="create_group.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Create Group
                </a>
            </div>

            <?= getFlashMessage() ?>

            <?php if (!empty($groups)): ?>
                <div class="row">
                    <?php foreach ($groups as $group): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm group-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-<?php echo $groupTypeColors[$group['group_type']] ?? 'secondary'; ?> d-flex align-items-center justify-content-center me-3" style="width:50px;height:50px;">
                                                <i class="fas fa-<?php echo $groupTypeIcons[$group['group_type']] ?? 'users'; ?> text-white fa-lg"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($group['group_name']); ?></h5>
                                                <span class="badge bg-<?php echo $groupTypeColors[$group['group_type']] ?? 'secondary'; ?>">
                                                    <?php echo htmlspecialchars($group['group_type']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex align-items-center text-muted mb-2">
                                            <i class="fas fa-users me-2"></i>
                                            <span><?php echo $group['member_count']; ?> member<?php echo $group['member_count'] != 1 ? 's' : ''; ?></span>
                                        </div>
                                        <div class="d-flex align-items-center text-muted mb-2">
                                            <i class="fas fa-calendar me-2"></i>
                                            <span>Created <?php echo formatDate($group['created_at']); ?></span>
                                        </div>
                                        <div class="d-flex align-items-center text-muted">
                                            <i class="fas fa-user me-2"></i>
                                            <span>By <?php echo htmlspecialchars($group['creator_name'] ?? 'Unknown'); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">
                                            <i class="fas fa-clock me-1"></i>
                                            Joined <?php echo formatDate($group['member_since']); ?>
                                        </span>
                                        <div>
                                            <a href="edit_group.php?id=<?= $group['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="view.php?type=group&id=<?= $group['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="delete.php?type=group&id=<?= $group['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this group"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">You are not part of any groups yet.</p>
                        <a href="create_group.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Create Your First Group
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
