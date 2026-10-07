<?php

/**
 * admin/routines/create.php — Add Class Routine Slot
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Add Routine Slot — Admin Panel';
$adminPageHeading = 'Add Class Routine Slot';
$adminPageSubheading = 'Define weekly routine day, time slot, room allocation & subject focus';
$activeModule = 'routines';

$pdo = db();
$preBatchId = (int)($_GET['batch_id'] ?? 0);

$batches = $pdo->query('SELECT id, batch_name, class_days, start_time, end_time FROM batches WHERE is_deleted = 0 ORDER BY id ASC')->fetchAll();

$errors = [];
$batchId = $preBatchId;
$dayOfWeek = 'Saturday';
$startTime = '08:00';
$endTime = '09:30';
$roomNumber = 'Room-1';
$notes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $batchId = (int)($_POST['batch_id'] ?? 0);
    $dayOfWeek = clean_input($_POST['day_of_week'] ?? 'Saturday');
    $startTime = clean_input($_POST['start_time'] ?? '');
    $endTime = clean_input($_POST['end_time'] ?? '');
    $roomNumber = clean_input($_POST['room_number'] ?? 'Room-1');
    $notes = clean_input($_POST['notes'] ?? '');

    if ($batchId <= 0) {
        $errors[] = 'Please select a valid batch.';
    }
    if (empty($startTime) || empty($endTime)) {
        $errors[] = 'Start and End times are required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO routines (batch_id, day_of_week, start_time, end_time, room_number, notes) VALUES (:bid, :dow, :st, :et, :room, :notes)');
            $stmt->execute([
                ':bid' => $batchId,
                ':dow' => $dayOfWeek,
                ':st' => $startTime,
                ':et' => $endTime,
                ':room' => $roomNumber,
                ':notes' => $notes,
            ]);

            set_flash('success', "Routine slot for {$dayOfWeek} created successfully.");
            header('Location: ' . base_url('admin/routines/?batch_id=' . $batchId));
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?= e(base_url('admin/routines/')) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Routines
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
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-secondary">Select Academic Batch <span class="text-danger">*</span></label>
                        <select name="batch_id" class="form-select" required onchange="autoFillTimes(this)">
                            <option value="">-- Choose Batch --</option>
                            <?php foreach ($batches as $b): ?>
                                <option value="<?= $b['id'] ?>" data-st="<?= date('H:i', strtotime($b['start_time'])) ?>" data-et="<?= date('H:i', strtotime($b['end_time'])) ?>" <?= $batchId === (int)$b['id'] ? 'selected' : '' ?>>
                                    <?= e($b['batch_name']) ?> (<?= e($b['class_days']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Day of Week <span class="text-danger">*</span></label>
                        <select name="day_of_week" class="form-select" required>
                            <option value="Saturday" <?= $dayOfWeek === 'Saturday' ? 'selected' : '' ?>>Saturday (শনিবার)</option>
                            <option value="Sunday" <?= $dayOfWeek === 'Sunday' ? 'selected' : '' ?>>Sunday (রবিবার)</option>
                            <option value="Monday" <?= $dayOfWeek === 'Monday' ? 'selected' : '' ?>>Monday (সোমবার)</option>
                            <option value="Tuesday" <?= $dayOfWeek === 'Tuesday' ? 'selected' : '' ?>>Tuesday (মঙ্গলবার)</option>
                            <option value="Wednesday" <?= $dayOfWeek === 'Wednesday' ? 'selected' : '' ?>>Wednesday (বুধবার)</option>
                            <option value="Thursday" <?= $dayOfWeek === 'Thursday' ? 'selected' : '' ?>>Thursday (বৃহস্পতিবার)</option>
                            <option value="Friday" <?= $dayOfWeek === 'Friday' ? 'selected' : '' ?>>Friday (শুক্রবার - Special/Exam)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Room / Classroom</label>
                        <input type="text" name="room_number" class="form-control" value="<?= e($roomNumber) ?>" placeholder="e.g. Room-1 or Main Hall">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Class Start Time <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" id="routineStart" class="form-control" value="<?= e($startTime) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Class End Time <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" id="routineEnd" class="form-control" value="<?= e($endTime) ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Topic / Session Notes (Optional)</label>
                        <input type="text" name="notes" class="form-control" value="<?= e($notes) ?>" placeholder="e.g. Geometry CQ Practice & Board Question Drill">
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-calendar-check me-1"></i> Save Routine Slot
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function autoFillTimes(select) {
    const opt = select.options[select.selectedIndex];
    const st = opt.getAttribute('data-st');
    const et = opt.getAttribute('data-et');
    if (st && et) {
        document.getElementById('routineStart').value = st;
        document.getElementById('routineEnd').value = et;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
