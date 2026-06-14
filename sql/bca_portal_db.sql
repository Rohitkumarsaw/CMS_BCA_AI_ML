-- =====================================================
-- BCA AI/ML PORTAL - COMPLETE DATABASE SCHEMA
-- =====================================================

CREATE DATABASE IF NOT EXISTS bca_portal_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bca_portal_db;

-- =====================================================
-- 1. USERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') DEFAULT 'student',
    name VARCHAR(100) NOT NULL,
    college VARCHAR(200) DEFAULT '',
    semester INT DEFAULT 1,
    roll_no VARCHAR(50) DEFAULT '',
    email VARCHAR(100) DEFAULT '',
    phone VARCHAR(20) DEFAULT '',
    address TEXT,
    photo_path VARCHAR(255) DEFAULT '',
    language VARCHAR(10) DEFAULT 'en',
    dark_mode TINYINT(1) DEFAULT 0,
    notif_homework TINYINT(1) DEFAULT 1,
    notif_exam TINYINT(1) DEFAULT 1,
    notif_payment TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- 2. ATTENDANCE TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    subject VARCHAR(100) NOT NULL,
    status ENUM('Present', 'Absent', 'Late') NOT NULL DEFAULT 'Present',
    semester INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_attendance_user_date (user_id, date),
    INDEX idx_attendance_subject (subject),
    INDEX idx_attendance_semester (semester)
) ENGINE=InnoDB;

-- =====================================================
-- 3. HOMEWORK TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS homework (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    description TEXT,
    due_date DATE NOT NULL,
    file_path VARCHAR(255) DEFAULT '',
    status ENUM('Not Submitted', 'Submitted', 'Pending') DEFAULT 'Not Submitted',
    semester INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_homework_user (user_id),
    INDEX idx_homework_due (due_date),
    INDEX idx_homework_subject (subject)
) ENGINE=InnoDB;

-- =====================================================
-- 4. SCHEDULE TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    semester INT NOT NULL DEFAULT 1,
    day VARCHAR(20) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    teacher_name VARCHAR(100) DEFAULT '',
    room_no VARCHAR(50) DEFAULT '',
    type ENUM('Lecture', 'Lab', 'Tutorial', 'Activity') DEFAULT 'Lecture',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_schedule_semester (semester),
    INDEX idx_schedule_day (day)
) ENGINE=InnoDB;

-- =====================================================
-- 5. EXAMS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exam_name VARCHAR(200) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room_no VARCHAR(50) DEFAULT '',
    type ENUM('Theory', 'Practical', 'Viva', 'Internal') DEFAULT 'Theory',
    semester INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_exams_user (user_id),
    INDEX idx_exams_date (date),
    INDEX idx_exams_subject (subject)
) ENGINE=InnoDB;

-- =====================================================
-- 6. SYLLABUS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS syllabus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    semester INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    topic VARCHAR(300) NOT NULL,
    status ENUM('Not Started', 'In Progress', 'Completed') DEFAULT 'Not Started',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_syllabus_user_sem (user_id, semester),
    INDEX idx_syllabus_subject (subject)
) ENGINE=InnoDB;

-- =====================================================
-- 7. NOTES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    title VARCHAR(200) NOT NULL,
    type ENUM('PDF', 'Image', 'Text', 'Video') DEFAULT 'Text',
    file_path VARCHAR(255) DEFAULT '',
    semester INT NOT NULL DEFAULT 1,
    tags VARCHAR(500) DEFAULT '',
    is_favorite TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notes_user (user_id),
    INDEX idx_notes_subject (subject),
    INDEX idx_notes_semester (semester)
) ENGINE=InnoDB;

-- =====================================================
-- 8. GRADES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    exam_name VARCHAR(200) NOT NULL,
    marks_obtained DECIMAL(5,2) NOT NULL,
    total_marks DECIMAL(5,2) NOT NULL,
    semester INT NOT NULL DEFAULT 1,
    date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_grades_user (user_id),
    INDEX idx_grades_subject (subject),
    INDEX idx_grades_semester (semester)
) ENGINE=InnoDB;

