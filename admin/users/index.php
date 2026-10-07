<?php

/**
 * admin/users/index.php — Admin Users & RBAC Permissions Management
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';
require_role_or_abort(['super_admin', 'admin']);

$pdo = db();

// Filter & Search
$search = clean_input($_GET['search'] ?? '');
$roleFilter = clean_input($_GET['role'] ?? '');

$sql = '
    SELECT u.*, r.name AS role_name, r.slug AS role_slug
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE u.is_deleted = 0
';
$params = [];

if (!empty($search)) {
    $sql .= ' AND (u.name LIKE :search OR u.email LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}

if (!empty($roleFilter)) {
    $sql .= ' AND r.slug = :role';
    $params[':role'] = $roleFilter;
}

$sql .= ' ORDER BY u.role_id ASC, u.id ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get Roles for filter dropdown
$roles = $pdo->query('SELECT * FROM roles ORDER BY id ASC')->fetchAll();

// Statistics
$totalUsers = count($users);
$activeUsers = 0;
$superAdmins = 0;
$staffUsers = 0;

foreach ($users as $u) {
    if ($u['status'] === 'active') {
        $activeUsers++;
    }
    if ($u['role_slug'] === 'super_admin') {
        $superAdmins++;
    } else {
        $staffUsers++;
    }
}

$pageTitle = 'User Management & Permissions';
$activeModule = 'users';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-shield-lock-fill text-primary me-2"></i>User & Role Management</h4>
        <p class="text-muted small mb-0">Create administrative staff accounts and configure role-based permissions (RBAC).</p>
    </div>
    <div>
        <a href="<?= e(base_url('admin/users/create.php')) ?>" class="btn btn-primary fw-bold shadow-sm">
            <i class="bi bi-person-plus-fill me-1"></i> Add New User
        </a>
    </div>
</div>

<!-- Flash Messages -->
<?php if ($msg = get_flash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= e($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($err = get_flash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($err) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Stat Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="admin-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 p-3 text-primary fs-4">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0"><?= $totalUsers ?></h3>
                    <div class="text-muted small">Total Users</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success bg-opacity-10 p-3 text-success fs-4">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0 text-success"><?= $activeUsers ?></h3>
                    <div class="text-muted small">Active Accounts</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning bg-opacity-10 p-3 text-warning fs-4">
                    <i class="bi bi-shield-shaded"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0 text-warning"><?= $superAdmins ?></h3>
                    <div class="text-muted small">Super Admins</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info bg-opacity-10 p-3 text-info fs-4">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0 text-info"><?= $staffUsers ?></h3>
                    <div class="text-muted small">Staff & Teachers</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="admin-card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="role" class="form-select">
                    <option value="">-- Filter by Role --</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= e($r['slug']) ?>" <?= $roleFilter === $r['slug'] ? 'selected' : '' ?>>
                            <?= e($r['name']) ?> (<?= e($r['slug']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary w-100 fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <?php if (!empty($search) || !empty($roleFilter)): ?>
                    <a href="<?= e(base_url('admin/users/')) ?>" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>User Details</th>
                    <th>Role & Permissions</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th class="text-end" style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-person-x fs-2 d-block mb-2 opacity-50"></i>
                            No users found matching your criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $index => $u): ?>
                        <tr>
                            <td class="text-muted fw-semibold"><?= $index + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong class="text-dark d-block"><?= e($u['name']) ?></strong>
                                        <small class="text-muted"><?= e($u['email']) ?></small>
                                        <?php if ((int)$u['id'] === (int)($_SESSION['admin_user_id'] ?? 0)): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 0.68rem;">You</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php
                                $roleBadge = match($u['role_slug']) {
                                    'super_admin' => 'bg-danger text-white',
                                    'admin'       => 'bg-primary text-white',
                                    'teacher'     => 'bg-success text-white',
                                    default       => 'bg-secondary text-white',
                                };
                                ?>
                                <span class="badge rounded-pill px-2.5 py-1 <?= $roleBadge ?>">
                                    <i class="bi <?= $u['role_slug'] === 'super_admin' ? 'bi-shield-fill-check' : 'bi-person-badge' ?> me-1"></i>
                                    <?= e($u['role_name']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($u['status'] === 'active'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Active
                                    </span>
                                <?php elseif ($u['status'] === 'suspended'): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        <i class="bi bi-slash-circle me-1"></i> Suspended
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                        <i class="bi bi-pause-circle me-1"></i> Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted">
                                <?= !empty($u['last_login']) ? date('d M Y, h:i A', strtotime($u['last_login'])) : '<span class="text-muted opacity-50">Never</span>' ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= e(base_url('admin/users/edit.php?id=' . $u['id'])) ?>" class="btn btn-outline-primary" title="Edit User & Permissions">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <?php if ((int)$u['id'] !== (int)($_SESSION['admin_user_id'] ?? 0) && (int)$u['id'] !== 1): ?>
                                        <button type="button" class="btn btn-outline-danger" title="Delete User"
                                                onclick="confirmDelete(<?= $u['id'] ?>, '<?= e(addslashes($u['name'])) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary opacity-50" disabled title="Cannot delete your own account or root admin">
                                            <i class="bi bi-lock-fill"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Role Permissions Reference Accordion -->
<div class="admin-card mt-4 p-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-info-circle-fill text-primary me-2"></i>Role-Based Access Control (RBAC) Overview</h6>
    <div class="row g-3">
        <div class="col-md-3">
            <div class="p-3 border rounded-3 bg-light h-100">
                <span class="badge bg-danger mb-2">Super Administrator</span>
                <p class="small text-muted mb-0">Full, unrestricted access to all modules, settings, user creation, fee ledger, and database controls.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 border rounded-3 bg-light h-100">
                <span class="badge bg-primary mb-2">Administrator</span>
                <p class="small text-muted mb-0">Manages courses, batches, students, fee ledger, notices, routines, and exam results.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 border rounded-3 bg-light h-100">
                <span class="badge bg-success mb-2">Teacher</span>
                <p class="small text-muted mb-0">Can take daily attendance, view student lists for assigned batches, and view class routines.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 border rounded-3 bg-light h-100">
                <span class="badge bg-secondary mb-2">Receptionist / Front Desk</span>
                <p class="small text-muted mb-0">Handles instant demo/admission leads, marks daily attendance, and accepts monthly tuition fees.</p>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal Form -->
<form id="deleteForm" method="POST" action="<?= e(base_url('admin/users/delete.php')) ?>" style="display: none;">
    <?= csrf_field() ?>
    <input type="hidden" name="id" id="deleteId" value="">
</form>

<script>
function confirmDelete(id, name) {
    if (confirm('Are you sure you want to delete user account "' + name + '"? This will disable their access immediately.')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
