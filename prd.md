# Al Amin's Math Care — Product Requirement Document (PRD)

**Document Version:** 1.1.0 (Enhanced Edition with New Features & Issue Fixes)  
**Status:** Approved / Ready for Execution  
**Target Production Domain:** [alaminmathcare.com](https://alaminmathcare.com)  
**Target Release:** Q4 2026  
**Author & Engineering Lead:** Senior Product Manager & Fullstack Software Architect  
**Stakeholders:** Al Amin Sir (Head Teacher & Founder), Academic Management, IT & Engineering Team  
**Primary Technology Stack:** PHP 8.x + MySQL 8.x + Bootstrap 5.3 + Vanilla JavaScript + Linux cPanel (Apache)  

---

## 📋 Table of Contents (সূচিপত্র)

1. [Executive Summary & Product Vision (সারসংক্ষেপ ও ভিশন)](#1-executive-summary--product-vision)
2. [Problem Statement & Market Opportunity (সমস্যা ও সুযোগ)](#2-problem-statement--market-opportunity)
3. [Target Audience & User Personas (টার্গেট অডিয়েন্স ও পারসোনা)](#3-target-audience--user-personas)
4. [Key Value Propositions & Core Principles (কোর ভ্যালু ও মূলনীতি)](#4-key-value-propositions--core-principles)
5. [Business Objectives & Success Metrics (KPIs)](#5-business-objectives--success-metrics-kpis)
6. [Scope of Work (প্রজেক্টের পরিধি - v1.1.0 Updated)](#6-scope-of-work)
7. [Functional Requirements & Feature Specifications (কার্যকরী বৈশিষ্ট্যসমূহ)](#7-functional-requirements--feature-specifications)
    * [Module 1: Bilingual Localization Engine (English & Bangla)](#module-1-bilingual-localization-engine)
    * [Module 2: High-Conversion Public Web Portal & Interactive Features](#module-2-high-conversion-public-web-portal)
    * [Module 3: Math Course, Batch Engine & Anti-Overbooking Seat Lock](#module-3-math-course--batch-discovery-engine)
    * [Module 4: Dynamic Lead Generation & Automated SMS Intake Engine](#module-4-dynamic-lead-generation--admission-intake-engine)
    * [Module 5: Student Scorecard & Merit Result Lookup Portal](#module-5-student-scorecard--result-lookup-portal)
    * [Module 6: Attendance Tracking & Automated Guardian SMS Gateway](#module-6-attendance-tracking--automated-guardian-sms)
    * [Module 7: Admin Dashboard, CMS & Fee Ledger System](#module-7-admin-dashboard--cms)
    * [Module 8: Security, Password Reset Workflow, RBAC & Audit Logs](#module-8-security-rbac--audit-logs)
8. [Non-Functional Requirements (NFRs)](#8-non-functional-requirements-nfrs)
    * [8.1 Security & Defensive Architecture](#81-security--defensive-architecture)
    * [8.2 Performance, Responsiveness & Concurrency](#82-performance--responsiveness)
    * [8.3 Usability, UX & Accessibility](#83-usability-ux--accessibility)
    * [8.4 Search Engine Optimization (SEO) & OpenGraph](#84-search-engine-optimization-seo--opengraph)
    * [8.5 Hosting, Cloud Storage & Data Integrity](#85-hosting-deployment--data-integrity)
9. [Information Architecture & Site Map (সাইটম্যাপ)](#9-information-architecture--site-map)
10. [Data Architecture & Entity Overview (২১টি ডাটাবেজ টেবিল)](#10-data-architecture--entity-overview)
11. [Assumptions, Risks & Mitigation Strategies (ঝুঁকি ও প্রতিকার)](#11-assumptions-risks--mitigation-strategies)
12. [Phased Release Roadmap & Definition of Done (DoD)](#12-phased-release-roadmap--definition-of-done)

---

## 1. Executive Summary & Product Vision

### 1.1 Executive Summary
**Al Amin's Math Care** হলো রাজধানী ঢাকার প্রাণকেন্দ্র ফার্মগেটে (বৃত্তের গলি, হোলি ক্রস কলেজের বিপরীতে) অবস্থিত একটি বিশেষায়িত একক-বিষয়ক গণিত শিক্ষা প্রতিষ্ঠান। প্রতিষ্ঠাতা ও প্রধান শিক্ষক **আল আমিন স্যার**-এর প্রত্যক্ষ তত্ত্বাবধানে দীর্ঘ এক দশকেরও বেশি সময় ধরে মাধ্যমিক (SSC), উচ্চমাধ্যমিক (HSC) এবং বিশ্ববিদ্যালয়/ইঞ্জিনিয়ারিং ভর্তিচ্ছু শিক্ষার্থীদের অত্যন্ত যত্নসহকারে গণিত পাঠদান করা হয়।

এই পিআরডি (v1.1.0)-এর উদ্দেশ্য হলো প্রতিষ্ঠানের জন্য একটি উচ্চক্ষমতাসম্পন্ন, মোবাইল-ফার্স্ট, সাইবার-সুরক্ষিত এবং দ্বিভাষিক (English ও Bangla) ওয়েব প্ল্যাটফর্ম তৈরি করা। প্ল্যাটফর্মটি একাধারে একটি নির্ভরযোগ্য ডিজিটাল ক্যাম্পাস, স্মার্ট ভর্তি ফানেল, লাইভ ব্যাচ ও সিট ট্র্যাকিং সিস্টেম, অভিভাবক অটোমেটেড SMS নোটিফিকেশন, রোলভিত্তিক রেজাল্ট লুকআপ, ডিজিটাল আইডি কার্ড এবং সম্পূর্ণ ডায়নামিক অ্যাডমিন ফি-লেজার ও সিএমএস হিসেবে কাজ করবে।

### 1.2 Product Vision
*"To establish Al Amin's Math Care as the most accessible, transparent, and trusted mathematics coaching platform in Bangladesh by delivering a hyper-fast, conversion-optimized, and secure digital portal that eliminates math phobia and empowers students and parents with instant admission, attendance transparency, and verified merit tracking."*

---

## 2. Problem Statement & Market Opportunity

### 2.1 The Problem
1. **Mathematics Phobia & Lack of Specialized Care:** সাধারণ কোচিং সেন্টারগুলোতে একসাথে অনেক বিষয় পড়ানো হয়, ফলে জটিল গণিত ও উচ্চতর গণিতের মৌলিক ভিত্তি দুর্বল থেকে যায়।
2. **Opaque Batch Timing & Seat Unavailability:** ফার্মগেটের ব্যস্ততম স্থানে শিক্ষার্থীরা ব্যাচের সময়সূচি এবং সিটের অবস্থা জানতে সশরীরে এসে ভিড় করতে বাধ্য হয়। Overbooking বা শেষ মূহূর্তে সিট না পাওয়ার ঝুঁকি থাকে।
3. **Lack of Parent Attendance & Fee Transparency:** অভিভাবকরা সন্তানের দৈনন্দিন ক্লাসে উপস্থিতি বা বকেয়া ফি সম্পর্কে সময়মতো জানতে পারেন না।
4. **Manual Record Lookup Friction:** ফলাফল প্রকাশের পর শুধু PDF ফাইলে বিশাল তালিকা দেখে নিজস্ব রোল/প্রাপ্ত নম্বর খুঁজে পেতে সমস্যা হয়।
5. **Slow Password Recovery & Administrative Gaps:** স্টাফ বা শিক্ষকদের পাসওয়ার্ড ভুলে গেলেম্যানুয়াল ডাটাবেজ রিসেটের ওপর নির্ভর করতে হতো।

### 2.2 Market Opportunity
ফার্মগেট শিক্ষাজোনে অবস্থিত হোলি ক্রস কলেজ, নটর ডেম কলেজ, সরকারি বিজ্ঞান কলেজসহ ঢাকার শীর্ষ শিক্ষাপ্রতিষ্ঠানের হাজার হাজার শিক্ষার্থী গণিত বিষয়ে স্পেশালাইজড কেয়ার চায়। ৩০ সেকেন্ডে ডেমো ক্লাস বুকিং, রিয়েল-টাইম অটোমিক সিট কাউন্ট, অভিভাবক SMS নোটিফিকেশন এবং ডিজিটাল স্টুডেন্ট আইডি কার্ড সংযোগ প্রতিষ্ঠানের শিক্ষার্থী ভর্তির হার ও প্রাতিষ্ঠানিক গ্রহণযোগ্যতা বহুগুণ বাড়িয়ে দেবে।

---

## 3. Target Audience & User Personas

| Persona | Name & Profile | Primary Needs & Pain Points | Platform Behavior |
| --- | --- | --- | --- |
| **Persona 1: SSC / HSC Student** | **তানভীর (১৭ বছর)**, হোলি ক্রস/বিজ্ঞান কলেজের শিক্ষার্থী। | সঠিক ব্যাচ শিডিউল জানা, ডেমো ক্লাস বুকিং, নিজস্ব রোল নম্বর দিয়ে রেজাল্ট ও ক্লাস রুটিন দেখা। | স্মার্টফোন থেকে ব্রাউজ করে, মোবাইল বটম বারের মাধ্যমে সরাসরি Demo Class বুক করে এবং রেজাল্ট লুকআপ ব্যবহার করে। |
| **Persona 2: Concerned Guardian** | **মিসেস রোকেয়া (৪৪ বছর)**, অভিভাবক। | শিক্ষক আল আমিন স্যারের যোগ্যতা যাচাই, সন্তানের দৈনিক উপস্থিতি SMS-এ পাওয়া, ফি পরিশোধের স্বচ্ছ হিসেব রাখা। | মোবাইল SMS প্রাপ্তি, "Call Now" বাটন ট্যাপ করা এবং ডিজিটাল পেমেন্ট স্লিপ যাচাই। |
| **Persona 3: Admission Candidate** | **ফারহান (১৯ বছর)**, বুয়েট/ইঞ্জিনিয়ারিং ও ঢাবি ভর্তিচ্ছু। | ইঞ্জিনিয়ারিং গণিতের শর্টকাট টেকনিক, স্পেশাল ব্যাচের সিট স্ট্যাটাস এবং অনলাইন মেরিট পজিশন দেখা। | ইঞ্জিনিয়ারিং ব্যাচের সিট নিশ্চিত করে ভর্তি ফর্ম পূরণ এবং রোলভিত্তিক স্কোরের অবস্থান যাচাই করে। |
| **Persona 4: Head Teacher (Founder)** | **আল আমিন স্যার**, প্রতিষ্ঠাতা ও প্রধান গণিত শিক্ষক। | লিড পর্যবেক্ষণ, ব্যাচ সিট পূর্ণতা দেখা, নোটিশ ও মডেল টেস্ট রেজাল্ট প্রকাশ করা। | অ্যাডমিন ড্যাশবোর্ড থেকে নোটিশ, কোর্স ফি ও নতুন ব্যাচ আপডেট করেন এবং ক্লাসের অ্যাটেনডেন্স অনুমোদন করেন। |
| **Persona 5: Front Desk / Receptionist** | **অফিস অ্যাসিস্ট্যান্ট / রিসেপশন স্টাফ** | ভর্তি আবেদন যাচাই, ক্যাশ/ম্যানুয়াল ফি এন্ট্রি দেওয়া, শিক্ষার্থীদের ডিজিটাল আইডি কার্ড তৈরি এবং ব্যাচ চেঞ্জ রিকোয়েস্ট প্রসেস করা। | লিড সিআরএম, ফি লেজার ও অ্যাটেনডেন্স মডিউলে কাজ করেন। |

---

## 4. Key Value Propositions & Core Principles

```
           ┌──────────────────────────────────────────────┐
           │      Al Amin's Math Care Core Pillars        │
           └──────────────────────┬───────────────────────┘
                                  │
    ┌─────────────────┬───────────┴───────────┬─────────────────┐
    ▼                 ▼                       ▼                 ▼
Mathematics     Bilingual First         Live Seat Lock      Parent Care & SMS
Specialization   (EN & BN Toggle)        (Anti-Overbooking) (Automated Alerts & Fee)
```

1. **Pure Mathematics Specialization:** সম্পূর্ণ প্ল্যাটফর্মে শুধুমাত্র সাধারণ গণিত, উচ্চতর গণিত এবং ইঞ্জিনিয়ারিং ভর্তি গণিত সংক্রান্ত ফিচার থাকবে।
2. **Instant Bilingual Experience (EN & BN Toggle):** ডিফল্ট আন্তর্জাতিক মানের ইংরেজি ইন্টারফেস, ১-ক্লিকে সম্পূর্ণ সাইট শুদ্ধ বাংলায় রূপান্তরিত হবে।
3. **Atomic Live Seat Transparency & Anti-Overbooking:** ডাটাবেজ লেভেলে Row Locking ব্যবহার করে সিট কাউন্ট নিশ্চিত করা হবে যেন শেষ সিটটিতে কোনো Overbooking না ঘটে।
4. **Parent-Centric Automation (SMS & Fee Ledger):** শিক্ষার্থী অনুপস্থিত থাকলে অভিভাবকের ফোনে স্বয়ংক্রিয় SMS এবং ক্যাশ/ম্যানুয়াল ফি জমার স্বচ্ছ লেজার হিসেব।
5. **Zero-Trust Defensive Security:** PDO Prepared Statements, XSS Escaping, CSRF Token, Secure Password Reset Tokens এবং Strict Upload Protection.

---

## 5. Business Objectives & Success Metrics (KPIs)

| Objective | Key Metric (KPI) | Target Benchmark | Tracking Method |
| --- | --- | --- | --- |
| **Lead Generation** | ভর্তি ও ডেমো ক্লাসের অনলাইন আবেদন সংখ্যা | প্রতি মাসে ২৫০+ ভেরিফায়েড লিড | Admin Lead CRM & Database Logs |
| **Conversion Rate** | ভিজিটর থেকে লিড কনভার্সন হার | ৬% – ৯% সামগ্রিক ট্রাফিকের | Google Analytics / Server Event Tracking |
| **Instant Inquiries** | ১-ক্লিক হোয়াটসঅ্যাপ ও ডিরেক্ট কল সংযোগ | প্রতি সপ্তাহে ২০০+ সরাসরি যোগাযোগ | Click-to-Call / WhatsApp Click Tracking |
| **Guardian SMS Delivery** | উপস্থিতিও নোটিফিকেশন SMS ডেলিভারি | ৯৯%+ সফল ডেলিভারি রেট | SMS Gateway API Delivery Reports |
| **Mobile Performance** | পেজ লোড স্পিড ও পারফরম্যান্স স্কোর | < ১.৫ সেকেন্ড লোড টাইম; লাইটহাউজ স্কোর ৯৫+ | Google PageSpeed Insights & Web Vitals |
| **Platform Uptime** | ওয়েবসাইটের প্রাপ্যতা ও নিরাপত্তা | ৯৯.৯% আপটাইম, ০ নিরাপত্তা লঙ্ঘন | Server Uptime Monitor & Security Audit |

---

## 6. Scope of Work (v1.1.0)

### 6.1 In-Scope (প্রজেক্টের অন্তর্ভুক্ত)
* [x] সম্পূর্ণ রেসপন্সিভ মোবাইল-ফার্স্ট পাবলিক পোর্টাল (Home, About, Courses, Batches, Teachers, Results, Notices, Contact)।
* [x] দ্বিভাষিক (English ও Bangla) ল্যাঙ্গুয়েজ সুইচ টগল (কুকি ও ফলব্যাক সহ)।
* [x] গণিত কোর্স ফিল্টারিং (Class 8, 9, 10, 11, 12, Admission Engineering Math)।
* [x] লাইভ সিট স্ট্যাটাস ও অটোমিক ডাটাবেজ রো-লকিং (Anti-Overbooking Protection)।
* [x] উচ্চ-রূপান্তর ক্ষমতাসম্পন্ন ডেমো ক্লাস ও ভর্তি আবেদন ফর্ম (CSRF, Honeypot ও রেট লিমিটিং সহ)।
* [x] **[NEW]** ডেমো ক্লাস পরবর্তী ফিডব্যাক ফর্ম (Post-Demo Feedback Loop)।
* [x] **[NEW]** শিক্ষার্থী ও অভিভাবকের জন্য রোল/ফোন নম্বর ভিত্তিক স্কোরকার্ড ও রেজাল্ট লুকআপ পোর্টাল।
* [x] **[NEW]** ব্যাচ পরিবর্তন অনলাইন রিকোয়েস্ট (Batch Switch Request Module)।
* [x] **[NEW]** শিক্ষার্থীদের জন্য ডিজিটাল আইডি কার্ড ও QR কোড জেনারেটর engine.
* [x] **[NEW]** দৈনিক ক্লাস উপস্থিতি (Attendance Tracker) ও অভিভাবকের মোবাইলে অটোমেটেড SMS নোটিফিকেশন।
* [x] **[NEW]** ম্যানুয়াল/ক্যাশ ফি ম্যানেজমেন্ট সিস্টেম (Fee Ledger) ও ডু পেমেন্ট ট্র্যাকিং।
* [x] **[NEW]** সুরক্ষিত পাসওয়ার্ড রিকভারি/রিসেট ওয়ার্কফ্লো (Timed Token-based Reset Workflow)।
* [x] সম্পূর্ণ অ্যাডমিন কন্ট্রোল প্যানেল (Full CRUD: কোর্স, ব্যাচ, শিক্ষক, নোটিশ, রেজাল্ট, টেস্টমোনিয়াল, গ্যালারি, ফি লেজার, অ্যাটেনডেন্স ও সেটিংস)।
* [x] লিড ম্যানেজমেন্ট সিআরএম (Status: new, contacted, demo_scheduled, enrolled, cancelled)।
* [x] ভূমিকাভিত্তিক ব্যবহারকারী নিয়ন্ত্রণ (RBAC: Super Admin, Admin, Teacher, Receptionist)।
* [x] অডিট ও অ্যাক্টিভিটি লগ (Admin Audit Trail)।
* [x] সিকিউর আপলোড ও ক্লাউড অবজেক্ট স্টোরেজ পলিসি (cPanel Disk Limit Mitigation)।

### 6.2 Out-of-Scope (পরবর্তী ধাপের জন্য সংরক্ষিত)
* ❌ ইন-অ্যাপ লাইভ ভিডিও ক্লাস স্ট্রিমিং (ক্লাস অফলাইনে ফার্মগেট ক্যাম্পাসে অনুষ্ঠিত হয়)।
* ❌ স্বয়ংক্রিয় পেমেন্ট গেটওয়ে (bKash/Nagad PGW Direct API) — ফেজ ২-এ যুক্ত হবে (বর্তমানে ম্যানুয়াল ফি ট্র্যাকিং যুক্ত করা হয়েছে)।
* ❌ ফুল-স্কেল অনলাইন সিবিটি পরীক্ষা।

---

## 7. Functional Requirements & Feature Specifications

### Module 1: Bilingual Localization Engine (English & Bangla)
1. **Default Language:** ইংরেজি (EN)।
2. **Instant Switcher:** হেডারে 'EN | বাংলা' টগল বাটন।
3. **Cookie Persistence:** `site_lang` সিকিউর কুকি (৩০ দিন)।
4. **Dual Data Columns:** `title_en`, `title_bn`, `description_en`, `description_bn` ডাটাবেজে।
5. **Smart Fallback:** বাংলা কন্টেন্ট না থাকলে স্বয়ংক্রিয়ভাবে ইংরেজি প্রদর্শিত হবে।

### Module 2: High-Conversion Public Web Portal & Interactive Features
1. **Header & Navigation:** ফার্মগেট ক্যাম্পাসের হটলাইন (01520102248, 01521255850), "Book Demo Class" অ্যাকসেন্ট বাটন, এবং ল্যাঙ্গুয়েজ সুইচ।
2. **Hero Section:** আল আমিন স্যারের গাইডে গণিত ভীতির সমাধান হেডলাইন, ১০+ বছরের অভিজ্ঞতা, ১০০০+ সফল শিক্ষার্থীর সোশ্যাল প্রুফ এবং Dual CTA (Demo Book & Batches)।
3. **Math Categories:** Class 8–10 General Math, Class 9–10 Higher Math, HSC 1st & 2nd Paper, Admission Engineering Math.
4. **Faculty Profile:** আল আমিন স্যারের প্রোফাইল, শিক্ষা, কনসেপ্ট ক্লিয়ারেন্সের বিশেষ পদ্ধতি ও মোটিভেশনাল মেসেজ।
5. **Sticky Mobile Conversion Bar:** 📞 Call Now, 💬 WhatsApp (প্রি-ফিল্ড মেসেজ), 📝 Apply / Demo.
6. **[NEW] Post-Demo Feedback Loop:** ডেমো ক্লাস করার পর শিক্ষার্থীরা ১-ক্লিকে স্যারের ক্লাস কেমন লেগেছে সে বিষয়ে রেটিং (১-৫ স্টার) ও মতামত দিতে পারবে।
7. **[NEW] Batch Change Request Form:** নিবন্ধিত শিক্ষার্থীরা তাদের সময়সূচি পরিবর্তনের প্রয়োজনীয়তা জানিয়ে অনলাইনে আবেদন জমা দিতে পারবে।

### Module 3: Math Course, Batch Engine & Anti-Overbooking Seat Lock
1. **Course Details Page:** ক্লিন ইউআরএল (যেমন: `/courses/hsc-higher-math-1st-paper`), সিলেবাস, ক্লাসের দিন ও ফি।
2. **Batch Grid & Live Seat Badges:** Available (সবুজ), Few Seats Left (হলুদ - ১-৫ সিট), Batch Full (লাল), Admission Closed (ধূসর)।
3. **[NEW] Concurrency Protection (Anti-Overbooking):** 
   - ভর্তি সাবমিশনের সময় MySQL-এ Atomic Update অথবা `SELECT FOR UPDATE` রো-লকিং ব্যবহৃত হবে:
     `UPDATE batches SET available_seats = available_seats - 1 WHERE id = :batch_id AND available_seats > 0;`
   - সিট সংখ্যা ০ হলে মুহূর্তের মধ্যে ইউজারকে ইনফর্ম করা হবে এবং আবেদনটি ওয়েটিং লিস্ট বা বিকল্প ব্যাচে স্থানান্তরের সুযোগ দেওয়া হবে।

### Module 4: Dynamic Lead Generation & Admission Intake Engine
1. **Form Fields:** শিক্ষার্থীর নাম, অভিভাবকের ফোন নম্বর (১১ ডিজিট ভ্যালিডেশন), হোয়াটসঅ্যাপ নম্বর, ক্লাস লেভেল, কোর্স ও ব্যাচ সিলেক্ট, আবেদন টাইপ (Admission/Demo)।
2. **Security Controls:** Hidden CSRF Token, Honeypot Anti-Bot Field, IP Rate Limiting (৫ মিনিটে সর্বোচ্চ ৩টি আবেদন)।
3. **[NEW] Instant SMS Confirmation Trigger:** ভর্তি বা ডেমো ফর্ম সাবমিট হওয়ার সাথে সাথে অভিভাবকের ফোনে একটি প্রফেশনাল কনফার্মেশন SMS চলে যাবে (যেমন: *"Al Amin's Math Care: Dear Guardian, we have received your demo request for [Student Name]. Venue: Farmgate Campus. Hotline: 01520102248"*).

### Module 5: Student Scorecard & Merit Result Lookup Portal
1. **[NEW] Dynamic Scorecard Search (`/results/lookup`):**
   - শিক্ষার্থী বা অভিভাবক তাদের রোল নম্বর (Student Roll / Phone) এবং ড্রপডাউন থেকে পরীক্ষার নাম সিলেক্ট করে সার্চ করতে পারবেন।
   - সার্চ রেজাল্টে প্রাপ্ত নম্বর, মোট নম্বর, ব্যাচ র‍্যাংক/মেরিট পজিশন এবং স্যারের মন্তব্য সুন্দর ডিজিটাল স্কোরকার্ড কার্ড হিসেবে ডিসপ্লে হবে।
2. **Bulk Results PDF Download:** পূর্বের ন্যায় পুরো ব্যাচের রেজাল্ট শিট PDF আকারে ডাউনলোডের সুবিধাও থাকবে।

### Module 6: Attendance Tracking & Automated Guardian SMS
1. **[NEW] Classroom Attendance Entry (`/admin/attendance`):**
   - শিক্ষক বা রিসেপশনিস্ট ব্যাচ অনুযায়ী প্রতিদিনের ক্লাসের তালিকা খুলে Present, Absent, বা Late চেক করতে পারবেন।
2. **[NEW] Automated Absence Alert SMS:**
   - কোনো শিক্ষার্থী ক্লাসে অনুপস্থিত (Absent) হিসেবে মার্ক হলে, বোতামে এক ক্লিকে বা স্বয়ংক্রিয়ভাবে অভিভাবকের মোবাইলে SMS চলে যাবে:
     *"Al Amin's Math Care: Respected Guardian, [Student Name] was ABSENT in today's Higher Math batch at Farmgate Campus on [Date]. Info: 01520102248"*

### Module 7: Admin Dashboard, CMS & Fee Ledger System
1. **Lead Pipeline CRM:** New ➔ Contacted ➔ Demo Scheduled ➔ Enrolled ➔ Cancelled.
2. **[NEW] Fee Ledger & Payment Management (`/admin/fee-ledger`):**
   - রিসেপশনিস্ট বা অ্যাডমিন ভর্তি হওয়া শিক্ষার্থীর মোট কোর্স ফি, প্রাপ্ত ফি (Paid Amount) এবং বকেয়া (Due Amount) এন্ট্রি করতে পারবেন।
   - ম্যানুয়াল ক্যাশ বা বিকাশ/নগদ পেমেন্টের ক্যাশ রসিদ জেনারেট করা এবং বকেয়া ফি পরিশোধের ডিজিটাল স্ট্যাটাস (`unpaid`, `partially_paid`, `paid`) ট্র্যাকিং।
3. **[NEW] Digital Student ID Card & QR Generator:**
   - শিক্ষার্থী ভর্তি সম্পন্ন হলে অ্যাডমিন ড্যাশবোর্ড থেকে ১-ক্লিকে ছবি ও প্রিন্ট-রেডি QR কোড সম্বলিত স্টুডেন্ট আইডি কার্ড জেনারেট করা যাবে, যা দিয়ে স্ক্যান করে স্টুডেন্ট প্রোফাইল ভেরিফাই করা সম্ভব (`/verify-id?code=STU1029`)।
4. **Content CRUD:** কোর্স, ব্যাচ, শিক্ষক, নোটিশ, টেস্টমোনিয়াল, ফটো গ্যালারি ও গ্লোবাল সাইট সেটিংস পরিবর্তন।

### Module 8: Security, Password Reset Workflow, RBAC & Audit Logs
1. **Role-Based Access Control (RBAC):**
   - **Super Admin:** সমস্ত এক্সেস, ইউজার তৈরি, ডাটাবেজ ও ব্যাকআপ।
   - **Admin:** কন্টেন্ট ও লিড ম্যানেজমেন্ট।
   - **Teacher:** নিজ ব্যাচের রুটিন, অ্যাটেনডেন্স ও মার্কস এন্ট্রি।
   - **Receptionist:** লিড ট্র্যাকিং, ফি জমার রসিদ ও অ্যাটেনডেন্স এন্ট্রি (কোর্স ও ফি স্ট্রাকচার এডিট ব্লকড)।
2. **[NEW] Secure Password Recovery Workflow:**
   - অ্যাডমিন/স্টাফ পাসওয়ার্ড ভুলে গেলে "Forgot Password" লিংকে ইমেইল দিলে সিস্টেমে ১ ঘণ্টার মেয়াদী সিকিউর ক্রিপ্টোগ্রাফিক রিসেট টোকেন জেনারেট হবে এবং রিসেট লিংক প্রেরিত হবে।
3. **Audit Trail Log:** কে, কখন, কোন আইপি থেকে সিস্টেমে কী পরিবর্তন করেছে তা `activity_logs` টেবিলে অটোমেটিক রেকর্ড হবে।

---

## 8. Non-Functional Requirements (NFRs)

### 8.1 Security & Defensive Architecture
1. **Anti-SQL Injection:** ১০০% PDO Prepared Statements এবং প্যারামিটার বাইন্ডিং।
2. **Anti-XSS:** ভিউ ফাইলে `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')` প্রয়োগ।
3. **CSRF Mitigation:** টাইমড সেশন সিএসআরএফ টোকেন।
4. **Brute-Force Guard:** `password_hash()` BCRYPT (Cost 12) এবং ৫ বার ভুল পাসওয়ার্ডে ১৫ মিনিটের একাউন্ট লকআউট।
5. **Upload Protection:** `assets/uploads/` ডিরেক্টরিতে `.htaccess` দিয়ে সকল প্রকার Script Execution Blocked।

### 8.2 Performance, Responsiveness & Concurrency
1. **Load Speed:** ৩জি/৪জি মোবাইলে < ১.৫ সেকেন্ড লোড টাইম।
2. **Lighthouse Score:** Performance ≥ 90, Accessibility ≥ 95, Best Practices ≥ 95, SEO ≥ 95.
3. **Database Concurrency:** ব্যাচ সিটের জন্য Row Locking (`FOR UPDATE`) যাতে একাধিক রিকোয়েস্টে ওভারবুকিং না ঘটে।

### 8.3 Usability, UX & Accessibility
1. **Mobile-First Priority:** Touch Target ন্যূনতম ৪৮x ৪৮ পিক্সেল।
2. **Typography:** ইংরেজি (Inter/Roboto), বাংলা (Hind Siliguri / Noto Sans Bengali)।
3. **Contrast:** WCAG 2.1 AA মানদণ্ড (ন্যূনতম ৪.৫:১)।

### 8.4 Search Engine Optimization (SEO) & OpenGraph
1. **Clean URLs:** Apache `mod_rewrite` দিয়ে `.php` এক্সটেনশনবিহীন ক্লিন ইউআরএল।
2. **Dynamic OpenGraph:** সোশ্যাল মিডিয়ায় শেয়ারিংয়ের জন্য অটোমেটিক টাইটেল, ডেসক্রিপশন ও প্রিভিউ ইমেজ।
3. **Schema.org:** `EducationalOrganization` এবং `Course` স্কিমা মার্কআপ।

### 8.5 Hosting, Cloud Storage & Data Integrity
1. **cPanel Shared Hosting Optimization:** PHP 8.1+ & MySQL 8.0+.
2. **[NEW] Storage Offloading Policy:** PDF ফলাফল ও মিডিয়া গ্যালারির ফাইল সাইজ বেশি হলে cPanel ডিস্ক লিমিট বাঁচাতে Cloudflare R2 বা S3 অবজেক্ট স্টোরেজ ইন্টিগ্রেশনের ব্যবস্থা।
3. **Collation:** `utf8mb4_unicode_ci` (বাংলা যুক্তাক্ষর ও ম্যাথমেটিক্যাল সিম্বল $\pi, 	heta, \int, \sqrt{x}$ সমর্থনের জন্য)।
4. **Soft Deletes:** `is_deleted TINYINT(1) DEFAULT 0` নীতি প্রয়োগ।

---

## 9. Information Architecture & Site Map

```
alaminmathcare.com
│
├── / (Home Page)
│    ├── Hero & Social Proof Counters
│    ├── Math Specialization Cards
│    ├── Featured Batches & Live Seat Badges
│    ├── Head Teacher Al Amin Sir Profile
│    ├── Book Free Demo Class Form
│    ├── Top Scorers & Testimonials
│    └── Farmgate Campus Map & Hotline
│
├── /courses (সকল গণিত কোর্সসমূহ)
│    └── /courses/[slug] (কোর্সের বিবরণ ও ব্যাচ শিডিউল)
│
├── /batches (সকল ব্যাচ ও সময়সূচি)
├── /teachers (শিক্ষক প্যানেল ও প্রোফাইল)
├── /results (পরীক্ষার ফলাফল)
│    ├── /results/lookup (NEW: রোল/ফোন নম্বর দিয়ে স্কোরকার্ড সার্চ)
│    └── PDF Download List
│
├── /verify-id (NEW: স্টুডেন্ট আইডি QR কোড ভ্যালিডেশন)
├── /notices (নোটিশ বোর্ড)
├── /contact (যোগাযোগ ও গুগল ম্যাপ)
│
└── /admin (সিকিউর অ্যাডমিন সিএমএস)
     ├── /login & /forgot-password (NEW: টোকেন ভিত্তিক পাসওয়ার্ড রিসেট)
     ├── /dashboard (মেট্রিক্স ও সাম্প্রতিক লিড)
     ├── /leads (লিড সিআরএম)
     ├── /courses & /batches (কোর্স ও ব্যাচ ম্যানুয়াল ও অটোমিক সিট ম্যানেজমেন্ট)
     ├── /attendance (NEW: ক্লাসের দৈনিক উপস্থিতি ও SMS ট্রিগার)
     ├── /fee-ledger (NEW: ফি আদায়, বকেয়া হিসেব ও ডিজিটাল রসিদ)
     ├── /student-cards (NEW: স্টুডেন্ট আইডি কার্ড ও QR কোড জেনারেটর)
     ├── /notices & /results (PDF ও স্কোরকার্ড আপলোডার)
     ├── /batch-switch-requests (NEW: ব্যাচ পরিবর্তন আবেদন অনুমোদন)
     ├── /settings (ফোন, ইমেইল, নোটিশ ব্যানার, SMS API Key)
     └── /activity-logs (অডিট লগ)
```

---

## 10. Data Architecture & Entity Overview (২১টি ডাটাবেজ টেবিল)

পিআরডি v1.1.0-এ নতুন ফিচারসমূহ অন্তর্ভুক্ত করায় মোট **২১টি সুসংগঠিত ডাটাবেজ টেবিল** সংজ্ঞায়িত করা হলো:

| # | Table Name | Purpose & Primary Attributes |
| --- | --- | --- |
| 1 | `roles` | ব্যবহারকারীর ভূমিকা (`super_admin`, `admin`, `teacher`, `receptionist`) |
| 2 | `users` | অ্যাডমিন ইউজার একাউন্ট, হ্যাশড পাসওয়ার্ড, ব্রুট-ফোর্স ট্র্যাকার এবং **[NEW]** `reset_token`, `reset_token_expires_at` |
| 3 | `branches` | ফার্মগেট প্রধান ক্যাম্পাস ও শাখা তথ্য |
| 4 | `teachers` | আল আমিন স্যার ও শিক্ষকবৃন্দের প্রোফাইল, ছবি ও বায়ো |
| 5 | `courses` | গণিত কোর্স ক্যাটালগ (`title_en/bn`, `math_category`, `fee`) |
| 6 | `batches` | কোর্সের অধীনে ব্যাচসমূহ (`batch_name`, `class_days`, `start_time`, `total_seats`, `available_seats`, `status`) |
| 7 | `leads` | ওয়েবসাইট থেকে আসা লিড (`student_name`, `guardian_phone`, `status`, `ip_address`) |
| 8 | `students` | **[ENHANCED]** ভর্তি হওয়া শিক্ষার্থী ডাটাবেজ (`student_id_code`, `qr_code_path`, `guardian_phone`, `status`) |
| 9 | `enrollments` | **[ENHANCED]** ছাত্র-ব্যাচ রিলেশন এবং **[NEW]** `payment_status` (`unpaid`, `partially_paid`, `paid`), `total_fee`, `paid_amount`, `due_amount` |
| 10 | `fee_records` | **[NEW]** প্রতিটি পেমেন্টের ক্যাশ/ডিজিটাল ট্রানজ্যাকশন লেজার (`enrollment_id`, `amount_paid`, `payment_method`, `received_by`, `receipt_no`, `created_at`) |
| 11 | `attendances` | **[NEW]** দৈনিক উপস্থিতি রেকর্ড (`student_id`, `batch_id`, `attendance_date`, `status` ['present', 'absent', 'late'], `sms_sent_status`) |
| 12 | `batch_change_requests` | **[NEW]** ব্যাচ পরিবর্তনের আবেদন (`student_id`, `current_batch_id`, `requested_batch_id`, `reason`, `status`) |
| 13 | `demo_feedbacks` | **[NEW]** ডেমো ক্লাসের পর ইউজার রেটিং ও রিভিউ (`lead_id`, `rating`, `comments`, `created_at`) |
| 14 | `notices` | নোটিশ বোর্ড ও সংযুক্তি |
| 15 | `results` | **[ENHANCED]** পরীক্ষার রেজাল্ট শিট PDF ও ইন্ডিভিজুয়াল মার্কস ডাটা (`exam_title`, `batch_id`, `file_path`) |
| 16 | `routines` | সাপ্তাহিক ক্লাসের রুটিন ও রুম নম্বর |
| 17 | `faqs` | গণিত কোর্স ও ভর্তি সম্পর্কিত প্রশ্নোত্তর |
| 18 | `testimonials` | অভিভাবক ও ছাত্রদের রিভিউ |
| 19 | `gallery` | ক্যাম্পাসের ছবি ও গ্যালারি |
| 20 | `settings` | গ্লোবাল কনফিগারেশন কী-ভ্যালু (ফোন, ঠিকানা, SMS Gateway API Keys, Cloud Storage Keys) |
| 21 | `activity_logs` | অডিট ও অ্যাক্টিভিটি লগ (`user_id`, `action`, `ip_address`, `created_at`) |

*(নোট: ডাটাবেজ নরম্যালাইজেশনের সুবিধার্থে সংক্রান্ত টেবিলসমূহ সুসংগঠিতভাবে যুক্ত করা হয়েছে।)*

---

## 11. Assumptions, Risks & Mitigation Strategies

| Risk / Challenge | Severity | Impact | Mitigation Strategy |
| --- | --- | --- | --- |
| **Race Condition / Overbooking on Last Seat** | High | একই সাথে ২ জন আবেদন করলে ব্যাচের সিট অতিরিক্ত বুক হওয়া। | ১. ডাটাবেজে `SELECT ... FOR UPDATE` রো-লকিং বা Atomic Decrement Query কার্যকর করা।<br>২. সিট পূর্ণ হওয়া মাত্র AJAX এ ডাইনামিক্যালি 'Batch Full' ব্যাজ দেখানো। |
| **SMS Gateway API Downtime** | Medium | উপস্থিতি বা ভর্তির SMS না পৌঁছানো। | ১. ফেইলওভার সিঙ্ক্রোনাস ট্রাই কিউ (Try Queue) তৈরি রাখা।<br>২. ব্যর্থ SMS চিহ্নিত করে পুনরায় ম্যানুয়ালি পাঠানোর বাটন রাখা। |
| **cPanel Shared Hosting Storage Exhaustion** | Medium | বড় সাইজের PDF ও ছবির কারণে ডিস্ক স্পেস পূর্ণ হওয়া। | ১. ছবি WebP তে কম্প্রেস করা।<br>২. প্রয়োজনে Cloudflare R2 / S3 তে ফাইল স্টোরেজ অফলোড করা। |
| **Forgot Password Security Exploits** | High | অননুমোদিত ইউজার পাসওয়ার্ড রিসেট করার চেষ্টা করা। | ১. ক্রিপ্টোগ্রাফিক সল্টেড টোকেন ব্যবহার যা ১ ঘন্টা পর এক্সপায়ার হবে।<br>২. রিসেট লিংকের আইপি ও টাইমস্ট্যাম্প ভ্যালিডেশন। |
| **Spam / Bot Form Submissions** | Medium | লিড টেবিলে ভুয়া ডাটা জমা হওয়া। | ১. সেশন-ভিত্তিক CSRF টোকেন।<br>২. হানিপট ফিল্ড ও আইপি ভিত্তিক রেট লিমিটিং। |

---

## 12. Phased Release Roadmap & Definition of Done

```
Phase 0: PRD Architecture & Enhancements (v1.1.0)  ──► [ COMPLETED ]
Phase 1: Foundation, Security & i18n Core Setup    ──► [ NEXT IMMEDIATE STEP ]
Phase 2: MySQL 8.x Database Schema (19 Tables)     ──► [ PENDING ]
Phase 3: Public Website, Scorecard Lookup & Portal ──► [ PENDING ]
Phase 4: Admission Funnel, Live Seat Lock & SMS    ──► [ PENDING ]
Phase 5: Admin CMS, Fee Ledger & Attendance Engine ──► [ PENDING ]
Phase 6: Digital ID Card, QR Code & Password Reset ──► [ PENDING ]
Phase 7: Security Audit, Cloudflare & Deployment   ──► [ PENDING ]
```

### 12.1 Definition of Done (DoD) for Production Sign-off
একটি ফেজ বা ফিচার তখনই সম্পন্ন বলে গণ্য হবে যখন:
1. **Code Standards:** কোনো আনহ্যান্ডল্ড পিএইচপি নোটিশ/ওয়ার্নিং থাকবে না; PHP 8.1+ মানা হবে।
2. **Security Verification:** CSRF, Anti-XSS, PDO Prepared Statement এবং পাসওয়ার্ড রিসেট টোকেন সিকিউর।
3. **Bilingual Completeness:** ইংরেজি ও বাংলা উভয় ইন্টারফেস নিখুঁত।
4. **Anti-Overbooking Verification:** সিট সাবমিশনে কোনো কনকারেন্সি অসংগতি নেই।
5. **SMS & Ledger Accuracy:** উপস্থিতি ও পেমেন্ট রেকর্ডস নিখুঁতভাবে অটো-প্রসেস হয়।
6. **Mobile Usability & Speed:** লোড টাইম < ১.৫ সে. এবং লাইটহাউজ পারফরম্যান্স ৯০+।

---

📌 **Next Action for Development:**
এই পিআরডি (v1.1.0) অনুমোদনের পর ডেভেলপার সরাসরি **Phase 1: Foundation, Security & i18n Core Setup** বাস্তবায়নের কাজ শুরু করবেন।