-- =====================================================
-- 9. PROJECTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    semester INT NOT NULL DEFAULT 1,
    subject VARCHAR(100) DEFAULT '',
    tech_stack VARCHAR(500) DEFAULT '',
    file_path VARCHAR(255) DEFAULT '',
    image_path VARCHAR(255) DEFAULT '',
    link VARCHAR(500) DEFAULT '',
    category ENUM('Academic', 'Personal', 'Internship', 'Final Year') DEFAULT 'Academic',
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_projects_user (user_id),
    INDEX idx_projects_semester (semester)
) ENGINE=InnoDB;

-- =====================================================
-- 10. INTERNSHIPS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS internships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company VARCHAR(200) NOT NULL,
    role VARCHAR(200) NOT NULL,
    duration_start DATE NOT NULL,
    duration_end DATE DEFAULT NULL,
    location VARCHAR(200) DEFAULT '',
    description TEXT,
    skills_gained TEXT,
    certificate_path VARCHAR(255) DEFAULT '',
    payment DECIMAL(10,2) DEFAULT 0,
    status ENUM('Applied', 'Interviewing', 'Selected', 'Completed') DEFAULT 'Applied',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_internships_user (user_id)
) ENGINE=InnoDB;

-- =====================================================
-- 11. PAYMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    semester INT NOT NULL DEFAULT 1,
    payment_type VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50) DEFAULT '',
    transaction_id VARCHAR(100) DEFAULT '',
    receipt_path VARCHAR(255) DEFAULT '',
    status ENUM('Paid', 'Unpaid', 'Partial') DEFAULT 'Paid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_payments_user (user_id),
    INDEX idx_payments_semester (semester)
) ENGINE=InnoDB;

-- =====================================================
-- 12. HOLIDAYS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS holidays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    holiday_name VARCHAR(200) NOT NULL,
    type ENUM('National', 'College', 'Private') DEFAULT 'National',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_holidays_user (user_id),
    INDEX idx_holidays_date (date)
) ENGINE=InnoDB;

-- =====================================================
-- 13. EVENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_name VARCHAR(200) NOT NULL,
    date DATE NOT NULL,
    time TIME DEFAULT NULL,
    location VARCHAR(200) DEFAULT '',
    description TEXT,
    type ENUM('Sports', 'Technical', 'Cultural', 'Workshop', 'Other') DEFAULT 'Technical',
    image_path VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_events_user (user_id),
    INDEX idx_events_date (date)
) ENGINE=InnoDB;

-- =====================================================
-- 14. ANNOUNCEMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('Homework', 'Exam', 'Holiday', 'Event', 'General') DEFAULT 'General',
    priority ENUM('High', 'Medium', 'Low') DEFAULT 'Medium',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_announcements_user (user_id),
    INDEX idx_announcements_type (type)
) ENGINE=InnoDB;

-- =====================================================
-- 15. PROFILES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    college VARCHAR(200) DEFAULT '',
    semester INT DEFAULT 1,
    roll_no VARCHAR(50) DEFAULT '',
    email VARCHAR(100) DEFAULT '',
    phone VARCHAR(20) DEFAULT '',
    address TEXT,
    photo_path VARCHAR(255) DEFAULT '',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 16. STUDY PLANS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS study_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    topic VARCHAR(300) NOT NULL,
    date DATE NOT NULL,
    time_slot VARCHAR(50) DEFAULT '',
    priority ENUM('High', 'Medium', 'Low') DEFAULT 'Medium',
    status ENUM('Planned', 'In Progress', 'Completed') DEFAULT 'Planned',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_study_plans_user (user_id),
    INDEX idx_study_plans_date (date)
) ENGINE=InnoDB;

-- =====================================================
-- 17. SKILLS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    level ENUM('Beginner', 'Intermediate', 'Advanced') DEFAULT 'Beginner',
    category ENUM('Programming', 'AI', 'ML', 'Data', 'WebDev', 'Other') DEFAULT 'Programming',
    status ENUM('Learning', 'Completed') DEFAULT 'Learning',
    date_completed DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_skills_user (user_id)
) ENGINE=InnoDB;

-- =====================================================
-- 18. CERTIFICATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS certifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cert_name VARCHAR(200) NOT NULL,
    issuing_org VARCHAR(200) NOT NULL,
    date DATE NOT NULL,
    duration VARCHAR(50) DEFAULT '',
    certificate_path VARCHAR(255) DEFAULT '',
    link VARCHAR(500) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_certs_user (user_id)
) ENGINE=InnoDB;

