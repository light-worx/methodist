<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $pageName ?? 'Connexion' }}</title>

  <!-- PWA manifest -->
  <link rel="manifest" href="{{ url('/manifest.json') }}" crossorigin="use-credentials" />
  <meta name="theme-color" content="#000000">

  <!-- Chrome/Android & iOS -->
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="application-name" content="Connexion">
  <link rel="icon" sizes="512x512" href="{{ asset('images/icons/android/android-launchericon-512-512.png') }}">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <meta name="apple-mobile-web-app-title" content="Connexion">
  <link rel="apple-touch-icon" href="{{ asset('images/icons/ios/512.png') }}">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="{{ asset('images/icons/android/android-launchericon-512-512.png') }}">

  <!-- CSS -->
  <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/leaflet.css') }}">
  <link rel="stylesheet" href="{{ asset('css/choices.min.css') }}">
  <script src="{{ asset('js/choices.min.js') }}"></script>
  <script src="{{ asset('js/leaflet.js') }}"></script>

  <style>
    a { text-decoration: none; }

    /* ── Header ── */
    .pwa-header {
      height: 56px;
      background-color: #fff;
      border-bottom: 1px solid #ddd;
      position: sticky;
      top: 0;
      z-index: 1020;
    }
    .pwa-header .navbar-title { font-size: 1.2rem; line-height: 1; }
    .pwa-header .btn {
      display: flex; align-items: center; justify-content: center;
      width: 40px; height: 40px;
    }
    @media (max-width: 576px) {
      .pwa-header { height: 50px; }
      .pwa-header .navbar-title { font-size: 1rem; }
      .pwa-header .btn { width: 36px; height: 36px; }
    }

    /* ── Setup nudge ── */
    #setupNudge {
      position: sticky;
      top: 56px;
      z-index: 1015;
      border-radius: 0;
      margin: 0;
      display: none;
      align-items: center;
      gap: .5rem;
      padding: .5rem 1rem;
      border-left: none; border-right: none; border-top: none;
    }
    #setupNudge.show { display: flex !important; }
    @media (max-width: 576px) { #setupNudge { top: 50px; } }

    /* ── Settings offcanvas ── */
    #offcanvasSettings .offcanvas-header {
      background-color: #f8f9fa;
      border-bottom: 1px solid #dee2e6;
    }
    .settings-card {
      background: #f8f9fa;
      border: 1px solid #dee2e6;
      border-radius: .5rem;
      padding: .875rem 1rem;
    }
    .settings-card.locked { opacity: .55; pointer-events: none; }
    .settings-section-label {
      font-size: .68rem; font-weight: 700;
      letter-spacing: .08em; text-transform: uppercase;
      color: #6c757d; margin-bottom: 0;
    }

    /* ── Mobile input group ── */
    #mobileInputGroup {
      border: 1px solid #dee2e6;
      border-radius: .375rem;
      overflow: hidden;
    }
    #mobileInputGroup:focus-within {
      border-color: #86b7fe;
      box-shadow: 0 0 0 .25rem rgba(13,110,253,.25);
    }
    #dialCode {
      border: none;
      border-right: 1px solid #dee2e6;
      border-radius: 0;
      background: #f8f9fa;
      padding: 0 8px;
      font-size: .8rem;
      color: #212529;
      cursor: pointer;
      flex-shrink: 0;
    }
    #mobileInput.form-control {
      border: none !important;
      border-radius: 0 !important;
      box-shadow: none !important;
    }

    /* ── Bottom toolbar ── */
    .pwa-bottom-toolbar {
      position: fixed; bottom: 0; width: 100vw; height: 56px;
      background-color: #f8f9fa; z-index: 1030;
      border-top: 1px solid #ddd;
      display: flex; justify-content: space-around; align-items: center;
    }
    .pwa-bottom-toolbar button,
    .pwa-bottom-toolbar a {
      flex: 1; text-align: center;
      transition: transform 0.2s, color 0.2s;
    }
    .pwa-bottom-toolbar button:disabled { opacity: 0.3; pointer-events: none; }
    .pwa-bottom-toolbar button:not(:disabled):hover,
    .pwa-bottom-toolbar a:hover { color: #0d6efd; transform: scale(1.1); }
  </style>
</head>

<body>

  <!-- ══ Header ══ -->
  <header class="pwa-header d-flex align-items-center justify-content-between px-3 py-2">
    <button class="btn p-0" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu"
            aria-label="Open menu">
      <i class="bi bi-list fs-3"></i>
    </button>
    <div class="flex-grow-1 text-center">
      <span class="navbar-title fw-semibold">{{ $pageName ?? 'Connexion' }}</span>
    </div>
    <button class="btn p-0" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#offcanvasSettings"
            aria-label="Open settings">
      <i class="bi bi-person fs-3"></i>
    </button>
  </header>


  <!-- ══ Setup nudge ══ -->
  <div id="setupNudge" class="alert alert-warning mb-0" role="alert" aria-live="polite">
    <i class="bi bi-info-circle-fill flex-shrink-0"></i>
    <span class="flex-grow-1 small">
      <strong>One quick step:</strong> pick your circuit to personalise the app.
    </span>
    <button type="button" class="btn btn-warning btn-sm flex-shrink-0" id="nudgeOpenSettings">
      Set up
    </button>
    <button type="button" class="btn-close ms-1 flex-shrink-0" id="nudgeDismiss" aria-label="Dismiss"></button>
  </div>


  <!-- ══ Left nav offcanvas ══ -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenu">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">Menu</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="list-unstyled">
        <li><a href="/"           class="d-block py-2"><i class="bi bi-house me-2"></i> Home</a></li>
        <li><a href="/lectionary" class="d-block py-2"><i class="bi bi-book me-2"></i> Lectionary</a></li>
        <li><a href="/ideas"      class="d-block py-2"><i class="bi bi-lightbulb me-2"></i> Ministry ideas</a></li>
        <li><a href="/admin"      class="d-block py-2"><i class="bi bi-lock me-2"></i> Login</a></li>
      </ul>
    </div>
  </div>


  <!-- ══ Right settings offcanvas ══ -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSettings" aria-labelledby="offcanvasSettingsLabel">

    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="offcanvasSettingsLabel">Settings</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body px-3 py-3">

      <!-- ── Circuit card ── -->
      <div class="settings-card mb-3" id="cardCircuit">
        <p class="settings-section-label mb-2">Your circuit</p>
        <div class="d-flex align-items-center gap-2">
          <select class="form-select form-select-sm flex-grow-1" id="circuitSelect">
            <option value="">Choose…</option>
            @foreach($circuits as $circuit)
              <option value="{{ $circuit->id }}">{{ $circuit->circuit }}</option>
            @endforeach
          </select>
          <button class="btn btn-primary btn-sm px-3" id="btnSaveCircuit">Save</button>
        </div>
      </div>

      <!-- ── Email card ── -->
      <div class="settings-card mb-3" id="cardEmail">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <p class="settings-section-label mb-0">Email</p>
          <span id="emailBadge" class="badge d-none"></span>
        </div>

        <div id="emailInputRow" class="d-flex align-items-center gap-2 mb-1">
          <input type="email" class="form-control form-control-sm flex-grow-1"
                 id="emailInput" placeholder="you@example.com">
          <button class="btn btn-primary btn-sm px-3" id="btnSendPin">Send code</button>
        </div>

        <div id="pinRow" class="d-none">
          <p class="small text-muted mb-2">
            Enter the 6-digit code sent to <strong id="pinEmailDisplay"></strong>
          </p>
          <div class="d-flex align-items-center gap-2">
            <input type="text" inputmode="numeric" maxlength="6"
                   class="form-control form-control-sm text-center flex-grow-1"
                   id="pinInput" placeholder="000000"
                   style="font-size:1.25rem;letter-spacing:.2em">
            <button class="btn btn-primary btn-sm px-3" id="btnVerifyPin">Verify</button>
          </div>
          <div class="d-flex align-items-center justify-content-between mt-1">
            <button class="btn btn-link btn-sm p-0 text-muted" id="btnResendPin">Resend code</button>
            <button class="btn btn-link btn-sm p-0 text-muted" id="btnChangeEmail">Change email</button>
          </div>
        </div>

        <div id="emailFeedback" class="small mt-1 d-none"></div>
      </div>

      <!-- ── Mobile card — locked until email verified ── -->
      <div class="settings-card mb-3 locked" id="cardMobile">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <p class="settings-section-label mb-0">Mobile number</p>
          <span id="mobileLockBadge" class="badge bg-secondary" style="font-size:.65rem">
            Verify email first
          </span>
        </div>

        <!-- Fused dial-code + number input -->
        <div class="d-flex align-items-stretch mb-1" id="mobileInputGroup">
          <select id="dialCode" disabled>
            <option value="+27"  >🇿🇦 +27</option>
            <option value="+267" >🇧🇼 +267</option>
            <option value="+264" >🇳🇦 +264</option>
            <option value="+258" >🇲🇿 +258</option>
            <option value="+266" >🇱🇸 +266</option>
            <option value="+268" >🇸🇿 +268</option>
          </select>
          <input type="tel" id="mobileInput"
                 class="form-control form-control-sm"
                 placeholder="082 000 0000"
                 disabled>
        </div>

        <div class="d-flex align-items-center justify-content-between">
          <small id="mobilePreview" class="text-muted"></small>
          <button class="btn btn-primary btn-sm px-3" id="btnSaveMobile" disabled>Save</button>
        </div>
      </div>

      <!-- ── Notifications card — locked until mobile added ── -->
      <div class="settings-card locked" id="cardNotif">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <p class="settings-section-label mb-0">Notifications</p>
          <span id="notifLockBadge" class="badge bg-secondary" style="font-size:.65rem">
            Add mobile first
          </span>
        </div>
        <div class="d-flex flex-column gap-2">
          @foreach(['notif_lectionary' => 'Lectionary reminders', 'notif_circuit' => 'Circuit news', 'notif_ideas' => 'Ministry ideas'] as $key => $label)
            <div class="d-flex align-items-center justify-content-between">
              <label class="form-check-label small" for="{{ $key }}">{{ $label }}</label>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input notif-toggle" type="checkbox"
                       id="{{ $key }}" data-key="{{ $key }}" disabled>
              </div>
            </div>
          @endforeach
        </div>
      </div>

    </div>
  </div>


  <!-- ══ Main content ══ -->
  <main class="pt-1 px-3 pb-5" id="pwaMainContent">
    <div class="d-flex justify-content-center my-2">
      <button id="installPwaBtn" class="btn btn-primary btn-md d-none">
        <i class="bi bi-download me-2"></i> Install App
      </button>
    </div>
    <div id="pwaContentWrapper">
      {{ $slot }}
    </div>
  </main>


  <!-- ══ Bottom toolbar ══ -->
  <nav class="pwa-bottom-toolbar shadow-sm">
    <button class="btn btn-link text-dark" id="pwaBackBtn" title="Back" disabled>
      <i class="bi bi-arrow-left fs-4"></i>
    </button>
    <a class="btn btn-link text-dark" href="/" title="Home">
      <i class="bi bi-house fs-4"></i>
    </a>
    <a class="btn btn-link text-dark" href="/ideas" title="Ideas">
      <i class="bi bi-lightbulb fs-4"></i>
    </a>
  </nav>


  <!-- Bootstrap must load before @stack('scripts') and app logic -->
  <script src="{{ asset('js/bootstrap.min.js') }}"></script>
  @stack('scripts')
  <script>

    // ══ Shared API helper ════════════════════════════════════════════
    async function api(path, body = null) {
      const opts = {
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        credentials: 'same-origin',
        method: body ? 'POST' : 'GET',
      };
      if (body) opts.body = JSON.stringify(body);
      const res  = await fetch('/api' + path, opts);
      const data = await res.json();
      if (!res.ok) throw new Error(data.error ?? 'Something went wrong.');
      return data;
    }


    // ══ Settings panel ═══════════════════════════════════════════════
    (function () {
      let prefs = {};

      const el   = id => document.getElementById(id);
      const show = id => el(id).classList.remove('d-none');
      const hide = id => el(id).classList.add('d-none');

      function setFeedback(msg, type = 'danger') {
        const fb = el('emailFeedback');
        fb.textContent = msg;
        fb.className   = `small mt-1 text-${type}`;
        fb.classList.remove('d-none');
        setTimeout(() => fb.classList.add('d-none'), 4000);
      }

      // ── Mobile helpers ───────────────────────────────────────────

      // Strip leading zeros from local number and combine with dial code
      // e.g. dialCode="+27", local="0821234567" → "+27821234567"
      //      dialCode="+27", local="821234567"  → "+27821234567"
      function buildE164() {
        const code  = el('dialCode').value;
        const local = el('mobileInput').value.trim().replace(/\s+/g, '');
        const stripped = local.startsWith('0') ? local.slice(1) : local;
        return stripped ? code + stripped : '';
      }

      function updateMobilePreview() {
        const full = buildE164();
        el('mobilePreview').textContent = full
          ? 'Saves as ' + full
          : '';
      }

      // Restore a stored E.164 number back into the two fields
      // e.g. "+27821234567" → dialCode="+27", input="0821234567"
      function restoreMobile(e164) {
        if (!e164) return;
        const codes = ['+268', '+267', '+266', '+264', '+263', '+258', '+27'];
        const match = codes.find(c => e164.startsWith(c));
        if (match) {
          el('dialCode').value   = match;
          el('mobileInput').value = '0' + e164.slice(match.length);
        } else {
          el('mobileInput').value = e164;
        }
        updateMobilePreview();
      }

      // ── Render UI from current prefs state ───────────────────────
      function renderPrefs() {

        // Circuit
        if (prefs.circuit_id) {
          el('circuitSelect').value = prefs.circuit_id;
        }

        // Email badge
        const badge = el('emailBadge');
        badge.className = 'badge';
        if (prefs.email_verified) {
          badge.textContent = 'Verified';
          badge.classList.add('bg-success', 'text-white');
          el('emailInput').value    = prefs.email ?? '';
          el('emailInput').disabled = true;
          el('btnSendPin').disabled = true;
          hide('pinRow');
          show('emailInputRow');
        } else if (prefs.email) {
          badge.textContent = 'Unverified';
          badge.classList.add('bg-warning', 'text-dark');
          el('emailInput').value    = prefs.email;
          el('emailInput').disabled = false;
          el('btnSendPin').disabled = false;
        }
        badge.classList.remove('d-none');

        // Mobile — unlocked once email verified
        const mobileUnlocked = !!prefs.email_verified;
        el('cardMobile').classList.toggle('locked', !mobileUnlocked);
        el('dialCode').disabled      = !mobileUnlocked;
        el('mobileInput').disabled   = !mobileUnlocked;
        el('btnSaveMobile').disabled = !mobileUnlocked;
        if (mobileUnlocked) hide('mobileLockBadge');
        if (prefs.mobile) restoreMobile(prefs.mobile);

        // Notifications — unlocked once mobile saved
        const notifUnlocked = mobileUnlocked && !!prefs.mobile;
        el('cardNotif').classList.toggle('locked', !notifUnlocked);
        if (notifUnlocked) hide('notifLockBadge');
        document.querySelectorAll('.notif-toggle').forEach(t => {
          t.disabled = !notifUnlocked;
          if (prefs[t.dataset.key] !== undefined) t.checked = prefs[t.dataset.key];
        });

        // Dismiss nudge if circuit now set
        if (prefs.circuit_id) el('setupNudge').classList.remove('show');
      }

      // ── Load prefs when panel opens ──────────────────────────────
      el('offcanvasSettings').addEventListener('show.bs.offcanvas', async () => {
        try {
          prefs = await api('/preferences');
          renderPrefs();
        } catch (e) {
          console.warn('Could not load preferences:', e);
        }
      });

      // ── Save circuit ─────────────────────────────────────────────
      el('btnSaveCircuit').addEventListener('click', async () => {
        const circuit_id = el('circuitSelect').value;
        if (!circuit_id) { alert('Please choose a circuit.'); return; }
        try {
          await api('/preferences', { circuit_id });
          prefs.circuit_id = circuit_id;
          renderPrefs();
        } catch (e) { alert(e.message); }
      });

      // ── Send PIN ─────────────────────────────────────────────────
      el('btnSendPin').addEventListener('click', async () => {
        const email = el('emailInput').value.trim();
        if (!email) { setFeedback('Please enter an email address.'); return; }
        el('btnSendPin').disabled = true;
        try {
          await api('/preferences/send-pin', { email });
          prefs.email          = email;
          prefs.email_verified = false;
          el('pinEmailDisplay').textContent = email;
          hide('emailInputRow');
          show('pinRow');
          setFeedback('Code sent — check your inbox.', 'success');
        } catch (e) {
          setFeedback(e.message);
        } finally {
          el('btnSendPin').disabled = false;
        }
      });

      // ── Resend PIN ───────────────────────────────────────────────
      el('btnResendPin').addEventListener('click', async () => {
        try {
          await api('/preferences/send-pin', { email: prefs.email });
          setFeedback('New code sent.', 'success');
        } catch (e) { setFeedback(e.message); }
      });

      // ── Change email ─────────────────────────────────────────────
      el('btnChangeEmail').addEventListener('click', () => {
        hide('pinRow');
        show('emailInputRow');
        el('emailInput').value    = '';
        el('emailInput').disabled = false;
        el('btnSendPin').disabled = false;
        el('pinInput').value      = '';
      });

      // ── Verify PIN ───────────────────────────────────────────────
      el('btnVerifyPin').addEventListener('click', async () => {
        const pin = el('pinInput').value.trim();
        if (pin.length !== 6) { setFeedback('Please enter the full 6-digit code.'); return; }
        el('btnVerifyPin').disabled = true;
        try {
          await api('/preferences/verify-pin', { pin });
          prefs.email_verified = true;
          renderPrefs();
          setFeedback('Email verified!', 'success');
        } catch (e) {
          setFeedback(e.message);
        } finally {
          el('btnVerifyPin').disabled = false;
        }
      });

      // ── Mobile preview as user types ─────────────────────────────
      el('mobileInput').addEventListener('input', updateMobilePreview);
      el('dialCode').addEventListener('change', updateMobilePreview);

      // ── Save mobile ──────────────────────────────────────────────
      el('btnSaveMobile').addEventListener('click', async () => {
        const mobile = buildE164();
        if (!mobile) { setFeedback('Please enter a mobile number.'); return; }
        try {
          await api('/preferences', { mobile });
          prefs.mobile = mobile;
          renderPrefs();
          setFeedback('Mobile number saved.', 'success');
        } catch (e) { setFeedback(e.message); }
      });

      // ── Notification toggles ─────────────────────────────────────
      document.querySelectorAll('.notif-toggle').forEach(toggle => {
        toggle.addEventListener('change', async function () {
          try {
            await api('/preferences', { [this.dataset.key]: this.checked });
            prefs[this.dataset.key] = this.checked;
          } catch (e) {
            this.checked = !this.checked;
            setFeedback(e.message);
          }
        });
      });

    })();


    // ══ Nudge banner ═════════════════════════════════════════════════
    document.addEventListener('DOMContentLoaded', function () {

      api('/preferences').then(prefs => {
        if (!prefs.circuit_id) {
          document.getElementById('setupNudge').classList.add('show');
        }
      }).catch(() => {
        document.getElementById('setupNudge').classList.add('show');
      });

      document.getElementById('nudgeOpenSettings').addEventListener('click', () => {
        document.getElementById('setupNudge').classList.remove('show');
        new bootstrap.Offcanvas(document.getElementById('offcanvasSettings')).show();
      });

      document.getElementById('nudgeDismiss').addEventListener('click', () => {
        document.getElementById('setupNudge').classList.remove('show');
      });

      // ── Back button ──────────────────────────────────────────────
      const backBtn = document.getElementById('pwaBackBtn');

      function updateBackButton() {
        backBtn.disabled = ['/', '/index.html'].includes(window.location.pathname);
      }

      backBtn.addEventListener('click', () => window.history.back());
      window.addEventListener('popstate',     updateBackButton);
      window.addEventListener('pushstate',    updateBackButton);
      window.addEventListener('replacestate', updateBackButton);
      updateBackButton();
    });


    // ══ Service worker ════════════════════════════════════════════════
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker
        .register("{{ url('/service-worker.js') }}", { scope: '/' })
        .then(reg => console.log('SW registered:', reg.scope))
        .catch(err => console.warn('SW registration failed:', err));
    }


    // ══ PWA install prompt ════════════════════════════════════════════
    if (location.protocol === 'https:' || ['localhost', '127.0.0.1'].includes(location.hostname)) {
      let deferredPrompt;
      const installBtn = document.getElementById('installPwaBtn');

      window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        installBtn.classList.remove('d-none');
      });

      installBtn.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        console.log('Install outcome:', outcome);
        deferredPrompt = null;
        installBtn.classList.add('d-none');
      });

      window.addEventListener('appinstalled', () => {
        console.log('PWA installed');
        installBtn.classList.add('d-none');
      });
    }

  (function () {

      // ── VAPID public key (set in .env as VAPID_PUBLIC_KEY) ───────────
      const VAPID_PUBLIC_KEY = '{{ config("webpush.vapid.public_key") }}';

      function urlBase64ToUint8Array(base64String) {
          const padding = '='.repeat((4 - base64String.length % 4) % 4);
          const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
          const raw     = atob(base64);
          return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
      }

      // ── Subscribe the browser to Web Push ───────────────────────────
      async function subscribeToPush() {
          if (!('PushManager' in window)) {
              console.warn('Push not supported in this browser.');
              return false;
          }

          const reg = await navigator.serviceWorker.ready;

          // Check for existing subscription first
          let subscription = await reg.pushManager.getSubscription();

          if (!subscription) {
              try {
                  subscription = await reg.pushManager.subscribe({
                      userVisibleOnly:      true,
                      applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
                  });
              } catch (e) {
                  console.warn('Push subscription failed:', e);
                  return false;
              }
          }

          // Send endpoint + keys to our API
          const key    = subscription.getKey('p256dh');
          const auth   = subscription.getKey('auth');
          const encode = k => btoa(String.fromCharCode(...new Uint8Array(k)));

          try {
              await api('/preferences/push-subscribe', {
                  endpoint: subscription.endpoint,
                  keys: {
                      p256dh: encode(key),
                      auth:   encode(auth),
                  },
              });
              return true;
          } catch (e) {
              console.warn('Failed to save push subscription:', e);
              return false;
          }
      }

      // ── Unsubscribe ──────────────────────────────────────────────────
      async function unsubscribeFromPush() {
          const reg = await navigator.serviceWorker.ready;
          const subscription = await reg.pushManager.getSubscription();
          if (subscription) {
              await subscription.unsubscribe();
          }
          // Clear on server
          await api('/preferences', { push_endpoint: null });
      }

      // ── Hook into notif toggles ──────────────────────────────────────
      // When the first notification toggle is switched on, request push permission
      // and subscribe. If permission is denied, revert the toggle.
      document.querySelectorAll('.notif-toggle').forEach(toggle => {
          toggle.addEventListener('change', async function () {
              const isFirstEnable = this.checked &&
                  [...document.querySelectorAll('.notif-toggle')].filter(t => t.checked).length === 1;

              if (this.checked && isFirstEnable) {
                  const permission = await Notification.requestPermission();

                  if (permission !== 'granted') {
                      this.checked = false;
                      alert('Please allow notifications in your browser settings to enable this.');
                      return;
                  }

                  const ok = await subscribeToPush();
                  if (!ok) {
                      this.checked = false;
                      return;
                  }
              }

              // Save the individual preference
              try {
                  await api('/preferences', { [this.dataset.key]: this.checked });
              } catch (e) {
                  this.checked = !this.checked;
              }

              // If all toggles are now off, unsubscribe entirely
              const anyOn = [...document.querySelectorAll('.notif-toggle')].some(t => t.checked);
              if (!anyOn) {
                  await unsubscribeFromPush();
              }
          });
      });

  })();
  </script>
</body>
</html>