<?php
$pageTitle = 'Projects';
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];
$userSemester = $_SESSION['user_semester'];

$semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

$where = "WHERE p.user_id = :user_id";
$params = [':user_id' => $userId];

if ($semester > 0) {
    $where .= " AND p.semester = :semester";
    $params[':semester'] = $semester;
}
if (!empty($category)) {
    $where .= " AND p.category = :category";
    $params[':category'] = $category;
}

$pagi = paginate($pdo, 'projects p', $where, $params, 20);
$query = "SELECT p.*
          FROM projects p
          $where
          ORDER BY p.created_at DESC" . $pagi['sql'];
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$projects = $stmt->fetchAll();

$countQuery = "SELECT p.semester, COUNT(*) as count 
               FROM projects p 
               WHERE p.user_id = :user_id 
               GROUP BY p.semester 
               ORDER BY p.semester";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute([':user_id' => $userId]);
$semesterCounts = $countStmt->fetchAll(PDO::FETCH_KEY_PAIR);

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
?>
<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-project-diagram me-2"></i>Projects</h2>
            <a href="add_project.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Add Project
            </a>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Projects</h5>
                        <h2><?php echo count($projects); ?></h2>
                    </div>
                </div>
            </div>
            <?php foreach ($semesterCounts as $sem => $count): ?>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Semester <?php echo $sem; ?></h5>
                            <h2><?php echo $count; ?> projects</h2>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <select name="semester" class="form-select">
                            <option value="0">All Semesters</option>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $semester == $i ? 'selected' : ''; ?>>
                                    Semester <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <option value="Academic" <?php echo $category == 'Academic' ? 'selected' : ''; ?>>Academic</option>
                            <option value="Personal" <?php echo $category == 'Personal' ? 'selected' : ''; ?>>Personal</option>
                            <option value="Internship" <?php echo $category == 'Internship' ? 'selected' : ''; ?>>Internship</option>
                            <option value="Final Year" <?php echo $category == 'Final Year' ? 'selected' : ''; ?>>Final Year</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="section-search-container">
            <i class="fas fa-search section-search-icon"></i>
            <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#projectsGrid">
        </div>
        <div class="row" id="projectsGrid">
            <?php if (empty($projects)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No projects found</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card project-card h-100">
                            <?php if (!empty($project['image_path'])): ?>
                                <img src="<?php echo $project['image_path']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($project['title']); ?>">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-project-diagram fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                                    <span class="badge bg-<?php 
                                        echo match($project['category']) {
                                            'Academic' => 'primary',
                                            'Personal' => 'success',
                                            'Internship' => 'warning',
                                            'Final Year' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo $project['category']; ?>
                                    </span>
                                </div>
                                
                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars(substr($project['description'], 0, 100)); ?>
                                    <?php echo strlen($project['description']) > 100 ? '...' : ''; ?>
                                </p>
                                
                                <?php if (!empty($project['tech_stack'])): ?>
                                    <div class="mb-2">
                                        <?php 
                                        $techs = explode(',', $project['tech_stack']);
                                        foreach (array_slice($techs, 0, 3) as $tech): ?>
                                            <span class="badge bg-light text-dark"><?php echo trim($tech); ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($techs) > 3): ?>
                                            <span class="badge bg-light text-dark">+<?php echo count($techs) - 3; ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo formatDate($project['start_date']); ?>
                                        <?php if (!empty($project['end_date'])): ?>
                                            - <?php echo formatDate($project['end_date']); ?>
                                        <?php endif; ?>
                                    </small>
                                </p>
                            </div>
                            
                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <?php if (!empty($project['file_path'])): ?>
                                            <a href="<?php echo $project['file_path']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="fas fa-file me-1"></i>File
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($project['link'])): ?>
                                            <a href="<?php echo $project['link']; ?>" class="btn btn-sm btn-outline-success" target="_blank">
                                                <i class="fas fa-link me-1"></i>Link
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <a href="edit_project.php?id=<?= $project['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="view.php?type=project&id=<?= $project['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="delete.php?type=project&id=<?= $project['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this project"><i class="fas fa-trash"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
    </div>

<?php include 'includes/footer.php'; ?>