-- =====================================================
-- 19. JOBS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_title VARCHAR(200) NOT NULL,
    company VARCHAR(200) NOT NULL,
    location VARCHAR(200) DEFAULT '',
    application_date DATE NOT NULL,
    status ENUM('Applied', 'Interviewing', 'Selected', 'Rejected') DEFAULT 'Applied',
    job_link VARCHAR(500) DEFAULT '',
    salary VARCHAR(50) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_jobs_user (user_id)
) ENGINE=InnoDB;

-- =====================================================
-- 20. BOOKS TABLE (Library)
-- =====================================================
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(200) DEFAULT '',
    subject VARCHAR(100) DEFAULT '',
    borrow_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    status ENUM('Borrowed', 'Returned') DEFAULT 'Borrowed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_books_user (user_id)
) ENGINE=InnoDB;

-- =====================================================
-- 21. RESOURCES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    type ENUM('Video', 'Article', 'Course', 'Book', 'Other') DEFAULT 'Video',
    link VARCHAR(500) DEFAULT '',
    subject VARCHAR(100) DEFAULT '',
    tags VARCHAR(500) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_resources_user (user_id)
) ENGINE=InnoDB;

-- =====================================================
-- 22. GROUPS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS `groups` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(200) NOT NULL,
    group_type ENUM('Attendance', 'Homework', 'Projects', 'General') DEFAULT 'General',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 23. GROUP MEMBERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS group_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_group_member (group_id, user_id)
) ENGINE=InnoDB;

-- =====================================================
-- 24. REPORTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    report_type VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_reports_user (user_id)
) ENGINE=InnoDB;

-- =====================================================
-- 25. CIRCULARS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS circulars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT,
    date DATE NOT NULL,
    file_path VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_circulars_user (user_id)
) ENGINE=InnoDB;

-- =====================================================
-- 26. LABS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS labs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lab_name VARCHAR(200) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    status ENUM('Completed', 'In Progress', 'Not Started') DEFAULT 'Not Started',
    report_path VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_labs_user (user_id)
) ENGINE=InnoDB;

-- =====================================================
-- 27. PRESENTATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS presentations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    status ENUM('Planned', 'Done') DEFAULT 'Planned',
    file_path VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_presentations_user (user_id)
) ENGINE=InnoDB;

-- =====================================================
-- 28. ASSIGNMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('Submitted', 'Not Submitted', 'Pending') DEFAULT 'Not Submitted',
    file_path VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_assignments_user (user_id)
) ENGINE=InnoDB;

-- =====================================================
-- 29. EXAM PREP TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS exam_prep (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exam_name VARCHAR(200) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    topics_to_cover TEXT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('Planned', 'In Progress', 'Completed') DEFAULT 'Planned',
    progress INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_exam_prep_user (user_id)
) ENGINE=InnoDB;

-- =====================================================
-- SAMPLE DATA INSERT
-- =====================================================

-- Default admin user (password: admin123)
INSERT INTO users (username, password, role, name, college, semester, roll_no, email, phone) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin User', 'BCA College', 1, 'BCA001', 'admin@bca.edu', '9999999999');

-- Sample student (password: password)
INSERT INTO users (username, password, role, name, college, semester, roll_no, email, phone) VALUES
('student1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Rahul Sharma', 'BCA AI/ML College', 4, 'BCA2024001', 'rahul@bca.edu', '9876543210');

-- Sample profile
INSERT INTO profiles (user_id, name, college, semester, roll_no, email, phone, address) VALUES
(2, 'Rahul Sharma', 'BCA AI/ML College', 4, 'BCA2024001', 'rahul@bca.edu', '9876543210', 'Delhi, India');

-- Sample attendance
INSERT INTO attendance (user_id, date, subject, status, semester) VALUES
(2, '2026-01-15', 'Programming C', 'Present', 1),
(2, '2026-01-15', 'Programming Python', 'Present', 1),
(2, '2026-01-15', 'DBMS', 'Absent', 1),
(2, '2026-01-16', 'Programming C', 'Present', 1),
(2, '2026-01-16', 'Programming Python', 'Late', 1),
(2, '2026-01-16', 'DBMS', 'Present', 1);

