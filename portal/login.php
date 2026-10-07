<?php

/**
 * portal/login.php — Student & Guardian Portal Login
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/i18n.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

// Already logged-in portal user → redirect to dashboard
if (!empty($_SESSION['portal_student_id'])) {
    header('Location: ' . base_url('portal/dashboard.php'));
    exit;
}

$errors          = [];
$studentId       = '';
$loginAttemptKey = 'portal_attempts_' . ($_SERVER['REMOTE_ADDR'] ?? '0');
$attempts        = $_SESSION[$loginAttemptKey] ?? 0;
$maxAttempts     = 5;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    if ($attempts >= $maxAttempts) {
        $errors[] = $locale === 'bn' 
            ? 'অতিরিক্ত ভুল চেষ্টার কারণে অ্যাকাউন্টটি সাময়িক লক করা হয়েছে। অনুগ্রহ করে কিছুক্ষণ পর চেষ্টা করুন।'
            : 'Too many failed attempts. Please wait a few minutes and try again.';
    } else {
        $studentId   = clean_input($_POST['student_id']   ?? '');
        $guardianPin = clean_input($_POST['guardian_pin'] ?? '');

        if (empty($studentId) || empty($guardianPin)) {
            $errors[] = $locale === 'bn'
                ? 'স্টুডেন্ট আইডি এবং গার্ডিয়ান পিন উভয়ই আবশ্যক।'
                : 'Student ID and Guardian PIN are required.';
        } else {
            $pdo  = db();
            $stmt = $pdo->prepare(
                'SELECT s.id, s.name, s.student_id_code, s.guardian_phone, s.class_level, s.status
                 FROM students s
                 WHERE s.student_id_code = :sid AND s.is_deleted = 0 LIMIT 1'
            );
            $stmt->execute([':sid' => $studentId]);
            $student = $stmt->fetch();

            // PIN = last 4 digits of guardian phone
            if (
                $student &&
                $student['status'] === 'active' &&
                strlen($student['guardian_phone']) >= 4 &&
                hash_equals(substr($student['guardian_phone'], -4), $guardianPin)
            ) {
                // Success — regenerate session
                session_regenerate_id(true);
                unset($_SESSION[$loginAttemptKey]);

                $_SESSION['portal_student_id']   = $student['id'];
                $_SESSION['portal_student_name'] = $student['name'];
                $_SESSION['portal_student_code'] = $student['student_id_code'];
                $_SESSION['portal_class_level']  = $student['class_level'];

                header('Location: ' . base_url('portal/dashboard.php'));
                exit;
            } else {
                $_SESSION[$loginAttemptKey] = ++$attempts;
                $errors[] = $locale === 'bn'
                    ? 'ভুল স্টুডেন্ট আইডি অথবা গার্ডিয়ান পিন। অনুগ্রহ করে আবার চেষ্টা করুন।'
                    : 'Invalid Student ID or Guardian PIN. Please check and try again.';
            }
        }
    }
}

$pageTitle       = ($locale === 'bn' ? 'স্টুডেন্ট ও অভিভাবক পোর্টাল' : 'Student & Guardian Portal') . ' — Al Amin\'s Math Care';
$metaDescription = 'Login to Al Amin\'s Math Care student portal to view monthly fees, payment receipts, attendance, and student ID card.';
?>
<!DOCTYPE html>
<html lang="<?= e($locale) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800;900&family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --brand-midnight: #0B192C;
            --brand-deep:     #1E3E62;
            --brand-cyan:     #008DDA;
            --brand-cyan-glow: rgba(0, 141, 218, 0.25);
            --brand-emerald:  #10B981;
            --brand-amber:    #F59E0B;
            --bg-slate:       #0A1118;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Hind Siliguri', 'Noto Sans Bengali', sans-serif;
            background: radial-gradient(circle at top, #1E3E62 0%, #0B192C 50%, #050B14 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            color: #E2E8F0;
        }
        .portal-card {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 1.5rem;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.55), 0 0 30px rgba(0, 141, 218, 0.15);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            animation: fadeIn 0.4s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .portal-header {
            background: linear-gradient(135deg, rgba(30, 62, 98, 0.6) 0%, rgba(11, 25, 44, 0.8) 100%);
            padding: 2.25rem 2rem 1.75rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
        }
        .portal-header .brand-logo {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--brand-cyan) 0%, #005691 100%);
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #FFFFFF;
            margin-bottom: .85rem;
            box-shadow: 0 8px 24px rgba(0, 141, 218, 0.35);
        }
        .portal-body { padding: 2rem 2.25rem 2.25rem; }
        .form-label {
            color: #CBD5E1;
            font-weight: 600;
            font-size: 0.86rem;
            margin-bottom: 0.4rem;
        }
        .input-group-text {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #94A3B8;
            border-radius: 0.75rem 0 0 0.75rem;
        }
        .form-control {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #F8FAFC;
            border-radius: 0 0.75rem 0.75rem 0;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            background: rgba(30, 41, 59, 0.95);
            border-color: var(--brand-cyan);
            color: #FFFFFF;
            box-shadow: 0 0 0 0.25rem var(--brand-cyan-glow);
        }
        .form-control::placeholder {
            color: #64748B;
        }
        .btn-portal {
            background: linear-gradient(135deg, #008DDA 0%, #006BB4 100%);
            border: none;
            color: #FFFFFF;
            font-weight: 700;
            padding: 0.85rem;
            border-radius: 0.75rem;
            font-size: 1rem;
            letter-spacing: 0.02em;
            transition: all 0.2s ease;
            box-shadow: 0 6px 20px rgba(0, 141, 218, 0.35);
        }
        .btn-portal:hover {
            background: linear-gradient(135deg, #009BEB 0%, #0077C7 100%);
            color: #FFFFFF;
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(0, 141, 218, 0.45);
        }
        .hint-box {
            background: rgba(0, 141, 218, 0.1);
            border: 1px solid rgba(0, 141, 218, 0.3);
            border-left: 4px solid var(--brand-cyan);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 0.82rem;
            color: #BAE6FD;
        }
        .lang-switch {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
        .lang-switch a {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #FFFFFF;
            padding: 0.2rem 0.55rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.15s;
        }
        .lang-switch a:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>
    <div class="portal-card">
        <div class="portal-header">
            <div class="lang-switch">
                <?php if ($locale === 'bn'): ?>
                    <a href="?lang=en">English</a>
                <?php else: ?>
                    <a href="?lang=bn">বাংলা</a>
                <?php endif; ?>
            </div>

            <div class="brand-logo">
                <span>∑</span>
            </div>
            <h4 class="fw-bold text-white mb-1">
                <?= $locale === 'bn' ? 'স্টুডেন্ট ও অভিভাবক পোর্টাল' : 'Student & Guardian Portal' ?>
            </h4>
            <p class="mb-0 text-white-50 small">
                Al Amin's Math Care — Farmgate, Dhaka
            </p>
        </div>

        <div class="portal-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger py-2 small mb-4 border-0" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #FCA5A5;">
                    <i class="bi bi-exclamation-circle-fill me-1"></i>
                    <?= e($errors[0]) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" autocomplete="off">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label" for="student_id">
                        <?= $locale === 'bn' ? 'স্টুডেন্ট আইডি (Student ID)' : 'Student ID' ?> <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
                        <input type="text" id="student_id" name="student_id" class="form-control"
                               value="<?= e($studentId) ?>"
                               placeholder="e.g. AMC-2026-001"
                               required autocomplete="username">
                    </div>
                    <div class="form-text text-white-50" style="font-size: 0.75rem;">
                        <?= $locale === 'bn' ? 'আপনার স্টুডেন্ট আইডি কার্ডে এই কোডটি রয়েছে।' : 'Your Student ID code is printed on your ID card.' ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="guardian_pin">
                        <?= $locale === 'bn' ? 'গার্ডিয়ান পিন (Guardian PIN)' : 'Guardian PIN' ?> <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-lock-fill"></i></span>
                        <input type="password" id="guardian_pin" name="guardian_pin" class="form-control"
                               placeholder="4-digit PIN"
                               maxlength="4" minlength="4" pattern="\d{4}"
                               required autocomplete="current-password"
                               style="border-radius: 0;">
                        <button type="button" class="input-group-text btn-pin-toggle" onclick="
                            var f=document.getElementById('guardian_pin');
                            f.type = f.type==='password' ? 'text' : 'password';
                            this.querySelector('i').classList.toggle('bi-eye');
                            this.querySelector('i').classList.toggle('bi-eye-slash');
                        " style="cursor:pointer; border-radius: 0 0.75rem 0.75rem 0; border-left:0;">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <div class="hint-box mb-4">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle-fill fs-6 mt-1 flex-shrink-0 text-info"></i>
                        <div>
                            <strong><?= $locale === 'bn' ? 'পিন (PIN) কী?' : 'What is my PIN?' ?></strong>
                            <div>
                                <?= $locale === 'bn' 
                                    ? 'আপনার নিবন্ধিত অভিভাবকের মোবাইল নম্বরের <strong>শেষ ৪টি সংখ্যা</strong> আপনার সিকিউরিটি পিন।'
                                    : 'Your PIN is the <strong>last 4 digits</strong> of your registered guardian mobile number.' ?>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-portal w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i>
                    <?= $locale === 'bn' ? 'পোর্টালে প্রবেশ করুন' : 'Login to Portal' ?>
                </button>
            </form>

            <div class="mt-4 pt-2 text-center border-top border-white-10">
                <a href="<?= e(base_url('')) ?>" class="text-decoration-none small text-white-50 hover-text-white">
                    <i class="bi bi-arrow-left me-1"></i> <?= $locale === 'bn' ? 'মূল ওয়েবসাইটে ফিরে যান' : 'Back to Main Website' ?>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
