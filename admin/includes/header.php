<?php

/**
 * admin/includes/header.php — Admin Panel Header Template
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/auth_guard.php';

$pageTitle = $adminPageTitle ?? 'Admin Panel — Al Amin\'s Math Care';
$activeModule = $activeModule ?? 'dashboard';
$adminUser = $_SESSION['admin_user'] ?? 'Admin';
$adminRole = $_SESSION['admin_role'] ?? 'super_admin';

$flashSuccess = get_flash('success');
$flashError = get_flash('error');
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
    <link href="<?= e(base_url('assets/css/main.css')) ?>" rel="stylesheet">
    <link href="<?= e(base_url('assets/css/admin.css')) ?>" rel="stylesheet">
    <style>
        :root {
            --sidebar-bg: #112239;
            --sidebar-active: #1e3a61;
            --sidebar-hover: #172d4c;
            --accent-gold: #f59e0b;
        }
        body {
            background-color: #f4f6f9;
            font-family: 'Inter', 'Noto Sans Bengali', sans-serif;
            color: #334155;
        }
        .admin-sidebar {
            background: var(--sidebar-bg);
            min-height: 100vh;
            color: #cbd5e1;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .admin-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .admin-nav-item {
            color: #94a3b8;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 2px;
        }
        .admin-nav-item:hover {
            background: var(--sidebar-hover);
            color: #ffffff;
            transform: translateX(3px);
        }
        .admin-nav-item.active {
            background: var(--sidebar-active);
            color: #ffffff;
            font-weight: 600;
            border-left: 3px solid var(--accent-gold);
        }
        .admin-nav-item i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .admin-nav-section-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            font-weight: 700;
            padding: 1rem 1rem 0.35rem;
        }
        .admin-topbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.85rem 1.5rem;
        }
        .card-custom {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            background: #ffffff;
        }
        .table-custom th {
            background: #f8fafc;
            color: #475569;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            padding: 0.85rem 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .table-custom td {
            padding: 0.85rem 1rem;
            vertical-align: middle;
            font-size: 0.9rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .badge-subtle-success { background: #dcfce7; color: #166534; }
        .badge-subtle-warning { background: #fef3c7; color: #92400e; }
        .badge-subtle-danger { background: #fee2e2; color: #991b1b; }
        .badge-subtle-info { background: #e0f2fe; color: #075985; }
        .badge-subtle-primary { background: #e0e7ff; color: #3730a3; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <?php require_once __DIR__ . '/sidebar.php'; ?>

        <!-- Main Content Area -->
        <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-3 min-vh-100 d-flex flex-column">
            <!-- Topbar -->
            <div class="admin-topbar rounded-3 mb-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-outline-secondary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#adminSidebarMobile">
                        <i class="bi bi-list"></i>
                    </button>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark"><?= e($adminPageHeading ?? $pageTitle) ?></h5>
                        <?php if (!empty($adminPageSubheading)): ?>
                            <small class="text-muted"><?= e($adminPageSubheading) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="<?= e(base_url('index.php')) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-globe me-1"></i> Public Site
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem;">
                                <?= strtoupper(substr($adminUser, 0, 1)) ?>
                            </span>
                            <span class="fw-semibold small"><?= e($adminUser) ?></span>
                            <span class="badge bg-secondary" style="font-size: 0.65rem;"><?= e($adminRole) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><a class="dropdown-item" href="<?= e(base_url('admin/settings/')) ?>"><i class="bi bi-gear me-2"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= e(base_url('admin/logout.php')) ?>"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Flash Notifications -->
            <?php if ($flashSuccess): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 py-2 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div><?= e($flashSuccess) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 py-2 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div><?= e($flashError) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