-- Sample homework
INSERT INTO homework (user_id, title, subject, description, due_date, status, semester) VALUES
(2, 'C Programming Assignment 1', 'Programming C', 'Write a program to calculate factorial', '2026-01-20', 'Submitted', 1),
(2, 'Python Data Types', 'Programming Python', 'Study and practice Python data types', '2026-01-22', 'Not Submitted', 1),
(2, 'DBMS Normalization', 'DBMS', 'Complete normalization exercises', '2026-01-25', 'Pending', 1);

-- Sample schedule
INSERT INTO schedule (semester, day, subject, start_time, end_time, teacher_name, room_no, type) VALUES
(4, 'Monday', 'Machine Learning Basics', '09:00:00', '10:00:00', 'Dr. Smith', 'Room 101', 'Lecture'),
(4, 'Monday', 'Deep Learning Intro', '10:00:00', '11:00:00', 'Prof. Johnson', 'Room 102', 'Lecture'),
(4, 'Monday', 'NLP Basics', '11:30:00', '12:30:00', 'Dr. Williams', 'Room 103', 'Lecture'),
(4, 'Monday', 'ML Lab', '14:00:00', '16:00:00', 'Prof. Brown', 'Lab 1', 'Lab'),
(4, 'Tuesday', 'Big Data Analytics', '09:00:00', '10:00:00', 'Dr. Davis', 'Room 101', 'Lecture'),
(4, 'Tuesday', 'Cloud Computing', '10:00:00', '11:00:00', 'Prof. Miller', 'Room 102', 'Lecture'),
(4, 'Tuesday', 'Elective 2', '11:30:00', '12:30:00', 'Dr. Wilson', 'Room 103', 'Lecture'),
(4, 'Wednesday', 'Machine Learning Basics', '09:00:00', '10:00:00', 'Dr. Smith', 'Room 101', 'Lecture'),
(4, 'Wednesday', 'Deep Learning Intro', '10:00:00', '11:00:00', 'Prof. Johnson', 'Room 102', 'Lecture'),
(4, 'Thursday', 'NLP Basics', '09:00:00', '10:00:00', 'Dr. Williams', 'Room 101', 'Lecture'),
(4, 'Thursday', 'Big Data Analytics', '10:00:00', '11:00:00', 'Dr. Davis', 'Room 102', 'Lecture'),
(4, 'Friday', 'Cloud Computing', '09:00:00', '10:00:00', 'Prof. Miller', 'Room 101', 'Lecture'),
(4, 'Friday', 'Elective 2', '10:00:00', '11:00:00', 'Dr. Wilson', 'Room 102', 'Lecture');

-- Sample exams
INSERT INTO exams (user_id, exam_name, subject, date, start_time, end_time, room_no, type, semester) VALUES
(2, 'Mid Term', 'Machine Learning Basics', '2026-03-15', '09:00:00', '12:00:00', 'Hall A', 'Theory', 4),
(2, 'Mid Term', 'Deep Learning Intro', '2026-03-17', '09:00:00', '12:00:00', 'Hall A', 'Theory', 4),
(2, 'Lab Practical', 'ML Lab', '2026-03-20', '10:00:00', '13:00:00', 'Lab 1', 'Practical', 4),
(2, 'End Term', 'Machine Learning Basics', '2026-05-10', '09:00:00', '12:00:00', 'Hall B', 'Theory', 4);

-- Sample grades
INSERT INTO grades (user_id, subject, exam_name, marks_obtained, total_marks, semester, date) VALUES
(2, 'Machine Learning Basics', 'Internal Assessment 1', 42, 50, 4, '2026-02-15'),
(2, 'Deep Learning Intro', 'Internal Assessment 1', 38, 50, 4, '2026-02-16'),
(2, 'NLP Basics', 'Internal Assessment 1', 45, 50, 4, '2026-02-17'),
(2, 'Big Data Analytics', 'Quiz 1', 18, 20, 4, '2026-02-20'),
(2, 'Cloud Computing', 'Quiz 1', 16, 20, 4, '2026-02-21'),
(2, 'Machine Learning Basics', 'Internal Assessment 2', 44, 50, 4, '2026-04-10'),
(2, 'Deep Learning Intro', 'Internal Assessment 2', 40, 50, 4, '2026-04-11');

