(function () {
  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function linkAttrs(url) {
    const safeUrl = escapeHtml(url);
    const isExternal = /^https?:\/\//i.test(url);
    const isHashOnly = url.startsWith("#");
    const attrs = isExternal
      ? ' target="_blank" rel="noopener noreferrer"'
      : isHashOnly
        ? ' data-scroll-link="true"'
        : "";
    return { href: safeUrl, attrs };
  }

  function mediaBlock(imageUrl, className) {
    if (!imageUrl) {
      return `<div class="${className}" aria-hidden="true"></div>`;
    }
    return `<div class="${className} has-image" aria-hidden="true"><img src="${escapeHtml(imageUrl)}" alt="" loading="lazy" decoding="async" /></div>`;
  }

  function renderPosts(posts) {
    const grid = document.getElementById("postsGrid");
    if (!grid || !Array.isArray(posts)) return;

    grid.innerHTML = posts
      .map(
        (post) => `
      <article class="story-card">
        <a class="story-card-hit" href="./post.php?id=${encodeURIComponent(post.id)}">
          ${mediaBlock(post.image_url, "story-media")}
          <div class="story-body">
            <p class="tag">${escapeHtml(post.tag)}</p>
            <h3 class="story-title">${escapeHtml(post.title)}</h3>
            <p class="muted">${escapeHtml(post.excerpt)}</p>
            <span class="textlink story-read-more">Read full story →</span>
          </div>
        </a>
      </article>`
      )
      .join("");
  }

  function renderSpotlight(items) {
    const grid = document.getElementById("spotlightGrid");
    if (!grid || !Array.isArray(items)) return;

    grid.innerHTML = items
      .map(
        (item) => `
      <article class="mini-card">
        <a class="mini-card-hit" href="./spotlight.php?id=${encodeURIComponent(item.id)}">
          ${mediaBlock(item.image_url, "mini-media")}
          <h3 class="mini-title">${escapeHtml(item.title)}</h3>
          <p class="muted">${escapeHtml(item.summary)}</p>
          <span class="textlink story-read-more">Read more →</span>
        </a>
      </article>`
      )
      .join("");
  }

  function setText(id, value) {
    const el = document.getElementById(id);
    if (el && value != null) el.textContent = value;
  }

  function applySettings(settings) {
    if (!settings) return;

    const heroImage = document.getElementById("heroImage");
    if (heroImage && settings.hero_image) {
      heroImage.src = settings.hero_image;
    }

    setText("heroEyebrow", settings.hero_eyebrow);
    setText("heroTitle", settings.hero_title);
    setText("heroSubtitle", settings.hero_subtitle);
    setText("updatesTitle", settings.updates_title);
    setText("updatesSubtitle", settings.updates_subtitle);
    setText("workTitle", settings.work_title);
    setText("workText", settings.work_text);
    setText("spotlightTitle", settings.spotlight_title);
    setText("spotlightSubtitle", settings.spotlight_subtitle);
    setText("inspirationTitle", settings.inspiration_title);
    setText("inspirationSubtitle", settings.inspiration_subtitle);
    setText("youtubeTitle", settings.youtube_title);
    setText("youtubeCaption", settings.youtube_caption);
    setText("subscribeTitle", settings.subscribe_title);
    setText("subscribeText", settings.subscribe_text);
    setText("joinCtaTitle", settings.join_cta_title);
    setText("joinCtaText", settings.join_cta_text);
    setText("contactTitle", settings.contact_title);
    setText("contactSubtitle", settings.contact_subtitle);

    const heroPrimary = document.getElementById("heroCtaPrimary");
    if (heroPrimary) {
      heroPrimary.textContent = settings.hero_cta_primary_label || heroPrimary.textContent;
      heroPrimary.href = settings.hero_cta_primary_url || heroPrimary.href;
    }

    const heroSecondary = document.getElementById("heroCtaSecondary");
    if (heroSecondary) {
      heroSecondary.textContent = settings.hero_cta_secondary_label || heroSecondary.textContent;
      heroSecondary.href = settings.hero_cta_secondary_url || heroSecondary.href;
    }

    const workCta = document.getElementById("workCta");
    if (workCta) {
      workCta.textContent = settings.work_cta_label || workCta.textContent;
      workCta.href = settings.work_cta_url || workCta.href;
    }

    const iframe = document.getElementById("youtubePlaylist");
    if (iframe && settings.youtube_playlist_id) {
      iframe.src = `https://www.youtube.com/embed/videoseries?list=${encodeURIComponent(settings.youtube_playlist_id)}`;
    }

    const links = document.getElementById("contactLinks");
    if (links) {
      const items = [
        ["Facebook page →", settings.facebook_page_url || "#contact"],
        ["Facebook group →", settings.facebook_group_url || "#contact"],
        ["Request donation/appeal →", settings.donation_url || "./appeal-request.html"],
      ];
      links.innerHTML = items
        .map(([label, url]) => {
          const link = linkAttrs(url);
          return `<li><a href="${link.href}" class="textlink"${link.attrs}>${escapeHtml(label)}</a></li>`;
        })
        .join("");
    }
  }

  async function loadContent() {
    try {
      const res = await fetch("./api/content.php", { headers: { Accept: "application/json" } });
      const data = await res.json();
      if (!data.success) return;

      applySettings(data.settings);
      renderPosts(data.posts);
      renderSpotlight(data.spotlight);
    } catch {
      // Static HTML remains as fallback.
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", loadContent);
  } else {
    loadContent();
  }
})();
