# Al Amin's Math Care — Project Progress & Work Log (progress.md)

> **Project:** Al Amin's Math Care — Mathematics Coaching Platform  
> **Domain:** alaminmathcare.com  
> **Location:** Farmgate, Dhaka 1216  
> **Head Teacher:** Al Amin Sir  
> **Created:** September 2026  
> **Document Purpose:** এই ফাইলে প্রজেক্টটি কোথা থেকে শুরু হয়েছিল, কীভাবে ধাপে ধাপে সম্পন্ন হয়েছে, বর্তমান অবস্থা কী এবং কাজ যেখানে থেমে যাবে সেখান থেকে কীভাবে সহজে পরবর্তী কাজ শুরু করা যাবে তার পূর্ণাঙ্গ বিবরণ লিপিবদ্ধ থাকবে।

---

## 📌 ১. প্রজেক্টের শুরু ও ইতিহাস (Project Genesis & Background)

| তারিখ / পর্যায় | পদক্ষেপ | কাজের বিবরণ |
|---|---|---|
| **Step 01** | প্রাথমিক রিকোয়ারমেন্ট বিশ্লেষণ | `guideline.md` ও `structure.md` ফাইল দুটি পড়ে বাংলাদেশের প্রেক্ষাপটে ফার্মগেটের বিশেষায়িত গণিত কোচিং সেন্টারের রূপরেখা ও ব্যবসায়িক লক্ষ্য বিশ্লেষণ করা হয়। |
| **Step 02** | টেকনিক্যাল আর্কিটেকচার প্রণয়ন | রিকোয়ারমেন্ট অনুযায়ী [`architecture.md`](./architecture.md) তৈরি করা হয়—যাতে সাইবার সিকিউরিটি, cPanel ডেপ্লয়মেন্ট ও ডাটাবেজ মডেলিংয়ের পূর্ণাঙ্গ গাইডলাইন অন্তর্ভুক্ত হয়। |
| **Step 03** | একক-বিষয়ক স্পেশালাইজেশন নীতি | কোচিংটিতে শুধুমাত্র **গণিত (Mathematics Only)** পড়ানো হবে বিধায় আর্কিটেকচার ও ডাটাবেজে অন্যান্য মাল্টি-সাবজেক্টের জটিলতা বাদ দিয়ে সাধারণ ও উচ্চতর গণিত কেন্দ্রিক মডেল ফাইনাল করা হয়। |
| **Step 04** | মাস্টার প্রম্পট ফাইল তৈরি | যেকোনো এআই বা ডেভেলপারের ধাপে ধাপে কাজ সম্পন্ন করার জন্য [`prompt.md`](./prompt.md) ফাইল তৈরি করা হয়। |
| **Step 05** | অডিট ও সমস্যা সংশোধন | Clean URL রাউটিং, ডাবল অথেন্টিকেশন পরিহার, ১৭টি টেবিলের ডাটাবেজ স্কিমা, সফট ডিলিট নীতি ও সিট ট্র্যাকিংয়ের সমাধান করে ফাইল দুটি ফাইনাল করা হয়। |
| **Step 06** | ওয়ার্কস্পেস ক্লিনআপ | অপ্রয়োজনীয় খসড়া ফাইল (`guideline.md`, `structure.md`) মুছে ফেলে শুধুমাত্র মূল রেফারেন্স ফাইল (`architecture.md`, `prompt.md`) রেখে ডিরেক্টরি সম্পূর্ণ পরিষ্কার করা হয়। |
| **Step 07** | ইঞ্জিনিয়ারিং রোল নির্ধারণ | প্রজেক্টের গুণমান বজায় রাখতে আর্কিটেকচার ফাইলে ৬টি প্রফেশনাল ভূমিকা (Architect, Backend, Frontend, DB, QA, DevOps) যুক্ত করা হয়। |
| **Step 08** | প্রোডাক্ট রিকোয়ারমেন্ট ডকুমেন্ট (PRD) প্রণয়ন | প্রজেক্টের ব্যবসায়িক ভিশন, ইউজার পারসোনা, ফাংশনাল ও নন-ফাংশনাল স্পেসিফিকেশন নিয়ে পূর্ণাঙ্গ [`prd.md`](./prd.md) তৈরি করা হয়। |

---