-- Sample notes
INSERT INTO notes (user_id, subject, title, type, semester, tags, is_favorite) VALUES
(2, 'Machine Learning Basics', 'ML Lecture Notes Week 1', 'PDF', 4, 'ml,lecture,week1', 1),
(2, 'Deep Learning Intro', 'Neural Networks Basics', 'PDF', 4, 'dl,neural,networks', 0),
(2, 'NLP Basics', 'NLP Introduction', 'Text', 4, 'nlp,introduction', 1),
(2, 'Programming C', 'C Programming Complete Notes', 'PDF', 1, 'c,programming,complete', 1);

-- Sample projects
INSERT INTO projects (user_id, title, description, semester, subject, tech_stack, category, start_date, end_date) VALUES
(2, 'Image Classifier', 'Deep learning based image classification system', 4, 'Deep Learning Intro', 'Python, TensorFlow, Keras', 'Academic', '2026-01-10', '2026-03-15'),
(2, 'Sentiment Analysis', 'NLP based sentiment analysis tool', 4, 'NLP Basics', 'Python, NLTK, Scikit-learn', 'Academic', '2026-02-01', NULL),
(2, 'Weather App', 'React based weather application', 2, 'Web Development', 'React, Node.js, API', 'Personal', '2025-06-01', '2025-07-15');

-- Sample internships
INSERT INTO internships (user_id, company, role, duration_start, duration_end, location, description, skills_gained, payment, status) VALUES
(2, 'TechCorp India', 'ML Intern', '2025-12-01', '2026-02-28', 'Bangalore', 'Worked on ML models for data analysis', 'Python, TensorFlow, Data Analysis', 15000.00, 'Completed'),
(2, 'AI Solutions', 'Data Science Intern', '2026-06-01', NULL, 'Remote', 'Working on NLP projects', 'NLP, Python, Data Processing', 20000.00, 'Applied');

-- Sample payments
INSERT INTO payments (user_id, semester, payment_type, amount, payment_date, payment_method, transaction_id, status) VALUES
(2, 1, 'Semester Fee', 45000.00, '2024-07-15', 'Online Banking', 'TXN001234', 'Paid'),
(2, 2, 'Semester Fee', 45000.00, '2025-01-10', 'Online Banking', 'TXN001235', 'Paid'),
(2, 3, 'Semester Fee', 45000.00, '2025-07-15', 'Credit Card', 'TXN001236', 'Paid'),
(2, 4, 'Semester Fee', 48000.00, '2026-01-10', 'Online Banking', 'TXN001237', 'Paid'),
(2, 4, 'Lab Fee', 5000.00, '2026-01-10', 'Online Banking', 'TXN001238', 'Paid'),
(2, 5, 'Semester Fee', 48000.00, '2026-07-15', 'Online Banking', '', 'Unpaid');

-- Sample holidays
INSERT INTO holidays (user_id, date, holiday_name, type) VALUES
(2, '2026-01-26', 'Republic Day', 'National'),
(2, '2026-03-10', 'Holi', 'National'),
(2, '2026-03-15', 'College Foundation Day', 'College'),
(2, '2026-08-15', 'Independence Day', 'National'),
(2, '2026-10-02', 'Gandhi Jayanti', 'National'),
(2, '2026-11-01', 'Diwali', 'National');

-- Sample events
INSERT INTO events (user_id, event_name, date, time, location, description, type) VALUES
(2, 'Tech Fest 2026', '2026-03-20', '10:00:00', 'College Auditorium', 'Annual technical festival', 'Technical'),
(2, 'AI Workshop', '2026-04-05', '09:00:00', 'Seminar Hall', 'Hands-on AI workshop', 'Workshop'),
(2, 'Sports Day', '2026-04-15', '08:00:00', 'Sports Ground', 'Annual sports day', 'Sports'),
(2, 'Cultural Night', '2026-05-01', '18:00:00', 'College Ground', 'Annual cultural night', 'Cultural');

