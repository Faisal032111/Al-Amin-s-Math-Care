
</main>

<?php require __DIR__ . '/mobile-nav.php'; ?>
<?php require __DIR__ . '/whatsapp-float.php'; ?>

<footer class="site-footer">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="footer-brand"><?= e($config['site_name']) ?></h5>
                <p class="text-muted-soft mb-3"><?= e(__('footer_tagline')) ?></p>
                <p class="small text-muted-soft mb-0"><?= e(config_address()) ?></p>
            </div>
            <div class="col-6 col-lg-4">
                <h6 class="footer-heading"><?= e(__('footer_quick_links')) ?></h6>
                <ul class="footer-links list-unstyled">
                    <li><a href="<?= e(base_url('index.php')) ?>"><?= e(__('nav_home')) ?></a></li>
                    <li><a href="<?= e(base_url('courses.php')) ?>"><?= e(__('nav_courses')) ?></a></li>
                    <li><a href="<?= e(base_url('batches.php')) ?>"><?= e(__('nav_batches')) ?></a></li>
                    <li><a href="<?= e(base_url('teachers.php')) ?>"><?= e(__('nav_teachers')) ?></a></li>
                    <li><a href="<?= e(base_url('results.php')) ?>"><?= e(__('nav_results')) ?></a></li>
                    <li><a href="<?= e(base_url('notices.php')) ?>"><?= e(__('nav_notices')) ?></a></li>
                    <li><a href="<?= e(base_url('admission.php')) ?>"><?= e(__('nav_admission')) ?></a></li>
                    <li><a href="<?= e(base_url('portal/login.php')) ?>"><?= $locale === 'bn' ? 'স্টুডেন্ট পোর্টাল' : 'Student Portal' ?></a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-4">
                <h6 class="footer-heading"><?= e(__('footer_contact')) ?></h6>
                <ul class="footer-links list-unstyled">
                    <li><a href="<?= e(phone_link($config['phone_primary'])) ?>"><?= e($config['phone_primary']) ?></a></li>
                    <li><a href="<?= e(phone_link($config['phone_secondary'])) ?>"><?= e($config['phone_secondary']) ?></a></li>
                    <li><a href="<?= e(whatsapp_link($config['whatsapp'], __('whatsapp_prefill', ['course' => __('mathematics')]))) ?>" target="_blank" rel="noopener">WhatsApp</a></li>
                </ul>
            </div>
        </div>
        <hr class="footer-divider my-4">
        <p class="small text-center text-muted-soft mb-0">
            &copy; <?= date('Y') ?> <?= e($config['site_name']) ?>. <?= e(__('footer_rights')) ?>
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(base_url('assets/js/main.js')) ?>"></script>
</body>
</html>
