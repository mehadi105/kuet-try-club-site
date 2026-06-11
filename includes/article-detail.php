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
/** @var string $imageAlt */
/** @var list<array{url: string, caption: string}> $displayImages */
/** @var list<array<string, mixed>> $relatedPosts */

$externalUrl = $externalUrl ?? '';
$externalLabel = $externalLabel ?? 'Related link →';
$imageUrl = $imageUrl ?? '';
$publishedDate = $publishedDate ?? '';
$imageAlt = $imageAlt ?? articleImageAlt($title, $eyebrow);
$displayImages = $displayImages ?? postDisplayImages($imageUrl, null);
$relatedPosts = $relatedPosts ?? [];
$isExternalLink = $externalUrl !== '' && isExternalUrl($externalUrl);
$hasMedia = $displayImages !== [];
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
          <div class="topbar-ctas">
            <a class="btn small topbar-join" href="./join.html">Join</a>
            <button
              id="navToggle"
              class="btn small nav-toggle"
              type="button"
              aria-controls="primaryNav"
              aria-expanded="false"
              aria-label="Open menu"
            >
              <span class="nav-toggle-icon" aria-hidden="true"></span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <div id="navBackdrop" class="nav-backdrop" hidden></div>

    <nav id="primaryNav" class="primary-nav" aria-label="Primary navigation">
      <div class="nav-panel">
        <div class="mobile-nav-head">
          <p class="mobile-nav-title">Menu</p>
          <button class="mobile-nav-close" type="button" aria-label="Close menu">×</button>
        </div>
        <div class="container nav-inner">
          <a href="./index.html">Home</a>
          <a href="./index.html#updates">News &amp; stories</a>
          <a href="./index.html#work">What we do</a>
          <a href="./index.html#spotlight">Spotlight</a>
          <a href="./index.html#inspiration">Inspirational stories</a>
          <a href="./appeal-request.html">Request appeal</a>
          <a href="./application-status.html">Check application status</a>
          <a href="./index.html#contact">Contact</a>
        </div>
      </div>
    </nav>

    <main id="main">
      <div class="container article-page">
        <a class="textlink article-back" href="<?= e($backUrl) ?>"><?= e($backLabel) ?></a>

        <article class="article-shell" aria-label="Story content">
          <div class="article-hero<?= $hasMedia ? '' : ' article-hero--content-only' ?>">
            <div class="article-hero-content">
              <header class="article-hero-header">
                <?php if (!$hasMedia): ?>
                  <p class="tag tag-pill tag-pill--on-dark"><?= e($eyebrow) ?></p>
                <?php endif; ?>
                <h1 class="article-title"><?= e($title) ?></h1>
                <?php if ($publishedDate !== ''): ?>
                  <p class="article-date">Published on <?= e($publishedDate) ?></p>
                <?php endif; ?>
                <?php if ($subtitle !== ''): ?>
                  <div class="article-summary-box">
                    <p><?= e($subtitle) ?></p>
                  </div>
                <?php endif; ?>
              </header>

              <div class="article-content prose">
                <?= $bodyHtml ?>
              </div>

              <?php if ($externalUrl !== ''): ?>
                <footer class="article-actions">
                  <a
                    class="btn primary article-cta"
                    href="<?= e($externalUrl) ?>"
                    <?= $isExternalLink ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                  >
                    <?= e($externalLabel) ?>
                  </a>
                </footer>
              <?php endif; ?>
            </div>

            <?php if ($hasMedia): ?>
              <aside class="article-hero-media" aria-label="Story images">
                <p class="tag tag-pill tag-pill--media"><?= e($eyebrow) ?></p>
                <?php foreach ($displayImages as $index => $image): ?>
                  <figure class="article-media-item">
                    <img
                      src="<?= e($image['url']) ?>"
                      alt="<?= e($image['caption'] !== '' ? $image['caption'] : $imageAlt) ?>"
                      loading="<?= $index === 0 ? 'eager' : 'lazy' ?>"
                      decoding="async"
                    />
                    <?php if ($image['caption'] !== ''): ?>
                      <figcaption class="article-media-caption"><?= e($image['caption']) ?></figcaption>
                    <?php endif; ?>
                  </figure>
                <?php endforeach; ?>
              </aside>
            <?php endif; ?>
          </div>
        </article>

        <?php if ($relatedPosts !== []): ?>
          <section class="article-related" aria-labelledby="related-posts-heading">
            <h2 id="related-posts-heading" class="article-related-title">Related news &amp; stories</h2>
            <div class="card-grid article-related-grid">
              <?php foreach ($relatedPosts as $related): ?>
                <?php
                  $relatedImage = trim((string) ($related['image_url'] ?? ''));
                  $relatedDate = formatArticleDate((string) ($related['created_at'] ?? ''));
                ?>
                <article class="story-card">
                  <a class="story-card-hit" href="./post.php?id=<?= (int) $related['id'] ?>">
                    <?php if ($relatedImage !== ''): ?>
                      <img
                        class="story-media"
                        src="<?= e($relatedImage) ?>"
                        alt=""
                        loading="lazy"
                        decoding="async"
                      />
                    <?php else: ?>
                      <div class="story-media story-media-fallback" aria-hidden="true">
                        <span class="story-media-placeholder">TRY</span>
                      </div>
                    <?php endif; ?>
                    <div class="story-body">
                      <div class="story-body-top">
                        <p class="tag"><?= e((string) ($related['tag'] ?? '')) ?></p>
                        <?php if ($relatedDate !== ''): ?>
                          <p class="story-meta"><time><?= e($relatedDate) ?></time></p>
                        <?php endif; ?>
                      </div>
                      <h3 class="story-title"><?= e((string) ($related['title'] ?? '')) ?></h3>
                      <p class="story-excerpt muted"><?= e((string) ($related['excerpt'] ?? '')) ?></p>
                      <span class="story-read-more">Read full story <span aria-hidden="true">→</span></span>
                    </div>
                  </a>
                </article>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>
      </div>
    </main>

    <footer class="footer" aria-label="Footer">
      <div class="container footer-bottom">
        <p class="muted">© <?= date('Y') ?> TRY KUET</p>
      </div>
    </footer>

    <script src="./scripts/site.js" defer></script>
  </body>
</html>