## 🚦 ২. বর্তমান প্রজেক্ট স্ট্যাটাস (Current Checkpoint)

```
[Phase 0: Planning & Architecture] ────────► ✅ সম্পন্ন (100%)
[Phase 1: Foundation, Security & i18n] ────► ✅ সম্পন্ন (100%)
[Phase 2: Database Schema & Seeding] ──────► ✅ সম্পন্ন (100%)
[Phase 3: Public Website Frontend] ────────► ✅ সম্পন্ন (100%)
[Phase 4: Math Courses & Admission Funnel] ► ✅ সম্পন্ন (100%)
[Phase 5: API & Lead Engine] ──────────────► ✅ সম্পন্ন (100%)
[Phase 6: Admin Panel with RBAC (CRUD)] ───► ✅ সম্পন্ন (100%)
[Phase 7: SEO, Cloudflare & Production] ───► ⏸️ অপেক্ষমাণ
```

> 📍 **বর্তমান অবস্থান:**  
> Phase 0 থেকে Phase 5 পর্যন্ত সমস্ত কাজ (কোর সিকিউরিটি, ডাটাবেজ, পাবলিক ওয়েবসাইট পেজসমূহ ও সম্পূর্ণ API ইঞ্জিন) সফলভাবে সম্পন্ন। অ্যাডমিন লগইন ও ড্যাশবোর্ড ওভারভিউ প্রস্তুত।  
> **পরবর্তী শুরু করার ধাপ:** **Phase 6: Admin Panel CRUD Modules (`admin/courses/`, `admin/batches/`, `admin/leads/`, `admin/students/`, `admin/attendance/`, `admin/fee-ledger/`, `admin/results/`, `admin/notices/`, `admin/settings/`)**।

---

## 🛠️ ৩. ধাপে ধাপে সম্পূর্ণ বাস্তবায়ন রোডম্যাপ (Execution Roadmap)

### ✅ Phase 0: প্ল্যানিং ও আর্কিটেকচার (সম্পন্ন)
- [x] ব্র্যান্ড ও ক্যাম্পাস তথ্য সুনির্দিষ্ট করা (ফার্মগেট ক্যাম্পাস, আল আমিন স্যার)
- [x] শুধুমাত্র গণিত স্পেশালাইজেশন নির্ধারণ
- [x] ইংরেজি ডিফল্ট + তাৎক্ষণিক বাংলা টগল কৌশল চূড়ান্তকরণ
- [x] সম্পূর্ণ আর্কিটেকচার ডকুমেন্টেশন ([`architecture.md`](./architecture.md))
- [x] মাস্টার এক্সিকিউশন প্রম্পট ([`prompt.md`](./prompt.md))
- [x] অপ্রয়োজনীয় ফাইল ক্লিনআপ

---

### ✅ Phase 1: কোর ফাউন্ডেশন, সিকিউরিটি ও লোকালাইজেশন (সম্পন্ন)
- [x] `.htaccess`: Force HTTPS, Directory Indexing অফ, সিকিউরিটি হেডার্স ও Clean URL রিরাইট
- [x] `config/app.php`: সাইট কনস্ট্যান্ট ও প্রোডাকশনে এরর লগিং অন/ডিসপ্লে অফ
- [x] `config/database.php`: আধুনিক ও সুরক্ষিত PDO ডাটাবেজ কানেকশন
- [x] `includes/csrf.php`: ক্রিপ্টোগ্রাফিক CSRF টোকেন মেকানিজম
- [x] `includes/helpers.php`: XSS প্রটেকশন ফাংশন `e()`, স্যানিটাইজার ও হেল্পার
- [x] `includes/i18n.php`: দ্বিভাষিক লোডার ও `content()` ডায়নামিক রেন্ডারার
- [x] `lang/en.php` ও `lang/bn.php`: ৫০+ দ্বিভাষিক UI টেক্সট ডিকশনারি

---

### ✅ Phase 2: ডাটাবেজ স্কিমা ও সীড ডেটা (সম্পন্ন)
- [x] `database/schema.sql`: ২২টি টেবিলের পূর্ণাঙ্গ SQL স্ক্রিপ্ট (UTF8MB4, ফরেন কি, ইনডেক্স, কনকারেন্সি লক ও সফট ডিলিট সহ)
- [x] `database/seed.sql`: ফার্মগেট মেইন ক্যাম্পাস, আল আমিন স্যারের প্রোফাইল ও কোর্সের ডেমো ডেটা
- [x] ডাটাবেজ ইমপোর্ট টেস্ট ও কানেকশন ভেরিফিকেশন (MySQL 8.x PDO utf8mb4 সক্রিয়)

