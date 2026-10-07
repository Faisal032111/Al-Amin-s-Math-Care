<?php

/**
 * admin/includes/sidebar.php — Admin Panel Sidebar Component
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

$active = $activeModule ?? 'dashboard';
?>
<nav id="adminSidebar" class="col-md-3 col-lg-2 d-md-block admin-sidebar collapse px-0">
    <div class="admin-brand d-flex align-items-center gap-2">
        <span class="brand-mark text-warning fw-bold fs-4">∑</span>
        <div>
            <div class="fw-bold text-white lh-1" style="font-size: 0.95rem;">Al Amin's</div>
            <div class="text-warning small" style="font-size: 0.72rem; letter-spacing: 0.05em;">MATH CARE ADMIN</div>
        </div>
    </div>

    <div class="p-3">
        <!-- Main Navigation -->
        <div class="admin-nav-section-title">Core Operations</div>
        <a class="admin-nav-item <?= $active === 'dashboard' ? 'active' : '' ?>" href="<?= e(base_url('admin/index.php')) ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a class="admin-nav-item <?= $active === 'leads' ? 'active' : '' ?>" href="<?= e(base_url('admin/leads/')) ?>">
            <i class="bi bi-inbox-fill"></i> Leads & Demos
        </a>
        <a class="admin-nav-item <?= $active === 'students' ? 'active' : '' ?>" href="<?= e(base_url('admin/students/')) ?>">
            <i class="bi bi-people-fill"></i> Students & Parents
        </a>
        <a class="admin-nav-item <?= $active === 'attendance' ? 'active' : '' ?>" href="<?= e(base_url('admin/attendance/')) ?>">
            <i class="bi bi-calendar-check-fill"></i> Daily Attendance
        </a>
        <a class="admin-nav-item <?= $active === 'fee_ledger' ? 'active' : '' ?>" href="<?= e(base_url('admin/fee-ledger/')) ?>">
            <i class="bi bi-receipt-cutoff"></i> Fee Ledger & Receipts
        </a>
        <a class="admin-nav-item <?= $active === 'student_cards' ? 'active' : '' ?>" href="<?= e(base_url('admin/student-cards/')) ?>">
            <i class="bi bi-person-badge-fill"></i> ID Cards & QR
        </a>
        <a class="admin-nav-item <?= $active === 'batch_switch' ? 'active' : '' ?>" href="<?= e(base_url('admin/batch-switch-requests/')) ?>">
            <i class="bi bi-arrow-left-right"></i> Batch Switch Requests
        </a>

        <!-- Academic Management -->
        <div class="admin-nav-section-title">Academic & Schedule</div>
        <a class="admin-nav-item <?= $active === 'courses' ? 'active' : '' ?>" href="<?= e(base_url('admin/courses/')) ?>">
            <i class="bi bi-book-fill"></i> Math Courses
        </a>
        <a class="admin-nav-item <?= $active === 'batches' ? 'active' : '' ?>" href="<?= e(base_url('admin/batches/')) ?>">
            <i class="bi bi-clock-history"></i> Batches & Seats
        </a>
        <a class="admin-nav-item <?= $active === 'routines' ? 'active' : '' ?>" href="<?= e(base_url('admin/routines/')) ?>">
            <i class="bi bi-calendar3"></i> Class Routines
        </a>
        <a class="admin-nav-item <?= $active === 'teachers' ? 'active' : '' ?>" href="<?= e(base_url('admin/teachers/')) ?>">
            <i class="bi bi-person-video3"></i> Teachers Faculty
        </a>
        <a class="admin-nav-item <?= $active === 'results' ? 'active' : '' ?>" href="<?= e(base_url('admin/results/')) ?>">
            <i class="bi bi-trophy-fill"></i> Exam Results
        </a>

        <!-- Content & Website -->
        <div class="admin-nav-section-title">Website Content</div>
        <a class="admin-nav-item <?= $active === 'notices' ? 'active' : '' ?>" href="<?= e(base_url('admin/notices/')) ?>">
            <i class="bi bi-megaphone-fill"></i> Notice Board
        </a>
        <a class="admin-nav-item <?= $active === 'testimonials' ? 'active' : '' ?>" href="<?= e(base_url('admin/testimonials/')) ?>">
            <i class="bi bi-chat-quote-fill"></i> Testimonials
        </a>
        <a class="admin-nav-item <?= $active === 'gallery' ? 'active' : '' ?>" href="<?= e(base_url('admin/gallery/')) ?>">
            <i class="bi bi-images"></i> Photo Gallery
        </a>
        <a class="admin-nav-item <?= $active === 'faqs' ? 'active' : '' ?>" href="<?= e(base_url('admin/faqs/')) ?>">
            <i class="bi bi-question-circle-fill"></i> FAQs
        </a>

        <!-- System & Access -->
        <?php if (check_admin_role(['super_admin', 'admin'])): ?>
        <div class="admin-nav-section-title">System & Security</div>
        <a class="admin-nav-item <?= $active === 'users' ? 'active' : '' ?>" href="<?= e(base_url('admin/users/')) ?>">
            <i class="bi bi-shield-lock-fill"></i> User Management
        </a>
        <a class="admin-nav-item <?= $active === 'settings' ? 'active' : '' ?>" href="<?= e(base_url('admin/settings/')) ?>">
            <i class="bi bi-sliders"></i> Global Settings
        </a>
        <?php else: ?>
        <a class="admin-nav-item <?= $active === 'settings' ? 'active' : '' ?>" href="<?= e(base_url('admin/settings/')) ?>">
            <i class="bi bi-sliders"></i> Global Settings
        </a>
        <?php endif; ?>
    </div>

    <div class="p-3 border-top border-secondary border-opacity-25 mt-auto">
        <a href="<?= e(base_url('admin/logout.php')) ?>" class="admin-nav-item text-danger">
            <i class="bi bi-box-arrow-right"></i> Sign Out
        </a>
    </div>
</nav>
