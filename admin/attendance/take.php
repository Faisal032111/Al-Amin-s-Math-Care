<?php

/**
 * admin/attendance/take.php — Take Batch Class Attendance
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../../includes/sms.php';

$batchId = (int)($_GET['batch_id'] ?? ($_POST['batch_id'] ?? 0));
$date = clean_input($_GET['date'] ?? ($_POST['date'] ?? date('Y-m-d')));

$pdo = db();

$stmt = $pdo->prepare('SELECT b.*, c.title_en as course_title, t.name_en as teacher_name FROM batches b JOIN courses c ON b.course_id = c.id JOIN teachers t ON b.teacher_id = t.id WHERE b.id = :id AND b.is_deleted = 0 LIMIT 1');
$stmt->execute([':id' => $batchId]);
$batch = $stmt->fetch();

if (!$batch) {
    set_flash('error', 'Batch not found.');
    header('Location: ' . base_url('admin/attendance/'));
    exit;
}

// Fetch all actively enrolled students in this batch
$stuStmt = $pdo->prepare('
    SELECT s.id, s.student_id_code, s.name, s.guardian_name, s.guardian_phone,
           a.status as current_status, a.sms_sent_status
    FROM students s
    JOIN enrollments e ON s.id = e.student_id
    LEFT JOIN attendances a ON s.id = a.student_id AND a.batch_id = :att_bid AND a.attendance_date = :dt
    WHERE e.batch_id = :enr_bid AND e.status = "active" AND s.is_deleted = 0
    ORDER BY s.student_id_code ASC
');
$stuStmt->execute([':att_bid' => $batchId, ':enr_bid' => $batchId, ':dt' => $date]);
$students = $stuStmt->fetchAll();

$errors = [];
$adminPageTitle = 'Take Attendance — ' . $batch['batch_name'];
$adminPageHeading = 'Class Attendance: ' . $batch['batch_name'];
$adminPageSubheading = 'Date: ' . date('d F, Y', strtotime($date)) . ' | Teacher: ' . $batch['teacher_name'];
$activeModule = 'attendance';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $attendanceData = $_POST['attendance'] ?? [];
    $sendSms = isset($_POST['send_sms']);
    $recordedBy = $_SESSION['admin_user_id'] ?? 1;

    if (empty($attendanceData)) {
        $errors[] = 'No attendance statuses received.';
    } else {
        try {
            $pdo->beginTransaction();

            $insertStmt = $pdo->prepare('
                INSERT INTO attendances (student_id, batch_id, attendance_date, status, recorded_by, sms_sent_status, sms_response)
                VALUES (:sid, :bid, :dt, :st, :uid, :sms_st, :sms_resp)
                ON DUPLICATE KEY UPDATE status = VALUES(status), recorded_by = VALUES(recorded_by)
            ');

            $absentStudentsForSms = [];

            foreach ($attendanceData as $studentId => $status) {
                $status = in_array($status, ['present', 'absent', 'late'], true) ? $status : 'present';
                $smsStatus = 'none';
                $smsResponse = null;

                $insertStmt->execute([
                    ':sid' => (int)$studentId,
                    ':bid' => $batchId,
                    ':dt' => $date,
                    ':st' => $status,
                    ':uid' => $recordedBy,
                    ':sms_st' => $smsStatus,
                    ':sms_resp' => $smsResponse,
                ]);

                if ($status === 'absent' && $sendSms) {
                    $absentStudentsForSms[] = (int)$studentId;
                }
            }

            $pdo->commit();

            // Send SMS to parents of absent students if requested
            if (!empty($absentStudentsForSms)) {
                $smsMsgTemplate = "Al Amin's Math Care: Dear Guardian, your child (:student) was absent in today's (:date) mathematics class (:batch). For query: 01520102248";
                
                foreach ($students as $stu) {
                    if (in_array((int)$stu['id'], $absentStudentsForSms, true) && !empty($stu['guardian_phone'])) {
                        $msg = str_replace(
                            [':student', ':date', ':batch'],
                            [$stu['name'], date('d/m/Y', strtotime($date)), $batch['batch_name']],
                            $smsMsgTemplate
                        );

                        $res = sms_send($stu['guardian_phone'], $msg, 'attendance', [
                            'student_id' => $stu['id'],
                            'batch_id' => $batchId,
                            'date' => $date
                        ]);

                        $smsSt = $res['success'] ? 'sent' : 'failed';
                        $pdo->prepare('UPDATE attendances SET sms_sent_status = :st, sms_response = :resp WHERE student_id = :sid AND batch_id = :bid AND attendance_date = :dt')->execute([
                            ':st' => $smsSt,
                            ':resp' => substr($res['message'], 0, 250),
                            ':sid' => $stu['id'],
                            ':bid' => $batchId,
                            ':dt' => $date,
                        ]);
                    }
                }
            }

            set_flash('success', 'Attendance for ' . count($attendanceData) . ' students recorded successfully.');
            header('Location: ' . base_url('admin/attendance/view.php?batch_id=' . $batchId . '&date=' . $date));
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Failed to save attendance: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?= e(base_url('admin/attendance/')) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Register
            </a>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary"><?= e($batch['class_days']) ?></span>
                <span class="badge bg-light text-secondary border"><?= date('h:i A', strtotime($batch['start_time'])) ?></span>
            </div>
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

        <form method="POST" action="">
            <?= csrf_field() ?>
            <input type="hidden" name="batch_id" value="<?= $batchId ?>">
            <input type="hidden" name="date" value="<?= e($date) ?>">

            <div class="card card-custom mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="text-dark">Student Attendance Sheet</strong>
                        <span class="text-muted small ms-2">(<?= count($students) ?> active students enrolled)</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="markAll('present')">Mark All Present</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAll('absent')">Mark All Absent</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student ID & Name</th>
                                <th>Guardian Phone</th>
                                <th class="text-center" style="width: 320px;">Attendance Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        No active students enrolled in this batch.
                                        <div class="mt-2">
                                            <a href="<?= e(base_url('admin/students/create.php?batch_id=' . $batchId)) ?>" class="btn btn-sm btn-primary">Admit Student to this Batch</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $i = 1; foreach ($students as $s): 
                                    $current = $s['current_status'] ?? 'present';
                                ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td>
                                            <span class="badge bg-dark font-monospace me-1"><?= e($s['student_id_code']) ?></span>
                                            <strong class="text-dark"><?= e($s['name']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="small text-secondary"><?= e($s['guardian_phone']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <input type="radio" class="btn-check att-radio" name="attendance[<?= $s['id'] ?>]" id="pres_<?= $s['id'] ?>" value="present" <?= $current === 'present' ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-success px-3" for="pres_<?= $s['id'] ?>">Present</label>

                                                <input type="radio" class="btn-check att-radio" name="attendance[<?= $s['id'] ?>]" id="abs_<?= $s['id'] ?>" value="absent" <?= $current === 'absent' ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-danger px-3" for="abs_<?= $s['id'] ?>">Absent</label>

                                                <input type="radio" class="btn-check att-radio" name="attendance[<?= $s['id'] ?>]" id="late_<?= $s['id'] ?>" value="late" <?= $current === 'late' ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-warning px-3" for="late_<?= $s['id'] ?>">Late</label>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (!empty($students)): ?>
                <div class="card card-custom p-3 mb-4 bg-light">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="send_sms" id="sendSms" value="1" checked>
                            <label class="form-check-label small fw-semibold text-dark" for="sendSms">
                                <i class="bi bi-chat-dots-fill text-primary me-1"></i> Send automated SMS absence alert to guardians of marked absent students
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-check2-circle me-1"></i> Save Class Attendance
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
function markAll(status) {
    document.querySelectorAll('.att-radio[value="' + status + '"]').forEach(function(radio) {
        radio.checked = true;
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