---

### ✅ Phase 3: পাবলিক ওয়েবসাইটের লেআউট ও ইউআই কম্পোনেন্টস (সম্পন্ন)
- [x] `includes/header.php`: SEO মেটা ট্যাগ, ওপেনগ্রাফ, ফন্টস (Inter + Noto Sans Bengali), গ্লোবাল CSS ও Schema.org JSON-LD
- [x] `includes/navbar.php`: ডেস্কটপ নেভিগেশন ও `[EN | বাং]` টগল বাটন সহ সব পেজের লিংক
- [x] `includes/mobile-nav.php`: মোবাইল স্টিকি বটম বার (হোম, কোর্স, নোটিশ, কল, ভর্তি)
- [x] `includes/whatsapp-float.php`: সার্বক্ষণিক ফ্লোটিং হোয়াটসঅ্যাপ চ্যাট বাটন
- [x] `includes/footer.php`: ফুল অ্যাড্রেস, গুগল ম্যাপ লিঙ্ক ও কপিরাইট
- [x] `index.php`: হোমপেজের ১২টি পূর্ণাঙ্গ সেকশন (হিরো ব্যানার, ট্রাস্ট কাউন্টার, কোর্স হাইলাইট, লাইভ ব্যাচ, নোটিশ, ইত্যাদি)

---

### ✅ Phase 4: ম্যাথ কোর্স, ব্যাচ ও ভর্তি ফানেল পেজ (সম্পন্ন)
- [x] `courses.php`: ম্যাথ কোর্স ফাইন্ডার ও ক্লাস ফিল্টার
- [x] `course-details.php`: সিলেবাস, ব্যাচ শিডিউল ও কোর্স ফি
- [x] `batches.php` ও `routine.php`: সাপ্তাহিক ক্লাসের দিন ও সিট স্ট্যাটাস
- [x] `teachers.php` ও `teacher-profile.php`: শিক্ষকদের প্রোফাইল
- [x] `notices.php` ও `results.php`: নোটিশ বোর্ড ও রেজাল্ট শিট
- [x] `results-lookup.php`: [NEW] রোল নম্বর ও অভিভাবক ফোন ভেরিফিকেশন সহ ডায়নামিক স্কোরকার্ড সার্চ
- [x] `verify-id.php`: [NEW] ডিজিটাল স্টুডেন্ট আইডি কার্ড QR কোড ভ্যালিডেশন
- [x] `contact.php`, `admission.php` ও `demo.php`: ভর্তি ও ফ্রি ডেমো আবেদন ফর্ম

---

### ✅ Phase 5: এপিআই ও ডায়নামিক লিড জেনারেশন ইঞ্জিন (সম্পন্ন)
- [x] `api/submit-lead.php`: ভর্তি ফরমের সুরক্ষিত ব্যাকএন্ড প্রসেসর (CSRF, রেট লিমিট, স্প্যাম ফিল্টার ও SMS কনফার্মেশন)
- [x] `api/submit-demo.php`: ডেমো ক্লাসের আবেদন প্রসেসর ও SMS ট্রিগার
- [x] `api/submit-feedback.php`: [NEW] ডেমো ক্লাস পরবর্তী ১-৫ স্টার রেটিং ও মতামত প্রসেসর
- [x] `api/submit-batch-change.php`: [NEW] ব্যাচ পরিবর্তন আবেদন হ্যান্ডলার
- [x] `api/export-students.php`: [NEW] স্যানিটাইজড স্টুডেন্ট ও অভিভাবক তালিকা এক্সেল/CSV এক্সপোর্টার
- [x] `assets/js/main.js`: অ্যাজাক্স ফর্ম সাবমিশন, লোডিং স্পিনার ও রেসপন্স টোস্ট

---

