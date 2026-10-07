<?php

/**
 * scratch/test_user_management.php — Test Admin User Creation, RBAC, and Permissions
 */

declare(strict_types=1);

echo "=== Al Amin's Math Care: Admin User & RBAC Management Tests ===\n\n";

$passCount = 0;
$failCount = 0;

function assert_test(string $name, bool $condition, string $details = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] $name\n";
        $passCount++;
    } else {
        echo " [FAIL] $name : $details\n";
        $failCount++;
    }
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$pdo = db();

// 1. Check Roles Table
$roles = $pdo->query('SELECT slug FROM roles')->fetchAll(PDO::FETCH_COLUMN);
assert_test('All 4 RBAC roles exist in DB', in_array('super_admin', $roles) && in_array('admin', $roles) && in_array('teacher', $roles) && in_array('receptionist', $roles));

// 2. Test User Creation
$testEmail = 'test_staff_' . time() . '@alaminmathcare.com';
$password = 'StaffPass@2026';
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('
    INSERT INTO users (name, email, password, role_id, status, created_at)
    VALUES (:name, :email, :password, :role_id, "active", NOW())
');
$created = $stmt->execute([
    ':name'     => 'Test Receptionist Staff',
    ':email'    => $testEmail,
    ':password' => $passwordHash,
    ':role_id'  => 4, // receptionist
]);
$newUserId = (int)$pdo->lastInsertId();

assert_test('User creation in DB succeeds', $created && $newUserId > 0, "User ID: $newUserId");

// 3. Test Password Verification
$stmtUser = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmtUser->execute([':id' => $newUserId]);
$user = $stmtUser->fetch();

assert_test('Password hash verifies correctly', password_verify($password, $user['password']));
assert_test('Assigned role is Receptionist (role_id 4)', (int)$user['role_id'] === 4);

// 4. Test User Editing (Promotion to Teacher & Status change)
$stmtEdit = $pdo->prepare('
    UPDATE users SET name = :name, role_id = :role_id, status = "active" WHERE id = :id
');
$edited = $stmtEdit->execute([
    ':name'    => 'Test Teacher Updated',
    ':role_id' => 3, // Teacher
    ':id'      => $newUserId,
]);

$stmtCheck = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmtCheck->execute([':id' => $newUserId]);
$updatedUser = $stmtCheck->fetch();

assert_test('User update (promotion to Teacher) succeeds', $edited && (int)$updatedUser['role_id'] === 3 && $updatedUser['name'] === 'Test Teacher Updated');

// 5. Test Soft Delete
$stmtDel = $pdo->prepare('UPDATE users SET is_deleted = 1, status = "inactive" WHERE id = :id');
$delSuccess = $stmtDel->execute([':id' => $newUserId]);

$stmtActive = $pdo->prepare('SELECT * FROM users WHERE id = :id AND is_deleted = 0');
$stmtActive->execute([':id' => $newUserId]);
$activeRecord = $stmtActive->fetch();

assert_test('User soft deletion succeeds (hidden from active queries)', $delSuccess && $activeRecord === false);

echo "\n============================================\n";
echo "Results: $passCount Passed, $failCount Failed.\n";
echo "============================================\n";
