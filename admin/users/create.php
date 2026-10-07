<?php

/**
 * admin/users/create.php — Create New Administrative User & Assign Role
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';
require_role_or_abort(['super_admin', 'admin']);

$pdo = db();
$errors = [];

// Fetch available roles
$roles = $pdo->query('SELECT * FROM roles ORDER BY id ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $name            = clean_input($_POST['name'] ?? '');
    $email           = clean_input($_POST['email'] ?? '');
    $password        = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $roleId          = (int)($_POST['role_id'] ?? 0);
    $status          = clean_input($_POST['status'] ?? 'active');

    // Validation
    if (empty($name)) {
        $errors[] = 'Full name is required.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    } else {
        // Check uniqueness
        $stmtCheck = $pdo->prepare('SELECT id FROM users WHERE email = :email AND is_deleted = 0 LIMIT 1');
        $stmtCheck->execute([':email' => $email]);
        if ($stmtCheck->fetch()) {
            $errors[] = 'A user with this email address already exists.';
        }
    }

    if (empty($password) || strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    } elseif ($password !== $passwordConfirm) {
        $errors[] = 'Password and Confirm Password do not match.';
    }

    $validRoleIds = array_column($roles, 'id');
    if (!in_array($roleId, $validRoleIds, true)) {
        $errors[] = 'Please select a valid role.';
    }

    if (!in_array($status, ['active', 'inactive', 'suspended'], true)) {
        $status = 'active';
    }

    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmtInsert = $pdo->prepare('
            INSERT INTO users (name, email, password, role_id, status, created_at)
            VALUES (:name, :email, :password, :role_id, :status, NOW())
        ');

        $stmtInsert->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':password' => $passwordHash,
            ':role_id'  => $roleId,
            ':status'   => $status,
        ]);

        set_flash('success', 'User account "' . $name . '" created successfully with selected permissions.');
        header('Location: ' . base_url('admin/users/'));
        exit;
    }
}

$pageTitle = 'Add New Admin User';
$activeModule = 'users';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-person-plus-fill text-primary me-2"></i>Create New User</h4>
        <p class="text-muted small mb-0">Create staff credentials and assign module permissions.</p>
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
                                   placeholder="e.g. Tanvir Ahmed"
                                   value="<?= e($_POST['name'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email Address (Login ID) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" required
                                   placeholder="tanvir@alaminmathcare.com"
                                   value="<?= e($_POST['email'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" id="password" name="password" class="form-control" required minlength="8"
                                   placeholder="Minimum 8 characters">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePass('password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Must contain at least 8 characters.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                            <input type="password" id="password_confirm" name="password_confirm" class="form-control" required minlength="8"
                                   placeholder="Re-type password">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePass('password_confirm', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Role & Access Level <span class="text-danger">*</span></label>
                        <select name="role_id" class="form-select" required id="roleSelect" onchange="updateRoleDetails()">
                            <option value="">-- Select Role --</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>" data-slug="<?= e($r['slug']) ?>" <?= (int)($_POST['role_id'] ?? 2) === (int)$r['id'] ? 'selected' : '' ?>>
                                    <?= e($r['name']) ?> (<?= e($r['slug']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Account Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="active" <?= ($_POST['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active (Can login immediately)</option>
                            <option value="inactive" <?= ($_POST['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive (Access disabled)</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="<?= e(base_url('admin/users/')) ?>" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-check-lg me-1"></i> Create User Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Role Explanation Sidebar Card -->
    <div class="col-lg-4">
        <div class="admin-card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-shield-check text-success me-2"></i>Selected Role Permissions</h6>
            <div id="roleDetailsBox" class="p-3 bg-light rounded-3 border">
                <strong id="roleNameText" class="text-primary d-block mb-1">Administrator</strong>
                <p id="roleDescText" class="small text-muted mb-0">
                    Full management capability for students, fees, attendance, batches, and website notices. Cannot delete other administrators.
                </p>
            </div>

            <hr class="my-3">

            <div class="small text-muted">
                <div class="fw-semibold text-dark mb-1"><i class="bi bi-lock me-1"></i>Security Note:</div>
                Passwords are automatically hashed using standard Bcrypt (`PASSWORD_BCRYPT`). All login attempts are recorded and rate-limited.
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
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