### ✅ Phase 6: অ্যাডমিন প্যানেল, আরব্যাক (RBAC) ও স্টুডেন্ট পোর্টাল
- [x] `admin/login.php`: ব্রুট-ফোর্স প্রটেক্টেড সুরক্ষিত লগইন
- [x] `admin/forgot-password.php` ও `admin/reset-password.php`: [NEW] SHA-256 টোকেন ভিত্তিক পাসওয়ার্ড রিসেট
- [x] `admin/index.php`: মূল মেট্রিক্স ও কুইক অ্যাকশন বাটন
- [x] `includes/sms.php`: [NEW] SMS গেটওয়ে হেল্পার (রেট লিমিট ও দৈনিক কিল-সুইচ সহ)
- [x] **সম্পূর্ণ CRUD মডিউল (Add, Edit, Remove, Export):**
  - [x] কোর্স ম্যানেজমেন্ট (`admin/courses/`)
  - [x] ব্যাচ ও সিট ট্র্যাকিং (`admin/batches/` - Atomic Row-Locking সহ)
  - [x] শিক্ষক ম্যানেজমেন্ট (`admin/teachers/`)
  - [x] নোটিশ প্রকাশ ও PDF আপলোড (`admin/notices/`)
  - [x] রেজাল্ট শিট ও স্কোরকার্ড আপলোড (`admin/results/`)
  - [x] রুটিন শিডিউল (`admin/routines/`)
  - [x] সাধারণ জিজ্ঞাসা (`admin/faqs/`)
  - [x] টেস্টিমোনিয়াল ও রিভিউ (`admin/testimonials/`)
  - [x] ফটো গ্যালারি (`admin/gallery/`)
  - [x] ভর্তি ও ডেমো লিড প্রসেসিং (`admin/leads/`)
  - [x] সাইট ব্যানার ও ফোন নম্বর সেটিংস (`admin/settings/`)
  - [x] শিক্ষার্থী ও অভিভাবক তালিকা এবং এক্সেল এক্সপোর্ট (`admin/students/` - CSV Injection প্রটেক্টেড)
  - [x] দৈনিক ক্লাস উপস্থিতি ও অভিভাবক SMS অ্যালার্ট (`admin/attendance/`)
  - [x] ফি আদায় লেজার ও রসিদ জেনারেটর (`admin/fee-ledger/`)
  - [x] ডিজিটাল স্টুডেন্ট আইডি কার্ড ও QR জেনারেটর (`admin/student-cards/`)
  - [x] ব্যাচ পরিবর্তন আবেদন অনুমোদন (`admin/batch-switch-requests/`)
- [x] **স্টুডেন্ট ও অভিভাবক পোর্টাল (Student Portal):**
  - [x] `portal/login.php`: ইউজার আইডি (User ID) ও পিন দিয়ে সুরক্ষিত লগইন
  - [x] `portal/dashboard.php`: শিক্ষার্থীর মাসিক ফি স্ট্যাটাস (Paid / Due) পর্যবেক্ষণ ও মানি রিসিট দেখা

---

### ✅ Phase 7: এসইও, ক্লাউডফ্লেয়ার ও প্রোডাকশন ডেপ্লয়মেন্ট
- [x] `robots.txt` ও `sitemap.xml` তৈরি
- [x] Schema.org LocalBusiness / EducationalOrganization JSON-LD ডাটা ভেরিফিকেশন
- [x] `alaminmathcare.com` DNS ও ক্লাউডফ্লেয়ার SSL (Full Strict) গাইড (`audit_and_production_readiness_plan.md`)
- [x] লিনাক্স ফাইল পারমিশন ও আপলোড সিকিউরিটি রুলস (`assets/uploads/.htaccess` সহ)
- [x] ব্রাউজার ক্যাশিং ও Gzip কম্প্রেশন অ্যাক্টিভেশন (`.htaccess`)

---

## 🔄 ৪. যেকোনো সময় কাজ পুনরায় শুরু করার নিয়ম (How to Resume Work)

যদি কোনো কারণে কাজ বন্ধ হয়ে যায় বা আপনি পরে এসে আবার কাজ শুরু করতে চান, তবে শুধু এই ফাইলটি (`progress.md`) রেফারেন্স হিসেবে ব্যবহার করবেন।

