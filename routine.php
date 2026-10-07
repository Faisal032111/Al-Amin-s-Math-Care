<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = page_title(__('page_routine_title'));

try {
    $pdo = db();
    $routines = $pdo->query('
        SELECT r.*, b.batch_name, b.batch_name_bn, c.title_en as course_title_en, c.title_bn as course_title_bn
        FROM routines r
        JOIN batches b ON r.batch_id = b.id
        LEFT JOIN courses c ON b.course_id = c.id
        WHERE r.is_deleted = 0 AND b.is_deleted = 0
        ORDER BY FIELD(r.day_of_week, "Saturday", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday"), r.start_time ASC
    ')->fetchAll();
} catch (Throwable $e) {
    $routines = [];
}

// Group by Day
$days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$grouped = [];
foreach ($days as $day) {
    $grouped[$day] = [];
}
foreach ($routines as $r) {
    $day = $r['day_of_week'];
    if (isset($grouped[$day])) {
        $grouped[$day][] = $r;
    }
}

$dayTranslations = [
    'Saturday'  => 'শনিবার',
    'Sunday'    => 'রবিবার',
    'Monday'    => 'সোমবার',
    'Tuesday'   => 'মঙ্গলবার',
    'Wednesday' => 'বুধবার',
    'Thursday'  => 'বৃহস্পতিবার',
    'Friday'    => 'শুক্রবার',
];

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container text-center">
        <h1><?= e(__('page_routine_title')) ?></h1>
        <p class="text-muted mb-0 lead">
            <?= $locale === 'bn' ? 'সাপ্তাহিক ক্লাসের দিন, সময় ও রুম নম্বর' : 'Weekly class timetable, shift timings, and classroom allocations' ?>
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Print / Download Routine Action -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="text-muted small">
                📍 <?= e(config_address()) ?>
            </span>
            <button onclick="window.print()" class="btn btn-outline-custom btn-sm">
                🖨️ <?= e(__('btn_print')) ?>
            </button>
        </div>

        <div class="row g-4">
            <?php foreach ($days as $day): 
                $daySchedule = $grouped[$day];
                if (empty($daySchedule)) continue;
                $dayLabel = $locale === 'bn' ? ($dayTranslations[$day] ?? $day) : $day;
            ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
                    <div class="card-header bg-primary text-white py-3 px-4 d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0 fw-bold">🗓️ <?= e($dayLabel) ?></h2>
                        <span class="badge bg-white text-primary fw-semibold"><?= count($daySchedule) ?> <?= $locale === 'bn' ? 'টি ক্লাস' : 'Classes' ?></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?= $locale === 'bn' ? 'ক্লাসের সময়' : 'Time' ?></th>
                                    <th><?= $locale === 'bn' ? 'ব্যাচ ও কোর্স' : 'Batch & Course' ?></th>
                                    <th><?= $locale === 'bn' ? 'রুম' : 'Room' ?></th>
                                    <th><?= $locale === 'bn' ? 'বিষয় / টপিক' : 'Session Topic / Activity' ?></th>
                                    <th><?= $locale === 'bn' ? 'শিক্ষক' : 'Teacher' ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($daySchedule as $item): 
                                    $batchName = $locale === 'bn' && !empty($item['batch_name_bn']) ? $item['batch_name_bn'] : $item['batch_name'];
                                    $courseName = $locale === 'bn' && !empty($item['course_title_bn']) ? $item['course_title_bn'] : ($item['course_title_en'] ?? '');
                                ?>
                                <tr>
                                    <td class="fw-bold text-primary" style="white-space: nowrap;">
                                        ⏰ <?= date('h:i A', strtotime($item['start_time'])) ?> - <?= date('h:i A', strtotime($item['end_time'])) ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= e($batchName) ?></div>
                                        <small class="text-muted"><?= e($courseName) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary fw-semibold">
                                            <?= e($item['room_number'] ?: 'Room-1') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-dark"><?= e($item['notes'] ?: 'Mathematics Core Lesson') ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold"><?= e($config['head_teacher']) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
