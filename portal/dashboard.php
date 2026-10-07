<?php

/**
 * portal/dashboard.php — Student & Guardian Portal Dashboard
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auth guard for portal
if (empty($_SESSION['portal_student_id'])) {
    header('Location: ' . base_url('portal/login.php'));
    exit;
}

require_once __DIR__ . '/../includes/i18n.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

$pdo       = db();
$studentId = (int)$_SESSION['portal_student_id'];

// Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['portal_student_id'], $_SESSION['portal_student_name'], $_SESSION['portal_student_code'], $_SESSION['portal_class_level']);
    header('Location: ' . base_url('portal/login.php'));
    exit;
}

// ── Fetch student + branch ──────────────────────────────────────────────────
$stmtStudent = $pdo->prepare(
    'SELECT s.*, b.name AS branch_name
     FROM students s
     LEFT JOIN branches b ON b.is_main = 1
     WHERE s.id = :id AND s.is_deleted = 0 LIMIT 1'
);
$stmtStudent->execute([':id' => $studentId]);
$student = $stmtStudent->fetch();

if (!$student || $student['status'] !== 'active') {
    session_destroy();
    header('Location: ' . base_url('portal/login.php'));
    exit;
}

// Active enrollment with batch info
$stmtEnroll = $pdo->prepare(
    'SELECT e.*, b2.batch_name, b2.class_days, b2.start_time, b2.end_time,
            c.title_en AS course_title_en, c.title_bn AS course_title_bn, c.class_level
     FROM enrollments e
     JOIN batches b2 ON b2.id = e.batch_id
     JOIN courses c  ON c.id  = b2.course_id
     WHERE e.student_id = :sid AND e.status = \'active\'
     ORDER BY e.id DESC LIMIT 1'
);
$stmtEnroll->execute([':sid' => $studentId]);
$enrollment = $stmtEnroll->fetch();

// Fee records (most recent 12 months)
$stmtFees = $pdo->prepare(
    'SELECT * FROM student_fees
     WHERE student_id = :sid
     ORDER BY fee_month DESC LIMIT 12'
);
$stmtFees->execute([':sid' => $studentId]);
$feeRecords = $stmtFees->fetchAll();

// Payment receipts (most recent 10)
$stmtReceipts = $pdo->prepare(
    'SELECT fr.*, e2.batch_id
     FROM fee_records fr
     LEFT JOIN enrollments e2 ON e2.id = fr.enrollment_id
     WHERE fr.student_id = :sid
     ORDER BY fr.created_at DESC LIMIT 10'
);
$stmtReceipts->execute([':sid' => $studentId]);
$receipts = $stmtReceipts->fetchAll();

// Attendance summary (current month)
$thisMonth      = date('Y-m');
$stmtAttendance = $pdo->prepare(
    'SELECT
        SUM(status = \'present\') AS present_count,
        SUM(status = \'absent\')  AS absent_count,
        SUM(status = \'late\')    AS late_count,
        COUNT(*) AS total_days
     FROM attendances
     WHERE student_id = :sid
       AND DATE_FORMAT(attendance_date, \'%Y-%m\') = :month'
);
$stmtAttendance->execute([':sid' => $studentId, ':month' => $thisMonth]);
$attendance = $stmtAttendance->fetch();

// Outstanding dues calculation
$totalDue = 0;
foreach ($feeRecords as $fr) {
    if ($fr['status'] !== 'paid') {
        $totalDue += (float)$fr['due_amount'];
    }
}

// Institute Contact Info from settings
$stmtSettings = $pdo->query('SELECT `key`, `value` FROM site_settings');
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
$contactPhone    = $settings['phone_primary']   ?? '01520102248';
$contactWhatsapp = $settings['whatsapp_number'] ?? '01520102248';

$pageTitle = ($locale === 'bn' ? 'স্টুডেন্ট ড্যাশবোর্ড' : 'Student Dashboard') . ' — ' . e($student['name']) . ' | Al Amin\'s Math Care';
?>
<!DOCTYPE html>
<html lang="<?= e($locale) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800;900&family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --brand-navy:    #0B192C;
            --brand-deep:    #1E3E62;
            --brand-cyan:    #008DDA;
            --brand-green:   #10B981;
            --brand-red:     #EF4444;
            --brand-amber:   #F59E0B;
            --bg-page:       #F8FAFC;
            --card-border:   #E2E8F0;
        }
        body {
            font-family: 'Inter', 'Hind Siliguri', 'Noto Sans Bengali', sans-serif;
            background: #F1F5F9;
            color: #1E293B;
            min-height: 100vh;
        }

        /* Top Navigation */
        .portal-nav {
            background: linear-gradient(135deg, var(--brand-navy) 0%, #112239 100%);
            padding: 0.85rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .portal-nav .brand {
            color: #FFFFFF;
            font-weight: 800;
            font-size: 1.1rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .portal-nav .brand-mark {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--brand-cyan) 0%, #005691 100%);
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-weight: 800;
            font-size: 1.1rem;
        }
        .portal-nav .user-badge {
            background: rgba(255, 255, 255, 0.12);
            color: #FFFFFF;
            border-radius: 2rem;
            padding: 0.35rem 0.95rem;
            font-size: 0.84rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .portal-nav .nav-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #FFFFFF;
            border-radius: 0.5rem;
            padding: 0.35rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.15s;
        }
        .portal-nav .nav-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #FFFFFF;
        }

        /* Hero Profile Card */
        .student-hero-card {
            background: linear-gradient(135deg, var(--brand-navy) 0%, #1E3E62 100%);
            border-radius: 1.25rem;
            color: #FFFFFF;
            padding: 1.75rem 2rem;
            box-shadow: 0 10px 30px rgba(11, 25, 44, 0.15);
            position: relative;
            overflow: hidden;
        }
        .student-hero-card::after {
            content: "∑";
            position: absolute;
            right: -10px;
            bottom: -30px;
            font-size: 10rem;
            font-weight: 900;
            color: rgba(255, 255, 255, 0.04);
            pointer-events: none;
        }

        /* Stat Cards */
        .stat-card {
            background: #FFFFFF;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            border: 1px solid var(--card-border);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            gap: 1.1rem;
            height: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }
        .stat-card .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .stat-card .stat-value {
            font-size: 1.55rem;
            font-weight: 800;
            line-height: 1.1;
        }
        .stat-card .stat-label {
            font-size: 0.78rem;
            color: #64748B;
            font-weight: 600;
            margin-top: 0.2rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        /* Section Cards */
        .section-card {
            background: #FFFFFF;
            border-radius: 1rem;
            border: 1px solid var(--card-border);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            padding: 1.5rem;
            height: 100%;
        }
        .section-title {
            font-size: 0.88rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            margin-bottom: 1.25rem;
            padding-bottom: 0.6rem;
            border-bottom: 1px solid #F1F5F9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Table styles */
        .portal-table {
            width: 100%;
            border-collapse: collapse;
        }
        .portal-table th {
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 0.65rem 0.75rem;
            border-bottom: 2px solid #E2E8F0;
            background: #F8FAFC;
        }
        .portal-table td {
            padding: 0.75rem;
            font-size: 0.88rem;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
        }
        .portal-table tr:hover td {
            background: #F8FAFC;
        }

        /* Attendance Ring */
        .attendance-ring-wrap {
            position: relative;
            width: 96px;
            height: 96px;
            flex-shrink: 0;
        }
        .attendance-ring-wrap svg {
            transform: rotate(-90deg);
        }
        .attendance-pct {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.15rem;
            font-weight: 800;
            color: #0F172A;
        }

        /* Status Badges */
        .badge-paid {
            background: #D1FAE5;
            color: #065F46;
            font-weight: 700;
        }
        .badge-due {
            background: #FEE2E2;
            color: #991B1B;
            font-weight: 700;
        }
        .badge-partial {
            background: #FEF3C7;
            color: #92400E;
            font-weight: 700;
        }

        /* Action Buttons */
        .btn-action-sm {
            padding: 0.3rem 0.65rem;
            font-size: 0.78rem;
            font-weight: 600;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>

<!-- Top Navigation -->
<nav class="portal-nav sticky-top">
    <div class="container-lg d-flex align-items-center justify-content-between">
        <a href="<?= e(base_url('')) ?>" class="brand">
            <span class="brand-mark">∑</span>
            <span>Al Amin's Math Care</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <?php if ($locale === 'bn'): ?>
                <a href="?lang=en" class="nav-btn d-none d-sm-inline-block">EN</a>
            <?php else: ?>
                <a href="?lang=bn" class="nav-btn d-none d-sm-inline-block">বাং</a>
            <?php endif; ?>

            <div class="user-badge">
                <i class="bi bi-person-circle text-info"></i>
                <span class="d-none d-md-inline"><?= e($student['name']) ?></span>
                <span class="badge bg-dark font-monospace text-white-50" style="font-size: 0.72rem;"><?= e($student['student_id_code']) ?></span>
            </div>

            <a href="?logout=1" class="nav-btn" onclick="return confirm('<?= $locale === 'bn' ? 'আপনি কি পোর্টাল থেকে লগআউট করতে চান?' : 'Logout from the portal?' ?>')">
                <i class="bi bi-box-arrow-right me-1"></i> <span class="d-none d-sm-inline"><?= $locale === 'bn' ? 'লগআউট' : 'Logout' ?></span>
            </a>
        </div>
    </div>
</nav>

<!-- Main Container -->
<div class="container-lg py-4">

    <!-- Student Hero Profile Banner -->
    <div class="student-hero-card mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-7 d-flex align-items-center gap-3">
                <?php if (!empty($student['photo']) && file_exists(__DIR__ . '/../' . $student['photo'])): ?>
                    <img src="<?= e(base_url($student['photo'])) ?>" alt="Student Photo" width="68" height="68"
                         class="rounded-circle object-fit-cover border border-3 border-info shadow-sm">
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-info text-dark fw-bold shadow-sm"
                         style="width:68px; height:68px; font-size:1.6rem; flex-shrink:0;">
                        <?= mb_strtoupper(mb_substr($student['name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>

                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <h4 class="fw-bold mb-0 text-white"><?= e($student['name']) ?></h4>
                        <span class="badge bg-success px-2 py-1" style="font-size: 0.75rem;">Active Student</span>
                    </div>
                    <div class="text-white-50 small d-flex align-items-center gap-3 flex-wrap">
                        <span><i class="bi bi-card-heading text-info me-1"></i><strong>ID:</strong> <?= e($student['student_id_code']) ?></span>
                        <span><i class="bi bi-mortarboard text-info me-1"></i><strong>Class:</strong> <?= e($student['class_level']) ?></span>
                        <?php if (!empty($student['school_college'])): ?>
                            <span><i class="bi bi-building text-info me-1"></i><?= e($student['school_college']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Action Links -->
            <div class="col-md-5 text-md-end">
                <div class="d-inline-flex gap-2 flex-wrap justify-content-md-end">
                    <a href="<?= e(base_url('admin/student-cards/view.php?id=' . $student['id'])) ?>" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3 py-2 fw-semibold">
                        <i class="bi bi-person-badge me-1"></i> <?= $locale === 'bn' ? 'ডিজিটাল আইডি কার্ড' : 'Digital ID Card' ?>
                    </a>
                    <a href="<?= e(base_url('results-lookup.php')) ?>" class="btn btn-sm btn-info text-dark rounded-pill px-3 py-2 fw-bold">
                        <i class="bi bi-trophy me-1"></i> <?= $locale === 'bn' ? 'পরীক্ষার রেজাল্ট' : 'Exam Results' ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat KPI Row -->
    <div class="row g-3 mb-4">
        <!-- Outstanding Due -->
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#FEE2E2; color:#DC2626;">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <div class="stat-value <?= $totalDue > 0 ? 'text-danger' : 'text-success' ?>">
                        ৳<?= number_format($totalDue, 0) ?>
                    </div>
                    <div class="stat-label"><?= $locale === 'bn' ? 'মোট বকেয়া ফি' : 'Total Due Fee' ?></div>
                </div>
            </div>
        </div>

        <!-- Present Count -->
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#D1FAE5; color:#059669;">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
                <div>
                    <div class="stat-value text-success"><?= (int)($attendance['present_count'] ?? 0) ?></div>
                    <div class="stat-label"><?= $locale === 'bn' ? 'উপস্থিতি (' . date('M') . ')' : 'Present (' . date('M') . ')' ?></div>
                </div>
            </div>
        </div>

        <!-- Absent Count -->
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#FEF3C7; color:#D97706;">
                    <i class="bi bi-calendar-x-fill"></i>
                </div>
                <div>
                    <div class="stat-value text-warning"><?= (int)($attendance['absent_count'] ?? 0) ?></div>
                    <div class="stat-label"><?= $locale === 'bn' ? 'অনুপস্থিতি (' . date('M') . ')' : 'Absent (' . date('M') . ')' ?></div>
                </div>
            </div>
        </div>

        <!-- Current Enrolled Batch -->
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#E0F2FE; color:#0284C7;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="stat-value text-truncate" style="font-size:1.05rem; color:#0284C7; max-width: 140px;">
                        <?= $enrollment ? e($enrollment['batch_name']) : 'N/A' ?>
                    </div>
                    <div class="stat-label"><?= $locale === 'bn' ? 'বর্তমান ব্যাচ' : 'Current Batch' ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main 2-Column Content Grid -->
    <div class="row g-4">

        <!-- Left Column: Fee Ledger & Receipts -->
        <div class="col-lg-7">

            <!-- Monthly Fee Ledger -->
            <div class="section-card mb-4">
                <div class="section-title">
                    <span><i class="bi bi-receipt-cutoff text-primary me-2"></i><?= $locale === 'bn' ? 'মাসিক ফি বিবরণী' : 'Monthly Fee Ledger' ?></span>
                    <span class="badge bg-light text-secondary border"><?= date('Y') ?></span>
                </div>

                <?php if (empty($feeRecords)): ?>
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-file-earmark-x fs-2 d-block mb-1 opacity-50"></i>
                        <?= $locale === 'bn' ? 'কোনো মাসিক ফি রেকর্ড পাওয়া যায়নি।' : 'No fee records found.' ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="portal-table">
                            <thead>
                                <tr>
                                    <th><?= $locale === 'bn' ? 'মাস' : 'Month' ?></th>
                                    <th><?= $locale === 'bn' ? 'ফি' : 'Fee' ?></th>
                                    <th><?= $locale === 'bn' ? 'পরিশোধিত' : 'Paid' ?></th>
                                    <th><?= $locale === 'bn' ? 'বকেয়া' : 'Due' ?></th>
                                    <th class="text-center"><?= $locale === 'bn' ? 'স্ট্যাটাস' : 'Status' ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($feeRecords as $fee): ?>
                                    <tr>
                                        <td class="fw-bold text-dark">
                                            <?= e(date('F Y', strtotime($fee['fee_month'] . '-01'))) ?>
                                        </td>
                                        <td class="fw-semibold">৳<?= number_format((float)$fee['fee_amount'], 0) ?></td>
                                        <td class="text-success fw-bold">৳<?= number_format((float)$fee['paid_amount'], 0) ?></td>
                                        <td class="<?= (float)$fee['due_amount'] > 0 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                            ৳<?= number_format((float)$fee['due_amount'], 0) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php $badgeClass = match($fee['status']) {
                                                'paid'    => 'badge-paid',
                                                'partial' => 'badge-partial',
                                                default   => 'badge-due',
                                            }; ?>
                                            <span class="badge rounded-pill px-2 py-1 <?= $badgeClass ?>">
                                                <?= ucfirst($fee['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Payment Receipts List -->
            <div class="section-card">
                <div class="section-title">
                    <span><i class="bi bi-file-earmark-check-fill text-success me-2"></i><?= $locale === 'bn' ? 'পেমেন্ট মানি রিসিট' : 'Payment Money Receipts' ?></span>
                    <span class="badge bg-light text-secondary border"><?= count($receipts) ?> Receipts</span>
                </div>

                <?php if (empty($receipts)): ?>
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-receipt fs-2 d-block mb-1 opacity-50"></i>
                        <?= $locale === 'bn' ? 'কোনো পেমেন্ট রিসিট পাওয়া যায়নি।' : 'No payment receipts found.' ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="portal-table">
                            <thead>
                                <tr>
                                    <th><?= $locale === 'bn' ? 'রিসিট নং' : 'Receipt No.' ?></th>
                                    <th><?= $locale === 'bn' ? 'পরিমাণ' : 'Amount' ?></th>
                                    <th><?= $locale === 'bn' ? 'তারিখ' : 'Date' ?></th>
                                    <th class="text-center"><?= $locale === 'bn' ? 'অ্যাকশন' : 'Action' ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($receipts as $r): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-primary font-monospace"><?= e($r['receipt_no']) ?></span>
                                            <span class="badge bg-light text-dark border ms-1" style="font-size:0.7rem;"><?= strtoupper(e($r['payment_method'])) ?></span>
                                        </td>
                                        <td class="fw-bold text-success">৳<?= number_format((float)$r['amount_paid'], 0) ?></td>
                                        <td class="text-muted small">
                                            <?= date('d M Y', strtotime(!empty($r['payment_date']) ? $r['payment_date'] : $r['created_at'])) ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= e(base_url('admin/fee-ledger/receipt.php?receipt_no=' . urlencode($r['receipt_no']))) ?>"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-primary btn-action-sm">
                                                <i class="bi bi-printer-fill me-1"></i> <?= $locale === 'bn' ? 'রিসিট দেখুন' : 'View' ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Right Column: Attendance & Batch Info -->
        <div class="col-lg-5">

            <!-- Attendance Donut Chart -->
            <div class="section-card mb-4">
                <div class="section-title">
                    <span><i class="bi bi-pie-chart-fill text-info me-2"></i><?= $locale === 'bn' ? 'ক্লাস উপস্থিতি (' . date('F Y') . ')' : 'Attendance (' . date('F Y') . ')' ?></span>
                </div>

                <?php
                    $totalDays  = max(1, (int)($attendance['total_days'] ?? 1));
                    $presentPct = round(((int)($attendance['present_count'] ?? 0) / $totalDays) * 100);
                    $r = 40; $c = 2 * M_PI * $r;
                    $dash = round(($presentPct / 100) * $c, 2);
                    $gap  = round($c - $dash, 2);
                ?>
                <div class="d-flex align-items-center gap-4 py-2">
                    <div class="attendance-ring-wrap">
                        <svg width="96" height="96" viewBox="0 0 96 96">
                            <circle cx="48" cy="48" r="<?= $r ?>" fill="none" stroke="#E2E8F0" stroke-width="10"/>
                            <circle cx="48" cy="48" r="<?= $r ?>" fill="none"
                                    stroke="<?= $presentPct >= 80 ? '#10B981' : ($presentPct >= 65 ? '#F59E0B' : '#EF4444') ?>"
                                    stroke-width="10"
                                    stroke-dasharray="<?= $dash ?> <?= $gap ?>"
                                    stroke-linecap="round"/>
                        </svg>
                        <div class="attendance-pct"><?= $presentPct ?>%</div>
                    </div>

                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                            <span class="small text-muted"><span class="badge bg-success me-1">●</span><?= $locale === 'bn' ? 'উপস্থিত' : 'Present' ?></span>
                            <strong class="text-success"><?= (int)($attendance['present_count'] ?? 0) ?> <?= $locale === 'bn' ? 'দিন' : 'days' ?></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                            <span class="small text-muted"><span class="badge bg-danger me-1">●</span><?= $locale === 'bn' ? 'অনুপস্থিত' : 'Absent' ?></span>
                            <strong class="text-danger"><?= (int)($attendance['absent_count'] ?? 0) ?> <?= $locale === 'bn' ? 'দিন' : 'days' ?></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted"><span class="badge bg-warning me-1">●</span><?= $locale === 'bn' ? 'দেরি' : 'Late' ?></span>
                            <strong class="text-warning"><?= (int)($attendance['late_count'] ?? 0) ?> <?= $locale === 'bn' ? 'দিন' : 'days' ?></strong>
                        </div>
                    </div>
                </div>

                <?php if ($presentPct < 75): ?>
                    <div class="alert alert-warning py-2 small mt-3 mb-0 border-0" style="background:#FEF3C7; color:#92400E;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <?= $locale === 'bn' 
                            ? 'আপনার উপস্থিতি <strong>৭৫%</strong> এর নিচে। গণিতে ভালো ফলাফলের জন্য নিয়মিত ক্লাসে উপস্থিত থাকুন।'
                            : 'Attendance is below <strong>75%</strong>. Regular class attendance is vital for math mastery.' ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Enrollment Details -->
            <?php if ($enrollment): ?>
                <div class="section-card mb-4">
                    <div class="section-title">
                        <span><i class="bi bi-mortarboard-fill text-primary me-2"></i><?= $locale === 'bn' ? 'এনরোলমেন্ট তথ্য' : 'Enrollment Info' ?></span>
                    </div>

                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="small text-muted mb-1"><?= $locale === 'bn' ? 'কোর্স' : 'Enrolled Course' ?></div>
                        <h6 class="fw-bold text-dark mb-0">
                            <?= $locale === 'bn' && !empty($enrollment['course_title_bn']) ? e($enrollment['course_title_bn']) : e($enrollment['course_title_en']) ?>
                        </h6>
                    </div>

                    <table class="w-100" style="font-size:0.86rem; border-collapse:collapse;">
                        <tr>
                            <td class="text-muted py-1.5"><i class="bi bi-people me-1"></i><?= $locale === 'bn' ? 'ব্যাচ' : 'Batch' ?></td>
                            <td class="text-end fw-semibold py-1.5"><?= e($enrollment['batch_name']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1.5"><i class="bi bi-calendar-event me-1"></i><?= $locale === 'bn' ? 'ক্লাসের দিন' : 'Class Days' ?></td>
                            <td class="text-end fw-semibold py-1.5"><?= e($enrollment['class_days']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1.5"><i class="bi bi-clock me-1"></i><?= $locale === 'bn' ? 'সময়সূচী' : 'Time' ?></td>
                            <td class="text-end fw-semibold py-1.5">
                                <?= date('h:i A', strtotime($enrollment['start_time'])) ?> – <?= date('h:i A', strtotime($enrollment['end_time'])) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1.5"><i class="bi bi-calendar-plus me-1"></i><?= $locale === 'bn' ? 'ভর্তির তারিখ' : 'Enrolled On' ?></td>
                            <td class="text-end fw-semibold py-1.5"><?= date('d M Y', strtotime($enrollment['enrollment_date'])) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1.5"><i class="bi bi-shield-check me-1"></i><?= $locale === 'bn' ? 'পেমেন্ট অবস্থা' : 'Fee Status' ?></td>
                            <td class="text-end py-1.5">
                                <?php $ps = $enrollment['payment_status']; ?>
                                <span class="badge rounded-pill px-2.5 py-1 <?= $ps === 'paid' ? 'badge-paid' : ($ps === 'partially_paid' ? 'badge-partial' : 'badge-due') ?>">
                                    <?= ucwords(str_replace('_', ' ', $ps)) ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Contact / Support Card -->
            <div class="section-card" style="background: linear-gradient(135deg, #F0F9FF 0%, #E0F2FE 100%); border-color: #BAE6FD;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:42px; height:42px; font-size:1.2rem;">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><?= $locale === 'bn' ? 'শিক্ষক ও সহায়তা কেন্দ্র' : 'Teacher & Help Desk' ?></h6>
                        <div class="small text-muted">Farmgate, Dhaka 1216</div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="tel:<?= e($contactPhone) ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                        <i class="bi bi-telephone-fill me-1"></i> <?= $locale === 'bn' ? 'সরাসরি কল করুন' : 'Call Office' ?> (<?= e($contactPhone) ?>)
                    </a>
                    <a href="https://wa.me/88<?= preg_replace('/\D/', '', $contactWhatsapp) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-success fw-semibold">
                        <i class="bi bi-whatsapp me-1"></i> <?= $locale === 'bn' ? 'হোয়াটসঅ্যাপে যোগাযোগ' : 'WhatsApp Support' ?>
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- Portal Footer -->
    <footer class="text-center text-muted small mt-5 pb-4">
        &copy; <?= date('Y') ?> <strong>Al Amin's Math Care</strong> — Farmgate, Dhaka 1216.
        <span class="mx-2">|</span>
        <a href="<?= e(base_url('')) ?>" class="text-primary text-decoration-none fw-semibold">
            <i class="bi bi-house-door me-1"></i><?= $locale === 'bn' ? 'মূল ওয়েবসাইটে ফিরুন' : 'Back to Website' ?>
        </a>
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
