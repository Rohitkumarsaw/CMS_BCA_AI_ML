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

### Default Login

| Username | Password |
|---|---|
| `rohit_sitm` | `Sitm@2026` |

---

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
├── config/              # Database and app configuration
├── css/                 # Stylesheets (45 files)
├── js/                  # JavaScript modules (40 files)
├── includes/            # Header, navbar, sidebar, footer, functions
├── sql/                 # Database migrations and schema
├── uploads/             # User-uploaded files
└── *.php                # 100+ module pages and handlers
```

---

## Developer

**Rohit Kumar Saw**  
BCA (AI/ML) — SITM College

[![GitHub](https://img.shields.io/badge/GitHub-Rohitkumarsaw-8a2be2?logo=github&logoColor=white)](https://github.com/Rohitkumarsaw)

---

<div align="center">
  <sub>Built with for BCA (AI/ML) at SITM College</sub>
  <br/>
  <sub>&copy; 2026 Rohit Kumar Saw &mdash; Educational Project</sub>
</div>
