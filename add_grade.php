<?php
$pageTitle = 'Add New Grade';
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$userId = $_SESSION['user_id'];
$userSemester = $_SESSION['user_semester'];
$subjects = getSemesterSubjects($userSemester);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = sanitize($_POST['subject']);
    $examName = sanitize($_POST['exam_name']);
    $marksObtained = (float)$_POST['marks_obtained'];
    $totalMarks = (float)$_POST['total_marks'];
    $date = sanitize($_POST['date']);
    $semester = (int)$_POST['semester'];
    $letterGrade = sanitize($_POST['letter_grade'] ?? '');
    if ($letterGrade === '' || $letterGrade === 'auto') $letterGrade = null;
    
    $query = "INSERT INTO grades (user_id, subject, exam_name, marks_obtained, total_marks, letter_grade, date, semester, created_at) 
              VALUES (:user_id, :subject, :exam_name, :marks_obtained, :total_marks, :letter_grade, :date, :semester, NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':user_id' => $userId,
        ':subject' => $subject,
        ':exam_name' => $examName,
        ':marks_obtained' => $marksObtained,
        ':total_marks' => $totalMarks,
        ':letter_grade' => $letterGrade,
        ':date' => $date,
        ':semester' => $semester
    ]);
    
    $detail = "Subject: " . $subject . " - Marks: " . $marksObtained . "/" . $totalMarks;
    setFlashMessage('success', 'Grade added successfully');
    notifyEmail('Grade', 'added', $detail);
    logActivity($pdo, $userId, $_SESSION['user_name'] ?? 'User', 'Added', 'Grade', $pdo->lastInsertId(), $detail);
    header('Location: grades.php');
    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
?>
<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-plus-circle me-2"></i>Add New Grade</h2>
            <a href="grades.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Grades
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" id="gradeForm">
                            <?= csrfField() ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <input type="text" class="form-control" id="subject" name="subject" list="subject_list" required placeholder="Type or select a subject">
                                    <datalist id="subject_list">
                                        <?php foreach ($subjects as $subj): ?>
                                            <option value="<?php echo $subj; ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="exam_name" class="form-label">Exam Name *</label>
                                    <input type="text" class="form-control" id="exam_name" name="exam_name" required>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="marks_obtained" class="form-label">Marks Obtained *</label>
                                    <input type="number" class="form-control" id="marks_obtained" name="marks_obtained" step="0.01" required>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="total_marks" class="form-label">Total Marks *</label>
                                    <input type="number" class="form-control" id="total_marks" name="total_marks" step="0.01" required>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="semester" class="form-label">Semester *</label>
                                    <select class="form-select" id="semester" name="semester" required>
                                        <?php for ($i = 1; $i <= 8; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo $i == $userSemester ? 'selected' : ''; ?>>
                                                Semester <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="date" class="form-label">Date *</label>
                                    <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="letter_grade" class="form-label">Letter Grade <small class="text-muted">(optional — leave as Auto to calculate)</small></label>
                                    <select class="form-select" id="letter_grade" name="letter_grade">
                                        <option value="auto">Auto Calculate</option>
                                        <option value="A+">A+</option>
                                        <option value="A">A</option>
                                        <option value="B+">B+</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                        <option value="F">F</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Save Grade
                                </button>
                                <a href="grades.php" class="btn btn-secondary ms-2">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Grade Preview</h5>
                    </div>
                    <div class="card-body text-center">
                        <div id="gradePreview" class="py-4">
                            <h3 id="previewPercentage">0%</h3>
                            <h1 id="previewGrade" class="display-1">-</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const marksInput = document.getElementById('marks_obtained');
    const totalInput = document.getElementById('total_marks');
    const previewPercentage = document.getElementById('previewPercentage');
    const previewGrade = document.getElementById('previewGrade');
    
    function updatePreview() {
        const marks = parseFloat(marksInput.value) || 0;
        const total = parseFloat(totalInput.value) || 1;
        const percentage = (marks / total * 100).toFixed(1);
        
        previewPercentage.textContent = percentage + '%';
        
        let grade = '-';
        let color = '#6c757d';
        
        if (percentage >= 90) { grade = 'A+'; color = '#28a745'; }
        else if (percentage >= 80) { grade = 'A'; color = '#28a745'; }
        else if (percentage >= 75) { grade = 'B+'; color = '#007bff'; }
        else if (percentage >= 70) { grade = 'B'; color = '#007bff'; }
        else if (percentage >= 60) { grade = 'C'; color = '#ffc107'; }
        else if (percentage >= 50) { grade = 'D'; color = '#fd7e14'; }
        else if (percentage > 0) { grade = 'F'; color = '#dc3545'; }
        
        previewGrade.textContent = grade;
        previewGrade.style.color = color;
    }
    
    marksInput.addEventListener('input', updatePreview);
    totalInput.addEventListener('input', updatePreview);
});
</script>

</div>

<?php include 'includes/footer.php'; ?>
