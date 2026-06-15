-- ============================================================
-- CMS (BCA AI/ML) — Complete Database Schema
-- ============================================================

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin','student','faculty') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample user: rohit_sitm / Sitm@2026 (bcrypt)
-- INSERT INTO users (username, password, email, full_name, role) VALUES ('rohit_sitm', '$2y$10$...', 'rohit@sitm.ac.in', 'Rohit Kumar', 'admin');

-- ============================================================
-- Achievements Wall
-- ============================================================
CREATE TABLE IF NOT EXISTS achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    category ENUM('award','hackathon','extracurricular','other') DEFAULT 'other',
    description TEXT,
    issuer VARCHAR(255),
    date_achieved DATE,
    link VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Meeting / Class Links
-- ============================================================
CREATE TABLE IF NOT EXISTS meeting_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    platform ENUM('zoom','google_meet','microsoft_teams','other') DEFAULT 'zoom',
    url VARCHAR(500) NOT NULL,
    description TEXT,
    meeting_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Placement Tracker
-- ============================================================
CREATE TABLE IF NOT EXISTS placement_tracker (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    role VARCHAR(255),
    application_date DATE,
    status ENUM('applied','shortlisted','interviewed','selected','rejected') DEFAULT 'applied',
    round_details TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Existing tables (abbreviated)
-- ============================================================
-- announcements, assignments, attendance, books, certifications,
-- circulars, events, exam_prep, exams, grades, groups, holidays,
-- homework, internships, jobs, labs, notes, presentations, projects,
-- reports, resources, schedule, skills, study_plans, syllabus,
-- faculty, leave_applications, routine_tasks, shopping_planner,
-- current_inventory, system_settings, notifications
