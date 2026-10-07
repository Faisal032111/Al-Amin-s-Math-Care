<?php

/**
 * admin/leads/index.php — Admission Leads & Demo Booking Inquiries
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Admission & Demo Leads — Admin Panel';
$adminPageHeading = 'Admission & Demo Class Inquiries';
$adminPageSubheading = 'Manage website leads, contact parents, track demo bookings & admit students';
$activeModule = 'leads';

$pdo = db();

$typeFilter = clean_input($_GET['type'] ?? '');
$statusFilter = clean_input($_GET['status'] ?? '');
$search = clean_input($_GET['q'] ?? '');

$sql = 'SELECT l.*, c.title_en as course_title, b.batch_name 
        FROM leads l 
        LEFT JOIN courses c ON l.course_id = c.id 
        LEFT JOIN batches b ON l.batch_id = b.id 
        WHERE l.is_deleted = 0';
$params = [];

if (!empty($typeFilter)) {
    $sql .= ' AND l.type = :type';
    $params[':type'] = $typeFilter;
}
if (!empty($statusFilter)) {
    $sql .= ' AND l.status = :status';
    $params[':status'] = $statusFilter;
}
if (!empty($search)) {
    $sql .= ' AND (l.student_name LIKE :q OR l.guardian_phone LIKE :q OR l.whatsapp_number LIKE :q)';
    $params[':q'] = '%' . $search . '%';
}

$sql .= ' ORDER BY l.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Filter & Search Bar -->
<div class="card card-custom p-3 mb-4">
    <form method="GET" action="" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control" value="<?= e($search) ?>" placeholder="Search student name, phone...">
            </div>
        </div>
        <div class="col-md-3">
            <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Inquiry Types</option>
                <option value="admission_enquiry" <?= $typeFilter === 'admission_enquiry' ? 'selected' : '' ?>>Direct Admission Enquiries</option>
                <option value="demo_request" <?= $typeFilter === 'demo_request' ? 'selected' : '' ?>>Free Demo Class Bookings</option>
                <option value="contact_message" <?= $typeFilter === 'contact_message' ? 'selected' : '' ?>>Contact Us Messages</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="new" <?= $statusFilter === 'new' ? 'selected' : '' ?>>New (Uncontacted)</option>
                <option value="contacted" <?= $statusFilter === 'contacted' ? 'selected' : '' ?>>Contacted</option>
                <option value="demo_scheduled" <?= $statusFilter === 'demo_scheduled' ? 'selected' : '' ?>>Demo Scheduled</option>
                <option value="admitted" <?= $statusFilter === 'admitted' ? 'selected' : '' ?>>Admitted</option>
                <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        <div class="col-md-2 text-end">
            <a href="<?= e(base_url('admin/leads/')) ?>" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
        </div>
    </form>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Student & Guardian Phone</th>
                    <th>Class / Course / Batch</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Notes & Message</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leads)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No inquiry records match the selected filters.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($leads as $l): ?>
                        <tr>
                            <td style="white-space: nowrap;">
                                <div class="small fw-semibold text-dark"><?= date('d M, Y', strtotime($l['created_at'])) ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?= date('h:i A', strtotime($l['created_at'])) ?></div>
                            </td>
                            <td>
                                <strong class="text-dark d-block"><?= e($l['student_name']) ?></strong>
                                <a href="tel:<?= e($l['guardian_phone']) ?>" class="small text-primary text-decoration-none d-inline-block mt-1 me-2">
                                    <i class="bi bi-telephone-fill me-1"></i><?= e($l['guardian_phone']) ?>
                                </a>
                                <?php if (!empty($l['whatsapp_number'])): ?>
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $l['whatsapp_number']) ?>" target="_blank" class="small text-success text-decoration-none d-inline-block mt-1">
                                        <i class="bi bi-whatsapp me-1"></i>WhatsApp
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= e($l['class_level']) ?></span>
                                <?php if (!empty($l['course_title'])): ?>
                                    <div class="small text-muted mt-1"><?= e($l['course_title']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($l['preferred_date'])): ?>
                                    <div class="small text-info mt-1"><i class="bi bi-calendar-event me-1"></i>Pref Date: <?= date('d M Y', strtotime($l['preferred_date'])) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $l['type'] === 'demo_request' ? 'badge-subtle-info' : ($l['type'] === 'admission_enquiry' ? 'badge-subtle-primary' : 'badge-subtle-secondary') ?>">
                                    <?= e(ucwords(str_replace('_', ' ', $l['type']))) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusBadge = match($l['status']) {
                                    'new' => 'badge-subtle-danger',
                                    'contacted' => 'badge-subtle-warning',
                                    'demo_scheduled' => 'badge-subtle-info',
                                    'admitted' => 'badge-subtle-success',
                                    'cancelled' => 'badge bg-secondary',
                                    default => 'badge bg-light text-dark'
                                };
                                ?>
                                <span class="badge <?= $statusBadge ?>">
                                    <?= ucfirst(str_replace('_', ' ', $l['status'])) ?>
                                </span>
                            </td>
                            <td style="max-width: 240px;">
                                <?php if (!empty($l['message'])): ?>
                                    <div class="small text-muted mb-1 text-truncate" title="<?= e($l['message']) ?>">
                                        <em>"<?= e($l['message']) ?>"</em>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($l['admin_notes'])): ?>
                                    <div class="small text-secondary bg-light p-1 rounded border">
                                        <i class="bi bi-sticky me-1"></i><?= e($l['admin_notes']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= e(base_url('admin/leads/edit.php?id=' . $l['id'])) ?>" class="btn btn-sm btn-outline-primary py-0 px-2 me-1" title="Update Status / Notes">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete Inquiry" onclick="confirmDelete('<?= e(base_url('admin/leads/delete.php')) ?>', <?= $l['id'] ?>, 'Are you sure you want to delete this lead record for \'<?= addslashes($l['student_name']) ?>\'?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
