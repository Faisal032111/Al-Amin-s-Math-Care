<?php

/**
 * admin/users/edit.php — Edit Administrative User & Role Permissions
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';
require_role_or_abort(['super_admin', 'admin']);

$pdo = db();
$id = (int)($_GET['id'] ?? 0);
$errors = [];

// Fetch user
$stmt = $pdo->prepare('
    SELECT u.*, r.slug AS role_slug, r.name AS role_name
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE u.id = :id AND u.is_deleted = 0 LIMIT 1
');
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('error', 'User account not found.');
    header('Location: ' . base_url('admin/users/'));
    exit;
}

// Fetch available roles
$roles = $pdo->query('SELECT * FROM roles ORDER BY id ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $name            = clean_input($_POST['name'] ?? '');
    $email           = clean_input($_POST['email'] ?? '');
    $password        = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $roleId          = (int)($_POST['role_id'] ?? $user['role_id']);
    $status          = clean_input($_POST['status'] ?? $user['status']);

    // Validation
    if (empty($name)) {
        $errors[] = 'Full name is required.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    } else {
        // Check uniqueness excluding this user
        $stmtCheck = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id AND is_deleted = 0 LIMIT 1');
        $stmtCheck->execute([':email' => $email, ':id' => $id]);
        if ($stmtCheck->fetch()) {
            $errors[] = 'Another user with this email address already exists.';
        }
    }

    // Protect primary root admin (ID 1)
    if ((int)$user['id'] === 1 && $roleId !== 1) {
        $errors[] = 'The Primary Super Administrator role cannot be changed.';
        $roleId = 1;
    }

    // Password validation (only if filled)
    if (!empty($password)) {
        if (strlen($password) < 8) {
            $errors[] = 'New password must be at least 8 characters long.';
        } elseif ($password !== $passwordConfirm) {
            $errors[] = 'New password and confirm password do not match.';
        }
    }

    $validRoleIds = array_column($roles, 'id');
    if (!in_array($roleId, $validRoleIds, true)) {
        $errors[] = 'Please select a valid role.';
    }

    if (!in_array($status, ['active', 'inactive', 'suspended'], true)) {
        $status = 'active';
    }

    // If current logged-in user is editing themselves, prevent self-deactivation
    if ((int)$user['id'] === (int)($_SESSION['admin_user_id'] ?? 0) && $status !== 'active') {
        $errors[] = 'You cannot deactivate your own active session.';
        $status = 'active';
    }

    if (empty($errors)) {
        if (!empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmtUpdate = $pdo->prepare('
                UPDATE users
                SET name = :name, email = :email, password = :password, role_id = :role_id, status = :status, updated_at = NOW()
                WHERE id = :id
            ');
            $stmtUpdate->execute([
                ':name'     => $name,
                ':email'    => $email,
                ':password' => $passwordHash,
                ':role_id'  => $roleId,
                ':status'   => $status,
                ':id'       => $id,
            ]);
        } else {
            $stmtUpdate = $pdo->prepare('
                UPDATE users
                SET name = :name, email = :email, role_id = :role_id, status = :status, updated_at = NOW()
                WHERE id = :id
            ');
            $stmtUpdate->execute([
                ':name'    => $name,
                ':email'   => $email,
                ':role_id' => $roleId,
                ':status'  => $status,
                ':id'      => $id,
            ]);
        }

        // If editing own name/role, refresh session
        if ((int)$user['id'] === (int)($_SESSION['admin_user_id'] ?? 0)) {
            $_SESSION['admin_user_name'] = $name;
            // Lookup new role slug
            foreach ($roles as $r) {
                if ((int)$r['id'] === $roleId) {
                    $_SESSION['admin_role'] = $r['slug'];
                    break;
                }
            }
        }

        set_flash('success', 'User account "' . $name . '" updated successfully.');
        header('Location: ' . base_url('admin/users/'));
        exit;
    }
}

$pageTitle = 'Edit User: ' . $user['name'];
$activeModule = 'users';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>Edit User Account</h4>
        <p class="text-muted small mb-0">Modify user profile, credentials, and access permissions.</p>
    </div>
    <div>
        <a href="<?= e(base_url('admin/users/')) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Users List
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Please fix the following errors:</h6>
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="admin-card p-4">
            <form method="POST" action="" autocomplete="off">
                <?= csrf_field() ?>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= e($_POST['name'] ?? $user['name']) ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email Address (Login ID) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" required
                                   value="<?= e($_POST['email'] ?? $user['email']) ?>">
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 border mb-3">
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-shield-lock me-1"></i>Change Password (Optional)</h6>
                    <p class="small text-muted mb-3">Leave blank if you want to keep the current password.</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="password" id="password" name="password" class="form-control" minlength="8"
                                       placeholder="Leave blank to keep unchanged">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                <input type="password" id="password_confirm" name="password_confirm" class="form-control" minlength="8"
                                       placeholder="Re-type new password">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('password_confirm', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Role & Access Level <span class="text-danger">*</span></label>
                        <select name="role_id" class="form-select" required id="roleSelect" onchange="updateRoleDetails()" <?= (int)$user['id'] === 1 ? 'disabled' : '' ?>>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>" data-slug="<?= e($r['slug']) ?>" <?= (int)($_POST['role_id'] ?? $user['role_id']) === (int)$r['id'] ? 'selected' : '' ?>>
                                    <?= e($r['name']) ?> (<?= e($r['slug']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ((int)$user['id'] === 1): ?>
                            <input type="hidden" name="role_id" value="1">
                            <div class="form-text text-muted">Primary Super Admin role is fixed.</div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Account Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="active" <?= ($_POST['status'] ?? $user['status']) === 'active' ? 'selected' : '' ?>>Active (Can login)</option>
                            <option value="inactive" <?= ($_POST['status'] ?? $user['status']) === 'inactive' ? 'selected' : '' ?>>Inactive (Disabled)</option>
                            <option value="suspended" <?= ($_POST['status'] ?? $user['status']) === 'suspended' ? 'selected' : '' ?>>Suspended (Blocked)</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="<?= e(base_url('admin/users/')) ?>" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Role Explanation Sidebar Card -->
    <div class="col-lg-4">
        <div class="admin-card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-shield-check text-success me-2"></i>Current Permissions</h6>
            <div id="roleDetailsBox" class="p-3 bg-light rounded-3 border mb-3">
                <strong id="roleNameText" class="d-block mb-1"><?= e($user['role_name']) ?></strong>
                <p id="roleDescText" class="small text-muted mb-0">
                    Loading role specifications...
                </p>
            </div>

            <div class="p-3 bg-light rounded-3 border small">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Account ID:</span>
                    <strong class="font-monospace">#<?= $user['id'] ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Created:</span>
                    <span><?= date('d M Y', strtotime($user['created_at'])) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Last Login:</span>
                    <span><?= !empty($user['last_login']) ? date('d M Y, h:i A', strtotime($user['last_login'])) : 'Never' ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

function updateRoleDetails() {
    const select = document.getElementById('roleSelect');
    const selectedOption = select.options[select.selectedIndex];
    const slug = selectedOption.getAttribute('data-slug');
    const nameText = document.getElementById('roleNameText');
    const descText = document.getElementById('roleDescText');

    if (slug === 'super_admin') {
        nameText.textContent = 'Super Administrator';
        nameText.className = 'text-danger fw-bold d-block mb-1';
        descText.textContent = 'Complete system control: Can add/remove users, change system settings, manage database backups, and access all coaching modules.';
    } else if (slug === 'admin') {
        nameText.textContent = 'Administrator';
        nameText.className = 'text-primary fw-bold d-block mb-1';
        descText.textContent = 'Manages students, admissions, fees, daily attendance, class schedules, exam results, and notices.';
    } else if (slug === 'teacher') {
        nameText.textContent = 'Teacher';
        nameText.className = 'text-success fw-bold d-block mb-1';
        descText.textContent = 'Restricted access: Can mark student attendance, view batch rosters, and check class schedule routines.';
    } else if (slug === 'receptionist') {
        nameText.textContent = 'Receptionist / Front Desk';
        nameText.className = 'text-secondary fw-bold d-block mb-1';
        descText.textContent = 'Operational desk: Handles instant student inquiries & demo leads, marks attendance, and accepts fee payments.';
    }
}
document.addEventListener('DOMContentLoaded', updateRoleDetails);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
