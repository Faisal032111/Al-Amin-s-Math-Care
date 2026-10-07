<?php

declare(strict_types=1);
?>
<div class="lang-toggle" role="group" aria-label="Language">
    <a href="<?= e(lang_url('en')) ?>" class="lang-btn <?= $locale === 'en' ? 'active' : '' ?>">EN</a>
    <a href="<?= e(lang_url('bn')) ?>" class="lang-btn <?= $locale === 'bn' ? 'active' : '' ?>">বাং</a>
</div>
