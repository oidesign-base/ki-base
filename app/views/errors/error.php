<?php
/**
 * @var int             $status
 * @var string          $title
 * @var \Throwable|null $debug  only when app.debug is on
 */
?>
<div class="card">
    <div class="card-body py-80 px-32 text-center">
        <p class="text-primary-600 fw-bold mb-16 display-3 line-height-1"><?= (int) $status ?></p>
        <h2 class="h6 mb-16"><?= e($title) ?></h2>
        <p class="text-secondary-light max-w-650-px mx-auto mb-24"><?= e(t('error.' . $status . '.text')) ?></p>
        <?php if ($status !== 503): ?>
        <a href="<?= e(url('/')) ?>" class="btn btn-primary-600 radius-8 px-40 py-16"><?= e(t('section.back_home')) ?></a>
        <?php endif; ?>

        <?php if ($debug !== null): ?>
        <pre class="text-start text-sm mt-32 p-16 bg-neutral-50 radius-8 overflow-auto"><?= e(get_class($debug) . ': ' . $debug->getMessage() . "\n" . $debug->getFile() . ':' . $debug->getLine() . "\n\n" . $debug->getTraceAsString()) ?></pre>
        <?php endif; ?>
    </div>
</div>
