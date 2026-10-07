<?php

/**
 * admin/leads/edit.php — Update Lead Status & Convert to Admission
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Manage Lead — Admin Panel';
$adminPageHeading = 'Review & Update Lead Inquiry';
$adminPageSubheading = 'Record contact notes, schedule demo classes & update conversion status';
$activeModule = 'leads';

$pdo = db();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT l.*, c.title_en as course_title, b.batch_name FROM leads l LEFT JOIN courses c ON l.course_id = c.id LEFT JOIN batches b ON l.batch_id = b.id WHERE l.id = :id AND l.is_deleted = 0 LIMIT 1');
$stmt->execute([':id' => $id]);
$lead = $stmt->fetch();

if (!$lead) {
    set_flash('error', 'Inquiry record not found.');
    header('Location: ' . base_url('admin/leads/'));
    exit;
}

$courses = $pdo->query('SELECT id, title_en, class_level FROM courses WHERE is_deleted = 0 ORDER BY id ASC')->fetchAll();
$batches = $pdo->query('SELECT id, batch_name, class_days, available_seats FROM batches WHERE is_deleted = 0 ORDER BY id ASC')->fetchAll();

$errors = [];
$status = $lead['status'];
$adminNotes = $lead['admin_notes'] ?? '';
$courseId = (int)($lead['course_id'] ?? 0);
$batchId = (int)($lead['batch_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $status = clean_input($_POST['status'] ?? 'new');
    $adminNotes = clean_input($_POST['admin_notes'] ?? '');
    $courseId = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;
    $batchId = !empty($_POST['batch_id']) ? (int)$_POST['batch_id'] : null;

    try {
        $update = $pdo->prepare('UPDATE leads SET status = :st, admin_notes = :notes, course_id = :cid, batch_id = :bid WHERE id = :id');
        $update->execute([
            ':st' => $status,
            ':notes' => $adminNotes,
            ':cid' => $courseId,
            ':bid' => $batchId,
            ':id' => $id,
        ]);

        set_flash('success', 'Lead record updated successfully.');
        header('Location: ' . base_url('admin/leads/'));
        exit;
    } catch (Throwable $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?= e(base_url('admin/leads/')) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Leads
            </a>
            <span class="badge bg-light text-secondary border">Lead ID #<?= $id ?></span>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger py-2 small">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Student & Lead Information Card -->
        <div class="card card-custom p-4 mb-4">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Inquiry Details</h6>
            <div class="row g-3">
                <div class="col-sm-6">
                    <small class="text-muted d-block">Student Name</small>
                    <strong class="text-dark fs-6"><?= e($lead['student_name']) ?></strong>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted d-block">Class Level</small>
                    <strong class="text-dark"><?= e($lead['class_level']) ?></strong>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted d-block">Guardian Phone</small>
                    <a href="tel:<?= e($lead['guardian_phone']) ?>" class="fw-bold text-primary text-decoration-none">
                        <i class="bi bi-telephone me-1"></i><?= e($lead['guardian_phone']) ?>
                    </a>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted d-block">WhatsApp Number</small>
                    <?php if (!empty($lead['whatsapp_number'])): ?>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $lead['whatsapp_number']) ?>" target="_blank" class="fw-bold text-success text-decoration-none">
                            <i class="bi bi-whatsapp me-1"></i><?= e($lead['whatsapp_number']) ?>
                        </a>
                    <?php else: ?>
                        <span class="text-muted">Not provided</span>
                    <?php endif; ?>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted d-block">Inquiry Type</small>
                    <span class="badge bg-light text-dark border"><?= e(ucwords(str_replace('_', ' ', $lead['type']))) ?></span>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted d-block">Submission Time & IP</small>
                    <span class="small text-secondary"><?= date('d M Y, h:i A', strtotime($lead['created_at'])) ?> (<?= e($lead['ip_address']) ?>)</span>
                </div>
                <?php if (!empty($lead['message'])): ?>
                    <div class="col-12">
                        <small class="text-muted d-block">Message / Specific Inquiry</small>
                        <div class="p-2 bg-light rounded text-dark small border"><?= e($lead['message']) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Update Status & Admin Notes Form -->
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Update Status & Progress Notes</h6>
            <form method="POST" action="">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Inquiry Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="new" <?= $status === 'new' ? 'selected' : '' ?>>New (Uncontacted)</option>
                            <option value="contacted" <?= $status === 'contacted' ? 'selected' : '' ?>>Contacted (Phone called / WhatsApp sent)</option>
                            <option value="demo_scheduled" <?= $status === 'demo_scheduled' ? 'selected' : '' ?>>Demo Class Scheduled</option>
                            <option value="admitted" <?= $status === 'admitted' ? 'selected' : '' ?>>Admitted (Confirmed Enrollment)</option>
                            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled / Not Interested</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Preferred Math Course</label>
                        <select name="course_id" class="form-select">
                            <option value="">-- Select Course --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $courseId === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['title_en']) ?> (<?= e($c['class_level']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-secondary">Assign / Suggested Batch</label>
                        <select name="batch_id" class="form-select">
                            <option value="">-- Select Batch --</option>
                            <?php foreach ($batches as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= $batchId === (int)$b['id'] ? 'selected' : '' ?>>
                                    <?= e($b['batch_name']) ?> (<?= e($b['class_days']) ?> — <?= $b['available_seats'] ?> seats left)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Counselor / Admin Notes</label>
                        <textarea name="admin_notes" rows="4" class="form-control" placeholder="Add notes regarding discussion with guardian, demo class time, payment commitments..."><?= e($adminNotes) ?></textarea>
                    </div>

                    <div class="col-12 mt-4 d-flex justify-content-between align-items-center">
                        <?php if ($status !== 'admitted'): ?>
                            <a href="<?= e(base_url('admin/students/create.php?lead_id=' . $id . '&name=' . urlencode($lead['student_name']) . '&phone=' . urlencode($lead['guardian_phone']) . '&class=' . urlencode($lead['class_level']))) ?>" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-person-check-fill me-1"></i> Convert to Enrolled Student
                            </a>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
