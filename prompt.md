# Al Amin's Math Care — Master Development Prompts (prompt.md)

> **Project:** Al Amin's Math Care (Mathematics Specialist Coaching)  
> **Based on:** [architecture.md](./architecture.md) & [structure.md](./structure.md)  
> **Target Domain:** alaminmathcare.com  
> **Tech Stack:** PHP 8.x + MySQL 8.x + Bootstrap 5.3 + Vanilla JS + Apache cPanel  
> **Purpose:** এই ফাইলটি ব্যবহার করে ধাপে ধাপে সম্পূর্ণ ওয়েবসাইট, সিকিউরিটি গার্ড, অ্যাডমিন প্যানেল এবং লিড জেনারেশন সিস্টেম নিখুঁতভাবে তৈরি করা যাবে।

---

## 📋 সূচিপত্র

1. [প্রজেক্ট কনটেক্সট ও মাস্টার ইন্সট্রাকশন (System Persona)](#1-প্রজেক্ট-কনটেক্সট-ও-মাস্টার-ইন্সট্রাকশন-system-persona)
2. [কোর আর্কিটেকচারাল ও সিকিউরিটি রুলস (Non-Negotiables)](#2-কোর-আর্কিটেকচারাল-ও-সিকিউরিটি-রুলস-non-negotiables)
3. [ধাপভিত্তিক এক্সিকিউশন প্রম্পটস (Phased Execution Prompts)](#3-ধাপভিত্তিক-এক্সিকিউশন-প্রম্পটস-phased-execution-prompts)
   - [Phase 1: ফাউন্ডেশন, সিকিউরিটি ও লোকালাইজেশন কোর](#phase-1-ফাউন্ডেশন-সিকিউরিটি-ও-লোকালাইজেশন-কোর)
   - [Phase 2: ডাটাবেজ স্কিমা ও সীড ডেটা (`schema.sql` & `seed.sql`)](#phase-2-ডাটাবেজ-স্কিমা-ও-সীড-ডেটা-schemasql--seedsql)
   - [Phase 3: পাবলিক ওয়েবসাইটের লেআউট ও ইউআই কম্পোনেন্টস](#phase-3-পাবলিক-ওয়েবসাইটের-লেআউট-ও-ইউআই-কম্পোনেন্টস)
   - [Phase 4: ম্যাথ কোর্স, ব্যাচ ও ভর্তি ফানেল পেজ](#phase-4-ম্যাথ-কোর্স-ব্যাচ-ও-ভর্তি-ফানেল-পেজ)
   - [Phase 5: এপিআই ও ডায়নামিক লিড জেনারেশন ইঞ্জিন](#phase-5-এপিআই-ও-ডায়নামিক-লিড-জেনারেশন-ইঞ্জিন)
   - [Phase 6: সিকিউর অ্যাডমিন প্যানেল ও আরব্যাক (RBAC)](#phase-6-সিকিউর-অ্যাডমিন-প্যানেল-ও-আরব্যাক-rbac)
   - [Phase 7: এসইও অপ্টিমাইজেশন ও প্রোডাকশন ডেপ্লয়মেন্ট চেক](#phase-7-এসইও-অপ্টিমাইজেশন-ও-প্রোডাকশন-ডেপ্লয়মেন্ট-চেক)
4. [দ্রুত ব্যবহারের জন্য সিঙ্গেল মাস্টার প্রম্পট (All-in-One Master Prompt)](#4-দ্রুত-ব্যবহারের-জন্য-সিঙ্গেল-মাস্টার-প্রম্পট-all-in-one-master-prompt)

---

## 1. প্রজেক্ট কনটেক্সট ও মাস্টার ইন্সট্রাকশন (System Persona)

যেকোনো AI মডেল বা ডেভেলপারের শুরুতে এই পরিচিতিটি অনুসরণ করা আবশ্যক:

```text
তুমি একজন সিনিয়র ফুলস্ট্যাক পিএইচপি, সাইবার সিকিউরিটি স্পেশালিস্ট এবং সিনিয়র ইউআই/ইউএক্স ডিজাইন ইঞ্জিনিয়ার (Senior UI/UX Design Engineer)। তুমি "Al Amin's Math Care" (alaminmathcare.com) নামক একটি বিশেষায়িত গণিত কোচিং সেন্টারের জন্য একটি ওয়ার্ল্ড-ক্লাস, নেক্সট-জেনারেশন (2026+ Visual Aesthetics), মোবাইল-ফার্স্ট ও হাইপার-সিকিউর ওয়েব প্ল্যাটফর্ম তৈরি করছো। 

কোচিং সম্পর্কিত মূল তথ্য:
- প্রতিষ্ঠান: Al Amin's Math Care
- প্রধান শিক্ষক: আল আমিন স্যার (Head Teacher)
- ক্যাম্পাস: ৪৬/১, বৃত্তের গলি, হোলি ক্রস কলেজের বিপরীতে, ফার্মগেট, ঢাকা ১২১৬
- মোবাইল: 01520102248, 01521255850 | হোয়াটসঅ্যাপ: 01520102248
- এক্সক্লুসিভ স্পেশালাইজেশন: শুধুমাত্র গণিত (Mathematics Only) — সাধারণ গণিত, উচ্চতর গণিত ১ম ও ২য় পত্র, ক্যাডেট/মডেল টেস্ট, ইঞ্জিনিয়ারিং ও বিশ্ববিদ্যালয় ভর্তি গণিত।
- প্রাইমারি ভাষা: English (ডিফল্ট), সাথে তাৎক্ষণিক বাংলা টগল (Bangla Toggle)।
- আর্কিটেকচার নীতি: architecture.md ও structure.md-এর নিয়ম হুবহু মেনে চলা।
- ইউআই/ইউএক্স নীতি: মডার্ন টাইপোগ্রাফি (Inter + Noto Sans Bengali), গ্লাস মরফিজম, সমৃদ্ধ কালার প্যালেট, মাইক্রো-ইন্টারঅ্যাকশন, থ্রি-ডি ফিল ব্যাজ এবং ফ্লুইড কনভার্সন ফানেল।
```

---

## 2. কোর আর্কিটেকচারাল ও সিকিউরিটি রুলস (Non-Negotiables)

কোডিংয়ের সময় কোনো অবস্থাতেই নিচের নিয়মের ব্যত্যয় ঘটবে না:

1. **Mathematics Specialization:** সাইটে শুধুমাত্র গণিতের কোর্স ও ব্যাচ থাকবে। কোনো মাল্টি-সাবজেক্ট অপশন থাকবে না।
2. **Dual Language Everywhere (English & Bangla):**
   - ইউজাররা এক ক্লিকেই সম্পূর্ণ ওয়েবসাইট ইংরেজি বা বাংলায় দেখতে পারবেন।
   - স্ট্যাটিক UI স্ট্রিং `lang/en.php` ও `lang/bn.php` থেকে `__('key')` ফাংশন দিয়ে লোড হবে।
   - সকল ডাটাবেজ টেবিল (`courses`, `teachers`, `notices`, `results`, `settings`)-এ `_en` এবং `_bn` উভয় কলাম থাকবে।
   - ফ্রন্টএন্ডে `content($row, 'field')` হেল্পারের মাধ্যমে ইউজারের সিলেক্ট করা ভাষা (অথবা ডিফল্ট ইংরেজি) স্বয়ংক্রিয়ভাবে রেন্ডার হবে।
3. **Full Admin Control (Add, Edit & Remove Everything):**
   - অ্যাডমিন প্যানেল থেকে ওয়েবসাইটের প্রতিটি উপাদান (কোর্স, ব্যাচ, শিক্ষক, নোটিশ, রেজাল্ট, রুটিন, গ্যালারি, রিভিউ, লিডস ও সাইট ব্যানার/সেটিংস) সম্পূর্ণভাবে **Add (নতুন যোগ), Edit (পরিবর্তন) এবং Remove / Delete (মুছে ফেলা)** করার ক্ষমতা থাকতে হবে।
   - অ্যাডমিন ফর্মে প্রতিটি ফিল্ডের জন্য ইংরেজি ও বাংলা উভয় ইনপুট বক্স থাকবে।
   - কোনো ডিলিট অ্যাকশন যাতে দুর্ঘটনাবশত না হয়, তার জন্য Confirmation Modal এবং POST + CSRF প্রটেকশন থাকবে।
4. **SQL Injection নির্মূল:** কোনো raw query বা স্ট্রিং কনক্যাটেনশন ব্যবহার করা যাবে না। প্রতিটি কুয়েরিতে PDO Prepared Statements (`$pdo->prepare()` ও `execute()`) বাধ্যতামূলক।
5. **XSS Protection:** ভিউ ফাইলে ইউজার ডাটা রেন্ডার করার সময় সর্বদা `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')` বা `e()` হেল্পার দিয়ে এস্কেপ করতে হবে।
6. **CSRF Tokens:** প্রতিটি POST ফর্মে লুকানো CSRF টোকেন ভ্যালিডেশন থাকতে হবে।
7. **No Direct Execution in Uploads:** `assets/uploads/` ডিরেক্টরিতে সব ধরনের `.php` স্ক্রিপ্ট এক্সিকিউশন সম্পূর্ণ ব্লক থাকতে হবে।
8. **Mobile-First Conversion:** মোবাইল বটম বার, ওয়ান-ক্লিক কল, হোয়াটসঅ্যাপ চ্যাট এবং ৩০-৬০ সেকেন্ডে ভর্তি আবেদন জমা নিশ্চিত করতে হবে।
9. **Next-Gen UI/UX & Visual Excellence Standards (2026+ Standards):**
   - সাধারণ বা ম্যাড়মেড়ে ডিজাইন সম্পূর্ণ বর্জনীয়; প্রিমিয়াম ও আধুনিক লুক (গ্লাস মরফিজম, প্রাণবন্ত গ্রেডিয়েন্ট, সফট শ্যাডো, এলিভেশন ডেপথ)।
   - বাংলা ও ইংরেজি টাইপোগ্রাফির দৃষ্টিনন্দন ব্যালেন্স (Inter + Noto Sans Bengali/Hind Siliguri)।
   - থাম্ব-জোন বান্ধব মোবাইল ইউজার এক্সপেরিয়েন্স, মাইক্রো-অ্যানিমেশন এবং ইন্টারঅ্যাক্টিভ ফিডব্যাক স্টেট।
   - গণিতের ফর্মুলা/প্রতীকের দৃষ্টিনন্দন উপস্থাপন এবং ট্রাস্ট-বিল্ডিং ব্যাজ ও ভিজ্যুয়াল এলিমেন্ট।

---

## 3. ধাপভিত্তিক এক্সিকিউশন প্রম্পটস (Phased Execution Prompts)

---

### Phase 1: ফাউন্ডেশন, সিকিউরিটি ও লোকালাইজেশন কোর

#### 🎯 প্রম্পট:
```text
Phase 1: Foundation & Security Setup for Al Amin's Math Care

তুমি architecture.md-এর নির্দেশনা অনুযায়ী প্রজেক্টের কোর কনফিগারেশন, সিকিউরিটি এবং লোকালাইজেশন ফাইলগুলো তৈরি করো।

প্রয়োজনীয় ফাইলসমূহ:
1. .htaccess (রুট ডিরেক্টরি):
   - Force HTTPS রিডাইরেক্ট
   - Directory Indexing বন্ধ (-Indexes)
   - সিকিউরিটি হেডার্স: X-Frame-Options (SAMEORIGIN), X-Content-Type-Options (nosniff), X-XSS-Protection, HSTS, Referrer-Policy
   - config, database, lang ফোল্ডারে সরাসরি ব্রাউজার এক্সেস ব্লক
   - assets/uploads-এ PHP এক্সিকিউশন নিষিদ্ধকরণ রুল
   - ক্লিন ইউআরএল রিরাইট:
     * ^courses/([a-zA-Z0-9_-]+)/?$ -> course-details.php?slug=$1
     * ^batches/([0-9]+)/?$ -> batch-details.php?id=$1
     * ^teachers/([a-zA-Z0-9_-]+)/?$ -> teacher-profile.php?slug=$1
     * ^notices/([0-9]+)/?$ -> notice-details.php?id=$1

2. config/app.php:
   - BASE_URL, APP_NAME ("Al Amin's Math Care"), APP_ENV (development/production)
   - প্রোডাকশন মোডে error_reporting(0) এবং display_errors=0
   - সিকিউর সেশন কনফিগারেশন (HttpOnly, Secure, SameSite=Strict)

3. config/database.php:
   - PDO কানেকশন ড্রাইভার (utf8mb4_unicode_ci)
   - Prepared statement emulation বন্ধ (ATTR_EMULATE_PREPARES => false)
   - এক্সেপশন মোড অন ও এরর লগিং (স্ক্রিনে পাসওয়ার্ড বা ডিবি এরর না দেখানো)

4. includes/csrf.php:
   - csrf_token() এবং verify_csrf_token() ফাংশন

5. includes/helpers.php:
   - e($str) ফাংশন (XSS escaping)
   - clean_input($data), slugify($text), bangla_date() হেল্পার

6. includes/i18n.php এবং lang/en.php, lang/bn.php:
   - ডিফল্ট ইংরেজি ভাষা লোড
   - ?lang=bn বা কুকি 'site_lang' থেকে ভাষা নির্ধারণ ও ৩০ দিনের কুকি সেট
   - __('key') গ্লোবাল ট্রান্সলেশন ফাংশন
   - content($row, 'field') ডায়নামিক কন্টেন্ট রেন্ডারার (ইংরেজি ও বাংলা ফলব্যাক সহ)
   - হেডার, বাটন, ফর্ম ও ফুটারের জন্য প্রয়োজনীয় ৫০+ ইংরেজি ও বাংলা অনুবাদ স্ট্রিং
```

---

### Phase 2: ডাটাবেজ স্কিমা ও সীড ডেটা (`schema.sql` & `seed.sql`)

#### 🎯 প্রম্পট:
```text
Phase 2: Database Design & Seeding for Al Amin's Math Care

architecture.md-এর সেকশন ৬.২ অনুযায়ী MySQL 8.x উপযোগী `database/schema.sql` এবং `database/seed.sql` তৈরি করো।

টেবিলের শর্তসমূহ:
1. ইঞ্জিন: InnoDB, Collation: utf8mb4_unicode_ci (যাতে বাংলা টেক্সট ও ম্যাথ সিম্বল পারফেক্ট থাকে)।
2. মোট ২২টি টেবিল সম্পূর্ণ ডিফাইন করতে হবে:
   - roles (super_admin, admin, teacher, receptionist)
   - users (id, role_id, name, email, password [bcrypt], failed_login_attempts, lockout_until, reset_token_hash, reset_token_expires_at, is_deleted)
   - branches (id, name, address, area, city, phone_primary, is_main, status)
   - teachers (id, user_id, slug, name_en, name_bn, designation_en, designation_bn, qualification, bio_en, bio_bn, photo, is_head_teacher, is_deleted)
   - courses (id, slug, title_en, title_bn, class_level, math_category [general_math, higher_math_1st, higher_math_2nd, combined_higher_math, admission_engineering_math], course_type, fee, duration, is_deleted)
   - batches (id, course_id, branch_id, teacher_id, batch_name, class_days, start_time, end_time, total_seats, available_seats, admission_status, is_deleted)
   - leads (id, type, student_name, guardian_phone, whatsapp_number, class_level, course_id, batch_id, preferred_date, status, ip_address, is_deleted)
   - students (id, user_id, student_id_code, name, guardian_name, guardian_phone, class_level, school_college, photo, qr_code_token, qr_code_path, status, is_deleted)
   - enrollments (id, student_id, batch_id, enrollment_date, total_fee, paid_amount, due_amount, payment_status, status)
   - student_fees (id, student_id, fee_month, fee_amount, paid_amount, due_amount, status [due, partial, paid], paid_date, payment_method, receipt_no, collected_by, notes)
   - fee_records (id, enrollment_id, student_id, amount_paid, payment_method, transaction_reference, receipt_no, received_by, remarks, created_at)
   - attendances (id, student_id, batch_id, attendance_date, status [present, absent, late], recorded_by, sms_sent_status, sms_response, created_at)
   - batch_change_requests (id, student_id, current_batch_id, requested_batch_id, reason, guardian_phone, status, reviewed_by, admin_note, created_at)
   - demo_feedbacks (id, lead_id, rating, understanding_level, comments, ip_address, created_at)
   - notices (id, title_en, title_bn, category, description_en, description_bn, attachment_file, is_published, is_deleted)
   - results (id, exam_title_en, exam_title_bn, batch_id, class_level, file_path, is_deleted)
   - routines (id, batch_id, day_of_week, start_time, end_time, room_number, is_deleted)
   - faqs (id, question_en, question_bn, answer_en, answer_bn, sort_order, is_deleted)
   - testimonials (id, student_name, quote_en, quote_bn, rating, photo, is_deleted)
   - gallery (id, image_path, caption_en, caption_bn, category, is_deleted)
   - settings (setting_key, setting_value)
   - activity_logs (id, user_id, action, module, ip_address, created_at)

3. সিট ট্র্যাকিং ও কনকারেন্সি নীতি (Anti-Overbooking Row-Lock):
   - প্রতিটি টেবিলে `is_deleted TINYINT(1) DEFAULT 0` থাকবে
   - সিট সংরক্ষণের সময় `SELECT available_seats FROM batches WHERE id = :id FOR UPDATE` এবং `UPDATE batches SET available_seats = available_seats - 1 WHERE id = :id AND available_seats > 0` ট্রানজেকশন আবশ্যক।

4. database/seed.sql-এ ডেমো ডেটা:
   - ব্রাঞ্চ: Farmgate Main Campus (46/1, Britter Goli, Opposite Holy Cross College, Farmgate, Dhaka 1216)
   - প্রধান শিক্ষক: Al Amin Sir (Head Teacher & Founder)
   - কোর্স: SSC General Math, SSC Higher Math, HSC Higher Math 1st Paper, HSC Higher Math 2nd Paper, Engineering & Admission Math Special Care
   - ডিফল্ট সাইট সেটিংস ও সুপার অ্যাডমিন অ্যাকাউন্ট
```

---

### Phase 3: পাবলিক ওয়েবসাইটের লেআউট ও ইউআই কম্পোনেন্টস

#### 🎯 প্রম্পট:
```text
Phase 3: Public UI Layout & Core Header/Footer Components

architecture.md অনুযায়ী সাইটের গ্লোবাল লেআউট ও মোবাইল-ফার্স্ট উপাদানগুলো তৈরি করো।

কম্পোনেন্টস ও পেজ:
1. includes/header.php:
   - Dynamic Page Title, Meta Description, OpenGraph ট্যাগ
   - Google Fonts: Inter (Latin) ও Noto Sans Bengali (Bangla)
   - Bootstrap 5.3 CSS ও assets/css/main.css ইনক্লুড
   - Schema.org LocalBusiness JSON-LD স্ক্রিপ্ট

2. includes/navbar.php:
   - লোগো: Al Amin's Math Care
   - মেনু লিঙ্ক: Home, About, Courses, Batches, Teachers, Results, Notices, Contact
   - অ্যাকশন বাটন: [Admission] হাইলাইটেড CTA বাটন
   - ভাষা টগল বাটন: [EN | বাং] (এক ক্লিকে ভাষা পরিবর্তনের লিঙ্ক)

3. includes/mobile-nav.php (স্টিকি বটম বার):
   - Home | Courses | Notices | Call Now (01520102248) | Admission

4. includes/whatsapp-float.php:
   - স্ক্রিনের ডানদিকের নিচে সার্বক্ষণিক ভাসমান হোয়াটসঅ্যাপ বাটন (01520102248)
   - প্রি-ফিল্ড মেসেজ: "Assalamu Alaikum, I would like to know about admission at Al Amin's Math Care."

5. includes/footer.php:
   - ফার্মগেট প্রধান ক্যাম্পাসের পূর্ণ ঠিকানা ও ল্যান্ডমার্ক
   - হটলাইন নম্বর, জরুরি লিংক, সোশ্যাল লিঙ্ক ও কপিরাইট
   - Bootstrap 5.3 JS ও assets/js/main.js

6. index.php (হোমপেজ — ১২টি সেকশন):
   - Hero Section: গণিতে দুর্বলতা দূর করার শক্তিশালী হেডলাইন, ভর্তির হাইলাইট ও কল/হোয়াটসঅ্যাপ/ডেমো বাটন
   - Trust Indicators: আল আমিন স্যারের অভিজ্ঞতা ও সফল শিক্ষার্থীদের পরিসংখ্যান
   - Popular Math Courses: সাধারণ ও উচ্চতর গণিতের কোর্স কার্ড
   - Upcoming Batches: সিট স্ট্যাটাস (Available/Full) সহ লাইভ ব্যাচ তালিকা
   - Faculty: আল আমিন স্যার ও শিক্ষক প্যানেলের পরিচিতি
   - Recent Results, Testimonials, Notices, FAQ, Location (Google Map Embed) ও ফাইনাল ভর্তি CTA
```

---

### Phase 4: ম্যাথ কোর্স, ব্যাচ ও ভর্তি ফানেল পেজ

#### 🎯 প্রম্পট:
```text
Phase 4: Specialized Math Pages & Conversion Funnel

Al Amin's Math Care-এর গণিত-কেন্দ্রিক পেজগুলো এবং কনভার্সন ফানেল কোড করো:

১. courses.php (ম্যাথ কোর্স ফাইন্ডার):
   - ক্লাস ফিল্টার (Class 6-8, Class 9-10/SSC, HSC 1st/2nd Year, Admission)
   - ম্যাথ ক্যাটাগরি ফিল্টার (সাধারণ গণিত, উচ্চতর গণিত, মডেল টেস্ট)
   - প্রতিটি কার্ডে: কোর্সের নাম, ক্লাস, শিক্ষক, সময়সূচি, ফি, সিট স্ট্যাটাস ও [View Details] [Apply] বাটন

২. course-details.php:
   - সম্পূর্ণ ম্যাথ সিলেবাসের টপিক তালিকা
   - সংশ্লিষ্ট রানিং ও আপকামিং ব্যাচ
   - কোর্স নির্দিষ্ট এফএকিউ এবং কুইক ভর্তি এনকোয়ারি ফর্ম

৩. batches.php ও routine.php:
   - সকল ব্যাচের ক্লাস ডে (Sun, Tue, Thu / Sat, Mon, Wed), ক্লাসের সময় ও শিক্ষকের নাম
   - সাপ্তাহিক ক্লাস রুটিনের টেবিল ভিউ ও নোটিশ লিংক

৪. admission.php ও demo.php:
   - আল্ট্রা-ফাস্ট ভর্তি এনকোয়ারি ফর্ম (নাম, অভিভাবকের ফোন, ক্লাস, কাঙ্ক্ষিত কোর্স, হোয়াটসঅ্যাপ)
   - ফ্রি ডেমো ক্লাস বুকিং ফর্ম
   - সাবমিট করার পর ধন্যবাদ মেসেজ ও সরাসরি হোয়াটসঅ্যাপে কথা বলার লিংক

৫. results-lookup.php (ডায়নামিক স্কোরকার্ড ও মেরিট সার্চ):
   - রোল নম্বর এবং অভিভাবকের ফোন নম্বরের শেষ ৪ ডিজিট দিয়ে দ্বৈত ভেরিফিকেশন (Anti-Scraping Protection)
   - প্রাপ্ত নম্বর, মোট নম্বর, ব্যাচ মেরিট র‍্যাংক ও স্যারের মন্তব্য ডিসপ্লে
   - প্রতি মিনিটে সর্বোচ্চ ৫টি সার্চের আইপি রেট লিমিটিং

৬. verify-id.php (ডিজিটাল আইডি কার্ড QR ভেরিফিকেশন):
   - ক্রিপ্টোগ্রাফিক টোকেন (`?token=...`) যাচাই করে স্টুডেন্টের আসল ছবি, নাম, আইডি ও ব্যাচ স্ট্যাটাস ডিসপ্লে
```

---

### Phase 5: এপিআই ও ডায়নামিক লিড জেনারেশন ইঞ্জিন

#### 🎯 প্রম্পট:
```text
Phase 5: Secure API & Async Lead Processing Engine

ইউজার যাতে পেজ রিলোড ছাড়াই মাত্র ৩০-৬০ সেকেন্ডে ফর্ম সাবমিট করতে পারে, তার জন্য API ও ক্লায়েন্ট-সাইড জাভাস্ক্রিপ্ট তৈরি করো:

১. api/submit-lead.php ও api/submit-demo.php:
   - কেবল POST রিকোয়েস্ট গ্রহণ করা (অন্যথায় 405 Method Not Allowed)
   - CSRF টোকেন ভ্যালিডেশন
   - রেট লিমিটিং: একই আইপি থেকে ঘণ্টায় সর্বোচ্চ ১০টি রিকোয়েস্ট
   - ইনপুট স্যানিটাইজেশন ও বাংলাদেশি মোবাইল নম্বর ভ্যালিডেশন (01XXXXXXXXX)
   - স্প্যাম প্রতিরোধে Honeypot Field ভ্যালিডেশন
   - ডাটাবেজের `leads` টেবিলে PDO Prepared Statement দিয়ে ডাটা সেভ
   - SMS গেটওয়ে ট্রিগার (includes/sms.php): অভিভাবকের ফোনে ভর্তি/ডেমো কনফার্মেশন SMS প্রেরণ (দৈনিক কোটা ও ২৪ ঘণ্টার ফোন কুলডাউন সাপেক্ষে)
   - রেসপন্স: JSON `{success: true, message: "..."}`

২. api/submit-feedback.php:
   - ডেমো ক্লাস পরবর্তী ১-৫ স্টার রেটিং, ক্লাসের বোঝাপড়া ও কমেন্টস সংরক্ষণ

৩. api/submit-batch-change.php:
   - স্টুডেন্টের ব্যাচ পরিবর্তনের কারণ ও অনুরোধ `batch_change_requests` টেবিলে জমা করা

৪. assets/js/main.js:
   - ফর্ম সাবমিশনে Fetch API দিয়ে অ্যাসিঙ্ক কল
   - সাবমিট বাটনে লোডিং স্পিনার ও বাটন ডিজেবল রাখা যাতে ডাবল ক্লিক না হয়
   - সাবমিশন শেষে সুন্দর টোস্ট বা অ্যালার্ট নোটিফিকেশন প্রদর্শন
```

---

### Phase 6: সিকিউর অ্যাডমিন প্যানেল ও আরব্যাক (RBAC)

#### 🎯 প্রম্পট:
```text
Phase 6: Admin Dashboard & Role-Based Access Control

architecture.md-এর সেকশন ১৩ অনুযায়ী অ্যাডমিন প্যানেল তৈরি করো:

১. admin/login.php, forgot-password.php ও reset-password.php:
   - সেশন সিকিউরিটি ও CSRF প্রোটেকশন
   - ব্রুট ফোর্স প্রোটেকশন (৫ বার ভুল পাসওয়ার্ড দিলে উক্ত আইপি ১৫ মিনিটের জন্য লকআউট)
   - `password_verify()` দিয়ে bcrypt পাসওয়ার্ড চেক ও সেশন রিজেনারেশন (`session_regenerate_id(true)`)
   - পাসওয়ার্ড রিসেট: ক্রিপ্টোগ্রাফিক টোকেনের SHA-256 হ্যাশ ডাটাবেজে স্টোর, ১৫ মিনিটের মেয়াদ ও জেনেরিক নোটিফিকেশন

২. includes/auth.php ও includes/sms.php:
   - `require_admin()` এবং `require_role($roles)` হেল্পার গার্ড (BOLA / IDOR প্রতিরোধ)
   - SMS গেটওয়ে হেল্পার: বাংলা ও ইংরেজি SMS প্রেরণ, রেট-লিমিট ও গ্লোবাল দৈনিক কিল-সুইচ

৩. admin/dashboard.php:
   - মূল মেট্রিক্স: মোট নতুন লিড, ডেমো রিকোয়েস্ট, রানিং ব্যাচ, সিট পূর্ণতার হার, আজকের অনুপস্থিতি ও বকেয়া ফি
   - কুইক অ্যাকশন বাটন: নতুন ব্যাচ তৈরি, উপস্থিতি এন্ট্রি, ফি কালেকশন, রেজাল্ট আপলোড

৪. অ্যাডমিন সম্পূর্ণ CRUD ও স্পেশাল মডিউলসমূহ:
   - admin/courses/ (Add, Edit, Remove: কোর্স টাইটেল EN/BN, ফি, সিলেবাস)
   - admin/batches/ (Add, Edit, Remove: ব্যাচ শিডিউল, সিট সংখ্যা ও রো-লকিং)
   - admin/teachers/ (Add, Edit, Remove: শিক্ষক বায়ো ও ছবি)
   - admin/attendance/ (NEW: ব্যাচ অনুযায়ী দৈনিক উপস্থিতি এন্ট্রি ও অভিভাবকের ফোনে অনুপস্থিতি SMS অ্যালার্ট)
   - admin/fee-ledger/ (NEW: ফি কালেকশন, রসিদ নম্বর, ক্যাশ/বিকাশ লেজার ও বকেয়া ফিল্টারিং)
   - admin/student-cards/ (NEW: প্রিন্ট-রেডি ডিজিটাল স্টুডেন্ট আইডি কার্ড ও QR কোড জেনারেটর)
   - admin/batch-switch-requests/ (NEW: ব্যাচ পরিবর্তনের আবেদন অনুমোদন ও সিট এডজাস্ট)
   - admin/students/ (NEW: স্টুডেন্ট ও অভিভাবক তালিকা স্যানিটাইজড Excel/CSV ডাউনলোড বাটন — CSV Injection প্রটেক্টেড)
   - admin/notices/ & admin/results/ (PDF ও স্কোরকার্ড আপলোড)
   - admin/testimonials/ & admin/gallery/ (রিভিউ অনুমোদন ও ছবি আপলোড)
   - admin/leads/ (View, Filter, Status Change: New -> Contacted -> Admitted)
   - admin/settings/ (ফোন নম্বর, হোয়াটসঅ্যাপ, ঠিকানা ও SMS API Key কনফিগারেশন)

৫. সিকিউর রিমুভ ও ডাটা প্রোটেকশন:
   - প্রতিটি ডিলিট অ্যাকশনে Confirmation Modal প্রদর্শন
   - POST রিকোয়েস্টে CSRF টোকেন ভ্যালিডেশন
   - ফাইল আনলিংক (`unlink`) ও সফট ডিলিট পলিসি নিশ্চিত করা
```

---

### Phase 7: এসইও অপ্টিমাইজেশন ও প্রোডাকশন ডেপ্লয়মেন্ট চেক

#### 🎯 প্রম্পট:
```text
Phase 7: SEO Hardening, Domain Pointing & Deployment Audit

প্রোডাকশন সার্ভারে (`alaminmathcare.com`) সাইটটি লাইভ করার পূর্বে নিম্নলিখিত চেক ও ফাইল নিশ্চিত করো:

১. SEO & Crawlers:
   - robots.txt তৈরি (পাবলিক পেজ এলাও, /admin/, /config/, /api/ ডিসঅ্যালাও)
   - sitemap.xml তৈরি (হোম, কোর্স, ব্যাচ, টিচার্স, রেজাল্ট পেজের ইউআরএল সহ)
   - ফার্মগেট ও ঢাকার জন্য LocalBusiness Schema.org ডাটা চেক

২. Cloudflare & DNS Readiness:
   - A রেকর্ড (@ ও www) সার্ভার আইপির সাথে পয়েন্ট করা
   - SSL/TLS মোড "Full (Strict)" নির্ধারণ
   - অটোমেটিক HTTPS রিরাইট ও মিনিফিকেশন (HTML, CSS, JS)

৩. লিনাক্স ফাইল পারমিশন অডিট:
   - ফোল্ডারসমূহ: 755
   - ফাইলসমূহ: 644
   - assets/uploads/: 755 (PHP স্ক্রিপ্ট এক্সিকিউশন বন্ধ নিশ্চিত করা)
   - config/database.php: 640

৪. সিকিউরিটি অডিট:
   - কোনো পেজে সরাসরি ডাটাবেজ এরর ডিসপ্লে হচ্ছে কিনা চেক করা (প্রোডাকশনে display_errors=0)
   - প্রতিটি ফর্মে CSRF ও PDO প্যারামিটার বাইন্ডিং নিশ্চিত করা
```

---

## 4. দ্রুত ব্যবহারের জন্য সিঙ্গেল মাস্টার প্রম্পট (All-in-One Master Prompt)

যদি আপনি পুরো প্রজেক্টটি কোনো AI মডেল বা এজেন্টকে একবারে নির্দেশ করতে চান, তবে নিচের মাস্টার প্রম্পটটি কপি করে ব্যবহার করতে পারেন:

```markdown
### 🚀 Al Amin's Math Care — All-in-One Execution Master Prompt

তুমি "Al Amin's Math Care" (alaminmathcare.com) প্রজেক্টের জন্য আর্কিটেকচার কমপ্লায়েন্ট পিএইচপি ওয়েব অ্যাপ্লিকেশন তৈরি করবে।

**প্রজেক্ট বিবরণ:**
- ব্র্যান্ড: Al Amin's Math Care
- অবস্থান: ৪৬/১, বৃত্তের গলি, হোলি ক্রস কলেজের বিপরীতে, ফার্মগেট, ঢাকা ১২১৬
- মোবাইল/হোয়াটসঅ্যাপ: 01520102248
- কোর্স: শুধুমাত্র গণিত (SSC General/Higher Math, HSC Higher Math 1st/2nd Paper, Admission Math)
- ভাষা: ডিফল্ট ইংরেজি, সাথে তাৎক্ষণিক বাংলা টগল বাটন
- স্ট্যাক: PHP 8.x + MySQL 8.x + Bootstrap 5.3 + Vanilla JS + Apache cPanel

**সিকিউরিটি ও আর্কিটেকচারাল নিয়মাবলী:**
1. **দ্বিভাষিক এক্সপেরিয়েন্স (English & Bangla):** ইউজাররা সাইটের প্রতিটি পেজ ইংরেজি ও বাংলা উভয় ভাষায় দেখতে পাবেন। স্ট্যাটিক টেক্সট `lang/` ডিকশনারি এবং ডাইনামিক কন্টেন্ট (কোর্স, নোটিশ, টিচার, রেজাল্ট) ডাটাবেজের `_en` ও `_bn` কলাম থেকে ইউজারের ভাষা পছন্দ অনুযায়ী স্বয়ংক্রিয়ভাবে রেন্ডার হবে।
2. **অ্যাডমিন প্যানেলে সম্পূর্ণ নিয়ন্ত্রণ (Add, Edit, Remove Everything):** অ্যাডমিন প্যানেল থেকে ওয়েবসাইটের প্রতিটি উপাদান (কোর্স, ব্যাচ, শিক্ষক, নোটিশ, রেজাল্ট, রুটিন, গ্যালারি, রিভিউ ও সেটিংস) সহজে **নতুন যোগ (Add), পরিবর্তন (Edit) এবং মুছে ফেলা (Remove/Delete)** করার ফর্ম ও টেবিল থাকবে। সাথে থাকবে দৈনিক ক্লাস উপস্থিতি ও অভিভাবক SMS ট্রিগার, ফি লেজার ও মানি রিসিট, ডিজিটাল আইডি কার্ড ও QR কোড জেনারেটর এবং স্যানিটাইজড Excel/CSV ডাউনলোড।
3. **ডেটাবেজ ও কোড সিকিউরিটি:** কোনো অবস্থাতেই SQL কনক্যাটেনশন ব্যবহার করবে না; সর্বত্র PDO Prepared Statements আবশ্যক। সিট বুকিংয়ে Atomic Row-Locking (`FOR UPDATE`) নিশ্চিত করবে। ভিউ ফাইলে ডাটা প্রিন্ট করতে `e()` বা `htmlspecialchars()` ব্যবহার করবে। পাসওয়ার্ড রিসেটে SHA-256 টোকেন হ্যাশিং এবং এক্সেল এক্সপোর্টে ফর্মুলা ইনজেকশন স্যানিটাইজার ব্যবহার করবে।
4. **CSRF ও রিমুভ সিকিউরিটি:** প্রতিটি POST সাবমিশন ও ডিলিট অপারেশনে ক্রিপ্টোগ্রাফিক CSRF টোকেন ভ্যালিডেশন এবং কনফার্মেশন পপআপ থাকবে।
5. **সার্ভার হার্ডেনিং (.htaccess):** ডিরেক্টরি ব্রাউজিং বন্ধ, HTTPS ফোর্স, সেন্সিটিভ ফাইল (.env, config, database) ব্লক এবং assets/uploads-এ কোনো PHP এক্সিকিউট হতে না দেওয়া।
6. **মোবাইল-ফার্স্ট ফানেল ও SMS নোটিফিকেশন:** মোবাইল স্টিকি বটম বার, ওয়ান-ক্লিক কল/হোয়াটসঅ্যাপ এবং ৩০-৬০ সেকেন্ডে ভর্তি/ডেমো আবেদন সাবমিশন ও অভিভাবকের ফোনে অটোমেটেড SMS কনফার্মেশন নিশ্চিত করবে।

এখন [architecture.md](./architecture.md) ও [prd.md](./prd.md) অনুসারে ধাপে ধাপে সম্পূর্ণ ফাইল কাঠামো এবং কার্যকরী কোড তৈরি শুরু করো।
```

---
*ডকুমেন্টটি সফলভাবে সংরক্ষিত: prompt.md — Al Amin's Math Care*
