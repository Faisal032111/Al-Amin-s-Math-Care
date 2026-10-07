<?php

/**
 * admin/faqs/edit.php — Edit Existing FAQ
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle    = 'Edit FAQ — Admin Panel';
$adminPageHeading  = 'Edit FAQ';
$adminPageSubheading = 'Update the bilingual question and answer';
$activeModule      = 'faqs';

$pdo = db();
$id  = (int)($_GET['id'] ?? 0);

$faq = $pdo->prepare('SELECT * FROM faqs WHERE id = :id AND is_deleted = 0');
$faq->execute([':id' => $id]);
$faq = $faq->fetch();

if (!$faq) {
    set_flash('error', 'FAQ not found.');
    header('Location: ' . base_url('admin/faqs/'));
    exit;
}

$errors = [];
$data   = $faq;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $data['question_en'] = clean_input($_POST['question_en'] ?? '');
    $data['question_bn'] = clean_input($_POST['question_bn'] ?? '');
    $data['answer_en']   = clean_input($_POST['answer_en'] ?? '');
    $data['answer_bn']   = clean_input($_POST['answer_bn'] ?? '');
    $data['category']    = clean_input($_POST['category'] ?? 'general');
    $data['sort_order']  = (int)($_POST['sort_order'] ?? 0);
    $data['is_active']   = isset($_POST['is_active']) ? 1 : 0;

    if (empty($data['question_en'])) $errors[] = 'Question (English) is required.';
    if (empty($data['answer_en']))   $errors[] = 'Answer (English) is required.';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                'UPDATE faqs SET question_en=:qen, question_bn=:qbn, answer_en=:aen, answer_bn=:abn,
                 category=:cat, sort_order=:ord, is_active=:act WHERE id=:id'
            );
            $stmt->execute([
                ':qen' => $data['question_en'],
                ':qbn' => $data['question_bn'],
                ':aen' => $data['answer_en'],
                ':abn' => $data['answer_bn'],
                ':cat' => $data['category'],
                ':ord' => $data['sort_order'],
                ':act' => $data['is_active'],
                ':id'  => $id,
            ]);
            set_flash('success', 'FAQ updated successfully.');
            header('Location: ' . base_url('admin/faqs/'));
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?= e(base_url('admin/faqs/')) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to FAQs
            </a>
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

        <div class="card card-custom p-4">
            <form method="POST" action="">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Question (English) <span class="text-danger">*</span></label>
                        <input type="text" name="question_en" class="form-control" value="<?= e($data['question_en']) ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Question (Bangla / বাংলা)</label>
                        <input type="text" name="question_bn" class="form-control" value="<?= e($data['question_bn'] ?? '') ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Answer (English) <span class="text-danger">*</span></label>
                        <textarea name="answer_en" rows="4" class="form-control" required><?= e($data['answer_en']) ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Answer (Bangla / বাংলা)</label>
                        <textarea name="answer_bn" rows="4" class="form-control"><?= e($data['answer_bn'] ?? '') ?></textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Category</label>
                        <select name="category" class="form-select">
                            <option value="general"   <?= $data['category'] === 'general'   ? 'selected' : '' ?>>General</option>
                            <option value="admission" <?= $data['category'] === 'admission' ? 'selected' : '' ?>>Admission</option>
                            <option value="fees"      <?= $data['category'] === 'fees'      ? 'selected' : '' ?>>Fees &amp; Payments</option>
                            <option value="schedule"  <?= $data['category'] === 'schedule'  ? 'selected' : '' ?>>Class Schedule</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= (int)$data['sort_order'] ?>" min="0" max="999">
                    </div>
                    <div class="col-md-4 d-flex align-items-center pt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive"
                                   <?= $data['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label small fw-semibold" for="isActive">Show on website</label>
                        </div>
                    </div>

                    <div class="col-12 mt-3 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-check-circle-fill me-1"></i> Update FAQ
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
