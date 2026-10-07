-- ============================================================
-- Al Amin's Math Care — Seed Data & Production Defaults
-- ============================================================

USE alaminmathcare;

-- 1. Roles
INSERT INTO `roles` (`id`, `slug`, `name`) VALUES
(1, 'super_admin', 'Super Administrator'),
(2, 'admin', 'Administrator'),
(3, 'teacher', 'Teacher'),
(4, 'receptionist', 'Front Desk / Receptionist')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 2. Initial Super Admin User (Password: Faisal@5511045)
INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `password`, `status`) VALUES
(1, 1, 'Al Amin Sir (Admin)', 'admin@alaminmathcare.com', '$2y$10$CCHU3/zQ27mX7vwRbIqu0.MeNV4MLnnatlzTlrd/re6ptnx8eM0.S', 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 3. Farmgate Main Branch
INSERT INTO `branches` (`id`, `name`, `name_bn`, `address`, `address_bn`, `area`, `city`, `postcode`, `landmark`, `phone_primary`, `is_main`, `status`) VALUES
(1, 'Farmgate (Main)', 'ফার্মগেট (মূল ক্যাম্পাস)', '46/1, Britter Goli, Opposite Holy Cross College, Farmgate, Dhaka 1216', '৪৬/১, বৃত্তের গলি, হোলি ক্রস কলেজের বিপরীতে, ফার্মগেট, ঢাকা ১২১৬', 'Farmgate', 'Dhaka', '1216', 'Opposite Holy Cross College', '01520102248', 1, 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `name_bn` = VALUES(`name_bn`), `address` = VALUES(`address`), `address_bn` = VALUES(`address_bn`), `phone_primary` = VALUES(`phone_primary`);

-- 4. Head Teacher: Al Amin Sir
INSERT INTO `teachers` (`id`, `user_id`, `slug`, `name_en`, `name_bn`, `designation_en`, `designation_bn`, `qualification`, `experience_years`, `bio_en`, `bio_bn`, `photo`, `is_head_teacher`, `sort_order`, `is_active`) VALUES
(1, 1, 'al-amin-sir', 'Al Amin Sir', 'আল আমিন স্যার', 'Head Teacher & Founder', 'প্রধান শিক্ষক ও প্রতিষ্ঠাতা', 'B.Sc (Hons), M.Sc in Mathematics', 10, 'Al Amin Sir is an expert mathematics educator in Farmgate, Dhaka, with a decade of proven experience mentoring SSC, HSC, and university admission aspirants.', 'আল আমিন স্যার ঢাকা ফার্মগেটের একজন স্বনামধন্য গণিত শিক্ষক। বিগত এক দশক ধরে তিনি এসএসসি, এইচএসসি এবং বিশ্ববিদ্যালয়/ইঞ্জিনিয়ারিং ভর্তিচ্ছু শিক্ষার্থীদের সফলতার সাথে দিকনির্দেশনা দিয়ে আসছেন।', NULL, 1, 1, 1)
ON DUPLICATE KEY UPDATE `name_en` = VALUES(`name_en`), `name_bn` = VALUES(`name_bn`), `designation_en` = VALUES(`designation_en`), `designation_bn` = VALUES(`designation_bn`), `qualification` = VALUES(`qualification`), `bio_en` = VALUES(`bio_en`), `bio_bn` = VALUES(`bio_bn`);

-- 5. Mathematics Courses
INSERT INTO `courses` (`id`, `slug`, `title_en`, `title_bn`, `class_level`, `math_category`, `course_type`, `duration`, `fee`, `fee_display_en`, `fee_display_bn`, `teacher_id`, `description_en`, `description_bn`, `is_popular`, `is_active`) VALUES
(1, 'class-6-8-mathematics', 'Class 6–8 Foundation Mathematics', 'ক্লাস ৬–৮ গণিত ফাউন্ডেশন', 'Class 6-8', 'general_math', 'regular', 'Monthly / Ongoing', 2000.00, '৳ 2,000 / month', '৳ ২,০০০ / মাস', 1, 'Build rock-solid mathematical fundamentals, algebraic intuition, and problem-solving skills for junior school exams and cadet preparation.', '৬ষ্ঠ থেকে ৮ম শ্রেণির শিক্ষার্থীদের জন্য গণিতের মৌলিক ভিত্তি, পাটিগণিত ও বীজগণিতের ভয় দূরীকরণে বিশেষ ফাউন্ডেশন ব্যাচ।', 0, 1),
(2, 'ssc-general-math', 'SSC General Mathematics', 'এসএসসি সাধারণ গণিত', 'Class 9-10 (SSC)', 'general_math', 'regular', 'Full Syllabus (1 Year)', 2500.00, '৳ 2,500 / month', '৳ ২,৫০০ / মাস', 1, 'Complete Board syllabus coverage with chapter-wise creative question solving (CQ) and quick MCQ tricks for SSC A+ guarantee.', 'এসএসসি সাধারণ গণিতের প্রতিটি অধ্যায়ের সৃজনশীল (CQ) সমাধান ও বহুনির্বাচনী (MCQ) শর্টকাট টেকনিকসহ শতভাগ এ+ নিশ্চিতকরণ ব্যাচ।', 1, 1),
(3, 'ssc-higher-math', 'SSC Higher Mathematics', 'এসএসসি উচ্চতর গণিত', 'Class 9-10 (SSC)', 'higher_math_1st', 'regular', 'Full Syllabus (1 Year)', 3000.00, '৳ 3,000 / month', '৳ ৩,০০০ / মাস', 1, 'Advanced concepts in Geometry, Coordinate Geometry, Trigonometry, and Calculus basics tailored specifically for SSC science candidates.', 'জ্যামিতি, স্থানাঙ্ক জ্যামিতি, ত্রিকোণমিতি এবং সম্ভাবনাসহ উচ্চতর গণিতের জটিল বিষয়সমূহের সহজবোধ্য ব্যাখ্যা ও পূর্ণাঙ্গ প্রস্তুতি।', 1, 1),
(4, 'hsc-higher-math-1st', 'HSC Higher Mathematics (1st Paper)', 'এইচএসসি উচ্চতর গণিত (১ম পত্র)', 'HSC 1st/2nd', 'higher_math_1st', 'regular', 'Full Syllabus (1 Year)', 3500.00, '৳ 3,500 / month', '৳ ৩,৫০০ / মাস', 1, 'Matrices, Determinants, Vectors, Coordinate Geometry, Circles, and Differential Calculus with deep conceptual clarity.', 'ম্যাট্রিক্স, নির্ণায়ক, সরলরেখা, বৃত্ত ও ক্যালকুলাসের প্রতিটি বিষয়ের গভীর ধারণাসহ এইচএসসি বোর্ড পরীক্ষার সেরা প্রস্তুতি।', 1, 1),
(5, 'hsc-higher-math-2nd', 'HSC Higher Mathematics (2nd Paper)', 'এইচএসসি উচ্চতর গণিত (২য় পত্র)', 'HSC 1st/2nd', 'higher_math_2nd', 'regular', 'Full Syllabus (1 Year)', 3500.00, '৳ 3,500 / month', '৳ ৩,৫০০ / মাস', 1, 'Real Numbers, Complex Numbers, Polynomials, Conics, Trigonometric Inverses, Statics & Dynamics preparation.', 'জটিল সংখ্যা, কনিক, বিপরীত ত্রিকোণমিতিক ফাংশন, দ্বিপদী বিস্তৃতি ও বলবিদ্যার জটিল গাণিতিক সমস্যার নিখুঁত সমাধান।', 1, 1),
(6, 'admission-engineering-math', 'Engineering & University Admission Math Special Care', 'ইঞ্জিনিয়ারিং ও বিশ্ববিদ্যালয় ভর্তি গণিত স্পেশাল কেয়ার', 'Admission', 'admission_engineering_math', 'special_care', '4 Months Intensive', 12000.00, '৳ 12,000 (Full Course)', '৳ ১২,০০০ (সম্পূর্ণ কোর্স)', 1, 'High-velocity problem-solving, BUET/CKRUET admission question bank analysis, and DU A-Unit mathematical mastery.', 'বুয়েট, কুয়েট, রুয়েট, চুয়েট ও ঢাকা বিশ্ববিদ্যালয় ক-ইউনিট ভর্তি পরীক্ষার বিগত বছরের প্রশ্নব্যাংক সমাধান ও টাইম ম্যানেজমেন্ট স্ট্র্যাটেজি।', 1, 1)
ON DUPLICATE KEY UPDATE `title_en` = VALUES(`title_en`), `title_bn` = VALUES(`title_bn`), `class_level` = VALUES(`class_level`), `math_category` = VALUES(`math_category`), `course_type` = VALUES(`course_type`), `duration` = VALUES(`duration`), `fee` = VALUES(`fee`), `fee_display_en` = VALUES(`fee_display_en`), `fee_display_bn` = VALUES(`fee_display_bn`), `description_en` = VALUES(`description_en`), `description_bn` = VALUES(`description_bn`), `is_popular` = VALUES(`is_popular`), `is_active` = VALUES(`is_active`);

-- 6. Batches
INSERT INTO `batches` (`id`, `course_id`, `branch_id`, `teacher_id`, `batch_name`, `batch_name_bn`, `class_days`, `start_time`, `end_time`, `start_date`, `total_seats`, `available_seats`, `admission_status`, `status`) VALUES
(1, 2, 1, 1, 'SSC-2027 Morning Batch', 'এসএসসি-২০২৭ মর্নিং ব্যাচ', 'Sat,Mon,Wed', '08:00:00', '09:30:00', '2026-10-01', 30, 24, 'open', 'upcoming'),
(2, 3, 1, 1, 'SSC-2027 Evening Batch', 'এসএসসি-২০২৭ ইভনিং ব্যাচ', 'Sat,Mon,Wed', '16:00:00', '17:30:00', '2026-10-01', 30, 18, 'open', 'upcoming'),
(3, 4, 1, 1, 'HSC-2027 Prime Batch', 'এইচএসসি-২০২৭ প্রাইম ব্যাচ', 'Sun,Tue,Thu', '10:00:00', '11:30:00', '2026-10-01', 30, 15, 'open', 'upcoming'),
(4, 5, 1, 1, 'HSC-2026 Revision & Model Test', 'এইচএসসি-২০২৬ মডেল টেস্ট ব্যাচ', 'Sun,Tue,Thu', '15:00:00', '16:30:00', '2026-10-01', 30, 12, 'open', 'upcoming'),
(5, 6, 1, 1, 'Varsity & Engineering Admission Math-1', 'ভার্সিটি ও ইঞ্জিনিয়ারিং ম্যাথ-১', 'Daily', '17:30:00', '19:30:00', '2026-10-15', 30, 8, 'open', 'upcoming')
ON DUPLICATE KEY UPDATE `batch_name` = VALUES(`batch_name`);

-- 7. Routines
INSERT INTO `routines` (`id`, `batch_id`, `day_of_week`, `start_time`, `end_time`, `room_number`, `notes`) VALUES
(1, 1, 'Saturday', '08:00:00', '09:30:00', 'Room-1', 'Algebra CQ Practice'),
(2, 1, 'Monday', '08:00:00', '09:30:00', 'Room-1', 'Geometry Proofs'),
(3, 1, 'Wednesday', '08:00:00', '09:30:00', 'Room-1', 'Weekly MCQ Test'),
(4, 3, 'Sunday', '10:00:00', '11:30:00', 'Room-2', 'Calculus Concept & Formula Drill'),
(5, 3, 'Tuesday', '10:00:00', '11:30:00', 'Room-2', 'Conics & Trigonometry Drill'),
(6, 3, 'Thursday', '10:00:00', '11:30:00', 'Room-2', 'Board Standard Weekly Assessment')
ON DUPLICATE KEY UPDATE `room_number` = VALUES(`room_number`);

-- 8. FAQs
INSERT INTO `faqs` (`id`, `question_en`, `question_bn`, `answer_en`, `answer_bn`, `category`, `sort_order`, `is_active`) VALUES
(1, 'Why is Al Amin\'s Math Care specialized only in Mathematics?', 'আল আমিনস ম্যাথ কেয়ার শুধুমাত্র গণিত কেন পড়ায়?', 'Mathematics requires dedicated focus, conceptual clarity, and continuous practice. By concentrating exclusively on Mathematics, we ensure every student receives personalized attention and masters every single theorem and problem without any distractions.', 'গণিত এমন একটি বিষয় যাতে ভালো ফলাফলের জন্য বিশেষ মনোযোগ, ধারণার স্পষ্টতা এবং নিয়মিত অনুশীলনের প্রয়োজন হয়। এককভাবে শুধু গণিতে নজর দেওয়ার ফলে শিক্ষক প্রতিটি ছাত্রের দুর্বলতা চিহ্নিত করে সর্বোচ্চ যত্ন নিতে পারেন।', 'general', 1, 1),
(2, 'Can I attend a free demo class before admission?', 'ভর্তির আগে কি ফ্রি ডেমো ক্লাস করার সুযোগ আছে?', 'Yes! We encourage every student and guardian to attend a free demo class to experience Al Amin Sir\'s teaching methodology first-hand before confirming admission.', 'হ্যাঁ! নিশ্চিতভাবে। যেকোনো শিক্ষার্থী বা অভিভাবক ভর্তির সিদ্ধান্ত নেওয়ার আগে আল আমিন স্যারের পাঠদান পদ্ধতি সরাসরি যাচাই করার জন্য বিনামূল্যে ডেমো ক্লাসে অংশ নিতে পারেন।', 'admission', 2, 1),
(3, 'Where is the coaching campus located in Farmgate?', 'ফার্মগেটে কোচিং ক্যাম্পাসের অবস্থান কোথায়?', 'We are located at 46/1, Britter Goli, right opposite Holy Cross College in Farmgate, Dhaka 1216. Just a 2-minute walk from Farmgate Metro Rail Station.', 'আমাদের ক্যাম্পাস ৪৬/১, বৃত্তের গলি, হোলি ক্রস কলেজের ঠিক বিপরীতে, ফার্মগেট, ঢাকা ১২১৬। ফার্মগেট মেট্রো রেল স্টেশন থেকে মাত্র ২ মিনিটের হাঁটার দূরত্ব।', 'campus', 3, 1),
(4, 'How are weekly class tests and evaluations conducted?', 'সাপ্তাহিক পরীক্ষা ও মূল্যায়ন কীভাবে হয়?', 'Every week, a chapter-specific CQ and OMR-based MCQ exam is conducted. Marks and ranks are shared via SMS to parents and accessible on our online scorecard portal.', 'প্রতি সপ্তাহে অধ্যায়ভিত্তিক সৃজনশীল পরীক্ষা ও OMR শিটে বহুনির্বাচনী পরীক্ষা নেওয়া হয়। ফলাফল তাৎক্ষণিক এসএমএস-এর মাধ্যমে অভিভাবককে জানানো হয় এবং ওয়েবসাইটে রোল নম্বর দিয়ে দেখা যায়।', 'academic', 4, 1)
ON DUPLICATE KEY UPDATE `question_en` = VALUES(`question_en`);

-- 9. Testimonials
INSERT INTO `testimonials` (`id`, `student_name`, `student_role`, `quote_en`, `quote_bn`, `rating`, `is_featured`) VALUES
(1, 'Tahmidur Rahman', 'HSC GPA 5.00 (Golden) — Currently BUET CSE', 'Al Amin Sir turned Higher Math from my most feared subject into my highest-scoring one. His calculus visualization techniques are world-class.', 'উচ্চতর গণিতে আমার যে ভয় ছিল, আল আমিন স্যারের ক্লাসে আসার পর তা পুরোপুরি কেটে যায়। স্যারের ক্যালকুলাসের টেকনিকগুলো অসাধারণ!', 5, 1),
(2, 'Nusrat Jahan', 'SSC GPA 5.00 (Holy Cross College)', 'Every chapter was broken down into easy concept maps. The weekly OMR tests gave me complete exam confidence.', 'প্রতিটি অধ্যায়ের সৃজনশীল ও বহুনির্বাচনী প্রশ্ন এত নিখুঁতভাবে প্র্যাকটিস করানো হতো যে বোর্ড পরীক্ষায় সব প্রশ্ন পরিচিত লেগেছে।', 5, 1),
(3, 'Md. Rafiqul Islam (Guardian)', 'Parent of Class 10 Student', 'The regular SMS updates on my son\'s attendance and test marks gave our family absolute peace of mind. Al Amin Sir\'s care is exceptional.', 'আমার ছেলের পড়াশোনার অগ্রগতি ও ক্লাসের উপস্থিতির আপডেট নিয়মিত এসএমএসে পেয়েছি। গণিতে ওর ফলাফল অনেক উন্নত হয়েছে।', 5, 1)
ON DUPLICATE KEY UPDATE `student_name` = VALUES(`student_name`);

-- 10. Notices
INSERT INTO `notices` (`id`, `title_en`, `title_bn`, `category`, `description_en`, `description_bn`, `is_pinned`, `is_published`) VALUES
(1, 'Admissions Open for SSC & HSC Mathematics Batches 2026-2027', 'এসএসসি ও এইচএসসি নতুন ব্যাচসমূহে ভর্তি চলছে (২০২৬-২০২৭)', 'admission', 'New mathematics batches for Class 9-10 (SSC) and Class 11-12 (HSC) are starting from October 1st. Limited seats (30 per batch) to ensure individual care.', '৯ম-১০ম (এসএসসি) ও একাদশ-দ্বাদশ (এইচএসসি) শ্রেণির নতুন গণিত ব্যাচ ১ অক্টোবর থেকে শুরু হবে। সীমিত আসন (প্রতি ব্যাচে ৩০ জন)। দ্রুত যোগাযোগ করুন।', 1, 1),
(2, 'Free Mathematics Demo Class Schedule for October', 'অক্টোবর মাসের ফ্রি গণিত ডেমো ক্লাসের সময়সূচী', 'urgent', 'Students can register for the upcoming Friday Demo Session at Farmgate Campus. Pre-registration via website is required.', 'ফার্মগেট ক্যাম্পাসে শুক্রবারের ফ্রি ডেমো ক্লাসে অংশ নেওয়ার জন্য ওয়েবসাইট থেকে নাম ও ফোন নম্বর দিয়ে রেজিস্ট্রেশন করতে অনুরোধ করা যাচ্ছে।', 1, 1)
ON DUPLICATE KEY UPDATE `title_en` = VALUES(`title_en`);

-- 11. Global Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Al Amin''s Math Care'),
('site_tagline_en', 'Expert Mathematics Coaching in Farmgate, Dhaka'),
('site_tagline_bn', 'ফার্মগেট, ঢাকায় বিশেষজ্ঞ গণিত কোচিং'),
('domain', 'alaminmathcare.com'),
('phone_primary', '01520102248'),
('phone_secondary', '01521255850'),
('whatsapp', '01520102248'),
('address_en', '46/1, Britter Goli, Opposite Holy Cross College, Farmgate, Dhaka 1216'),
('address_bn', '৪৬/১, বৃত্তের গলি, হোলি ক্রস কলেজের বিপরীতে, ফার্মগেট, ঢাকা ১২১৬'),
('head_teacher', 'Al Amin Sir'),
('default_locale', 'en')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- 12. Results (Sample)
INSERT INTO `results` (`id`, `exam_title_en`, `exam_title_bn`, `batch_id`, `class_level`, `file_path`, `published_date`, `is_deleted`) VALUES
(1, 'HSC Model Test-1 Mathematics Merit List', 'এইচএসসি মডেল টেস্ট-১ গণিত মেধা তালিকা', 4, 'HSC 1st/2nd', 'storage/results/model_test_1_hsc.pdf', '2026-09-20', 0),
(2, 'SSC Weekly Test Chapter 3 (Geometry) Result', 'এসএসসি সাপ্তাহিক পরীক্ষা অধ্যায় ৩ (জ্যামিতি) ফলাফল', 2, 'Class 9-10 (SSC)', 'storage/results/ssc_weekly_geom.pdf', '2026-09-22', 0)
ON DUPLICATE KEY UPDATE `exam_title_en` = VALUES(`exam_title_en`);

-- 13. Gallery (Sample)
INSERT INTO `gallery` (`id`, `image_path`, `caption_en`, `caption_bn`, `category`, `is_deleted`) VALUES
(1, 'assets/images/gallery/classroom_1.jpg', 'Interactive Mathematics Classroom at Farmgate', 'ফার্মগেট ক্যাম্পাসে আধুনিক গণিত ক্লাসরুম', 'classroom', 0),
(2, 'assets/images/gallery/sir_lecture.jpg', 'Al Amin Sir mentoring HSC students on Calculus', 'ক্যালকুলাস ক্লাসে শিক্ষার্থীদের বোঝাচ্ছেন আল আমিন স্যার', 'classroom', 0)
ON DUPLICATE KEY UPDATE `caption_en` = VALUES(`caption_en`);

-- 14. Sample Active Students
INSERT INTO `students` (`id`, `student_id_code`, `name`, `guardian_name`, `guardian_phone`, `class_level`, `school_college`, `qr_code_token`, `status`, `is_deleted`) VALUES
(1, 'AMC-2026-001', 'Tanvir Ahmed', 'Md. Kamal Hossain', '01711001122', 'Class 9-10 (SSC)', 'Government Laboratory High School', 'amc_token_tanvir_001', 'active', 0),
(2, 'AMC-2026-002', 'Nusrat Jahan Mim', 'Farhana Begum', '01819223344', 'Class 9-10 (SSC)', 'Holy Cross Girls High School', 'amc_token_mim_002', 'active', 0),
(3, 'AMC-2026-003', 'Abrar Fahim', 'Shahidul Islam', '01911334455', 'Class 9-10 (SSC)', 'Dhaka Residential Model College', 'amc_token_abrar_003', 'active', 0),
(4, 'AMC-2026-004', 'Sadia Islam Riya', 'Rezaul Karim', '01722445566', 'Class 9-10 (SSC)', 'Viqarunnisa Noon School', 'amc_token_sadia_004', 'active', 0),
(5, 'AMC-2026-005', 'Mahmudul Hasan', 'Anwarul Haque', '01611556677', 'Class 9-10 (SSC)', 'Motijheel Ideal School', 'amc_token_mahmud_005', 'active', 0)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 15. Student Enrollments into Batch 1 (SSC-2027 Morning Batch)
INSERT INTO `enrollments` (`id`, `student_id`, `batch_id`, `enrollment_date`, `total_fee`, `paid_amount`, `payment_status`, `status`) VALUES
(1, 1, 1, '2026-10-01', 2500.00, 2500.00, 'paid', 'active'),
(2, 2, 1, '2026-10-01', 2500.00, 2500.00, 'paid', 'active'),
(3, 3, 1, '2026-10-02', 2500.00, 1500.00, 'partially_paid', 'active'),
(4, 4, 1, '2026-10-02', 2500.00, 2500.00, 'paid', 'active'),
(5, 5, 1, '2026-10-03', 2500.00, 0.00, 'unpaid', 'active')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);


