<div align="center">
  <img src="favicon.svg" width="64" height="64" alt="CMS Logo"/>
  <h1>CMS (BCA AI/ML)</h1>
  <p><strong>Course Management System — SITM College</strong></p>

  [![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)](https://php.net)
  [![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)](https://mysql.com)
  [![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com)
  [![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
  [![License](https://img.shields.io/badge/License-Educational-8a2be2)](LICENSE)
  [![Last Commit](https://img.shields.io/github/last-commit/Rohitkumarsaw/CMS_BCA_AI_ML)](https://github.com/Rohitkumarsaw/CMS_BCA_AI_ML/commits/main)

  <hr/>
</div>

---

A full-stack **Course Management System** built for BCA (AI/ML) students at **SITM College**. Features a cyberpunk-glassmorphism dark UI, 30+ modules, and real-time AJAX interactions.

## Features

<details open>
<summary><strong>Student</strong></summary>

| Module | Description |
|---|---|
| **Dashboard** | Centralized analytics with charts, progress bars, and quick actions |
| **Attendance** | Mark, view, and filter attendance (Present/Absent/Late) |
| **Homework** | Submit assignments with file uploads and status tracking |
| **Schedule** | Weekly timetable with lecture/lab/tutorial slots |
| **Syllabus** | Topic-wise progress tracking (Not Started / In Progress / Completed) |

</details>

<details open>
<summary><strong>Academics</strong></summary>

| Module | Description |
|---|---|
| **Exams** | Schedule with date, time, room, and exam type |
| **Exam Preparation** | Study sessions with topic coverage and progress |
| **Grades** | Marks entry and percentage calculation per exam |
| **Notes** | Upload and organize notes by subject (PDF, image, video) |
| **Study Plans** | Daily/weekly planning with priority levels |

</details>

<details open>
<summary><strong>Portfolio & Career</strong></summary>

| Module | Description |
|---|---|
| **Projects** | Academic, personal, internship, and final-year project tracking |
| **Internships** | Application pipeline — applied, interview, selected, completed |
| **Jobs** | Job application tracker with status pipeline |
| **Certifications** | Certificate repository with issuing org, date, links |
| **Skills** | Skill catalog with beginner/intermediate/advanced levels |
| **Presentations** | Presentation planner with status tracking |
| **Resources** | Curated learning resources (videos, articles, courses, books) |
| **Academic Roadmap** | Checklist-based progress tracking with completion bars |

</details>

<details open>
<summary><strong>Administration</strong></summary>

| Module | Description |
|---|---|
| **Events Calendar** | Monthly grid view, live clock, CRUD operations |
| **Announcements** | Priority-based notices (High/Medium/Low) |
| **Circulars** | Official circulars with file attachments |
| **Groups** | Create and manage student groups |
| **Activity History** | Full audit log with CSV export |
| **Backup & Restore** | Full database export/import with character-level SQL parser |
| **PDF Export** | Server-side A4 reports via Dompdf |
| **CSV Export** | Downloadable reports for all modules |

</details>

<details open>
<summary><strong>UI/UX</strong></summary>

| Feature | Description |
|---|---|
| **Cyberpunk Glassmorphism** | Dark theme with glass-card effects, neon gradients, blur backdrops |
| **Section Search** | 43 per-module instant client-side search bars |
| **Responsive** | Mobile-first with collapsible sidebar |
| **SweetAlert2** | Custom dark-theme modal alerts |
| **Particle Animation** | Canvas-based cyan particle network on login |
| **Cache-Busting** | Automatic refreshing via `filemtime()` versioning |

</details>

---

## Tech Stack

<div align="center">

| Layer | Technology |
|---|---|
| **Frontend** | HTML5, CSS3, JavaScript, Bootstrap 5.3, Font Awesome 6 |
| **Backend** | PHP 8.2 — PDO, prepared statements, sessions, CSRF |
| **Database** | MySQL / MariaDB — 31 relational tables |
| **PDF** | Dompdf 2.0.4 with custom PSR-4 autoloader |
| **Security** | CSRF tokens, bcrypt hashing, prepared statements |
| **Server** | Apache (XAMPP) |

</div>

---

## Installation

```bash
# Clone the repo
git clone https://github.com/Rohitkumarsaw/CMS_BCA_AI_ML.git

# Move to XAMPP htdocs
copy CMS_BCA_AI_ML C:\xampp\htdocs\bca-portal

# Start Apache & MySQL via XAMPP Control Panel

# Open phpMyAdmin and import:
#   database/bca_portal_db.sql

# Configure database connection:
#   Edit config/config.php (default: root, no password)

# Access: http://localhost/bca-portal
```

## Database Schema

<details>
<summary><strong>31 Tables</strong></summary>

```  
announcements    assignments     attendance     books
certifications   circulars       events         exam_prep
exams            grades          group_members  groups
holidays         homework        internships    jobs
labs             notes           notifications  payments
presentations    profiles        projects       reports
resources        schedule        skills         study_plans
syllabus         user_subjects   users
```

</details>

<details>
<summary><strong>Additional Tables (v2)</strong></summary>

```
activity_logs      academic_roadmaps     roadmap_items
```

</details>

---

## Security

- **CSRF Protection** — Token validation on every form submission
- **Password Hashing** — `password_hash()` with bcrypt
- **Session Management** — Strict login enforcement on all protected routes
- **Prepared Statements** — SQL injection prevention via PDO
- **Input Sanitization** — Output escaped with `htmlspecialchars()`

---

## Project Structure

```
bca-portal/
├── ajax/
│   └── notifications.php
├── config/
│   ├── config.php
│   └── db_connection.php
├── css/
│   ├── about_settings.css
│   ├── achievements.css
│   ├── announcement.css
│   ├── assignment.css
│   ├── attendance.css
│   ├── auth.css
│   ├── backup.css
│   ├── certifications.css
│   ├── circular.css
│   ├── dark-fix.css
│   ├── dashboard.css
│   ├── event.css
│   ├── exam.css
│   ├── exam_prep.css
│   ├── faculty.css
│   ├── grades.css
│   ├── groups.css
│   ├── history.css
│   ├── holiday.css
│   ├── homework.css
│   ├── internship.css
│   ├── jobs.css
│   ├── lab.css
│   ├── leave.css
│   ├── library.css
│   ├── meetings.css
│   ├── notes.css
│   ├── payment.css
│   ├── placement.css
│   ├── planner.css
│   ├── presentation.css
│   ├── profile.css
│   ├── projects.css
│   ├── reports.css
│   ├── resources.css
│   ├── roadmap.css
│   ├── routine.css
│   ├── schedule.css
│   ├── skills.css
│   ├── study_plan.css
│   ├── style.css
│   ├── sweetalert2-dark.css
│   └── syllabus.css
├── includes/
│   ├── backup_handler.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   ├── navbar.php
│   └── sidebar.php
├── js/
│   ├── about_settings.js
│   ├── achievements.js
│   ├── announcement.js
│   ├── assignment.js
│   ├── attendance.js
│   ├── certifications.js
│   ├── circular.js
│   ├── dashboard.js
│   ├── event_calendar.js
│   ├── exam.js
│   ├── exam_prep.js
│   ├── faculty.js
│   ├── grades.js
│   ├── groups.js
│   ├── history.js
│   ├── holiday.js
│   ├── homework.js
│   ├── internship.js
│   ├── jobs.js
│   ├── lab.js
│   ├── leave.js
│   ├── library.js
│   ├── main.js
│   ├── meetings.js
│   ├── notes.js
│   ├── payment.js
│   ├── placement.js
│   ├── planner.js
│   ├── presentation.js
│   ├── profile.js
│   ├── projects.js
│   ├── reports.js
│   ├── resources.js
│   ├── roadmap.js
│   ├── routine.js
│   ├── schedule.js
│   ├── skills.js
│   ├── study_plan.js
│   ├── subjects.js
│   └── syllabus.js
├── sql/
│   ├── bca_portal_db.sql
│   └── migration_v2.sql
├── uploads/
├── about_settings.php
├── about_settings_handler.php
├── achievements.php
├── achievements_handler.php
├── add_announcement.php
├── add_assignment.php
├── add_attendance.php
├── add_book.php
├── add_certification.php
├── add_circular.php
├── add_event.php
├── add_exam.php
├── add_exam_prep.php
├── add_grade.php
├── add_holiday.php
├── add_homework.php
├── add_internship.php
├── add_job.php
├── add_lab.php
├── add_note.php
├── add_payment.php
├── add_presentation.php
├── add_project.php
├── add_resource.php
├── add_schedule.php
├── add_skill.php
├── add_study_plan.php
├── announcement.php
├── assignment.php
├── attendance.php
├── backup.php
├── certifications.php
├── change_password.php
├── circular.php
├── create_group.php
├── dashboard.php
├── delete.php
├── edit_announcement.php
├── edit_assignment.php
├── edit_attendance.php
├── edit_book.php
├── edit_certification.php
├── edit_circular.php
├── edit_event.php
├── edit_exam.php
├── edit_exam_prep.php
├── edit_grade.php
├── edit_group.php
├── edit_holiday.php
├── edit_homework.php
├── edit_internship.php
├── edit_job.php
├── edit_lab.php
├── edit_note.php
├── edit_payment.php
├── edit_presentation.php
├── edit_profile.php
├── edit_project.php
├── edit_resource.php
├── edit_schedule.php
├── edit_skill.php
├── edit_study_plan.php
├── event.php
├── event_handler.php
├── exam.php
├── exam_actions.php
├── exam_prep.php
├── export_excel.php
├── export_pdf.php
├── faculty.php
├── faculty_handler.php
├── grades.php
├── group.php
├── history.php
├── history_handler.php
├── holiday.php
├── homework.php
├── index.php
├── internship.php
├── jobs.php
├── lab.php
├── leave.php
├── leave_handler.php
├── library.php
├── login.php
├── logout.php
├── manage_subjects.php
├── meetings.php
├── meetings_handler.php
├── notes.php
├── payment.php
├── placement.php
├── placement_handler.php
├── planner.php
├── planner_handler.php
├── presentation.php
├── profile.php
├── projects.php
├── reports.php
├── resources.php
├── roadmap.php
├── roadmap_handler.php
├── routine.php
├── routine_handler.php
├── schedule.php
├── skills.php
├── study_plan.php
├── subjects_handler.php
├── syllabus.php
├── update_syllabus.php
└── view.php
```

---

## Developer

**Rohit Kumar Saw**  
BCA (AI/ML) — SITM College

[![GitHub](https://img.shields.io/badge/GitHub-Rohitkumarsaw-8a2be2?logo=github&logoColor=white)](https://github.com/Rohitkumarsaw)

---

<div align="center">
  <sub>Built with ❤️ for BCA (AI/ML) at SITM College</sub>
  <br/>
  <sub>&copy; 2026 Rohit Kumar Saw &mdash; Educational Project</sub>
</div>
