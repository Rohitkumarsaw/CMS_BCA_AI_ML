// Grades JS
document.addEventListener('DOMContentLoaded', function() {
    const marksInput = document.getElementById('marks_obtained');
    const totalInput = document.getElementById('total_marks');
    const percentageDisplay = document.getElementById('percentage');
    const gradeDisplay = document.getElementById('grade');
    
    function updateGrade() {
        if (marksInput && totalInput && percentageDisplay && gradeDisplay) {
            const marks = parseFloat(marksInput.value) || 0;
            const total = parseFloat(totalInput.value) || 1;
            const percentage = ((marks / total) * 100).toFixed(1);
            percentageDisplay.textContent = percentage + '%';
            
            let grade = 'F';
            if (percentage >= 90) grade = 'A+';
            else if (percentage >= 80) grade = 'A';
            else if (percentage >= 70) grade = 'B+';
            else if (percentage >= 60) grade = 'B';
            else if (percentage >= 50) grade = 'C';
            else if (percentage >= 40) grade = 'D';
            gradeDisplay.textContent = grade;
        }
    }
    
    if (marksInput) marksInput.addEventListener('input', updateGrade);
    if (totalInput) totalInput.addEventListener('input', updateGrade);
});
