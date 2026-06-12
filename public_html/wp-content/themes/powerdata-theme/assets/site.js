/**
 * PowerData Site JS
 * - Scroll reveal (IntersectionObserver)
 * - Mobile nav toggle
 * - AJAX contact form with Cloudflare Turnstile
 * - AJAX enrollment form with Cloudflare Turnstile
 */
(function () {
  'use strict';

  // ── 1. SCROLL REVEAL ──────────────────────────────────────────────────────
  const io = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) {
          e.target.classList.add('in');
          io.unobserve(e.target);
        }
      });
    },
    { threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
  );

  function bindReveal() {
    document.querySelectorAll('.reveal:not(.in)').forEach(function (el) {
      io.observe(el);
    });
  }

  document.addEventListener('DOMContentLoaded', bindReveal);
  bindReveal();

  // ── 2. MOBILE NAV TOGGLE ─────────────────────────────────────────────────
  function updateHeaderHeight() {
    var hdr = document.querySelector('.site-header');
    if (hdr) document.documentElement.style.setProperty('--header-h', hdr.offsetHeight + 'px');
  }
  document.addEventListener('DOMContentLoaded', updateHeaderHeight);
  window.addEventListener('resize', updateHeaderHeight);
  updateHeaderHeight();

  document.addEventListener('click', function (e) {
    // Toggle open
    var toggle = e.target.closest('[data-menu-toggle]');
    if (toggle) {
      var menu = document.getElementById('pd-mobile-menu');
      if (menu) menu.classList.toggle('open');
    }
    // Close when a link inside is clicked
    if (e.target.closest('#pd-mobile-menu a')) {
      var m = document.getElementById('pd-mobile-menu');
      if (m) m.classList.remove('open');
    }
  });

  // ── 3. HELPERS ────────────────────────────────────────────────────────────
  function getField(form, name) {
    var el = form.querySelector('[name="' + name + '"]');
    return el ? el.value.trim() : '';
  }

  function showMsg(form, success, message) {
    var msg = form.querySelector('.pd-formmsg');
    if (!msg) return;
    msg.style.display = 'flex';
    msg.style.color   = success ? 'var(--accent-deep)' : 'var(--red)';
    var span = msg.querySelector('.pd-formmsg-text');
    if (span) span.textContent = message;
  }

  function setLoading(btn, loading) {
    btn.disabled    = loading;
    btn.textContent = loading ? 'Sending…' : btn.dataset.label;
  }

  function getTurnstileToken(form) {
    // Essential Blocks / Turnstile stores response in a hidden input
    var input = form.querySelector('[name="cf-turnstile-response"]');
    return input ? input.value : '';
  }

  // ── 4. CONTACT FORM ───────────────────────────────────────────────────────
  document.addEventListener('submit', function (e) {
    var form = e.target;

    // Contact form
    if (form.id === 'pd-contact-form') {
      e.preventDefault();

      var btn = form.querySelector('button[type="submit"]');
      if (!btn.dataset.label) btn.dataset.label = btn.textContent;

      var name    = getField(form, 'pd_name');
      var email   = getField(form, 'pd_email');
      var message = getField(form, 'pd_message');
      var token   = getTurnstileToken(form);

      if (!name || !email || !message) {
        showMsg(form, false, 'Please fill in all required fields.');
        return;
      }

      if (!token) {
        showMsg(form, false, 'Please complete the human verification above.');
        return;
      }

      setLoading(btn, true);

      var data = new FormData();
      data.append('action',                'pd_contact_form');
      data.append('nonce',                 (window.pdAjax || {}).contactNonce || '');
      data.append('pd_name',               name);
      data.append('pd_email',              email);
      data.append('pd_message',            message);
      data.append('cf-turnstile-response', token);

      fetch((window.pdAjax || {}).url || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body:   data,
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          setLoading(btn, false);
          showMsg(form, res.success, res.data ? res.data.message : 'Done!');
          if (res.success) {
            form.reset();
            // Reset Turnstile widget if present
            if (window.turnstile) window.turnstile.reset();
          }
        })
        .catch(function () {
          setLoading(btn, false);
          showMsg(form, false, 'A network error occurred. Please try again.');
        });
    }

    // Enroll form
    if (form.id === 'pd-enroll-form') {
      e.preventDefault();

      var btn2 = form.querySelector('button[type="submit"]');
      if (!btn2.dataset.label) btn2.dataset.label = btn2.textContent;

      var name2     = getField(form, 'pd_name');
      var email2    = getField(form, 'pd_email');
      var course    = getField(form, 'pd_course');
      var learners  = getField(form, 'pd_learners');
      var token2    = getTurnstileToken(form);

      if (!name2 || !email2) {
        showMsg(form, false, 'Please fill in your name and email.');
        return;
      }

      if (!token2) {
        showMsg(form, false, 'Please complete the human verification above.');
        return;
      }

      setLoading(btn2, true);

      var data2 = new FormData();
      data2.append('action',                'pd_enroll_form');
      data2.append('nonce',                 (window.pdAjax || {}).enrollNonce || '');
      data2.append('pd_name',               name2);
      data2.append('pd_email',              email2);
      data2.append('pd_course',             course);
      data2.append('pd_learners',           learners);
      data2.append('cf-turnstile-response', token2);

      fetch((window.pdAjax || {}).url || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body:   data2,
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          setLoading(btn2, false);
          showMsg(form, res.success, res.data ? res.data.message : 'Done!');
          if (res.success) {
            form2.reset();
            if (window.turnstile) window.turnstile.reset();
          }
        })
        .catch(function () {
          setLoading(btn2, false);
          showMsg(form, false, 'A network error occurred. Please try again.');
        });
    }
  });

  // ── 5. FAQ ACCORDION (training page) ─────────────────────────────────────
  // Native <details> handles toggle — just ensure only one is open at a time
  document.addEventListener('toggle', function (e) {
    if (e.target.tagName !== 'DETAILS') return;
    if (!e.target.open) return;
    var siblings = e.target.closest('.pd-faq-list');
    if (!siblings) return;
    siblings.querySelectorAll('details[open]').forEach(function (d) {
      if (d !== e.target) d.open = false;
    });
  }, true);

  // ── 6. TRAINING ENROLL BUTTON → PRE-SELECT COURSE ────────────────────────
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-course]');
    if (!btn) return;
    var select = document.getElementById('pd-course-select');
    if (select) {
      select.value = btn.dataset.course;
    }
    // Scroll to enroll section
    var enroll = document.getElementById('enroll');
    if (enroll) enroll.scrollIntoView({ behavior: 'smooth' });
  });

})();
