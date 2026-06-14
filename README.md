# 🎓 CMS (BCA AI/ML) — College Management System

A premium, full‑stack **College Management System** built with **PHP 8**, **MySQL**, and a cyberpunk‑glassmorphism dark UI. Designed for BCA (AI/ML) students at **SITM College**, this CMS streamlines attendance tracking, homework submission, examination management, project portfolios, internship tracking, and more — all wrapped in a modern, responsive dashboard.

---

## ✨ Features

### 🧑‍🎓 Student Module
- **Dashboard** — Centralized overview with key metrics, charts, and quick actions
- **Attendance** — Mark, view, and track attendance with status filters (Present/Absent/Late)
- **Homework** — Submit assignments with file uploads and status tracking
- **Schedule** — Weekly class timetable with lecture/lab/tutorial types
- **Syllabus** — Topic‑wise syllabus tracking (Not Started / In Progress / Completed)

### 📚 Academics
- **Exams** — Exam schedule with date, time, room, and type (Theory/Practical/Viva/Internal)
- **Exam Preparation** — Planned study sessions with topic coverage and progress tracking
- **Grades** — Marks entry and percentage calculation per exam
- **Notes** — Upload and organize PDF, image, text, and video notes by subject
- **Study Plans** — Daily/weekly study planning with priority levels

### 📁 Portfolio & Career
- **Projects** — Academic, personal, internship, and final‑year project management
- **Internships** — Track applications, interviews, selections, and completions
- **Jobs** — Job application tracker with status pipeline
- **Certifications** — Certificate repository with issuing org, date, and link
- **Skills** — Skill catalog with beginner/intermediate/advanced levels
- **Presentations** — Presentation planner with status tracking
- **Resources** — Curated learning resources (videos, articles, courses, books)

### 📋 Administration
- **Events Calendar** — Monthly grid view with event dots, live clock, add‑event form, CRUD
- **Announcements** — Important notices with priority levels (High/Medium/Low)
- **Circulars** — Official circulars with file attachments
- **Groups** — Create and manage student groups
- **Backup & Restore** — Full database export (`.sql`) and import with character‑by‑character parser
- **PDF Export** — Server‑side report generation via **Dompdf** (A4, DejaVu Sans)
- **Excel Export** — CSV report downloads for all modules
- **Change Password** — Secure password update with current password verification

### 🎨 UI/UX
- **Cyberpunk Glassmorphism** — Dark theme with glass‑card effects, neon gradients, and blur backdrops
- **Responsive** — Fully mobile‑friendly with collapsible sidebar
- **Search Bar** — Centered glassmorphism search with neon focus glow
- **Notifications** — Real‑time bell icon with AJAX polling every 30 seconds
- **SweetAlert2** — Beautiful dark‑theme modal alerts with custom styling
- **Particle Animation** — Floating cyan particle network on the login page
- **Cache‑Busted Assets** — Automatic cache refresh via `filemtime()` versioning

---

## 🛠 Tech Stack

| Layer | Technology |
|---|---|
| **Frontend** | HTML5, CSS3, JavaScript, Bootstrap 5.3, Font Awesome 6 |
| **Backend** | PHP 8.2 (PDO, prepared statements, sessions, CSRF protection) |
| **Database** | MySQL / MariaDB (31 tables, relational) |
| **PDF Engine** | Dompdf 2.0.4 (custom PSR‑4 autoloader) |
| **Animations** | Canvas particle system, CSS transitions, backdrop‑filter |
| **Security** | CSRF tokens, password hashing (`bcrypt`), session‑based auth |
| **Server** | Apache (XAMPP) |

---

## 📁 Database Structure (31 Tables)

`announcements`, `assignments`, `attendance`, `books`, `certifications`, `circulars`, `events`, `exam_prep`, `exams`, `grades`, `group_members`, `groups`, `holidays`, `homework`, `internships`, `jobs`, `labs`, `notes`, `notifications`, `payments`, `presentations`, `profiles`, `projects`, `reports`, `resources`, `schedule`, `skills`, `study_plans`, `syllabus`, `user_subjects`, `users`

---

## 🚀 Installation

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (PHP 8+, MySQL, Apache)
- Git (for cloning)
- A modern web browser (Chrome, Firefox, Edge)

### Setup

```bash
# 1. Clone the repository
git clone https://github.com/Rohitkumarsaw/CMS_BCA_AI_ML.git

# 2. Move to XAMPP htdocs
copy CMS_BCA_AI_ML C:\xampp\htdocs\bca-portal

# 3. Start Apache & MySQL via XAMPP Control Panel

# 4. Create the database
# Open phpMyAdmin (http://localhost/phpmyadmin) and import:
#    database/bca_portal_db.sql

# 5. Configure database connection
# Edit: config/config.php (default: root, no password)

# 6. Access the portal
#    http://localhost/bca-portal
```

---

## 🎯 Sample Data

The database comes pre‑seeded with one sample entry per module for immediate testing:
- 📅 Upcoming events and holidays
- 📝 Homework assignments with due dates
- 📊 Attendance records and grade entries
- 📚 Books, notes, and study resources
- 💼 Internship and job applications
- 🏆 Certifications and project portfolios
- 📋 Exam schedules and syllabus topics

---

## 🔐 Security

- **CSRF Protection** — Every form includes a unique token validated server‑side
- **Password Hashing** — `password_hash()` with `PASSWORD_DEFAULT` (bcrypt)
- **Session Management** — Strict login checks on every protected page
- **Prepared Statements** — All SQL queries use PDO prepared statements to prevent SQL injection
- **Input Sanitization** — User input is sanitized before display

---

## 📸 Preview

> *Login — Cyberpunk glassmorphism with particle animation*
> ![Login](https://via.placeholder.com/800x450/0a0a1a/00d4ff?text=Login+Page)

> *Dashboard — Dark theme with neon accents*
> ![Dashboard](https://via.placeholder.com/800x450/191c24/8a2be2?text=Dashboard)

> *Events Calendar — Monthly grid with live clock*
> ![Events](https://via.placeholder.com/800x450/191c24/da70d6?text=Events+Calendar)

---

## 🧑‍💻 Developer

**Rohit Kumar Saw**  
BCA (AI/ML) — SITM College

[![GitHub](https://img.shields.io/badge/GitHub-Rohitkumarsaw-8a2be2?style=flat&logo=github)](https://github.com/Rohitkumarsaw)

---

## 📄 License

This project is developed for educational purposes as part of the BCA (AI/ML) programme at **SITM College**.
