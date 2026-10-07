# Al Amin's Math Care — Architecture Document

> **Project:** Al Amin's Math Care — Mathematics Coaching Website + Admin Panel  
> **Domain:** alaminmathcare.com  
> **Stack:** PHP 8.x + MySQL 8.x + HTML5 / CSS3 / Bootstrap 5 + Vanilla JavaScript  
> **Hosting:** Linux cPanel (Shared / VPS)  
> **Document:** architecture.md  
> **Last Updated:** September 2026

---

## 🏛️ Engineering Roles & Responsibilities

In designing, maintaining, and implementing this architecture, you are acting as a:

* **Senior Software Architect:** Ensuring system decoupling, high cohesion, clean URL routing, security-first posture, scalability, and seamless modular expansion.
* **Senior UI/UX Design Engineer:** Spearheading modern next-generation (2026+) visual aesthetics, dark/light harmonious color systems, sleek glassmorphism, mathematical precision styling, fluid mobile-first ergonomics (thumb-zone UX, floating CTAs, micro-animations, fast modals), bilingual typography perfection (Inter + Noto Sans Bengali), frictionless conversion funnels (30-60s instant demo/admission forms), and WCAG 2.1 AA accessible component design.
* **Senior Backend Engineer:** Writing robust, defensive PHP 8.x code with PDO prepared statements, session security, CSRF protection, and high-performance API endpoints.
* **Senior Frontend Engineer:** Crafting mobile-first, lightweight HTML5/CSS3/Bootstrap 5 layouts with native Bangla/English typography, micro-interactions, and fast load times.
* **Database Architect:** Designing an ACID-compliant, UTF8MB4 MySQL schema with strict foreign keys, indexing, audit logs, and soft-delete integrity.
* **QA Engineer:** Ensuring zero broken links, rigorous input sanitization, rate limiting, and cross-browser/mobile usability across devices.
* **DevOps Engineer:** Configuring Apache `.htaccess` security hardening, SSL/TLS enforcement, Cloudflare DNS, file permissions (755/644), and zero-downtime cPanel deployment.

---

## Table of Contents

