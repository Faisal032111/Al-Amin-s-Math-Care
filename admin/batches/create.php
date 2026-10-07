<?php

/**
 * admin/batches/create.php — Create New Batch
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Create Batch — Admin Panel';
$adminPageHeading = 'Create New Mathematics Batch';
$adminPageSubheading = 'Define batch name, class days, timing, seat limits & admission status';
$activeModule = 'batches';

$pdo = db();
$courses = $pdo->query('SELECT id, title_en, class_level FROM courses WHERE is_deleted = 0 AND is_active = 1 ORDER BY id ASC')->fetchAll();
$teachers = $pdo->query('SELECT id, name_en FROM teachers WHERE is_deleted = 0 ORDER BY id ASC')->fetchAll();
$branches = $pdo->query('SELECT id, name FROM branches WHERE status = "active" ORDER BY id ASC')->fetchAll();

$errors = [];
$batchName = '';
$batchNameBn = '';
$courseId = 1;
$branchId = 1;
$teacherId = 1;
$classDays = 'Sat,Mon,Wed';
$startTime = '08:00';
$endTime = '09:30';
$startDate = date('Y-m-d');
$totalSeats = 30;
$availableSeats = 30;
$admissionStatus = 'open';
$status = 'upcoming';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $batchName = clean_input($_POST['batch_name'] ?? '');
    $batchNameBn = clean_input($_POST['batch_name_bn'] ?? '');
    $courseId = (int)($_POST['course_id'] ?? 0);
    $branchId = (int)($_POST['branch_id'] ?? 1);
    $teacherId = (int)($_POST['teacher_id'] ?? 1);
    $classDays = clean_input($_POST['class_days'] ?? '');
    $startTime = clean_input($_POST['start_time'] ?? '');
    $endTime = clean_input($_POST['end_time'] ?? '');
    $startDate = clean_input($_POST['start_date'] ?? date('Y-m-d'));
    $totalSeats = (int)($_POST['total_seats'] ?? 30);
    $availableSeats = (int)($_POST['available_seats'] ?? $totalSeats);
    $admissionStatus = clean_input($_POST['admission_status'] ?? 'open');
    $status = clean_input($_POST['status'] ?? 'upcoming');

    if (empty($batchName)) {
        $errors[] = 'Batch Name is required.';
    }
    if ($courseId <= 0) {
        $errors[] = 'Please select a valid mathematics course.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO batches (course_id, branch_id, teacher_id, batch_name, batch_name_bn, class_days, start_time, end_time, start_date, total_seats, available_seats, admission_status, status) VALUES (:cid, :bid, :tid, :bn, :bnb, :cd, :st, :et, :sd, :ts, :as, :adms, :stat)');
            $stmt->execute([
                ':cid' => $courseId,
                ':bid' => $branchId,
                ':tid' => $teacherId,
                ':bn' => $batchName,
                ':bnb' => $batchNameBn,
                ':cd' => $classDays,
                ':st' => $startTime,
                ':et' => $endTime,
                ':sd' => $startDate,
                ':ts' => $totalSeats,
                ':as' => $availableSeats,
                ':adms' => $admissionStatus,
                ':stat' => $status,
            ]);

            set_flash('success', "Batch '{$batchName}' created successfully.");
            header('Location: ' . base_url('admin/batches/'));
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?= e(base_url('admin/batches/')) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Batches
            </a>
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

        <div class="card card-custom p-4">
            <form method="POST" action="">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Batch Name (English) <span class="text-danger">*</span></label>
                        <input type="text" name="batch_name" class="form-control" value="<?= e($batchName) ?>" required placeholder="e.g. SSC-2027 Morning Batch">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Batch Name (Bangla / বাংলা)</label>
                        <input type="text" name="batch_name_bn" class="form-control" value="<?= e($batchNameBn) ?>" placeholder="যেমন: এসএসসি-২০২৭ মর্নিং ব্যাচ">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Associated Math Course <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-select" required>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $courseId === (int)$c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['title_en']) ?> (<?= e($c['class_level']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Teacher Assigned</label>
                        <select name="teacher_id" class="form-select">
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= $teacherId === (int)$t['id'] ? 'selected' : '' ?>><?= e($t['name_en']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Class Days <span class="text-danger">*</span></label>
                        <input type="text" name="class_days" class="form-control" value="<?= e($classDays) ?>" required placeholder="Sat,Mon,Wed or Sun,Tue,Thu">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Class Start Time <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" class="form-control" value="<?= e($startTime) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Class End Time <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" class="form-control" value="<?= e($endTime) ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Batch Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= e($startDate) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Total Seats (Capacity)</label>
                        <input type="number" name="total_seats" class="form-control" value="<?= e((string)$totalSeats) ?>" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Available Seats (Remaining)</label>
                        <input type="number" name="available_seats" class="form-control" value="<?= e((string)$availableSeats) ?>" min="0" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Admission Status</label>
                        <select name="admission_status" class="form-select">
                            <option value="open" <?= $admissionStatus === 'open' ? 'selected' : '' ?>>Open (Admissions Ongoing)</option>
                            <option value="full" <?= $admissionStatus === 'full' ? 'selected' : '' ?>>Full (Houseful)</option>
                            <option value="closed" <?= $admissionStatus === 'closed' ? 'selected' : '' ?>>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Batch Academic Status</label>
                        <select name="status" class="form-select">
                            <option value="upcoming" <?= $status === 'upcoming' ? 'selected' : '' ?>>Upcoming (Starting soon)</option>
                            <option value="running" <?= $status === 'running' ? 'selected' : '' ?>>Running (Currently in session)</option>
                            <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Create Batch
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
