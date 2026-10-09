Al Amin's Math Care Management System
Target Institution: Al Amin's Math Care (Farmgate, Dhaka, Bangladesh)  |  Tech Stack: PHP 8.3+, MySQL 8.0+, Bootstrap 5.3, Native Vanilla JS
1. Executive Summary & System Overview
Al Amin's Math Care Management System is a modern, high-performance coaching management system and web portal engineered specifically for specialized mathematics education located in Farmgate, Dhaka. The platform serves students across Class 6–12, SSC General & Higher Math, HSC 1st & 2nd Paper, and Engineering Admission Coaching.
The platform integrates four core operational pillars into a unified native web application:
•	Public Web Portal: A dual-language (Bengali & English) responsive website for prospective students to explore catalogs, book free demo classes, apply online, and verify student ID cards.
•	Student & Parent Portal (/portal/): A self-service portal for students and guardians to track attendance visual charts, review due fees, inspect marksheet results, and download money receipts.
•	Admin Management Dashboard (/admin/): A complete administrative suite for managing student enrollments, batch routines, daily attendance tracking, automated SMS messaging, and fee collection.
•	Automated SMS Notification Engine: An integrated SMS gateway library (includes/sms.php) triggering real-time absent alerts, fee reminders, and admission confirmations in Bengali.
2. Core Features & System Capabilities
2.1 Public Web Portal Features
•	Glassmorphism UI & Modern Design: Built with custom design tokens, responsive CSS layout, accessible components, and smooth micro-animations.
•	Dual Language Support (i18n): Seamless dynamic language switching between Bengali (বাংলা) and English powered by a custom session dictionary.
•	Course & Batch Catalog: Interactive listing of math classes, batch schedules, course syllabi, fee structures, and real-time seat availability badges (e.g., 'Only 3 Seats Left').
•	Free Demo Class Booking & Admission: Online reservation workflows allowing new candidates to book trial sessions and complete online admission registrations.
•	Personalized Result & Marksheet Lookup: Instant examination mark lookup where students and parents search model test scores using Student ID / Roll and Guardian Phone Number.
•	Digital ID Card Verification (verify-id.php): QR code scanning endpoint allowing instant online authenticity checks of physical student ID cards.
•	Sticky Navigation & Live Messaging: Mobile-optimized touch navigation paired with a floating WhatsApp messaging widget for direct candidate queries.
2.2 Student & Guardian Portal (/portal/)
•	Secure Authentication: Protected login via Student ID and Guardian PIN (last 4 digits of guardian phone number) backed by brute-force rate-limiting.
•	Interactive Dashboard Visuals: Monthly donut visual chart displaying attendance breakdown (present, absent, late).
•	Financial Tracking & Receipts: Clear view of due fee status, payment histories, and instant print/download capabilities for digital money receipts.
•	Digital Student ID: On-screen digital ID card view for student identification.
2.3 Admin Management Dashboard (/admin/)
•	Student Profile Management: Comprehensive student enrollment tracking, batch assignment, profile updates, and guardian contacts.
•	Batch & Routine Scheduling: Class timing management, room allocations, instructor assignments, and maximum seat capacity controls.
•	Attendance Tracking System: Daily attendance marking tool that automatically triggers SMS alerts to guardians of absent students.
•	Fee Management & Invoicing: Collection tracking, automated due fee notifications, receipt generation, and monthly revenue overview.
•	Result & Exam Engine: Upload weekly/monthly model test marks, auto-calculate merit rankings, and export printable PDF result sheets.
•	Notice Board & Circulars: Publication of site notices, schedule change alerts, and downloadable PDF circulars.
•	Automated SMS Gateway (includes/sms.php): Bulk and transactional SMS dispatch engine for Bengali messages with rate-limiting and audit logging.
3. Technology Stack & Architectural Matrix
The application leverages a lightweight, high-performance native PHP architecture designed for fast response times and low server memory footprint.
Component	Technology / Library	Role & Architectural Implementation
Backend Engine	PHP 8.3+ (Native Modular)	Native modular PHP architecture, Object-Oriented/Procedural helpers, session management.
Database Layer	MySQL 8.0+ / MariaDB	PDO Database Abstraction with strict prepared statements and singleton connection pattern.
Frontend UI	HTML5, CSS3, Vanilla JS (ES6)	Custom Glassmorphism CSS design tokens, zero heavy JS framework dependencies.
UI Framework	Bootstrap 5.3 + FontAwesome	Responsive grid system, modal dialogs, and Bootstrap Icons integration.
Typography	Google Fonts	Inter (English body/headings) and Noto Sans Bengali (Bangla script rendering).
Web Server	Apache / PHP Built-in Server	.htaccess URL rewrites, asset caching, compression, and router.php for local dev.
Internationalization	Custom i18n Engine	Session-based dictionary switcher using lang/bn.php and lang/en.php files.