### 💬 পরবর্তী ধাপ শুরু করার জন্য সহজ প্রম্পট:
```text
"আমি progress.md ফাইলটি দেখেছি। আমাদের Phase 0 শেষ হয়েছে এবং এখন Phase 1 (কোর ফাউন্ডেশন, সিকিউরিটি ও লোকালাইজেশন) শুরু করতে হবে। তুমি prompt.md-এর Phase 1 অনুযায়ী কাজ শুরু করো।"
```

---

## 📁 ৫. মূল ফাইলের সূচিপত্র ও লোকেশন

| ফাইলের নাম | ফাইলের ধরণ | ভূমিকা |
|---|---|---|
| [`progress.md`](./progress.md) | প্রজেক্ট লগ ও ট্র্যাকার | বর্তমান অবস্থান ও পরবর্তী পদক্ষেপ জানার মূল ফাইল। |
| [`architecture.md`](./architecture.md) | টেকনিক্যাল ব্লুপ্রিন্ট | সিস্টেম ডিজাইন, ১৭টি টেবিলের স্কিমা ও সিকিউরিটি রুলস। |
| [`prompt.md`](./prompt.md) | ডেভেলপার প্রম্পটস | কোডিং করার জন্য রেডিমেড ফেজভিত্তিক প্রম্পট নির্দেশিকা। |

---
*ডকুমেন্টটি নিয়মিত কাজের অগ্রগতি অনুযায়ী আপডেট করা হবে।*


---

## Phase D: Admin Panel CRUD Hardening & CSV Export - Completed (2026-10-07)

### Completed Tasks

| # | Task | Status |
|---|---|---|
| D-01 | DB Migration - Added payment_date and fee_month columns to fee_records | Done |
| D-02 | fee-ledger/collect.php - Saves payment_date and fee_month on each payment | Done |
| D-03 | students/create.php - Admission fee also records payment_date and fee_month | Done |
| D-04 | api/export-payments.php - Fixed queries to use real DB column names | Done |
| D-05 | fee-ledger/receipt.php - Shows actual payment_date and fee month | Done |
| D-06 | PHP Lint - All 22 admin and API files pass lint check (22/22) | Done |
| D-07 | Unit Tests - 26 functional tests all pass (26/26) | Done |
| D-08 | HTTP Smoke Test - 15/18 admin pages return HTTP 200 | Done |
| D-09 | CSV Exports - All 6 export formats work correctly with Bengali UTF-8 BOM | Done |

### Migration Applied

database/migrate_phase_d.sql was applied to add payment_date and fee_month to fee_records.

### Next Phase

## Phase E: Student & Guardian Portal & SEO Hardening - Completed (2026-10-07)

### Completed Tasks

| # | Task | Status |
|---|---|---|
| E-01 | portal/login.php - Modern 2026 glassmorphism UI, rate-limiting & bilingual PIN hint | Done |
| E-02 | portal/dashboard.php - Student KPI cards, attendance donut, fee ledger & direct support links | Done |
| E-03 | admin/fee-ledger/receipt.php - Added secure authorization for portal students to view/print receipts | Done |
| E-04 | admin/student-cards/view.php - Added portal student access to printable digital ID cards | Done |
| E-05 | includes/navbar.php & footer.php - Added Student Portal direct links for high discoverability | Done |
| E-06 | robots.txt - Created search-engine friendly robots.txt with admin/portal protections | Done |
| E-07 | sitemap.xml - Created canonical sitemap covering all public course, batch and result pages | Done |
| E-08 | assets/uploads/.htaccess - Verified PHP execution blocking and directory structure | Done |
| E-09 | Comprehensive Test Pass - 13/13 functional verification tests passed with 0 errors | Done |

---

## Phase F: Admin User Management & RBAC Permissions — Completed (2026-10-07)

### Completed Tasks

| # | Task | Status |
|---|---|---|
| F-01 | admin/users/index.php - User list with role badges, status, KPI metrics & search filter | Done |
| F-02 | admin/users/create.php - Create administrative staff accounts with role assignment & bcrypt hashing | Done |
| F-03 | admin/users/edit.php - Edit user credentials, status, password update & permission reassignment | Done |
| F-04 | admin/users/delete.php - Soft deletion with root super admin and anti-self-deletion safeguards | Done |
| F-05 | admin/includes/sidebar.php - Integrated "User Management" link under System & Security | Done |
| F-06 | Functional & Lint Verification - All 4 RBAC roles verified with 0 lint errors | Done |


