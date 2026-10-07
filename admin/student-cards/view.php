<?php

/**
 * admin/student-cards/view.php — Printable Digital Student ID Card
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/i18n.php';
require_once __DIR__ . '/../../includes/helpers.php';

$isAdmin = !empty($_SESSION['admin_logged_in']);
$isPortalStudent = !empty($_SESSION['portal_student_id']);

if (!$isAdmin && !$isPortalStudent) {
    $_SESSION['flash_error'] = 'Please log in to view student card.';
    header('Location: ' . base_url('portal/login.php'));
    exit;
}

$id = (int)($_GET['id'] ?? 0);

// If portal student, strictly enforce their own ID (IDOR prevention)
if (!$isAdmin && $isPortalStudent) {
    $id = (int)$_SESSION['portal_student_id'];
}

$pdo = db();
$stmt = $pdo->prepare('
    SELECT s.*, b.batch_name, c.title_en as course_title, b.class_days
    FROM students s
    LEFT JOIN enrollments e ON s.id = e.student_id AND e.status = "active"
    LEFT JOIN batches b ON e.batch_id = b.id
    LEFT JOIN courses c ON b.course_id = c.id
    WHERE s.id = :id AND s.is_deleted = 0 LIMIT 1
');
$stmt->execute([':id' => $id]);
$student = $stmt->fetch();

if (!$student) {
    if ($isAdmin) {
        $_SESSION['flash_error'] = 'Student not found.';
        header('Location: ' . base_url('admin/student-cards/'));
    } else {
        header('Location: ' . base_url('portal/dashboard.php'));
    }
    exit;
}

$backUrl = $isAdmin ? base_url('admin/student-cards/') : base_url('portal/dashboard.php');
$backLabel = $isAdmin ? 'Back to Cards List' : 'Back to My Dashboard';

// Generate QR Token if not existing
if (empty($student['qr_code_token'])) {
    $qrToken = bin2hex(random_bytes(24));
    $pdo->prepare('UPDATE students SET qr_code_token = :qr WHERE id = :id')->execute([':qr' => $qrToken, ':id' => $id]);
    $student['qr_code_token'] = $qrToken;
}

$verifyUrl = base_url('verify-id.php?token=' . urlencode($student['qr_code_token']));
$qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($verifyUrl);

$pageTitle = 'Student ID Card — ' . $student['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', 'Noto Sans Bengali', sans-serif;
            padding: 2rem 1rem;
        }
        .id-cards-wrapper {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
            max-width: 900px;
            margin: 0 auto;
        }
        .id-card {
            width: 340px;
            height: 520px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 2px solid #e2e8f0;
            position: relative;
        }
        .id-card-header {
            background: linear-gradient(135deg, #112239 0%, #1e3a61 100%);
            color: #ffffff;
            padding: 1.25rem 1rem;
            text-align: center;
            position: relative;
        }
        .id-photo-frame {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 4px solid #ffffff;
            margin: -55px auto 10px;
            overflow: hidden;
            background: #e2e8f0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            z-index: 2;
            position: relative;
        }
        .id-card-back {
            background: #ffffff;
            padding: 1.5rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .id-card {
                box-shadow: none;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

<div class="no-print text-center mb-4">
    <a href="<?= e($backUrl) ?>" class="btn btn-sm btn-outline-secondary me-2">
        <i class="bi bi-arrow-left me-1"></i> <?= e($backLabel) ?>
    </a>
    <button onclick="window.print()" class="btn btn-sm btn-primary px-4 fw-bold">
        <i class="bi bi-printer-fill me-1"></i> Print ID Badge
    </button>
</div>

<div class="id-cards-wrapper">
    <!-- Card Front Side -->
    <div class="id-card">
        <div class="id-card-header pb-5">
            <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                <span class="brand-mark text-warning fw-bold fs-4">∑</span>
                <strong class="text-white fs-5">Al Amin's Math Care</strong>
            </div>
            <div class="text-warning small" style="font-size: 0.75rem; letter-spacing: 0.05em;">STUDENT IDENTITY CARD</div>
        </div>

        <div class="id-photo-frame">
            <?php if (!empty($student['photo']) && file_exists(__DIR__ . '/../../' . $student['photo'])): ?>
                <img src="<?= e(base_url($student['photo'])) ?>" alt="" class="w-100 h-100 object-fit-cover">
            <?php else: ?>
                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-secondary text-white fs-1 fw-bold">
                    <?= strtoupper(substr($student['name'], 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="p-3 text-center flex-grow-1 d-flex flex-column justify-content-between">
            <div>
                <h5 class="fw-bold text-dark mb-1"><?= e($student['name']) ?></h5>
                <div class="badge bg-dark font-monospace mb-2" style="font-size: 0.85rem;"><?= e($student['student_id_code']) ?></div>
                <div class="small text-muted mb-2"><?= e($student['school_college'] ?: 'Student') ?></div>
            </div>

            <div class="bg-light p-2 rounded-3 text-start small border mb-2" style="font-size: 0.8rem;">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Class:</span>
                    <strong class="text-dark"><?= e($student['class_level']) ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Batch:</span>
                    <span class="text-primary fw-semibold"><?= e($student['batch_name'] ?: 'General Math') ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Guardian Phone:</span>
                    <span class="text-dark"><?= e($student['guardian_phone']) ?></span>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-end pt-2 border-top">
                <div class="text-start" style="font-size: 0.65rem;">
                    <span class="text-muted d-block">Issued By:</span>
                    <strong class="text-dark">Farmgate Campus</strong>
                </div>
                <div class="text-end" style="font-size: 0.65rem;">
                    <span class="text-muted d-block">Authorized Sign:</span>
                    <strong class="text-primary">Al Amin Sir</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Back Side (QR Code & Guidelines) -->
    <div class="id-card id-card-back">
        <div>
            <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                <span class="brand-mark text-warning fw-bold fs-4">∑</span>
                <strong class="text-dark">Al Amin's Math Care</strong>
            </div>
            <p class="text-muted" style="font-size: 0.75rem;">Scan this QR code with any smartphone to verify the student's authentic status on our official portal.</p>
            
            <div class="p-2 border rounded-3 d-inline-block bg-light shadow-sm mb-3">
                <img src="<?= e($qrApiUrl) ?>" alt="QR Code" style="width: 130px; height: 130px;">
            </div>

            <div class="font-monospace text-muted small mb-3" style="font-size: 0.7rem;">
                verify-id.php?token=<?= substr($student['qr_code_token'], 0, 16) ?>...
            </div>
        </div>

        <div class="bg-light p-2 rounded-3 text-muted text-start border" style="font-size: 0.72rem; line-height: 1.4;">
            <strong>Instructions:</strong>
            <ul class="mb-0 ps-3">
                <li>Always carry this card during coaching classes.</li>
                <li>Report immediately if lost or misplaced.</li>
                <li>Contact Hotline: 01520102248</li>
            </ul>
        </div>

        <div class="border-top pt-2 text-muted" style="font-size: 0.68rem;">
            46/1, Britter Goli, Opp. Holy Cross College, Farmgate, Dhaka 1216
        </div>
    </div>
</div>

</body>
</html>
