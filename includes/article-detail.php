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
/** @var string $articleLayout */
/** @var string $breadcrumbSection */
/** @var string $breadcrumbSectionUrl */
/** @var string $featuredImageUrl */
/** @var list<array{url: string, caption: string}> $gridImages */
/** @var list<array<string, mixed>> $relatedEvents */

$articleLayout = $articleLayout ?? 'split';
$featuredImageUrl = $featuredImageUrl ?? '';
$gridImages = $gridImages ?? [];
$relatedEvents = $relatedEvents ?? [];
$breadcrumbSection = $breadcrumbSection ?? 'Recent events';
$breadcrumbSectionUrl = $breadcrumbSectionUrl ?? './index.html#recent-events';
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
          <a href="./index.html#recent-events">Recent events</a>
          <a href="./index.html#inspiration">Inspirational stories</a>
          <a href="./appeal-request.html">Request appeal</a>
          <a href="./index.html#contact">Contact</a>
        </div>
      </div>
    </nav>

    <main id="main">
      <div class="container article-page">
        <a class="textlink article-back" href="<?= e($backUrl) ?>"><?= e($backLabel) ?></a>

        <?php if ($articleLayout === 'event'): ?>
        <?php $hasFeaturedImage = $featuredImageUrl !== ''; ?>
        <div class="event-article-wrap">
          <article class="article-shell article-shell--event" aria-label="Event content">
            <nav class="event-breadcrumbs" aria-label="Breadcrumb">
              <a href="./index.html">Home</a>
              <span class="event-breadcrumbs-sep" aria-hidden="true">/</span>
              <a href="<?= e($breadcrumbSectionUrl) ?>"><?= e($breadcrumbSection) ?></a>
              <span class="event-breadcrumbs-sep" aria-hidden="true">/</span>
              <span class="event-breadcrumbs-current"><?= e($title) ?></span>
            </nav>

            <header class="event-article-header">
              <p class="event-article-eyebrow"><?= e($eyebrow) ?></p>
              <h1 class="event-article-title"><?= e($title) ?></h1>
              <?php if ($publishedDate !== ''): ?>
                <p class="event-article-date">Published on <?= e($publishedDate) ?></p>
              <?php endif; ?>
            </header>

            <?php if ($hasFeaturedImage): ?>
              <figure class="event-article-feature">
                <img
                  src="<?= e($featuredImageUrl) ?>"
                  alt="<?= e($imageAlt) ?>"
                  loading="eager"
                  decoding="async"
                />
              </figure>
            <?php endif; ?>

            <?php if ($gridImages !== []): ?>
              <section class="event-gallery-section" aria-label="Event photo highlights">
                <h2 class="event-gallery-heading">Event highlights</h2>
                <div class="event-gallery-grid">
                  <?php foreach ($gridImages as $index => $tile): ?>
                    <figure class="event-gallery-tile">
                      <img
                        src="<?= e($tile['url']) ?>"
                        alt="<?= e($tile['caption'] !== '' ? $tile['caption'] : $imageAlt) ?>"
                        loading="<?= $index < 3 ? 'eager' : 'lazy' ?>"
                        decoding="async"
                      />
                      <?php if ($tile['caption'] !== ''): ?>
                        <figcaption class="event-gallery-tile-label"><?= e($tile['caption']) ?></figcaption>
                      <?php endif; ?>
                    </figure>
                  <?php endforeach; ?>
                </div>
              </section>
            <?php endif; ?>

            <div class="event-article-body">
              <?php if ($subtitle !== ''): ?>
                <p class="event-article-lede"><?= e($subtitle) ?></p>
              <?php endif; ?>

              <div class="article-content prose event-article-content">
                <?= $bodyHtml ?>
              </div>

              <?php if ($externalUrl !== ''): ?>
                <footer class="event-article-actions">
                  <a
                    class="btn primary"
                    href="<?= e($externalUrl) ?>"
                    <?= $isExternalLink ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                  >
                    <?= e($externalLabel) ?>
                  </a>
                </footer>
              <?php endif; ?>
            </div>
          </article>

          <?php if ($relatedEvents !== []): ?>
            <aside class="event-sidebar" aria-label="More recent events">
              <div class="event-sidebar-panel">
                <h2 class="event-sidebar-title">Recent events</h2>
                <ul class="event-sidebar-list">
                  <?php foreach ($relatedEvents as $related): ?>
                    <?php $relatedDate = formatArticleDate((string) ($related['created_at'] ?? '')); ?>
                    <li class="event-sidebar-item">
                      <a class="event-sidebar-hit" href="./spotlight.php?id=<?= (int) $related['id'] ?>">
                        <?php if (trim((string) ($related['image_url'] ?? '')) !== ''): ?>
                          <img
                            class="event-sidebar-thumb"
                            src="<?= e((string) $related['image_url']) ?>"
                            alt=""
                            loading="lazy"
                            decoding="async"
                          />
                        <?php else: ?>
                          <span class="event-sidebar-thumb event-sidebar-thumb-fallback" aria-hidden="true">TRY</span>
                        <?php endif; ?>
                        <span class="event-sidebar-copy">
                          <span class="event-sidebar-item-title"><?= e((string) ($related['title'] ?? '')) ?></span>
                          <?php if ($relatedDate !== ''): ?>
                            <span class="event-sidebar-item-date"><?= e($relatedDate) ?></span>
                          <?php endif; ?>
                        </span>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </aside>
          <?php endif; ?>
        </div>
        <?php else: ?>
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
        <?php endif; ?>

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