4. Repository Directory Structure
The repository follows a clean, modular structure separating configuration, administrative modules, public routes, and shared libraries:
•	admin/ — Admin Dashboard modules, CRUD management, exam entry, and report generation.
•	api/ — AJAX REST endpoints and data export handlers for dynamic UI updates.
•	assets/ — CSS stylesheets, JS scripts, icons, images, and static resources.
•	config/ — Database connection settings (database.php) and global app configuration (app.php).
•	database/ — SQL schema migration scripts and setup files (infinityfree_setup.sql).
•	includes/ — Core security and helper libraries: db.php (PDO wrapper), sms.php (SMS gateway API), csrf.php (Anti-CSRF), i18n.php (Language engine), and helpers.php.
•	lang/ — Localization dictionaries containing bn.php (Bengali) and en.php (English) translation strings.
•	portal/ — Student and Guardian portal user interface and authentication handlers.
•	storage/ — Secure directory for notice PDF uploads, generated money receipts, and system activity logs.
•	.env.example — Environment variable template for local database credentials and debug flags.
•	.htaccess — Apache rewrite rules, security headers, compression, and clean URL routing.
•	router.php — Development server routing script for native PHP built-in web server.
•	start_dev.bat — 1-Click Windows batch script to start local development server instantly.
5. Local Development Setup & Installation Guide
5.1 System Prerequisites
•	PHP 8.1 or higher (PHP 8.3 recommended with pdo_mysql extension enabled)
•	MySQL 8.0+ or MariaDB Server (Running on port 3306 or 3307)
•	Composer (Optional for third-party packages)
•	Modern Web Browser (Chrome, Edge, Firefox, or Safari)
5.2 Step-by-Step Installation
Step 1: Clone the Repository
Execute the git clone command to pull the repository to your local web directory:
git clone https://github.com/your-username/al-amin-math-care.git
cd al-amin-math-care
Step 2: Environment Configuration
Copy the environment configuration template and set your database credentials:
cp .env.example .env
Configure the .env file with your local database connection parameters:
APP_ENV=development
APP_DEBUG=true

DB_HOST=127.0.0.1
DB_PORT=3307
DB_NAME=alaminmathcare
DB_USER=root
DB_PASS=your_db_password
Step 3: Database Schema Migration
Import the initial database setup script into MySQL:
mysql -u root -p alaminmathcare < database/infinityfree_setup.sql
Step 4: Launch Development Server
Start the local web server using one of the following methods:
# Method A: Windows 1-Click Batch Script
start_dev.bat

# Method B: Native PHP Server Router
php -S 0.0.0.0:8000 router.php
5.3 Application Access Endpoints
•	Public Web Portal: http://localhost:8000/
•	Student & Parent Portal: http://localhost:8000/portal/login.php
•	Admin Control Panel: http://localhost:8000/admin/
6. Security Architecture & Defense Best Practices
The application applies defense-in-depth web security standards to protect institutional and student data:
•	Prepared Statements (PDO): All database queries utilize strict PDO prepared statements, completely insulating the backend against SQL Injection (SQLi) attacks.
•	XSS Sanitization & Escaping: Global input sanitization via clean_input() and output HTML escaping using e() helpers prevent Cross-Site Scripting (XSS).
•	CSRF Token Protection: Form validation tokens managed by includes/csrf.php ensure state-changing HTTP POST actions originate from authenticated sessions.
•	Brute-Force Rate Limiting: Login attempt throttling is enforced on both Admin (/admin/) and Student portal (/portal/) authentication endpoints.
•	HTTP Security Headers (.htaccess): Configured with X-Frame-Options (clickjacking defense), X-XSS-Protection, X-Content-Type-Options, and Strict Transport Security (HSTS).
7. Contact & Support Information
Institution: Al Amin's Math Care (Farmgate, Dhaka, Bangladesh)
Phone / WhatsApp: +8801520102248
Official Website: alaminmathcare.com