1. [System Overview & Architecture Goals](#1-system-overview--architecture-goals)
2. [High-Level Architecture & Request Flow](#2-high-level-architecture--request-flow)
3. [Technology Stack & Decisions](#3-technology-stack--decisions)
4. [Project Directory & File Structure](#4-project-directory--file-structure)
5. [Application Layer Architecture](#5-application-layer-architecture)
6. [Database & Data Flow Architecture](#6-database--data-flow-architecture)
7. [Comprehensive Security Architecture (Hardening Guide)](#7-comprehensive-security-architecture-hardening-guide)
8. [Domain Setup & Production Deployment Architecture](#8-domain-setup--production-deployment-architecture)
9. [API & Dynamic Operations](#9-api--dynamic-operations)
10. [Localization Architecture (English & Bangla Toggle)](#10-localization-architecture-english--bangla-toggle)
11. [SEO & Discoverability Architecture](#11-seo--discoverability-architecture)
12. [Performance & Caching Architecture](#12-performance--caching-architecture)
13. [Admin Panel & Role-Based Access Control (RBAC)](#13-admin-panel--role-based-access-control-rbac)
14. [Future Scalability & Modular Upgrades](#14-future-scalability--modular-upgrades)

---

## 1. System Overview & Architecture Goals

Al Amin's Math Care ওয়েবসাইট একটি **Mobile-first, High-Performance Lead Generation + Student Information + Admission Support Platform**। এটি শুধুমাত্র একটি স্ট্যাটিক সাইট নয়, বরং কোচিং সেন্টারের সামগ্রিক পরিচালনা ও ছাত্র-অভিভাবকদের আস্থার প্ল্যাটফর্ম।

> 🎯 **কোর স্পেশালাইজেশন নীতি (Mathematics Only):**  
> এই কোচিং সেন্টারটিতে **শুধুমাত্র গণিত (Mathematics)** পড়ানো হবে। এখানে অন্য কোনো বিষয়ের মিশ্রণ নেই। ওয়েবসাইটের সমস্ত কোর্স ক্যাটাগরি, ব্যাচ ফিল্টারিং, রুটিন এবং সিলেবাস স্ট্রাকচার এককভাবে গণিত (সাধারণ গণিত, উচ্চতর গণিত ১ম ও ২য় পত্র, ক্যাডেট/মডেল টেস্ট এবং বিশ্ববিদ্যালয়/ইঞ্জিনিয়ারিং ভর্তি গণিত) কেন্দ্রিক ডিজাইন করা হয়েছে।

### আর্কিটেকচারের প্রধান লক্ষ্যসমূহ:
1. **Mathematics-First Curriculum Modeling:** সাধারণ গণিত ও উচ্চতর গণিতের বিভিন্ন অধ্যায়ভিত্তিক ট্র্যাকিং ও ব্যাচ ম্যানেজমেন্ট।
2. **Security-First Design:** ছাত্র-ছাত্রী ও অভিভাবকদের ব্যক্তিগত তথ্যের সর্বোচ্চ সুরক্ষা, পাসওয়ার্ড ক্রিপ্টোগ্রাফি, এবং ওয়েব অ্যাটাক (SQLi, XSS, CSRF, Brute Force) প্রতিরোধ।
3. **Seamless Domain & Hosting Deployment:** ডোমেইন সেটআপ (`alaminmathcare.com`), DNS রেকর্ডস, SSL সার্টিফিকেট এবং cPanel হোস্টিংয়ে যাতে কোনো ইরর বা ডাউনটাইম ছাড়া ডেপ্লয় করা যায় সেই স্ট্যান্ডার্ড নিশ্চিত করা।
4. **High Performance & Mobile First:** মোবাইল ডিভাইস এবং ধীরগতির নেটওয়ার্কেও যেন ১.৫ সেকেন্ডের মধ্যে পেজ লোড ও ৩০-৬০ সেকেন্ডে ভর্তি/ডেমো ফর্ম ফিলাপ করা যায়।
5. **Bilingual Dual-Language Engine:** ডিফল্ট ইংরেজি এবং দ্রুত বাংলা টগল (`en` / `bn`), যা UI এবং ডাটাবেজ কন্টেন্ট উভয় ক্ষেত্রেই প্রযোজ্য।
6. **Role-Based Content Management:** শিক্ষক, রিসেপশনিস্ট ও অ্যাডমিনের জন্য সুরক্ষিত ব্যাকএন্ড ড্যাশবোর্ড।

---

## 2. High-Level Architecture & Request Flow

### 2.1 Complete System Topology

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                             END USERS & CLIENTS                             │
│       Visitors / Admission Seekers / Parents / Students / Teachers / Admin  │
└──────────────────────────────────────┬──────────────────────────────────────┘
                                       │ (HTTPS / TLS 1.3 - Port 443)
                                       ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                          EDGE & DNS LAYER (CLOUDFLARE)                      │
│   • DNS Routing for alaminmathcare.com (A / CNAME / MX Records)              │
│   • DDoS Protection & Web Application Firewall (WAF)                        │
│   • Global CDN Edge Caching (CSS, JS, Images, Fonts)                        │
│   • Universal SSL/TLS Termination & Automatic HTTPS Rewrite                 │
└──────────────────────────────────────┬──────────────────────────────────────┘
                                       │ (Encrypted Origin Fetch)
                                       ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                      WEB SERVER LAYER (APACHE / CPANEL)                     │
│   • .htaccess Gateway: Security Headers, Mod_Rewrite, IP Restriction        │
│   • Prevent Directory Browsing & Block Sensitive Files (.env, config, sql)  │
│   • Mod_Deflate (Gzip) & Mod_Expires Browser Caching                        │
└──────────────────┬───────────────────────────────────────┬──────────────────┘
                   │                                       │
                   ▼ (Public Traffic)                      ▼ (Admin Traffic)
┌──────────────────────────────────────┐  ┌───────────────────────────────────┐
│         PUBLIC WEB APPLICATION       │  │        ADMIN PORTAL (/admin/)     │
│  • Home, Courses, Batches, Teachers  │  │  • Session Authentication Guard   │
│  • Notices, Results, Routines        │  │  • Role-Based Access Control      │
│  • Admission & Free Demo Lead Forms  │  │  • Lead & Admission Management    │
│  • WhatsApp Float & Direct Calling   │  │  • Course / Batch / Notice CRUD   │
└──────────────────┬───────────────────┘  └───────────────────┬───────────────┘
                   │                                          │
                   └───────────────────┬──────────────────────┘
                                       │
                                       ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                       CORE APPLICATION ENGINE (PHP 8.x)                     │
│   ├── config/ (app.php, database.php - protected outside web/blocked)       │
│   ├── includes/ (i18n.php, auth.php, csrf.php, helpers.php, header/footer)  │
│   ├── lang/ (en.php, bn.php UI dictionaries)                                │
│   └── api/ (AJAX lead submission, demo request, batch filters)              │
└──────────────────────────────────────┬──────────────────────────────────────┘
                                       │ (PDO Prepared Statements Only)
                                       ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         DATA STORAGE LAYER (MYSQL 8.x)                      │
│   • UTF8MB4 Collation (Full Bangla Script & Math Symbol Support)            │
│   • Principle of Least Privilege: Restricted DB User (No DROP/GRANT)        │
│   • Relational Schema: Courses, Batches, Leads, Users, Results, Settings    │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 2.2 Application Request Execution Flow

```text
1. Client Request → https://alaminmathcare.com/courses/ssc-math
2. .htaccess Inspection:
   ├── Check HTTPS (Redirect if HTTP)
   ├── Validate Query Strings (Block SQLi / Script injection signatures)
   └── Route Clean URL to appropriate PHP handler (e.g. course-details.php?slug=ssc-math)
3. Application Bootstrap:
   ├── session_start() with HttpOnly, Secure, SameSite cookies
   ├── Load config/app.php & config/database.php (PDO Instance)
   ├── Initialize i18n Engine: Detect language (Cookie / Param) → Load lang/en.php or lang/bn.php
   └── Generate or verify CSRF Token for POST requests
4. Controller / Business Logic:
   ├── Query DB via PDO Prepared Statements ($stmt->execute([$slug]))
   ├── Sanitize and normalize database records
5. View Rendering:
   ├── Combine Header + Navbar + Page Content + Footer
   ├── Apply __() localization helper to UI strings
   └── Output minified HTML with Strict Security Headers
```

---

## 3. Technology Stack & Decisions

| Layer | Selected Tech | Rationale & Advantage |
|---|---|---|
| **Server OS** | CloudLinux / Linux CentOS (cPanel) | সর্বাধিক স্থিতিশীল, সাশ্রয়ী ও লোকাল হোস্টিং প্রোভাইডারদের স্ট্যান্ডার্ড। |
| **Web Server** | Apache 2.4+ (with mod_rewrite, headers) | `.htaccess` দিয়ে সূক্ষ্ম ডিরেক্টরি পারমিশন ও সিকিউরিটি কন্ট্রোল সম্ভব। |
| **Backend Engine** | PHP 8.1 / 8.2 | আধুনিক টাইপিং, উন্নত মেমোরি পারফরম্যান্স, ও cPanel-এ সরাসরি নেটিভ সাপোর্ট। |
| **Database** | MySQL 8.0+ / MariaDB 10.4+ | নির্ভরযোগ্য রিলেশনাল ডাটাবেজ, ACID কমপ্লায়েন্ট, বাংলা লেখার জন্য `utf8mb4_unicode_ci` পারফেক্ট। |
| **Frontend Framework** | Bootstrap 5.3 (Vanilla CSS Hybrid) | কোনো জটিল Node/NPM বিল্ড স্টেপ ছাড়াই অত্যন্ত দ্রুত মোবাইল রেসপন্সিভ ডিজাইন তৈরি করা যায়। |
| **Typography** | Inter + Noto Sans Bengali | ইংরেজি ও বাংলা টেক্সট যাতে নিখুঁতভাবে রেন্ডার হয় এবং কোনো ফন্ট ভেঙে না যায়। |
| **Edge & Security** | Cloudflare (Free/Pro Plan) | ফ্রি SSL, বট শিল্ড, গ্লোবাল CDN এবং সাইবার অ্যাটাক থেকে ডোমেইনকে আড়াল রাখা। |

---

## 4. Project Directory & File Structure

নিচের ফোল্ডার কাঠামোটি এমনভাবে তৈরি যাতে পাবলিক ফাইল এবং সেন্সিটিভ কোর কোড আলাদা থাকে:

```text
alaminmathcare.com/ (public_html/)
│
├── index.php                          # হোমপেজ (১২টি সেকশন)
├── about.php                          # কোচিং পরিচিতি ও শিক্ষাদান পদ্ধতি
├── courses.php                        # কোর্স তালিকা ও ফিল্টারিং
├── course-details.php                 # নির্দিষ্ট কোর্সের বিস্তারিত পাতা
├── batches.php                        # সকল রানিং ও আপকামিং ব্যাচ
├── batch-details.php                  # নির্দিষ্ট ব্যাচের সিট স্ট্যাটাস ও শিডিউল
├── routine.php                        # সাপ্তাহিক ক্লাস রুটিন ভিউয়ার
├── teachers.php                       # শিক্ষক তালিকা
├── teacher-profile.php                # আল আমিন স্যার ও অন্যান্য শিক্ষকদের প্রোফাইল
├── results.php                        # পরীক্ষার ফলাফল ও সাফল্য
├── results-lookup.php                 # [NEW] রোল/ফোন নম্বর দিয়ে স্কোরকার্ড সার্চ
├── verify-id.php                      # [NEW] ডিজিটাল স্টুডেন্ট আইডি কার্ড QR ভেরিফিকেশন
├── notices.php                        # নোটিশ বোর্ড
├── notice-details.php                 # নোটিশের বিস্তারিত ও PDF ডাউনলোড
├── testimonials.php                   # শিক্ষার্থী ও অভিভাবকদের রিভিউ
├── gallery.php                        # ক্লাস ও ইভেন্টের ফটো গ্যালারি
├── faq.php                            # সাধারণ জিজ্ঞাসা ও উত্তর
├── contact.php                        # ঠিকানা, গুগল ম্যাপ ও যোগাযোগ ফর্ম
├── admission.php                      # ভর্তি এনকোয়ারি পেজ
├── demo.php                           # ফ্রি ডেমো ক্লাস রিকোয়েস্ট পেজ
├── 404.php                            # সিকিউর ও ইউজার-ফ্রেন্ডলি ইরর পেজ
│
├── admin/                             # [সুরক্ষিত অ্যাডমিন প্যানেল]
│   ├── index.php                      # লগইন অথবা ড্যাশবোর্ডে রিডাইরেক্ট
│   ├── login.php                      # অ্যাডমিন লগইন (Rate Limited + Brute Force Protected)
│   ├── forgot-password.php            # [NEW] টোকেন ভিত্তিক পাসওয়ার্ড রিসেট আবেদন
│   ├── reset-password.php             # [NEW] ক্রিপ্টোগ্রাফিক সল্টেড টোকেন ভ্যালিডেশন ও পাসওয়ার্ড পরিবর্তন
│   ├── logout.php                     # সুরক্ষিত সেশন ডিস্ট্রয়
│   ├── dashboard.php                  # মূল ড্যাশবোর্ড (KPI Metrics & Quick Stats)
│   ├── courses/                       # কোর্স তৈরি, এডিট ও ডিলিট
│   ├── batches/                       # ব্যাচ ম্যানেজমেন্ট ও সিট ট্র্যাকিং
│   ├── teachers/                      # শিক্ষক প্রোফাইল ম্যানেজমেন্ট
│   ├── students/                      # শিক্ষার্থী ও অভিভাবক ডাটাবেজ (Excel/CSV Export)
│   ├── attendance/                    # [NEW] দৈনিক ক্লাস উপস্থিতি ও অভিভাবক SMS ট্রিগার
│   ├── fee-ledger/                    # [NEW] ফি আদায় লেজার, বকেয়া ফিল্টার ও রসিদ জেনারেটর
│   ├── student-cards/                 # [NEW] ডিজিটাল স্টুডেন্ট আইডি কার্ড ও প্রিন্ট-রেডি QR কোড
│   ├── batch-switch-requests/         # [NEW] ব্যাচ পরিবর্তন আবেদন অনুমোদন/বাতিল
│   ├── leads/                         # ভর্তি ফর্ম থেকে আসা লিডস ও ফলো-আপ স্ট্যাটাস
│   ├── demo-requests/                 # ডেমো ক্লাসের আবেদন তালিকা ও ফিডব্যাক
│   ├── notices/                       # নোটিশ আপলোড ও পাবলিশ
│   ├── results/                       # রেজাল্ট শিট ও স্কোরকার্ড মার্কস আপলোড
│   ├── routines/                      # রুটিন আপডেট
│   ├── testimonials/                  # রিভিউ অ্যাপ্রুভাল সিস্টেম
│   ├── gallery/                       # ছবি আপলোড ও ক্যাটাগরি
│   ├── users/                         # অ্যাডমিন ইউজার ও রোল তৈরি
│   └── settings/                      # সাইটের মোবাইল, ঠিকানা, SMS API ও গ্লোবাল কনফিগারেশন
│
├── config/                            # [লুক্কায়িত কনফিগারেশন - ওয়েব থেকে ব্রাউজ নিষিদ্ধ]
│   ├── database.php                   # ডাটাবেজ কানেকশন (PDO)
│   └── app.php                        # গ্লোবাল কনস্ট্যান্ট ও এনভায়রনমেন্ট সেটিংস
│
├── lang/                              # [দ্বিভাষিক ডিকশনারি ফাইল]
│   ├── en.php                         # ইংরেজি অনুবাদ স্ট্রিং
│   └── bn.php                         # বাংলা অনুবাদ স্ট্রিং
│
├── includes/                          # [শেয়ার্ড মডিউল ও হেল্পার]
│   ├── header.php                     # মেটা ট্যাগ, এসইও ও সিএসএস ইনক্লুড
│   ├── footer.php                     # ফুটার, জাভাস্ক্রিপ্ট ও ক্লোজিং ট্যাগ
│   ├── navbar.php                     # ডেস্কটপ হেডার ও নেভিগেশন
│   ├── mobile-nav.php                 # মোবাইল স্টিকি বটম বার
│   ├── lang-toggle.php                # ভাষা পরিবর্তনকারী বাটন
│   ├── whatsapp-float.php             # সার্বক্ষণিক ফ্লোটিং হোয়াটসঅ্যাপ বাটন
│   ├── auth.php                       # অ্যাডমিন সেশন ও রোল গার্ড
│   ├── i18n.php                       # ভাষা লোডার ফাংশন
│   ├── csrf.php                       # CSRF টোকেন জেনারেটর ও ভ্যালিডেটর
│   ├── sms.php                        # [NEW] SMS গেটওয়ে হেল্পার (রেট লিমিট ও কোটা গার্ড সহ)
│   └── helpers.php                    # স্যানিটাইজার, স্ল্যাগ জেনারেটর, CSV ফর্মুলা নিউট্রালাইজার
│
├── assets/                            # [পাবলিক অ্যাসেটস]
│   ├── css/
│   │   ├── main.css                   # পাবলিক ওয়েবসাইট স্টাইলিং
│   │   └── admin.css                  # অ্যাডমিন প্যানেল স্টাইলিং
│   ├── js/
│   │   ├── main.js                    # ভ্যালিডেশন, ফিল্টার, টগল স্ক্রিপ্ট
│   │   └── admin.js                   # অ্যাডমিন চার্ট ও টেবিল স্ক্রিপ্ট
│   ├── images/
│   │   ├── logo/                      # ব্র্যান্ডিং ও লোগো
│   │   ├── teachers/                  # শিক্ষকদের অফিসিয়াল ছবি
│   │   └── banners/                   # ব্যানার ও ব্যাকগ্রাউন্ড ইমেজ
│   └── uploads/                       # [ইউজার আপলোড ডিরেক্টরি - এক্সিকিউশন নিষিদ্ধ]
│       ├── notices/                   # নোটিশের PDF ও ছবি
│       ├── results/                   # রেজাল্ট শিট
│       ├── qrcodes/                   # [NEW] স্টুডেন্ট আইডি কার্ড QR ইমেজ
│       └── gallery/                   # গ্যালারির ছবি
│
├── api/                               # [অ্যাজাক্স এন্ডপয়েন্টস]
│   ├── submit-lead.php                # ভর্তি ফরম সাবমিশন হ্যান্ডলার
│   ├── submit-demo.php                # ডেমো রিকোয়েস্ট হ্যান্ডলার
│   ├── submit-feedback.php            # [NEW] ডেমো পরবর্তী ফিডব্যাক হ্যান্ডলার
│   ├── submit-batch-change.php        # [NEW] ব্যাচ পরিবর্তন আবেদন হ্যান্ডলার
│   ├── export-students.php            # [NEW] স্যানিটাইজড স্টুডেন্ট ও অভিভাবক এক্সেল/সিএসভি এক্সপোর্ট
│   └── get-batches.php                # ফিল্টার অনুযায়ী ব্যাচ ডেটা লোড
│
├── database/
│   ├── schema.sql                     # ডাটাবেজ স্ট্রাকচার স্ক্রিপ্ট (২১টি টেবিল)
│   └── seed.sql                       # ডেমো ডেটা ও ডিফল্ট সেটিংস
│
├── .htaccess                          # Apache সিকিউরিটি রুলস ও রিরাইট
├── robots.txt                         # সার্চ ইঞ্জিন ক্রলার ডিরেক্টিভ
├── sitemap.xml                        # সার্চ ইঞ্জিন সাইটম্যাপ
└── README.md                          # ডেভেলপমেন্ট নির্দেশিকা
```

---

## 5. Application Layer Architecture

```
┌────────────────────────────────────────────────────────────────────────┐
│                        1. PRESENTATION LAYER                           │
│  - Semantic HTML5, CSS Grid / Flexbox, Bootstrap 5.3                   │
│  - Dynamic Language Ingestion via __('key') helper                     │
│  - Micro-interactions, Toast Notifications, Fast Mobile Modal Sheets    │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                        2. BUSINESS & SECURITY LAYER                    │
│  - Request Sanitization (helpers.php: clean_input(), e())              │
│  - CSRF Protection (csrf.php: Token generation & cryptographic verify) │
│  - Authentication & Authorization Engine (auth.php: RBAC)              │
│  - Lead & Admission Processor (api/submit-lead.php)                    │
│  - File Upload Inspector (MIME-Type & Magic Byte Analyzer)             │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                        3. DATA ACCESS LAYER                            │
│  - PDO MySQL Driver (UTF8MB4, Persistent connection disabled)          │
│  - Strict Prepared Statements ($pdo->prepare() + ->execute())          │
│  - Centralized Error Logging (error_log, zero exposure to frontend)   │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 6. Database & Data Flow Architecture

### 6.1 Entity Relationship Diagram (ERD)

```
 [roles] 1 ──── ∞ [users]
                     │ 1
                     └─── 1 [teachers] 1 ──── ∞ [batches] ∞ ──── 1 [courses]
                                                   │ 1                │ 1
                                                   │                  └─── ∞ [study_materials]
                                                   ├── ∞ [routines]
                                                   ├── ∞ [enrollments] ∞ ──── 1 [students]
                                                   └── ∞ [attendance]           │ 1
                                                                                └─── ∞ [payments]

 [branches] 1 ──── ∞ [batches]
 [courses]  1 ──── ∞ [leads]
 [exams]    1 ──── ∞ [results] ∞ ──── 1 [students]
```

### 6.2 Key Database Schema Specifications

-- ১. রোলস টেবিল
CREATE TABLE `roles` (
  `id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(30) NOT NULL UNIQUE, -- super_admin, admin, teacher, receptionist
  `name` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ২. অ্যাডমিন ও স্টাফ ইউজার টেবিল
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role_id` TINYINT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL, -- bcrypt
  `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
  `failed_login_attempts` TINYINT UNSIGNED DEFAULT 0,
  `lockout_until` DATETIME NULL,
  `last_login` DATETIME NULL,
  `reset_token_hash` VARCHAR(64) DEFAULT NULL, -- SHA-256 cryptographically hashed token
  `reset_token_expires_at` DATETIME DEFAULT NULL, -- Token expires in 15-20 minutes
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ৩. ব্রাঞ্চেস টেবিল (মাল্টি-ব্রাঞ্চ রেডি, ডিফল্ট: ফার্মগেট)
CREATE TABLE `branches` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `area` VARCHAR(100) NOT NULL,
  `city` VARCHAR(50) NOT NULL DEFAULT 'Dhaka',
  `postcode` VARCHAR(20) NOT NULL DEFAULT '1216',
  `landmark` VARCHAR(150) DEFAULT NULL,
  `phone_primary` VARCHAR(20) NOT NULL,
  `is_main` TINYINT(1) DEFAULT 1,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ৪. শিক্ষক প্যানেল টেবিল (দ্বিভাষিক)
CREATE TABLE `teachers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `name_en` VARCHAR(100) NOT NULL,
  `name_bn` VARCHAR(100) DEFAULT NULL,
  `designation_en` VARCHAR(100) NOT NULL, -- e.g. Head Teacher
  `designation_bn` VARCHAR(100) DEFAULT NULL,
  `qualification` VARCHAR(255) NOT NULL,
  `experience_years` TINYINT UNSIGNED DEFAULT 0,
  `bio_en` TEXT DEFAULT NULL,
  `bio_bn` TEXT DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `is_head_teacher` TINYINT(1) DEFAULT 0,
  `sort_order` SMALLINT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ৫. গণিত কোর্স টেবিল (দ্বিভাষিক)
CREATE TABLE `courses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `title_en` VARCHAR(200) NOT NULL,
  `title_bn` VARCHAR(200) DEFAULT NULL,
  `class_level` VARCHAR(50) NOT NULL, -- Class 6-8, Class 9-10 (SSC), HSC 1st/2nd, Admission
  `math_category` ENUM('general_math', 'higher_math_1st', 'higher_math_2nd', 'combined_higher_math', 'admission_engineering_math') NOT NULL DEFAULT 'general_math',
  `course_type` ENUM('regular', 'crash', 'model_test', 'special_care') DEFAULT 'regular',
  `duration` VARCHAR(50) DEFAULT NULL,
  `fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `description_en` TEXT DEFAULT NULL,
  `description_bn` TEXT DEFAULT NULL,
  `is_popular` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ৬. ব্যাচ ও সিট টেবিল
CREATE TABLE `batches` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `teacher_id` INT UNSIGNED NOT NULL,
  `batch_name` VARCHAR(100) NOT NULL,
  `class_days` VARCHAR(100) NOT NULL, -- e.g. "Sun,Tue,Thu"
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `start_date` DATE NOT NULL,
  `total_seats` SMALLINT UNSIGNED NOT NULL DEFAULT 30,
  `available_seats` SMALLINT UNSIGNED NOT NULL DEFAULT 30,
  `admission_status` ENUM('open', 'full', 'closed') DEFAULT 'open',
  `status` ENUM('upcoming', 'running', 'completed') DEFAULT 'upcoming',
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ৭. ভর্তি ও ডেমো লিড টেবিল
CREATE TABLE `leads` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `type` ENUM('admission_enquiry', 'demo_request', 'contact_message') NOT NULL,
  `student_name` VARCHAR(100) NOT NULL,
  `guardian_phone` VARCHAR(20) NOT NULL,
  `whatsapp_number` VARCHAR(20) DEFAULT NULL,
  `class_level` VARCHAR(50) NOT NULL,
  `course_id` INT UNSIGNED DEFAULT NULL,
  `batch_id` INT UNSIGNED DEFAULT NULL,
  `preferred_date` DATE NULL,
  `status` ENUM('new', 'contacted', 'demo_scheduled', 'admitted', 'cancelled') DEFAULT 'new',
  `admin_notes` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`guardian_phone`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ৮. স্টুডেন্ট টেবিল (Phase-2 স্টুডেন্ট পোর্টাল রেডি)
CREATE TABLE `students` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL UNIQUE,
  `student_id_code` VARCHAR(30) UNIQUE NOT NULL, -- e.g. AMC-2026-001
  `name` VARCHAR(100) NOT NULL,
  `guardian_name` VARCHAR(100) DEFAULT NULL,
  `guardian_phone` VARCHAR(20) NOT NULL,
  `class_level` VARCHAR(50) NOT NULL,
  `school_college` VARCHAR(150) DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `qr_code_token` VARCHAR(64) UNIQUE DEFAULT NULL, -- Cryptographic Non-sequential token for ID verification
  `qr_code_path` VARCHAR(255) DEFAULT NULL, -- Path to generated QR image
  `status` ENUM('active', 'passed_out', 'dropped') DEFAULT 'active',
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ৯. এনরোলমেন্ট টেবিল (স্টুডেন্ট - ব্যাচ সম্পর্ক ও ফি ওভারভিউ)
CREATE TABLE `enrollments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT UNSIGNED NOT NULL,
  `batch_id` INT UNSIGNED NOT NULL,
  `enrollment_date` DATE NOT NULL,
  `total_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `paid_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `due_amount` DECIMAL(10,2) GENERATED ALWAYS AS (`total_fee` - `paid_amount`) STORED,
  `payment_status` ENUM('unpaid', 'partially_paid', 'paid') NOT NULL DEFAULT 'unpaid',
  `status` ENUM('active', 'completed', 'transferred') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ১০. নোটিশ বোর্ড টেবিল (দ্বিভাষিক)
CREATE TABLE `notices` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title_en` VARCHAR(255) NOT NULL,
  `title_bn` VARCHAR(255) DEFAULT NULL,
  `category` ENUM('general', 'admission', 'exam', 'routine', 'holiday', 'urgent') DEFAULT 'general',
  `description_en` TEXT DEFAULT NULL,
  `description_bn` TEXT DEFAULT NULL,
  `attachment_file` VARCHAR(255) DEFAULT NULL, -- PDF/Image file path
  `is_pinned` TINYINT(1) DEFAULT 0,
  `is_published` TINYINT(1) DEFAULT 1,
  `expiry_date` DATE NULL,
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ১১. পরীক্ষার ফলাফল টেবিল (PDF / শিট ভিত্তিক)
CREATE TABLE `results` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `exam_title_en` VARCHAR(255) NOT NULL,
  `exam_title_bn` VARCHAR(255) DEFAULT NULL,
  `batch_id` INT UNSIGNED NULL,
  `class_level` VARCHAR(50) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL, -- PDF Marks Sheet
  `published_date` DATE NOT NULL,
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ১২. ক্লাস রুটিন টেবিল
CREATE TABLE `routines` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `batch_id` INT UNSIGNED NOT NULL,
  `day_of_week` ENUM('Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `room_number` VARCHAR(30) DEFAULT 'Room-1',
  `notes` VARCHAR(255) DEFAULT NULL,
  `is_deleted` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ১৩. সাধারণ জিজ্ঞাসা (FAQ) টেবিল (দ্বিভাষিক)
CREATE TABLE `faqs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `question_en` VARCHAR(255) NOT NULL,
  `question_bn` VARCHAR(255) DEFAULT NULL,
  `answer_en` TEXT NOT NULL,
  `answer_bn` TEXT DEFAULT NULL,
  `category` VARCHAR(50) DEFAULT 'general',
  `sort_order` SMALLINT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `is_deleted` TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ১৪. টেস্টিমোনিয়াল / রিভিউ টেবিল (দ্বিভাষিক)
CREATE TABLE `testimonials` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_name` VARCHAR(100) NOT NULL,
  `student_role` VARCHAR(100) DEFAULT 'SSC/HSC Batch',
  `quote_en` TEXT NOT NULL,
  `quote_bn` TEXT DEFAULT NULL,
  `rating` TINYINT UNSIGNED DEFAULT 5,
  `photo` VARCHAR(255) DEFAULT NULL,
  `is_featured` TINYINT(1) DEFAULT 1,
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ১৫. ফটো গ্যালারি টেবিল
CREATE TABLE `gallery` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `image_path` VARCHAR(255) NOT NULL,
  `caption_en` VARCHAR(200) DEFAULT NULL,
  `caption_bn` VARCHAR(200) DEFAULT NULL,
  `category` ENUM('classroom', 'events', 'achievements') DEFAULT 'classroom',
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ১৬. গ্লোবাল সাইট সেটিংস টেবিল
CREATE TABLE `settings` (
  `setting_key` VARCHAR(100) PRIMARY KEY,
  `setting_value` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ১৭. অ্যাডমিন অ্যাক্টিভিটি অডিট লগ
CREATE TABLE `activity_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(100) NOT NULL, -- e.g. "Deleted Batch #3", "Updated Course #1"
  `module` VARCHAR(50) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ১৮. স্টুডেন্ট মাসিক ফি শিডিউল ও বকেয়া লেজার টেবিল (Student Monthly Invoices & Due Schedule)
-- ভূমিকা: প্রতি মাসে শিক্ষার্থীর জন্য কত টাকা ফি ধার্য হলো এবং উক্ত মাসের বর্তমান স্ট্যাটাস (due, partial, paid) ট্র্যাক করা
CREATE TABLE `student_fees` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT UNSIGNED NOT NULL,
  `fee_month` VARCHAR(7) NOT NULL, -- e.g. '2026-09' (YYYY-MM)
  `fee_amount` DECIMAL(10,2) NOT NULL,
  `paid_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `due_amount` DECIMAL(10,2) GENERATED ALWAYS AS (`fee_amount` - `paid_amount`) STORED,
  `status` ENUM('due', 'partial', 'paid') NOT NULL DEFAULT 'due',
  `last_paid_date` DATE DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_student_month` (`student_id`, `fee_month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ১৯. পেমেন্ট ট্রানজ্যাকশন ও মানি রিসিট টেবিল (Payment Transaction Receipts Log)
-- ভূমিকা: প্রতিটি নগদ/বিকাশ জমার বিপরীতে তাৎক্ষণিক ইউনিক ক্যাশ মানি রিসিট নম্বর ও অডিট ট্রানজ্যাকশন রেকর্ড
CREATE TABLE `fee_records` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `enrollment_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `amount_paid` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('cash', 'bkash', 'nagad', 'bank', 'other') NOT NULL DEFAULT 'cash',
  `transaction_reference` VARCHAR(100) DEFAULT NULL,
  `receipt_no` VARCHAR(50) UNIQUE NOT NULL,
  `received_by` INT UNSIGNED DEFAULT NULL,
  `remarks` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`received_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ২০. দৈনিক ক্লাস উপস্থিতি ও অভিভাবক SMS ট্র্যাকার টেবিল (Attendances)
CREATE TABLE `attendances` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT UNSIGNED NOT NULL,
  `batch_id` INT UNSIGNED NOT NULL,
  `attendance_date` DATE NOT NULL,
  `status` ENUM('present', 'absent', 'late') NOT NULL DEFAULT 'present',
  `recorded_by` INT UNSIGNED DEFAULT NULL,
  `sms_sent_status` ENUM('none', 'pending', 'sent', 'failed') NOT NULL DEFAULT 'none',
  `sms_response` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`recorded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `unique_attendance` (`student_id`, `batch_id`, `attendance_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ২১. ব্যাচ পরিবর্তন আবেদন টেবিল (Batch Change Requests)
CREATE TABLE `batch_change_requests` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT UNSIGNED NOT NULL,
  `current_batch_id` INT UNSIGNED NOT NULL,
  `requested_batch_id` INT UNSIGNED NOT NULL,
  `reason` TEXT NOT NULL,
  `guardian_phone` VARCHAR(20) NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` INT UNSIGNED DEFAULT NULL,
  `admin_note` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`current_batch_id`) REFERENCES `batches`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`requested_batch_id`) REFERENCES `batches`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ২২. ডেমো ক্লাস ফিডব্যাক ও রিভিউ টেবিল (Demo Feedbacks)
CREATE TABLE `demo_feedbacks` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `lead_id` INT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL, -- 1 to 5 Stars
  `understanding_level` ENUM('excellent', 'good', 'average', 'difficult') DEFAULT 'good',
  `comments` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

### 6.3 সিট ট্র্যাকিং, কনকারেন্সি লক ও সফট ডিলিট নীতি (Seat Tracking, Concurrency & Soft Delete)
- **অটোমিক কনকারেন্সি সিট লকিং নীতি (Anti-Overbooking Row-Lock):** 
  একই সময়ে শেষ অবশিষ্ট সিটের জন্য একাধিক আবেদন পড়লে ওভারবুকিং ঠেকাতে পিএইচপি ব্যাকএন্ডে ডাটাবেজ ট্রানজেকশন এবং `SELECT FOR UPDATE` কার্যকর করা হবে:
  ```sql
  START TRANSACTION;
  -- ১. রো-লকিং দিয়ে বর্তমান উপলব্ধ সিট রিড করা
  SELECT available_seats FROM batches WHERE id = :batch_id FOR UPDATE;
  
  -- ২. শুধুমাত্র available_seats > 0 হলেই সিট কমানো
  UPDATE batches SET available_seats = available_seats - 1 WHERE id = :batch_id AND available_seats > 0;
  
  -- ৩. সিট নিশ্চিত হলে এনরোলমেন্ট ডাটা ইনসার্ট করা
  INSERT INTO enrollments (student_id, batch_id, enrollment_date, status) VALUES (:student_id, :batch_id, CURDATE(), 'active');
  COMMIT;
  ```
  যদি `UPDATE` কোয়েরির affected rows শূন্য (০) হয়, তবে ট্রানজেকশন `ROLLBACK` হয়ে ইউজারকে "ব্যাচটি পূর্ণ হয়ে গেছে" মেসেজ দেবে এবং বিকল্প ব্যাচ বা ওয়েটিং লিস্টে যুক্ত করার অপশন প্রদর্শন করবে।
- **সফট ডিলিট পলিসি (Soft Delete):** অ্যাডমিন প্যানেল থেকে কোনো কোর্স, ব্যাচ, শিক্ষক বা নোটিশ ডিলিট করা হলে তা ডাটাবেজ থেকে স্থায়ীভাবে বাদ না গিয়ে `is_deleted = 1` হয়ে যাবে। ফলে পাবলিক সাইটে তা অদৃশ্য থাকবে কিন্তু পূর্বের হিস্ট্রি নিরাপদ থাকবে। চাইলে সুপার অ্যাডমিন ট্র্যাশ বিন থেকে তা স্থায়ীভাবে রিমুভ করতে পারবেন।

---

## 7. Comprehensive Security Architecture (Hardening Guide)

ওয়েবসাইটে ডেটা সিকিউরিটি ও হ্যাকিং প্রতিরোধের জন্য এই আর্কিটেকচারে **Defense in Depth** কৌশল গ্রহণ করা হয়েছে।

### 7.1 Defense In Depth (স্তরভিত্তিক সুরক্ষা)

```
     [1. Cloudflare Edge Firewall]  ← DDoS, Bot Mitigation, WAF
                 │
                 ▼
     [2. Apache .htaccess Layer]    ← File Restrictions, Security Headers, No-PHP In Uploads
                 │
                 ▼
     [3. Application Logic Layer]   ← CSRF Tokens, Rate Limiting, Input Sanitization
                 │
                 ▼
     [4. Data Access Layer]         ← PDO Prepared Statements, Passwords via Bcrypt
                 │
                 ▼
     [5. Database User Isolation]   ← Minimum Permissions, Strict Host Binding
```

### 7.2 ডেটা সুরক্ষার কোডিং প্যাটার্ন

#### ক. SQL Injection সম্পূর্ণ নির্মূল (PDO Prepared Statements)
কোথাও কাঁচা কনক্যাটেনশন (`$sql = "SELECT * FROM users WHERE id=" . $_GET['id']`) ব্যবহার করা যাবে না।
```php
// সর্বদা এই প্যাটার্ন নিশ্চিত করতে হবে:
$stmt = $pdo->prepare("SELECT id, title_en, fee FROM courses WHERE slug = :slug AND is_active = 1 LIMIT 1");
$stmt->execute([':slug' => $slug]);
$course = $stmt->fetch();
```

#### খ. XSS (Cross-Site Scripting) প্রতিরোধ
ইউজার ইনপুট ব্রাউজারে রেন্ডার করার সময় সর্বদা `htmlspecialchars()` পাস করতে হবে:
```php
// includes/helpers.php
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// ব্যবহার:
<h1><?= e($course['title_en']) ?></h1>
```

#### গ. CSRF (Cross-Site Request Forgery) টোকেন যাচাই
প্রতিটি POST ফরমের ভেতরে লুকানো ক্রিপ্টোগ্রাফিক টোকেন থাকবে:
```php
// includes/csrf.php
function get_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}
```

#### ঘ. ফাইল আপলোড সিকিউরিটি (PDF ও ইমেজ)
অ্যাডমিন প্যানেল বা রেজাল্ট শিট আপলোডে সরাসরি ফাইল এক্সটেনশন বিশ্বাস করা যাবে না:
```php
// ১. MIME Type এবং Magic Byte চেক করা:
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['attachment']['tmp_name']);

$allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
if (!in_array($mime, $allowedMimes)) {
    throw new Exception("অবৈধ ফাইল ফরম্যাট! কেবল ছবি অথবা PDF অনুমোদিত।");
}

// ২. ফাইলের নাম সম্পূর্ণ র‍্যান্ডমাইজ করা (শেল স্ক্রিপ্ট ইনজেকশন আটকাতে):
$extension = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
$safeFileName = bin2hex(random_bytes(16)) . '.' . strtolower($extension);
```

#### ঙ. পাসওয়ার্ড সিকিউরিটি ও ব্রুট ফোর্স লকআউট
```php
// পাসওয়ার্ড হ্যাশ:
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// ব্রুট ফোর্স প্রতিরোধ:
// ৫ বার ভুল পাসওয়ার্ড দিলে উক্ত আইপি/ইউজারের অ্যাকাউন্ট ১৫ মিনিটের জন্য সাময়িক লক হবে।
```

### 7.3 সুরক্ষিত `.htaccess` কনফিগারেশন

প্রজেক্টের রুট ডিরেক্টরিতে এই কনফিগারেশন ফাইলটি থাকবে:

```apache
# ----------------------------------------------------------------------
# AL AMIN'S MATH CARE — SECURE APACHE SERVER CONFIGURATION
# ----------------------------------------------------------------------

# ১. ডিরেক্টরি ব্রাউজিং সম্পূর্ণ বন্ধ রাখা
Options -Indexes -MultiViews

# ২. Rewrite Engine চালু করা
RewriteEngine On

# ৩. HTTP থেকে স্বয়ংক্রিয় HTTPS এ রিডাইরেক্ট (SSL Enforcement)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# ৪. সংবেদনশীল ফাইল ও ডিরেক্টরি সরাসরি ব্রাউজ করা ব্লক করা
<FilesMatch "^(\.env|\.git|\.htaccess|composer\.json|schema\.sql|seed\.sql)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# ৫. config, database, lang ফোল্ডারে বাইরের কারও প্রবেশ নিষিদ্ধ
RewriteRule ^(config|database|lang)/ - [F,L,NC]

# ৬. গ্লোবাল সিকিউরিটি রেসপন্স হেডার্স
<IfModule mod_headers.c>
    # Clickjacking আক্রমণ প্রতিরোধ
    Header always set X-Frame-Options "SAMEORIGIN"
    # MIME-Type স্নিফিং প্রতিরোধ
    Header always set X-Content-Type-Options "nosniff"
    # ব্রাউজার XSS ফিল্টার সক্রিয় করা
    Header always set X-XSS-Protection "1; mode=block"
    # ডেটা সোর্স পলিসি
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    # ব্রাউজার হার্ডওয়্যার এক্সেস সীমাবদ্ধ করা
    Header always set Permissions-Policy "camera=(), microphone=(), geolocation=()"
    # HSTS - এক বছরের জন্য ব্রাউজারকে HTTPS মনে রাখতে বলা
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
</IfModule>

# ৭. আপলোড ডিরেক্টরির ভেতরে কোনো PHP স্ক্রিপ্ট রান হতে না দেওয়া (Critical)
# (assets/uploads/.htaccess হিসেবেও আলাদা সেভ করা উচিত)
<Directory "assets/uploads">
    php_flag engine off
    RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .php8
    AddType text/plain .php
</Directory>

# ৮. ক্লিন ইউআরএল রাউটিং (Clean URL Rewrite Rules)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^courses/([a-zA-Z0-9_-]+)/?$ course-details.php?slug=$1 [L,QSA]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^batches/([0-9]+)/?$ batch-details.php?id=$1 [L,QSA]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^teachers/([a-zA-Z0-9_-]+)/?$ teacher-profile.php?slug=$1 [L,QSA]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^notices/([0-9]+)/?$ notice-details.php?id=$1 [L,QSA]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^results/lookup/?$ results-lookup.php [L,QSA]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^verify-id/?$ verify-id.php [L,QSA]

# ৯. ইউজার ফ্রেন্ডলি কাস্টম ইরর পেজ
ErrorDocument 404 /404.php
ErrorDocument 403 /404.php
ErrorDocument 500 /404.php
```

### 7.4 অ্যাডভান্সড সাইবার সিকিউরিটি প্রোটোকল (Advanced Defensive Safeguards)

প্রজেক্টের স্পর্শকাতর ডাটা (রেজাল্ট, শিক্ষার্থী তালিকা, ফোন নম্বর, অ্যাডমিন অ্যাকাউন্ট) সম্পূর্ণ সুরক্ষিত রাখতে নিচের ৭টি অত্যাবশ্যক সিকিউরিটি মেকানিজম কোডিং লেভেলে কার্যকর করা বাধ্যতামূলক:

#### ১. SMS ফ্লাডিং ও টোল ফ্রড প্রতিরোধ (SMS Toll Fraud Mitigation)
- **ফোন নম্বর লেভেল কুলডাউন:** একই ফোন নম্বরে ২৪ ঘণ্টায় সর্বোচ্চ ১টি স্বয়ংক্রিয় ভর্তি/ডেমো নিশ্চিতকরণ SMS পাঠানো যাবে।
- **দৈনিক গ্লোবাল কিল-সুইচ (Kill-Switch):** ডাটাবেজের `settings` টেবিলে `daily_sms_limit` (ডিফল্ট: ১৫০টি) সংরক্ষিত থাকবে। কোনো কারণে দিনে ১৫০টি SMS সম্পন্ন হলে স্বয়ংক্রিয়ভাবে পাবলিক SMS ডিসেবল হয়ে যাবে এবং অ্যাডমিন ইমেইলে সতর্কবার্তা পৌঁছাবে।
- **প্রিপেইড এপিআই ব্যালেন্স প্রটেকশন:** পাবলিক ফর্ম সাবমিটের ক্ষেত্রে কোনো বহিরাগত বা আন্তর্জাতিক ফোন নম্বরে SMS ট্রিগার হবে না; শুধুমাত্র বাংলাদেশি অনুমোদিত নম্বর (`^01[3-9]\d{8}$`) গ্রাহ্য হবে।

#### ২. স্টুডেন্ট ডাটা স্ক্র্যাপিং ও প্রাইভেসি প্রতিরোধ (Anti-Scraping Scorecard)
- `/results/lookup` পেজে শুধু রোল নম্বর দিয়ে রেজাল্ট দেখা যাবে না; শিক্ষার্থী/অভিভাবকের পরিচয় নিশ্চিত করতে **দ্বৈত ভেরিফিকেশন (Roll Number + Guardian Phone Number-এর শেষ ৪ ডিজিট)** আবশ্যক।
- ব্রুট-ফোর্স স্ক্র্যাপিং ঠেকাতে আইপি প্রতি প্রতি মিনিটে সর্বোচ্চ ৫টি রেজাল্ট সার্চের রেট-লিমিট কার্যকর থাকবে।
- আইডি কার্ড যাচাই পেজে (`verify-id.php`) কোনো ক্রমান্বয়িক আইডি (যেমন: `1, 2, 3`) এক্সপোজ হবে না; বরং ক্রিপ্টোগ্রাফিক ৩২-ক্যারেক্টার `qr_code_token` ব্যবহৃত হবে।

#### ৩. পাসওয়ার্ড রিসেট টোকেন হার্ডেনিং (CWE-640 Defense)
- রিসেট টোকেন জেনারেশনের ক্ষেত্রে ক্রিপ্টোগ্রাফিক র্যান্ডম বাইটস (`bin2hex(random_bytes(32))`) ব্যবহার করতে হবে।
- **ডাটাবেজে র টোকেন রাখা সম্পূর্ণ নিষিদ্ধ:** ডাটাবেজের `reset_token_hash` কলামে টোকেনের **SHA-256 হ্যাশ** (`hash('sha256', $plain_token)`) সংরক্ষিত থাকবে।
- টোকেনের মেয়াদ সর্বোচ্চ ১৫ মিনিট থাকবে এবং পাসওয়ার্ড পরিবর্তনের সাথে সাথে টোকেন অবিলম্বে নাল (`NULL`) করে দিতে হবে।
- **User Enumeration প্রতিরোধ:** ইমেইল সিস্টেমে থাকুক বা না থাকুক, ব্যবহারকারীকে সবসময় জেনেরিক নোটিফিকেশন প্রদর্শন করা হবে: *"আপনার ইমেইলটি নিবন্ধিত থাকলে পাসওয়ার্ড রিসেট লিংক পাঠানো হয়েছে।"*

#### ৪. এক্সেল এক্সপোর্ট ফর্মুলা ইনজেকশন প্রতিরোধ (CSV Injection / CWE-1236)
- অ্যাডমিন যখন স্টুডেন্ট বা অভিভাবক তালিকা এক্সেলে ডাউনলোড করবেন, তখন কোনো ক্ষতিকর ফর্মুলা এক্সিকিউশন আটকাতে স্যানিটাইজার হেল্পার প্রয়োগ করা হবে:
```php
function sanitize_excel_cell($value): string {
    $firstChar = substr((string)$value, 0, 1);
    if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"])) {
        return "'" . $value; // সিঙ্গেল কোট যোগ করে ফর্মুলা নিউট্রালাইজ করা
    }
    return (string)$value;
}
```

#### ৫. ডিপ ফাইল আপলোড ভ্যালিডেশন (Beyond Extension Checking)
- আপলোডের সময় ইউজারের দেওয়া মূল নাম সম্পূর্ণ বর্জন করে ক্রিপ্টোগ্রাফিক নাম (`bin2hex(random_bytes(16)) . '.' . $ext`) দেওয়া হবে।
- ক্লায়েন্ট-প্রদত্ত `$_FILES['type']`-এর ওপর নির্ভর না করে সার্ভার-সাইডে পিএইচপির `finfo_file(FILEINFO_MIME_TYPE)` দিয়ে রিয়েল MIME টাইপ যাচাই করা হবে।

#### ৬. ব্রোকেন অবজেক্ট লেভেল অথোরাইজেশন (BOLA / IDOR) গার্ড
- শিক্ষক বা রিসেপশনিস্ট লগইন অবস্থায় কোনো ব্যাচ বা অ্যাটেনডেন্স এডিটের সময় ইউআরএলের আইডি পরিবর্তন করে অন্যের ডাটা পরিবর্তন করতে পারবে না। প্রতিটি কন্ট্রোলারে ইউজারের অ্যাসাইনড ব্যাচ এবং রোলের মালিকানা কঠোরভাবে চেক হবে।

#### ৭. সেশন নিরাপত্তা ও অটো-টাইমআউট
- অ্যাডমিন সেশন ৩০ মিনিট নিষ্ক্রিয় থাকলে স্বয়ংক্রিয়ভাবে সেশন ডিস্ট্রয় ও লগআউট হবে। লগইনের পর `session_regenerate_id(true)` দিয়ে সেশন ফিক্সেশন আক্রমণ প্রতিরোধ নিশ্চিত করা হবে।

---

## 8. Domain Setup & Production Deployment Architecture

যাতে ডোমেইন পয়েন্ট করতে বা হোস্টিং সার্ভারে ডেপ্লয় করতে কোনো সমস্যায় পড়তে না হয়, তার নিখুঁত গাইডলাইন নিচে দেওয়া হলো:

### 8.1 Domain & DNS Routing Strategy (`alaminmathcare.com`)

| Type | Name / Host | Target / Value | Proxy Status (Cloudflare) | বিবরণ |
|---|---|---|---|---|
| **A** | `@` | `SERVER_IP_ADDRESS` | **Proxied (Orange Cloud)** | মূল ডোমেইনের রুট আইপি |
| **CNAME** | `www` | `alaminmathcare.com` | **Proxied (Orange Cloud)** | www সাবডোমেইন রিডাইরেক্ট |
| **MX** | `@` | `mail.alaminmathcare.com` | **DNS Only (Grey Cloud)** | বিজনেস ইমেইলের জন্য |
| **TXT** | `@` | `v=spf1 +a +mx ~all` | **DNS Only** | ইমেইল স্প্যাম প্রটেকশন (SPF) |
| **TXT** | `_dmarc` | `v=DMARC1; p=none;` | **DNS Only** | ডোমেইন অথেন্টিকেশন পলিসি |

#### ডোমেইন সেটআপের সঠিক ক্রম:
1. **Namecheap / GoDaddy / Local BD Registrar** থেকে `alaminmathcare.com` ডোমেইন ক্রয় করা।
2. **Cloudflare Account**-এ লগইন করে ডোমেইনটি অ্যাড করা এবং যে ২টি Cloudflare Nameserver পাওয়া যাবে (e.g. `adam.ns.cloudflare.com`), তা ডোমেইন রেজিস্ট্রারের কন্ট্রোল প্যানেলে Nameservers হিসেবে সেট করা।
3. Cloudflare ড্যাশবোর্ডে গিয়ে **SSL/TLS Settings**-এ মোড সিলেক্ট করতে হবে: **`Full (Strict)`**।

---

### 8.2 cPanel হোস্টিং কনফিগারেশন স্টেপস

1. **cPanel ড্যাশবোর্ডে প্রবেশ করুন:**
   - **Domains** সেকশনে গিয়ে `alaminmathcare.com` যোগ করুন। Document Root হবে: `public_html`।
2. **PHP Version সিলেক্ট করুন:**
   - **MultiPHP Manager**-এ যান। `PHP 8.1` অথবা `PHP 8.2` সিলেক্ট করুন।
   - **PHP Extensions** সক্রিয় করুন: `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `curl`, `openssl`।
3. **ডাটাবেজ তৈরি:**
   - **MySQL Databases** উইজার্ডে যান।
   - ডাটাবেজ নাম দিন: `alaminma_db` (প্রিফিক্স সহ)।
   - ডাটাবেজ ইউজার তৈরি করুন: `alaminma_user` এবং শক্তিশালী পাসওয়ার্ড জেনারেট করুন।
   - **Privileges:** `SELECT`, `INSERT`, `UPDATE`, `DELETE`, `CREATE`, `INDEX`, `ALTER` দিন। (কখনই `DROP DATABASE` বা `SUPER` পারমিশন প্রয়োজন নেই)।
4. **টেবিল ইমপোর্ট:**
   - cPanel থেকে **phpMyAdmin** ওপেন করে `database/schema.sql` ফাইলটি ইমপোর্ট করুন।
5. **ফাইল পারমিশন স্ট্যান্ডার্ড (Linux Permissions):**
   - সকল ডিরেক্টরি পারমিশন: `755` (`drwxr-xr-x`)
   - সকল ফাইল পারমিশন: `644` (`-rw-r--r--`)
   - `assets/uploads/` ডিরেক্টরি: `755` (Web server writable)
   - `config/database.php`: `640` বা `600` (কেবল ওনার রিড করতে পারবে)

---

### 8.3 Environment Isolation (`config/database.php`)

লাইভ সার্ভার ও লোকাল মেশিনের ক্রেডেনশিয়াল আলাদা রাখতে প্রজেক্টে এই ড্রাইভার প্যাটার্ন ব্যবহার হবে:

```php
<?php
// config/database.php
declare(strict_types=1);

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'alaminma_db';
$dbUser = getenv('DB_USER') ?: 'alaminma_user';
$dbPass = getenv('DB_PASS') ?: 'YOUR_STRONG_SECRET_PASSWORD';
$charset = 'utf8mb4';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // আসল প্রিপেয়ার্ড স্টেটমেন্ট ব্যবহার
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    // প্রোডাকশন মোডে বিস্তারিত ডেটাবেজ ইরর কখনোই স্ক্রিনে দেখানো যাবে না
    error_log("[Database Connection Error]: " . $e->getMessage());
    http_response_code(500);
    die("দুঃখিত, ডাটাবেজ সংযোগে সমস্যা হচ্ছে। কিছু সময় পর পুনরায় চেষ্টা করুন।");
}
```

---

## 9. API & Dynamic Operations

প্রজেক্টের গুরুত্বপূর্ণ ইন্টার‍্যাকশনগুলো (যেমন ভর্তি আবেদন, ডেমো বুকিং) অ্যাজাক্স (`Fetch API`) দিয়ে পরিচালিত হবে যাতে পেজ রিফ্রেশ না করে ইউজার মাত্র ৩০-৬০ সেকেন্ডে রেসপন্স পায়।

### 9.1 API Endpoints Specification

| Endpoint | Method | উদ্দেশ্য | ইনপুট ফিল্ডস | আউটপুট |
|---|---|---|---|---|
| `/api/submit-lead.php` | `POST` | ভর্তি অনুসন্ধান ফরম | `student_name`, `guardian_phone`, `class_level`, `course_id`, `csrf_token` | JSON `{status, message}` |
| `/api/submit-demo.php` | `POST` | ডেমো ক্লাসের আবেদন | `student_name`, `phone`, `class_level`, `preferred_date`, `csrf_token` | JSON `{status, message}` |
| `/api/submit-feedback.php` | `POST` | ডেমো ক্লাসের রেটিং ও মতামত | `lead_id`, `rating`, `understanding_level`, `comments`, `csrf_token` | JSON `{status, message}` |
| `/api/submit-batch-change.php` | `POST` | ব্যাচ পরিবর্তন আবেদন | `student_id`, `current_batch_id`, `requested_batch_id`, `reason`, `guardian_phone` | JSON `{status, message}` |
| `/api/export-students.php` | `GET` | স্টুডেন্ট ও অভিভাবক তালিকা এক্সেল/CSV | `type` ('students' বা 'guardians'), `batch_id`, `class_level` | File Download (`.csv` UTF-8 BOM) |
| `/api/get-batches.php` | `GET` | রিয়েলটাইম ব্যাচ সিট স্ট্যাটাস | `course_id` (Query string) | JSON `[{id, name, available_seats, status}]` |

### 9.2 API Security Standards
1. **Method Check:** POST ছাড়া ফর্ম সাবমিশন এক্সেপ্ট করবে না (`$_SERVER['REQUEST_METHOD'] !== 'POST'` হলে `405 Method Not Allowed`)।
2. **Rate Limiting:** প্রতি আইপি থেকে ঘণ্টায় সর্বোচ্চ ১০টির বেশি লিড সাবমিট করা যাবে না।
3. **Payload Sanitization:** ফোন নম্বর ফিল্টারিং (`preg_replace('/[^0-9+]/', '', $phone)`), স্প্যাম বট ঠেকাতে ফর্মের মধ্যে লুকানো **Honeypot Field** ব্যবহার।
4. **Excel Export Authorization:** `/api/export-students.php` কেবল অথেনটিকেটেড অ্যাডমিন (`super_admin` বা `admin`) রোল ছাড়া অন্য কারো জন্য অ্যাক্সেসযোগ্য নয় (`require_admin()`)।

### 9.3 SMS Gateway Integration Engine (`includes/sms.php`)
1. **SMS Gateway ড্রাইভার:** বাংলাদেশের শীর্ষস্থানীয় SMS গেটওয়ে (যেমন: Greenweb / BulkSMSBD / Onnorokom SMS API) cURL-এর মাধ্যমে সংযুক্ত থাকবে।
2. **অটোমেটেড ট্রিগার লজিক:**
   - ভর্তি/ডেমো সাবমিট হলে তাৎক্ষণিক অভিভাবকের মোবাইলে কনফার্মেশন SMS পাঠানো।
   - ক্লাসে কোনো শিক্ষার্থী Absent মার্ক হলে ড্যাশবোর্ড থেকে তাৎক্ষণিক অভিভাবকের মোবাইলে অ্যালার্ট SMS প্রেরণ।
3. **ফেইলওভার ও লগিং:** প্রতিটি প্রেরিত SMS-এর স্ট্যাটাস ও গেটওয়ে রেসপন্স `attendances` অথবা ডেডিকেটেড লগ ফাইলে সংরক্ষিত থাকবে।

---

## 10. Localization Architecture (English & Bangla Toggle)

সাইটটির ডিফল্ট ভাষা **English**, তবে হেডার থেকে এক ক্লিকেই **বাংলা** মোডে রূপান্তর করা যাবে।

### 10.1 Language Resolution Flow

```text
Incoming Request
       │
       ├── ১. URL Parameter (?lang=bn অথবা ?lang=en) থাকলে Priority-1
       ├── ২. কুকি ($_COOKIE['site_lang']) চেক করা (Priority-2)
       └── ৩. ডিফল্ট ফলব্যাক: 'en'
```

### 10.2 Dual Language Engine Implementation

```php
// includes/i18n.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$supportedLanguages = ['en', 'bn'];
$currentLang = 'en'; // Default English

if (isset($_GET['lang']) && in_array($_GET['lang'], $supportedLanguages)) {
    $currentLang = $_GET['lang'];
    setcookie('site_lang', $currentLang, time() + (86400 * 30), "/"); // 30 দিন
    $_SESSION['site_lang'] = $currentLang;
} elseif (isset($_COOKIE['site_lang']) && in_array($_COOKIE['site_lang'], $supportedLanguages)) {
    $currentLang = $_COOKIE['site_lang'];
}

$langFile = __DIR__ . "/../lang/{$currentLang}.php";
$translations = file_exists($langFile) ? require($langFile) : [];

// ১. স্ট্যাটিক UI অনুবাদ হেল্পার ফাংশন
function __(string $key): string {
    global $translations;
    return $translations[$key] ?? $key;
}

function get_current_lang(): string {
    global $currentLang;
    return $currentLang;
}

// ২. ডায়নামিক ডাটাবেজ কন্টেন্ট রেন্ডার হেল্পার (English ও Bangla অটোমেটিক সুইচ ও ফলব্যাক)
function content(array $row, string $fieldBase): string {
    $lang = get_current_lang();
    $fieldLang = $fieldBase . '_' . $lang;       // e.g. title_bn বা title_en
    $fieldDefault = $fieldBase . '_en';          // ডিফল্ট fallback

    if (!empty($row[$fieldLang])) {
        return htmlspecialchars((string)$row[$fieldLang], ENT_QUOTES, 'UTF-8');
    }
    return htmlspecialchars((string)($row[$fieldDefault] ?? ''), ENT_QUOTES, 'UTF-8');
}
```

### 10.3 Bilingual Content Management Strategy
- **উভয় ভাষার জন্য ডাটাবেজ কলাম:** সাইটের প্রতিটি ডাইনামিক টেবিল (`courses`, `teachers`, `notices`, `results`, `testimonials`, `faqs`, `settings`)-এ English (`_en`) এবং Bangla (`_bn`) ফিল্ড সংরক্ষিত থাকবে।
- **ইউজার অভিজ্ঞতা:** একজন ভিজিটর হেডার থেকে `[বাং]` টগল চাপলে পুরো ওয়েবসাইটের বাটন, মেনু, কোর্সের নাম, সিলেবাস, নোটিশের শিরোনাম ও শিক্ষকের পরিচয়পত্র সরাসরি বাংলায় রূপান্তরিত হবে। একইভাবে `[EN]` চাপলে সম্পূর্ণ সাইট ইংরেজিতে রেন্ডার হবে।
- **অ্যাডমিন ইনপুট:** অ্যাডমিন প্যানেলে যেকোনো কোর্স, নোটিশ বা টিচার যুক্ত করার সময় পাশাপাশি ২টি ইনপুট বক্স থাকবে: একটি ইংরেজির জন্য এবং একটি বাংলার জন্য।

---

## 11. SEO & Discoverability Architecture

লোকাল সার্চ ইঞ্জিন অপ্টিমাইজেশনের (বিশেষ করে **Farmgate, Dhaka** এলাকার গণিত কোচিং সার্চ) জন্য এই আর্কিটেকচার বিশেষভাবে প্রস্তুত।

### 11.1 Dynamic OpenGraph & Meta Generation

প্রতিটি পেজের শুরুতে পেজ-স্পেসিফিক মেটা ভ্যারিয়েবল সেট করতে হবে:
```php
<?php
// courses.php এর শুরুতে
$pageTitle = "Math Courses — SSC, HSC & Admission | Al Amin's Math Care";
$pageDesc = "Find specialized mathematics coaching batches for SSC, HSC, and University Admission seekers at Farmgate, Dhaka.";
$canonicalUrl = "https://alaminmathcare.com/courses";
require_once 'includes/header.php';
?>
```

### 11.2 Structured Schema.org JSON-LD Markup
`includes/header.php`-এ স্বয়ংক্রিয়ভাবে অন্তর্ভুক্ত থাকবে:
```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "EducationalOrganization",
  "name": "Al Amin's Math Care",
  "url": "https://alaminmathcare.com",
  "logo": "https://alaminmathcare.com/assets/images/logo/logo.png",
  "description": "Specialized Mathematics Coaching for SSC, HSC, and Admission in Farmgate, Dhaka.",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "46/1, Britter Goli, Opposite Holy Cross College",
    "addressLocality": "Farmgate",
    "addressRegion": "Dhaka",
    "postalCode": "1216",
    "addressCountry": "BD"
  },
  "telephone": ["+8801520102248", "+8801521255850"],
  "priceRange": "$$"
}
</script>
```

---

## 12. Performance & Caching Architecture

| অপ্টিমাইজেশন কৌশল | প্রয়োগ পদ্ধতি | প্রত্যাশিত ফলাফল |
|---|---|---|
| **Image Modernization** | সকল ব্যানার ও শিক্ষক প্রোফাইল ছবি `.webp` ফরম্যাটে কনভার্ট এবং `<img loading="lazy">` ব্যবহার | ৫০-৭০% ব্যান্ডউইথ সাশ্রয় |
| **Gzip / Deflate** | `.htaccess`-এর মাধ্যমে HTML, CSS, JS ফাইল কম্প্রেস করে ব্রাউজারে পাঠানো | ফাইল সাইজ ৬০% হ্রাস |
| **Browser Cache Expiry** | স্ট্যাটিক ফাইলগুলোতে ১ মাসের এক্সপায়ার হেডার সেট করা (`Cache-Control: max-age=2592000`) | পেজ রিভিজিটে мгновен লোডিং |
| **Database Indexing** | `courses(slug)`, `batches(status, course_id)`, `leads(guardian_phone)` ইনডেক্সিং | কোয়েরি স্পিড < ৫ মিলি-সেকেন্ড |

---

## 13. Admin Panel & Role-Based Access Control (RBAC)

অ্যাডমিন প্যানেলে বিভিন্ন লেভেলের স্টাফদের জন্য নির্দিষ্ট পারমিশন নির্ধারিত:

```
                  ┌──────────────────────┐
                  │     SUPER ADMIN      │
                  │ (Al Amin Sir / Owner)│
                  └──────────┬───────────┘
                             │ Full control (Settings, Users, Finances)
                             ▼
                  ┌──────────────────────┐
                  │    BRANCH MANAGER    │
                  │   (Admin / Manager)  │
                  └──────────┬───────────┘
                             │ Batches, Courses, Lead Tracking, Notices
              ┌──────────────┴──────────────┐
              ▼                             ▼
   ┌──────────────────────┐      ┌──────────────────────┐
   │       TEACHER        │      │     RECEPTIONIST     │
   │ Routine, Attendance, │      │ New Leads, Calls,    │
   │ Exam Marks Entry     │      │ Demo Class Bookings  │
   └──────────────────────┘      └──────────────────────┘
```

### 13.4 সম্পূর্ণ অ্যাডমিন প্যানেল CRUD অপারেশন স্পেসিফিকেশন (Add, Edit, Remove / Delete)

অ্যাডমিন প্যানেল থেকে ওয়েবসাইটের সমস্ত উপাদান গতিশীলভাবে পরিচালনা (যোগ, পরিবর্তন, ও মুছে ফেলা) করার জন্য নিচের আর্কিটেকচার বাধ্যতামূলক:

| মডিউল | Add (নতুন যোগ) | Edit (পরিবর্তন) | Remove / Delete (মুছে ফেলা) | দ্বিভাষিক সাপোর্ট (EN + BN) |
|---|:---:|:---:|:---:|:---:|
| **ম্যাথ কোর্স (Courses)** | ✅ নতুন কোর্স ও সিলেবাস তৈরি | ✅ কোর্স ফি, বর্ণনা ও ক্লাস লেভেল আপডেট | ✅ ডিলিট / আর্কাইভ বাটন (কনফার্মেশন সহ) | `title_en`, `title_bn`, `desc_en`, `desc_bn` |
| **ব্যাচ (Batches)** | ✅ নতুন ব্যাচ, সময় ও শিক্ষক নির্ধারণ | ✅ সিট সংখ্যা, সময় ও রানিং স্ট্যাটাস আপডেট | ✅ ব্যাচ রিমুভ / ক্লোজ করা | `batch_name` (EN/BN) |
| **শিক্ষক প্যানেল (Teachers)** | ✅ শিক্ষক যোগ ও ফটো আপলোড | ✅ শিক্ষাগত যোগ্যতা, অভিজ্ঞতা ও বায়ো এডিট | ✅ শিক্ষক প্রোফাইল রিমুভ করা | `name_en`, `name_bn`, `bio_en`, `bio_bn` |
| **নোটিশ বোর্ড (Notices)** | ✅ নোটিশ পাবলিশ ও PDF/ইমেজ সংযুক্তি | ✅ নোটিশের বিষয়বস্তু ও এক্সপায়ারি ডেট এডিট | ✅ নোটিশ ডিলিট বা আনপাবলিশ করা | `title_en`, `title_bn`, `desc_en`, `desc_bn` |
| **পরীক্ষার রেজাল্ট (Results)** | ✅ রেজাল্ট শিট আপলোড ও শিক্ষার্থী তালিকা | ✅ পরীক্ষার নাম ও মার্কস শিট রিপ্লেস | ✅ ভুল রেজাল্ট মুছে ফেলা | `exam_title_en`, `exam_title_bn` |
| **ক্লাস রুটিন (Routines)** | ✅ সাপ্তাহিক সময়সূচি ও শিডিউল এন্ট্রি | ✅ ক্লাসের দিন ও সময় পরিবর্তন | ✅ রুটিন স্লট বাতিল/রিমুভ | `class_days`, `routine_notes` |
| **টেস্টিমোনিয়াল (Reviews)** | ✅ নতুন শিক্ষার্থী/অভিভাবক রিভিউ এন্ট্রি | ✅ রিভিউ টেক্সট সংশোধন | ✅ অপ্রাসঙ্গিক রিভিউ মুছে ফেলা | `quote_en`, `quote_bn`, `student_name` |
| **ফটো গ্যালারি (Gallery)** | ✅ একাধিক ক্লাস ও অনুষ্ঠানের ফটো আপলোড | ✅ ক্যাপশন ও ক্যাটাগরি এডিট | ✅ অপ্রয়োজনীয় ছবি সার্ভার থেকে ডিলিট | `caption_en`, `caption_bn` |
| **ভর্তি ও ডেমো লিডস (Leads)** | ✅ অফলাইন লিড সরাসরি এন্ট্রি | ✅ স্ট্যাটাস চেঞ্জ (New → Contacted → Admitted) | ✅ স্প্যাম লিড পার্মানেন্টলি ডিলিট | নোটস ও ফলো-আপ হিস্ট্রি |
| **শিক্ষার্থী ও অভিভাবক (Students & Guardians)** | ✅ নতুন শিক্ষার্থী এন্ট্রি ও আইডি জেনারেট | ✅ শিক্ষার্থী ও অভিভাবকের তথ্য এডিট | ✅ সফট ডিলিট / আর্কাইভ (এবং **এক ক্লিকে Excel / CSV ডাউনলোড**) | `student_id_code`, `guardian_name`, `phone` |
| **মাসিক ফি ব্যবস্থাপনা (Monthly Fees)** | ✅ মাসিক ফি কালেকশন ও রিসিট জেনারেট | ✅ বকেয়া ও পেইড স্ট্যাটাস আপডেট | ✅ ভুল এন্ট্রি রিভার্সাল ও অডিট লগ | মাসভিত্তিক `status` (Paid/Due), `receipt_no` |
| **ক্লাস উপস্থিতি ও SMS (Attendance & SMS)** | ✅ দৈনিক উপস্থিতি এন্ট্রি ও অভিভাবক SMS ট্রিগার | ✅ উপস্থিতি স্ট্যাটাস (Present/Absent/Late) সংশোধন | ❌ স্থায়ী ডিলিট নিষিদ্ধ (অডিট ট্রেইল বজায় রাখতে) | `status`, `sms_sent_status` |
| **ডিজিタル আইডি ও QR (Student ID Cards)** | ✅ প্রিন্ট-রেডি আইডি কার্ড ও QR জেনারেশন | ✅ স্টুডেন্ট ফটো ও ব্যাচ তথ্য আপডেট | ✅ আইডি কার্ড রিভোক / সফট ডিলিট | `qr_code_token`, `qr_code_path` |
| **ব্যাচ পরিবর্তন রিকোয়েস্ট (Batch Switch)** | ✅ আবেদন যাচাই ও সিট ক্যাপাসিটি চেক | ✅ অনুমোদন (Approved) দিয়ে সিট শিফট | ✅ বাতিল আবেদন আর্কাইভ | `reason`, `admin_note`, `status` |
| **সাইট সেটিংস (Settings)** | ✅ নতুন ব্যানার / নোটিফিকেশন বার চালু | ✅ মোবাইল নম্বর, হোয়াটসঅ্যাপ, SMS API Key এডিট | ✅ পুরোনো ব্যানার বা এনাউন্সমেন্ট সরানো | `hero_title_en`, `hero_title_bn`, `address` |

#### ডিলিট বা রিমুভ অপারেশনের সিকিউরিটি রুলস:
1. **CSRF Protected Deletions:** কোনো ডিলিট অ্যাকশন সাধারণ GET লিংকের মাধ্যমে হবে না; অবশ্যই POST ফর্ম ও CSRF টোকেন ভ্যালিডেশনের মাধ্যমে হতে হবে।
2. **Modal Confirmation:** ভুলবশত চাপ লাগা রোধে ব্রাউজারে Bootstrap Confirmation Modal বা SweetAlert পপআপ প্রদর্শন।
3. **File Cleanup:** কোনো নোটিশ, রেজাল্ট বা গ্যালারি ছবি ডাটাবেজ থেকে রিমুভ করলে সার্ভারের `assets/uploads/` ফোল্ডার থেকেও ফিজিক্যাল ফাইল আনলিংক (`unlink($filePath)`) নিশ্চিত করা।

---

## 14. Future Scalability & Modular Upgrades

প্রজেক্টটি এমনভাবে আর্কিটেক্ট করা হয়েছে যাতে পরবর্তীতে বড় কোনো রি-রাইট ছাড়াই নিচের ফিচারগুলো যোগ করা সম্ভব:

1. **Phase 2 — Student & Guardian Portal (`/portal/` বা `/student/`):**
   - স্টুডেন্ট আইডি / ইউজার আইডি এবং পাসওয়ার্ড/পিন দিয়ে লগইন।
   - **মাসিক ফি স্ট্যাটাস ট্র্যাকিং:** শিক্ষার্থী কোন কোন মাসের ফি পরিশোধ করেছে (`Paid`) এবং কোন কোন মাসের ফি বকেয়া (`Due`), তার স্পষ্ট বিবরণ ও মানি রিসিট ভিউ।
   - নিজের ক্লাসের রুটিন, নোটিশ, শিট ও রেজাল্ট কার্ড এক ক্লিকে ডাউনলোড।
   - **এক্সেল এক্সপোর্ট ইঞ্জিন:** অ্যাডমিন প্যানেল থেকে ফিল্টার অনুযায়ী সম্পূর্ণ শিক্ষার্থী তালিকা (Student List) এবং অভিভাবক তালিকা (Guardian List) এক ক্লিকে Excel (.xlsx / CSV) ফাইলে ডাউনলোড।
2. **Phase 3 — Payment Gateway Integration:**
   - bKash ও Nagad মার্চেন্ট API (বা SSLCOMMERZ) প্লাগ-ইন করার জন্য `payments` টেবিলে ট্রানজ্যাকশন আইডি ও স্ট্যাটাস ট্র্যাক করার আর্কিটেকচার আগে থেকেই ডিফাইন করা।
3. **Phase 4 — Automated SMS Alerts:**
   - বাংলাদেশে লোকাল SMS গেটওয়ে (e.g. Greenweb, BulkSMSBD) ইন্টিগ্রেশন করে লিড সাবমিটের সাথে সাথে অ্যাডমিনের মোবাইলে ও অভিভাবকের মোবাইলে কনফার্মেশন SMS পাঠানো।

---

## সংক্ষেপ ও সমাপনী পর্যবেক্ষণ

এই `architecture.md` ফাইলটি অনুসরণ করে কাজ করলে:
- ডোমেইন ও ডিএনএস কনফিগারেশনে কোনো ক্র্যাশ বা কনফ্লিক্ট হবে না।
- ডেটাবেজ ও সার্ভার লেভেলে সর্বোচ্চ সাইবার সিকিউরিটি বজায় থাকবে।
- সাইটটি গুগল সার্চে খুব দ্রুত র‍্যাঙ্ক করতে পারবে এবং ভিজিটররা কোনো প্রকার ল্যাগ ছাড়াই স্বাচ্ছন্দ্যে ব্রাউজ করতে পারবে।