-- Sample announcements
INSERT INTO announcements (user_id, title, message, type, priority) VALUES
(2, 'Mid Term Schedule Released', 'The mid term examination schedule has been released. Check the exam section for details.', 'Exam', 'High'),
(2, 'Holiday Notice', 'College will remain closed on March 15 for Foundation Day.', 'Holiday', 'Medium'),
(2, 'Assignment Deadline Extended', 'The deadline for ML assignment has been extended to March 25.', 'Homework', 'High'),
(2, 'Tech Fest Registration', 'Register for Tech Fest 2026. Last date: March 15.', 'Event', 'Medium');

-- Sample syllabus (Sem 4 subjects)
INSERT INTO syllabus (user_id, semester, subject, topic, status) VALUES
(2, 4, 'Machine Learning Basics', 'Introduction to ML', 'Completed'),
(2, 4, 'Machine Learning Basics', 'Supervised Learning', 'Completed'),
(2, 4, 'Machine Learning Basics', 'Unsupervised Learning', 'In Progress'),
(2, 4, 'Machine Learning Basics', 'Reinforcement Learning', 'Not Started'),
(2, 4, 'Machine Learning Basics', 'Model Evaluation', 'Not Started'),
(2, 4, 'Deep Learning Intro', 'Neural Networks', 'Completed'),
(2, 4, 'Deep Learning Intro', 'CNN', 'In Progress'),
(2, 4, 'Deep Learning Intro', 'RNN', 'Not Started'),
(2, 4, 'Deep Learning Intro', 'GAN', 'Not Started'),
(2, 4, 'NLP Basics', 'Text Processing', 'Completed'),
(2, 4, 'NLP Basics', 'Sentiment Analysis', 'In Progress'),
(2, 4, 'NLP Basics', 'Named Entity Recognition', 'Not Started'),
(2, 4, 'Big Data Analytics', 'Introduction to Big Data', 'Completed'),
(2, 4, 'Big Data Analytics', 'Hadoop Ecosystem', 'In Progress'),
(2, 4, 'Big Data Analytics', 'Spark', 'Not Started'),
(2, 4, 'Cloud Computing', 'Cloud Basics', 'Completed'),
(2, 4, 'Cloud Computing', 'AWS Services', 'In Progress'),
(2, 4, 'Cloud Computing', 'Azure Services', 'Not Started');

-- Sample skills
INSERT INTO skills (user_id, skill_name, level, category, status) VALUES
(2, 'Python', 'Advanced', 'Programming', 'Completed'),
(2, 'Machine Learning', 'Intermediate', 'ML', 'Learning'),
(2, 'Deep Learning', 'Beginner', 'AI', 'Learning'),
(2, 'HTML/CSS', 'Advanced', 'WebDev', 'Completed'),
(2, 'JavaScript', 'Intermediate', 'WebDev', 'Learning'),
(2, 'SQL', 'Advanced', 'Data', 'Completed'),
(2, 'TensorFlow', 'Beginner', 'AI', 'Learning');

-- Sample certifications
INSERT INTO certifications (user_id, cert_name, issuing_org, date, duration) VALUES
(2, 'Python for Data Science', 'Coursera', '2025-06-15', '4 weeks'),
(2, 'Machine Learning Fundamentals', 'edX', '2025-09-20', '6 weeks'),
(2, 'AWS Cloud Practitioner', 'Amazon', '2025-12-10', '3 weeks');

-- Sample jobs
INSERT INTO jobs (user_id, job_title, company, location, application_date, status, salary) VALUES
(2, 'ML Engineer Intern', 'Google', 'Bangalore', '2026-02-01', 'Applied', '50000/month'),
(2, 'Data Science Intern', 'Microsoft', 'Hyderabad', '2026-02-15', 'Interviewing', '45000/month'),
(2, 'AI Research Intern', 'Amazon', 'Bangalore', '2026-03-01', 'Applied', '60000/month');

-- Sample books
INSERT INTO books (user_id, title, author, subject, borrow_date, return_date, status) VALUES
(2, 'Introduction to Algorithms', 'Thomas Cormen', 'Algorithms', '2026-01-10', '2026-02-10', 'Returned'),
(2, 'Deep Learning', 'Ian Goodfellow', 'Deep Learning', '2026-02-01', NULL, 'Borrowed'),
(2, 'Pattern Recognition', 'Duda Hart', 'ML', '2026-02-15', NULL, 'Borrowed');

