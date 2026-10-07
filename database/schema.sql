-- ============================================================
-- Al Amin's Math Care — Complete Production Database Schema (22 Tables)
-- Architecture: architecture.md Section 6.2
-- UTF8MB4 + Foreign Keys + Soft-Delete & Anti-Overbooking
-- ============================================================

CREATE DATABASE IF NOT EXISTS alaminmathcare
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE alaminmathcare;

-- 1. Roles Table
CREATE TABLE IF NOT EXISTS `roles` (
  `id` TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(30) NOT NULL UNIQUE,
  `name` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role_id` TINYINT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
  `failed_login_attempts` TINYINT UNSIGNED DEFAULT 0,
  `lockout_until` DATETIME NULL,
  `last_login` DATETIME NULL,
  `reset_token_hash` VARCHAR(64) DEFAULT NULL,
  `reset_token_expires_at` DATETIME DEFAULT NULL,
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Branches Table
CREATE TABLE IF NOT EXISTS `branches` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `name_bn` VARCHAR(100) DEFAULT NULL,
  `address` VARCHAR(255) NOT NULL,
  `address_bn` VARCHAR(255) DEFAULT NULL,
  `area` VARCHAR(100) NOT NULL,
  `city` VARCHAR(50) NOT NULL DEFAULT 'Dhaka',
  `postcode` VARCHAR(20) NOT NULL DEFAULT '1216',
  `landmark` VARCHAR(150) DEFAULT NULL,
  `phone_primary` VARCHAR(20) NOT NULL,
  `is_main` TINYINT(1) DEFAULT 1,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Teachers Table
CREATE TABLE IF NOT EXISTS `teachers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `name_en` VARCHAR(100) NOT NULL,
  `name_bn` VARCHAR(100) DEFAULT NULL,
  `designation_en` VARCHAR(100) NOT NULL DEFAULT 'Head Teacher',
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

-- 5. Courses Table
CREATE TABLE IF NOT EXISTS `courses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `title_en` VARCHAR(200) NOT NULL,
  `title_bn` VARCHAR(200) DEFAULT NULL,
  `class_level` VARCHAR(50) NOT NULL,
  `math_category` ENUM('general_math', 'higher_math_1st', 'higher_math_2nd', 'combined_higher_math', 'admission_engineering_math') NOT NULL DEFAULT 'general_math',
  `course_type` ENUM('regular', 'crash', 'model_test', 'special_care') DEFAULT 'regular',
  `duration` VARCHAR(50) DEFAULT NULL,
  `fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `fee_display_en` VARCHAR(80) DEFAULT 'Contact us',
  `fee_display_bn` VARCHAR(80) DEFAULT 'যোগাযোগ করুন',
  `teacher_id` INT UNSIGNED DEFAULT NULL,
  `description_en` TEXT DEFAULT NULL,
  `description_bn` TEXT DEFAULT NULL,
  `is_popular` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Batches Table (with Anti-Overbooking tracking)
CREATE TABLE IF NOT EXISTS `batches` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `teacher_id` INT UNSIGNED NOT NULL,
  `batch_name` VARCHAR(100) NOT NULL,
  `batch_name_bn` VARCHAR(100) DEFAULT NULL,
  `class_days` VARCHAR(100) NOT NULL,
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

-- 7. Leads Table (Admission enquiries and demo requests)
CREATE TABLE IF NOT EXISTS `leads` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `type` ENUM('admission_enquiry', 'demo_request', 'contact_message') NOT NULL,
  `student_name` VARCHAR(100) NOT NULL,
  `guardian_phone` VARCHAR(20) NOT NULL,
  `whatsapp_number` VARCHAR(20) DEFAULT NULL,
  `class_level` VARCHAR(50) NOT NULL,
  `course_id` INT UNSIGNED DEFAULT NULL,
  `batch_id` INT UNSIGNED DEFAULT NULL,
  `preferred_date` DATE NULL,
  `branch_name` VARCHAR(120) DEFAULT 'Farmgate (Main)',
  `message` TEXT DEFAULT NULL,
  `status` ENUM('new', 'contacted', 'demo_scheduled', 'admitted', 'cancelled') DEFAULT 'new',
  `admin_notes` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`guardian_phone`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Students Table (Portal ready)
CREATE TABLE IF NOT EXISTS `students` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL UNIQUE,
  `student_id_code` VARCHAR(30) UNIQUE NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `guardian_name` VARCHAR(100) DEFAULT NULL,
  `guardian_phone` VARCHAR(20) NOT NULL,
  `class_level` VARCHAR(50) NOT NULL,
  `school_college` VARCHAR(150) DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `qr_code_token` VARCHAR(64) UNIQUE DEFAULT NULL,
  `qr_code_path` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'passed_out', 'dropped') DEFAULT 'active',
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Enrollments Table
CREATE TABLE IF NOT EXISTS `enrollments` (
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

-- 10. Notices Table
CREATE TABLE IF NOT EXISTS `notices` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title_en` VARCHAR(255) NOT NULL,
  `title_bn` VARCHAR(255) DEFAULT NULL,
  `category` ENUM('general', 'admission', 'exam', 'routine', 'holiday', 'urgent') DEFAULT 'general',
  `description_en` TEXT DEFAULT NULL,
  `description_bn` TEXT DEFAULT NULL,
  `attachment_file` VARCHAR(255) DEFAULT NULL,
  `is_pinned` TINYINT(1) DEFAULT 0,
  `is_published` TINYINT(1) DEFAULT 1,
  `expiry_date` DATE NULL,
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Results Table
CREATE TABLE IF NOT EXISTS `results` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `exam_title_en` VARCHAR(255) NOT NULL,
  `exam_title_bn` VARCHAR(255) DEFAULT NULL,
  `batch_id` INT UNSIGNED NULL,
  `class_level` VARCHAR(50) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `published_date` DATE NOT NULL,
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Routines Table
CREATE TABLE IF NOT EXISTS `routines` (
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

-- 13. FAQs Table
CREATE TABLE IF NOT EXISTS `faqs` (
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

-- 14. Testimonials Table
CREATE TABLE IF NOT EXISTS `testimonials` (
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

-- 15. Gallery Table
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `image_path` VARCHAR(255) NOT NULL,
  `caption_en` VARCHAR(200) DEFAULT NULL,
  `caption_bn` VARCHAR(200) DEFAULT NULL,
  `category` ENUM('classroom', 'events', 'achievements') DEFAULT 'classroom',
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(100) PRIMARY KEY,
  `setting_value` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Activity Logs Table
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Student Fees Ledger
CREATE TABLE IF NOT EXISTS `student_fees` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT UNSIGNED NOT NULL,
  `fee_month` VARCHAR(7) NOT NULL,
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

-- 19. Fee Records / Payment Receipts Log
CREATE TABLE IF NOT EXISTS `fee_records` (
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

-- 20. Attendances Table
CREATE TABLE IF NOT EXISTS `attendances` (
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

-- 21. Batch Change Requests Table
CREATE TABLE IF NOT EXISTS `batch_change_requests` (
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

-- 22. Demo Feedbacks Table
CREATE TABLE IF NOT EXISTS `demo_feedbacks` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `lead_id` INT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `understanding_level` ENUM('excellent', 'good', 'average', 'difficult') DEFAULT 'good',
  `comments` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
