<?php

/**
 * admin/routines/index.php — Weekly Class Routine Slots
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Class Routines — Admin Panel';
$adminPageHeading = 'Weekly Class Routines & Room Slots';
$adminPageSubheading = 'Manage weekly class schedule timetable, room numbers & routine notes';
$activeModule = 'routines';

$pdo = db();

$batchFilter = (int)($_GET['batch_id'] ?? 0);

$sql = 'SELECT r.*, b.batch_name, c.title_en as course_title, t.name_en as teacher_name
        FROM routines r
        JOIN batches b ON r.batch_id = b.id
        JOIN courses c ON b.course_id = c.id
        JOIN teachers t ON b.teacher_id = t.id
        WHERE r.is_deleted = 0';
$params = [];

if ($batchFilter > 0) {
    $sql .= ' AND r.batch_id = :bid';
    $params[':bid'] = $batchFilter;
}

$sql .= ' ORDER BY FIELD(r.day_of_week, "Saturday", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday"), r.start_time ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$routines = $stmt->fetchAll();

$batches = $pdo->query('SELECT id, batch_name FROM batches WHERE is_deleted = 0 ORDER BY id ASC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card card-custom p-3 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <form method="GET" action="" class="d-flex align-items-center gap-2">
            <select name="batch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Batches Routine</option>
                <?php foreach ($batches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $batchFilter === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['batch_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($batchFilter > 0): ?>
                <a href="<?= e(base_url('admin/routines/')) ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
            <?php endif; ?>
        </form>

        <a href="<?= e(base_url('admin/routines/create.php' . ($batchFilter ? '?batch_id=' . $batchFilter : ''))) ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-calendar-plus me-1"></i> Add Routine Slot
        </a>
    </div>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>Day of Week</th>
                    <th>Batch & Course</th>
                    <th>Time Slot</th>
                    <th>Room</th>
                    <th>Lesson / Topic Notes</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($routines)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No routine slots found. Click "Add Routine Slot" to create one.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($routines as $rt): ?>
                        <tr>
                            <td>
                                <strong class="badge bg-primary fs-6"><?= e($rt['day_of_week']) ?></strong>
                            </td>
                            <td>
                                <strong class="text-dark d-block"><?= e($rt['batch_name']) ?></strong>
                                <small class="text-muted"><?= e($rt['course_title']) ?> (<?= e($rt['teacher_name']) ?>)</small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    <i class="bi bi-clock me-1 text-primary"></i><?= date('h:i A', strtotime($rt['start_time'])) ?> – <?= date('h:i A', strtotime($rt['end_time'])) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= e($rt['room_number'] ?: 'Room-1') ?></span>
                            </td>
                            <td>
                                <span class="small text-secondary"><?= e($rt['notes'] ?: '—') ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= e(base_url('admin/routines/edit.php?id=' . $rt['id'])) ?>" class="btn btn-sm btn-outline-primary py-0 px-2 me-1" title="Edit Slot">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete Slot" onclick="confirmDelete('<?= e(base_url('admin/routines/delete.php')) ?>', <?= $rt['id'] ?>, 'Are you sure you want to delete this routine slot for \'<?= addslashes($rt['batch_name']) ?>\' on <?= $rt['day_of_week'] ?>?')">
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
