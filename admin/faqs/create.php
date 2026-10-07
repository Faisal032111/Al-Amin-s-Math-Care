<?php

/**
 * admin/faqs/create.php — Add New FAQ
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle    = 'Add FAQ — Admin Panel';
$adminPageHeading  = 'Add New FAQ';
$adminPageSubheading = 'Create a bilingual FAQ entry to display on the website';
$activeModule      = 'faqs';

$pdo    = db();
$errors = [];
$data   = [
    'question_en' => '',
    'question_bn' => '',
    'answer_en'   => '',
    'answer_bn'   => '',
    'category'    => 'general',
    'sort_order'  => 0,
    'is_active'   => 1,
];

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
                'INSERT INTO faqs (question_en, question_bn, answer_en, answer_bn, category, sort_order, is_active)
                 VALUES (:qen, :qbn, :aen, :abn, :cat, :ord, :act)'
            );
            $stmt->execute([
                ':qen' => $data['question_en'],
                ':qbn' => $data['question_bn'],
                ':aen' => $data['answer_en'],
                ':abn' => $data['answer_bn'],
                ':cat' => $data['category'],
                ':ord' => $data['sort_order'],
                ':act' => $data['is_active'],
            ]);
            set_flash('success', 'FAQ added successfully.');
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
                        <input type="text" name="question_en" class="form-control" value="<?= e($data['question_en']) ?>"
                               required placeholder="e.g. What subjects does Al Amin's Math Care teach?">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Question (Bangla / বাংলা)</label>
                        <input type="text" name="question_bn" class="form-control" value="<?= e($data['question_bn']) ?>"
                               placeholder="যেমন: আল আমিন'স ম্যাথ কেয়ার কোন বিষয় পড়ায়?">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Answer (English) <span class="text-danger">*</span></label>
                        <textarea name="answer_en" rows="4" class="form-control" required
                                  placeholder="Write the detailed answer in English..."><?= e($data['answer_en']) ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Answer (Bangla / বাংলা)</label>
                        <textarea name="answer_bn" rows="4" class="form-control"
                                  placeholder="বাংলায় উত্তর লিখুন..."><?= e($data['answer_bn']) ?></textarea>
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
                        <div class="form-text">Lower numbers appear first.</div>
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
                            <i class="bi bi-plus-circle-fill me-1"></i> Save FAQ
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
