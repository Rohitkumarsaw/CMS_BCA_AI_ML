<div align="center">
  <img src="favicon.svg" width="64" height="64" alt="CMS Logo"/>
  <h1>CMS (BCA AI/ML)</h1>
  <p><strong>Course Management System — SITM College</strong></p>

  [![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)](https://php.net)
  [![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)](https://mysql.com)
  [![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com)
  [![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
  [![PHPMailer](https://img.shields.io/badge/PHPMailer-v7.1-FF5722?logo=gmail&logoColor=white)](https://github.com/PHPMailer/PHPMailer)
  [![License](https://img.shields.io/badge/License-Educational-8a2be2)](LICENSE)
  [![Last Commit](https://img.shields.io/github/last-commit/Rohitkumarsaw/CMS_BCA_AI_ML)](https://github.com/Rohitkumarsaw/CMS_BCA_AI_ML/commits/main)

  <hr/>
</div>

---

A full-stack **Course Management System** built for BCA (AI/ML) students at **SITM College**. Features a cyberpunk-glassmorphism dark UI, 42+ modules, real-time AJAX interactions, email notifications, and PDF/CSV reporting.

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
| **Labs** | Lab session management and tracking |
| **Assignments** | Full assignment lifecycle with submission tracking |

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
| **Subjects** | Manage and organize academic subjects |
| **Routine** | Daily class routine viewer |

</details>

<details open>
<summary><strong>Portfolio & Career</strong></summary>

| Module | Description |
|---|---|
| **Projects** | Academic, personal, internship, and final-year project tracking |
| **Internships** | Application pipeline — applied, interview, selected, completed |
| **Jobs** | Job application tracker with status pipeline |
| **Placement** | Placement preparation and tracking |
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
| **Home** | Premium welcome page with hero, stats, features, activity timeline |
| **Events Calendar** | Monthly grid view, live clock, CRUD operations |
| **Announcements** | Priority-based notices (High/Medium/Low) |
| **Circulars** | Official circulars with file attachments |
| **Groups** | Create and manage student groups |
| **Faculty** | Faculty management and directory |
| **Leave** | Leave application and approval system |
| **Profile** | User profile management |
| **Activity History** | Full audit log with CSV export |
| **Email Notifications** | Real-time email alerts via PHPMailer + Gmail SMTP for all CRUD operations (OTP removed, kept for system alerts) |
| **SMTP Settings** | In-app SMTP configuration with test email — recipient email editable from UI |
| **Backup & Restore** | Full database export/import with character-level SQL parser |
| **About Settings** | Site information and configuration |
| **Reports** | Analytics dashboard with PDF/CSV export and email reporting |

</details>

<details open>
<summary><strong>UI/UX</strong></summary>

| Feature | Description |
|---|---|
| **Sections Hub** | Visual grid of all 42 modules with instant client-side search |
| **Sidebar Toggle** | Enable/disable sidebar for full-screen mode (persisted via localStorage) |
| **Cyberpunk Glassmorphism** | Dark theme with glass-card effects, neon gradients, blur backdrops |
| **Module Search** | Instant client-side search bar on every module page |
| **Responsive** | Mobile-first with collapsible sidebar and overlay |
| **SweetAlert2** | Custom dark-theme modal alerts |
| **Particle Animation** | Canvas-based cyan particle network on login |
| **Cache-Busting** | Automatic refreshing via `filemtime()` versioning |
| **Back to Sections** | Navigation button on all module pages |
| **Two-Factor Auth** | Google Authenticator TOTP with backup password |

</details>

---

## Tech Stack

<div align="center">

| Layer | Technology |
|---|---|
| **Frontend** | HTML5, CSS3, JavaScript, Bootstrap 5.3, Font Awesome 6 |
| **Backend** | PHP 8.2 — PDO, prepared statements, sessions, CSRF |
| **Database** | MySQL / MariaDB — 31+ relational tables |
| **Email** | PHPMailer 7.1.1 — Gmail SMTP with App Password |
| **PDF** | Dompdf 2.0.4 with custom PSR-4 autoloader |
| **CSV** | Custom CSV export with proper encoding |
| **Security** | CSRF tokens, bcrypt hashing, prepared statements, TOTP 2FA |
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

# [Optional] Configure email notifications:
#   Copy config/mail.example.php → config/mail.php
#   Edit config/mail.php with your Gmail App Password

# Configure database connection:
#   Edit config/config.php (default: root, no password)

# Access: http://localhost/bca-portal

# Default login credentials are seeded in the database
```

---

## Database Schema

<details>
<summary><strong>Core Tables</strong></summary>

```
announcements     assignments     attendance     books
certifications    circulars       events         exam_prep
exams             grades          group_members  groups
holidays          homework        internships    jobs
labs              notes           notifications  payments
presentations     profiles        projects       reports
resources         schedule        skills         study_plans
syllabus          user_subjects   users
```

</details>

<details>
<summary><strong>Additional Tables (v2+)</strong></summary>

```
activity_logs      academic_roadmaps     roadmap_items
system_settings
```

</details>

---

## Security

- **CSRF Protection** — Token validation on every form submission
- **Password Hashing** — `password_hash()` with bcrypt
- **Two-Factor Auth** — Google Authenticator TOTP (time-based one-time密码) for login
- **Backup Password** — bcrypt-hashed fallback for TOTP unavailability
- **Session Management** — Strict login enforcement on all protected routes
- **Prepared Statements** — SQL injection prevention via PDO
- **Input Sanitization** — Output escaped with `htmlspecialchars()`
- **Credential Protection** — SMTP credentials excluded from git via `.gitignore`

---

## New in Latest Version

| Feature | Details |
|---|---|
| **Google Authenticator 2FA** | Replaced email OTP with TOTP via Google Authenticator. QR code setup (client-side qrcodejs), copy secret key, enable/disable |
| **Backup Password** | bcrypt-hashed fallback login when GA app is unavailable. Set, verify (AJAX), change, or remove from GA setup page |
| **Session-Resilient TOTP** | `totp_uid` hidden field in forms survives session loss — no more "Session expired" errors |
| **SweetAlert2 Confirmations** | All destructive actions (disable GA, remove backup) use Swal instead of native `confirm()` |
| **Profile Navbar Button** | "Back to Sections" now visible on profile page |

---

## Project Structure

```
bca-portal/
├── libraries/
│   └── GoogleAuthenticator.php  # TOTP secret generation, code verification, QR URL
├── ajax/
│   └── notifications.php
├── config/
│   ├── config.php
│   ├── db_connection.php
│   ├── mail.example.php
│   └── mail.php (gitignored)
├── css/
│   ├── [module].css          # Per-module stylesheets
│   ├── style.css             # Core layout + sidebar collapse CSS
│   ├── dark-fix.css
│   └── sweetalert2-dark.css
├── includes/
│   ├── footer.php
│   ├── functions.php
│   ├── header.php            # Global sidebar-collapse inline script
│   ├── navbar.php            # Sidebar toggle button (mobile)
│   └── sidebar.php           # 42 navigation links
├── js/
│   ├── [module].js           # Per-module JavaScript
│   └── main.js               # Core JS (toggleSidebar, notifications)
├── reports/
│   ├── reports.php           # Reports dashboard
│   ├── generate_pdf.php      # PDF generation
│   ├── export_csv.php        # CSV export
│   ├── get_data.php          # AJAX data endpoint
│   └── email_report.php      # Email report distribution
├── sql/
│   ├── bca_portal_db.sql
│   └── migration_v2.sql
├── uploads/
├── ga_setup.php               # Google Authenticator 2FA setup + backup password management
├── home.php                    # Premium welcome page
├── sections.php                # Sections hub with search + sidebar toggle
├── dashboard.php
├── attendance.php
├── homework.php
├── schedule.php
├── [all 42 module pages].php
├── login.php
├── logout.php
├── index.php
└── ...
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
