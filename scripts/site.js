(function () {
  const prefersReduced =
    window.matchMedia &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function normalizePath(pathname) {
    const path = (pathname || "/").replace(/\/$/, "");
    if (path === "") return "index";
    const file = path.split("/").pop() || "index";
    return file.replace(/\.html$/i, "") || "index";
  }

  function getHeaderOffset() {
    const topbar = document.querySelector(".topbar");
    const nav = document.querySelector(".primary-nav");
    let height = topbar ? topbar.offsetHeight : 58;

    if (nav && window.getComputedStyle(nav).display !== "none") {
      height += nav.offsetHeight;
    }

    return height + 12;
  }

  function syncScrollOffset() {
    document.documentElement.style.setProperty(
      "--header-height",
      `${document.querySelector(".topbar")?.offsetHeight || 58}px`
    );
    document.documentElement.style.setProperty(
      "--scroll-offset",
      `${getHeaderOffset()}px`
    );
  }

  function scrollToHash(hash, behavior) {
    if (!hash || hash === "#") return false;

    const target = document.querySelector(hash);
    if (!target) return false;

    const top =
      target.getBoundingClientRect().top +
      window.scrollY -
      getHeaderOffset();

    window.scrollTo({
      top: Math.max(0, top),
      behavior: behavior || (prefersReduced ? "auto" : "smooth"),
    });

    return true;
  }

  function setNavOpen(open) {
    const nav = document.getElementById("primaryNav");
    const btn = document.getElementById("navToggle");
    const backdrop = document.getElementById("navBackdrop");
    if (!nav || !btn || !backdrop) return;

    nav.classList.toggle("is-open", open);
    btn.setAttribute("aria-expanded", String(open));
    backdrop.hidden = !open;
    document.documentElement.classList.toggle("nav-open", open);
    syncScrollOffset();
  }

  function initNav() {
    const nav = document.getElementById("primaryNav");
    const btn = document.getElementById("navToggle");
    const backdrop = document.getElementById("navBackdrop");
    if (!nav || !btn || !backdrop) return;

    btn.addEventListener("click", () => {
      setNavOpen(!nav.classList.contains("is-open"));
    });

    backdrop.addEventListener("click", () => setNavOpen(false));

    nav.addEventListener("click", (e) => {
      if (e.target.closest("a")) {
        setNavOpen(false);
      }
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") setNavOpen(false);
    });
  }

  function initAnchorNavigation() {
    document.addEventListener("click", (e) => {
      const link = e.target.closest("a[href]");
      if (!link) return;

      const href = link.getAttribute("href");
      if (!href || href === "#") {
        e.preventDefault();
        return;
      }

      let url;
      try {
        url = new URL(href, window.location.href);
      } catch {
        return;
      }

      const isSameDocument =
        url.origin === window.location.origin &&
        normalizePath(url.pathname) === normalizePath(window.location.pathname);

      const nav = document.getElementById("primaryNav");
      const menuWasOpen = nav && nav.classList.contains("is-open");

      if (menuWasOpen && (link.closest("#primaryNav") || link.closest(".topbar-actions"))) {
        setNavOpen(false);
      }

      if (!url.hash) return;

      if (!isSameDocument) return;

      if (!href.startsWith("#") && !href.includes("#")) return;

      e.preventDefault();

      const runScroll = () => {
        if (!scrollToHash(url.hash)) {
          window.location.hash = url.hash;
          return;
        }
        history.replaceState(null, "", `${url.pathname}${url.hash}`);
      };

      if (menuWasOpen && !prefersReduced) {
        window.setTimeout(runScroll, 150);
      } else {
        runScroll();
      }
    });

    const runInitialHash = () => {
      if (!window.location.hash) return;
      window.setTimeout(() => {
        if (!scrollToHash(window.location.hash, prefersReduced ? "auto" : "smooth")) {
          const target = document.querySelector(window.location.hash);
          if (target) {
            target.scrollIntoView({
              behavior: prefersReduced ? "auto" : "smooth",
              block: "start",
            });
          }
        }
      }, 120);
    };

    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", runInitialHash);
    } else {
      runInitialHash();
    }
  }

  function initReveal() {
    if (prefersReduced) return;

    const elements = Array.from(document.querySelectorAll(".reveal"));
    if (elements.length === 0) return;

    if (!("IntersectionObserver" in window)) {
      elements.forEach((el) => el.classList.add("is-visible"));
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          if (!entry.isIntersecting) continue;
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        }
      },
      { threshold: 0.12, rootMargin: "0px 0px -5% 0px" }
    );

    elements.forEach((el) => observer.observe(el));
  }

  function initPublicForms() {
    const subscribeForm = document.getElementById("subscribeForm");
    const subscribeSuccess = document.getElementById("subscribeSuccess");
    if (subscribeForm) {
      subscribeForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (!subscribeForm.checkValidity()) {
          subscribeForm.reportValidity();
          return;
        }
        if (subscribeSuccess) subscribeSuccess.hidden = true;
        const email = subscribeForm.querySelector('input[name="email"]');
        try {
          const body = new FormData();
          body.append("email", email ? email.value : "");
          const res = await fetch("./api/submit-subscribe.php", {
            method: "POST",
            body,
          });
          const data = await res.json();
          if (subscribeSuccess) {
            subscribeSuccess.textContent =
              data.message || (data.success ? "Subscribed." : "Could not subscribe.");
            subscribeSuccess.hidden = false;
          }
          if (data.success) subscribeForm.reset();
        } catch {
          if (subscribeSuccess) {
            subscribeSuccess.textContent =
              "Could not reach the server. Start PHP with: php -S localhost:8000";
            subscribeSuccess.hidden = false;
          }
        }
      });
    }

    const contactForm = document.getElementById("contactForm");
    const contactSuccess = document.getElementById("contactFormSuccess");
    const contactError = document.getElementById("contactFormError");
    if (contactForm) {
      contactForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (contactSuccess) contactSuccess.hidden = true;
        if (contactError) contactError.hidden = true;
        if (!contactForm.checkValidity()) {
          contactForm.reportValidity();
          return;
        }

        const submitBtn = contactForm.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        try {
          const res = await fetch("./api/submit-contact.php", {
            method: "POST",
            body: new FormData(contactForm),
          });
          const data = await res.json();
          if (data.success) {
            if (contactSuccess) {
              contactSuccess.textContent = data.message || "Message sent.";
              contactSuccess.hidden = false;
            }
            contactForm.reset();
          } else if (contactError) {
            contactError.textContent = data.message || "Could not send message.";
            contactError.hidden = false;
          }
        } catch {
          if (contactError) {
            contactError.textContent =
              "Could not reach the server. Start PHP with: php -S localhost:8000";
            contactError.hidden = false;
          }
        } finally {
          if (submitBtn) submitBtn.disabled = false;
        }
      });
    }
  }

  syncScrollOffset();
  window.addEventListener("resize", syncScrollOffset);
  initNav();
  initAnchorNavigation();
  initReveal();
  initPublicForms();
})();