-- Sample resources
INSERT INTO resources (user_id, name, type, link, subject, tags) VALUES
(2, 'Andrew Ng ML Course', 'Course', 'https://coursera.org/learn/machine-learning', 'ML', 'ml,course,andrew-ng'),
(2, 'TensorFlow Documentation', 'Article', 'https://tensorflow.org/docs', 'Deep Learning', 'tensorflow,docs'),
(2, 'Kaggle Competitions', 'Other', 'https://kaggle.com', 'Data Science', 'kaggle,competition');

-- =====================================================
-- 30. NOTIFICATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('homework', 'exam', 'payment', 'announcement', 'general') NOT NULL DEFAULT 'general',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    reference_id INT DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notif_user (user_id),
    INDEX idx_notif_user_read (user_id, is_read),
    INDEX idx_notif_type_ref (type, reference_id)
) ENGINE=InnoDB;

-- =====================================================
-- 31. USER SUBJECTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS user_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    semester INT NOT NULL,
    subject_name VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_sem_subject (user_id, semester, subject_name),
    INDEX idx_user_subjects_user (user_id),
    INDEX idx_user_subjects_semester (semester)
) ENGINE=InnoDB;

-- Sample user_subjects (for user_id=2, semester 4)
INSERT INTO user_subjects (user_id, semester, subject_name) VALUES
(2, 4, 'Machine Learning Basics'),
(2, 4, 'Deep Learning Intro'),
(2, 4, 'NLP Basics'),
(2, 4, 'Big Data Analytics'),
(2, 4, 'Cloud Computing'),
(2, 4, 'Elective 2');

-- Sample labs
INSERT INTO labs (user_id, lab_name, subject, date, status) VALUES
(2, 'Lab 1: Python Basics', 'Programming Python', '2024-08-10', 'Completed'),
(2, 'Lab 2: Data Structures', 'Data Structures', '2025-02-15', 'Completed'),
(2, 'Lab 3: ML Model Building', 'Machine Learning Basics', '2026-02-20', 'Completed'),
(2, 'Lab 4: Neural Network', 'Deep Learning Intro', '2026-03-15', 'In Progress');

-- Sample presentations
INSERT INTO presentations (user_id, title, subject, date, status) VALUES
(2, 'ML Project Presentation', 'Machine Learning Basics', '2026-04-01', 'Planned'),
(2, 'NLP Research Paper', 'NLP Basics', '2026-03-10', 'Done');

-- Sample assignments
INSERT INTO assignments (user_id, title, subject, due_date, status) VALUES
(2, 'C Programming Assignment 1', 'Programming C', '2024-09-01', 'Submitted'),
(2, 'Data Structures Assignment', 'Data Structures', '2025-03-15', 'Submitted'),
(2, 'ML Algorithm Implementation', 'Machine Learning Basics', '2026-04-10', 'Not Submitted'),
(2, 'DL Model Training', 'Deep Learning Intro', '2026-04-20', 'Pending');

-- Sample study plans
INSERT INTO study_plans (user_id, subject, topic, date, time_slot, priority, status) VALUES
(2, 'Machine Learning Basics', 'Study SVM Algorithm', '2026-03-25', '09:00-11:00', 'High', 'Planned'),
(2, 'Deep Learning Intro', 'Practice CNN Implementation', '2026-03-26', '14:00-16:00', 'Medium', 'Planned'),
(2, 'NLP Basics', 'Read Sentiment Analysis Paper', '2026-03-27', '10:00-12:00', 'High', 'In Progress');

-- Sample exam prep
INSERT INTO exam_prep (user_id, exam_name, subject, topics_to_cover, start_date, end_date, status, progress) VALUES
(2, 'Mid Term', 'Machine Learning Basics', 'Supervised Learning, Unsupervised Learning, Model Evaluation', '2026-03-01', '2026-03-14', 'In Progress', 60),
(2, 'Mid Term', 'Deep Learning Intro', 'Neural Networks, CNN, RNN', '2026-03-01', '2026-03-16', 'Planned', 0);

-- Sample circulars
INSERT INTO circulars (user_id, title, message, date) VALUES
(2, 'Semester Registration Open', 'Semester 5 registration has started. Last date: July 30.', '2026-06-01'),
(2, 'Library Hours Extended', 'Library will remain open till 8 PM during exam period.', '2026-05-15');
