<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $metaDescription */
/** @var string $eyebrow */
/** @var string $title */
/** @var string $subtitle */
/** @var string $backUrl */
/** @var string $backLabel */
/** @var string $bodyHtml */
/** @var string $imageUrl */
/** @var string $externalUrl */
/** @var string $externalLabel */
/** @var string $publishedDate */

$externalUrl = $externalUrl ?? '';
$externalLabel = $externalLabel ?? 'Related link →';
$imageUrl = $imageUrl ?? '';
$publishedDate = $publishedDate ?? '';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($pageTitle) ?> — TRY KUET</title>
    <meta name="description" content="<?= e($metaDescription) ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="./style.css" />
  </head>
  <body>
    <a class="skip-link" href="#main">Skip to content</a>

    <header class="topbar" aria-label="Site header">
      <div class="container topbar-inner">
        <a class="logo" href="./index.html" aria-label="TRY KUET home">
          <span class="logo-mark" aria-hidden="true">TRY</span>
          <span class="logo-text">Social Service Club</span>
        </a>

        <div class="topbar-actions">
          <a class="btn small primary" href="./join.html">Join</a>
          <a class="btn small btn-contact" href="./index.html#contact">Contact</a>
          <button
            id="navToggle"
            class="btn small nav-toggle"
            type="button"
            aria-controls="primaryNav"
            aria-expanded="false"
            aria-label="Open menu"
          >
            <span aria-hidden="true">☰</span>
          </button>
        </div>
      </div>
    </header>

    <div id="navBackdrop" class="nav-backdrop" hidden></div>

    <nav id="primaryNav" class="primary-nav" aria-label="Primary navigation">
      <div class="container nav-inner">
        <a href="./index.html">Home</a>
        <a href="./index.html#updates">News & stories</a>
        <a href="./index.html#work">What we do</a>
        <a href="./index.html#spotlight">Spotlight</a>
        <a href="./join.html">Join TRY</a>
        <a href="./index.html#contact">Contact</a>
      </div>
    </nav>

    <main id="main">
      <header class="page-header">
        <div class="container">
          <p class="eyebrow"><?= e($eyebrow) ?></p>
          <h1><?= e($title) ?></h1>
          <?php if ($subtitle !== ''): ?>
            <p class="muted page-header-lede"><?= e($subtitle) ?></p>
          <?php endif; ?>
          <?php if ($publishedDate !== ''): ?>
            <p class="article-date"><?= e($publishedDate) ?></p>
          <?php endif; ?>
          <a class="textlink" href="<?= e($backUrl) ?>"><?= e($backLabel) ?></a>
        </div>
      </header>

      <article class="section article-section" aria-label="Article content">
        <div class="container article-layout">
          <?php if ($imageUrl !== ''): ?>
            <figure class="article-hero">
              <img src="<?= e($imageUrl) ?>" alt="" loading="lazy" decoding="async" />
            </figure>
          <?php endif; ?>

          <div class="article-content">
            <?= $bodyHtml ?>
          </div>

          <?php if ($externalUrl !== '' && isExternalUrl($externalUrl)): ?>
            <div class="article-actions">
              <a class="btn primary" href="<?= e($externalUrl) ?>" target="_blank" rel="noopener noreferrer">
                <?= e($externalLabel) ?>
              </a>
            </div>
          <?php endif; ?>
        </div>
      </article>
    </main>

    <footer class="footer" aria-label="Footer">
      <div class="container footer-bottom">
        <p class="muted">© <?= date('Y') ?> TRY KUET</p>
      </div>
    </footer>

    <script src="./scripts/site.js" defer></script>
  </body>
</html>
