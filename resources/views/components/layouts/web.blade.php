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
  <link rel="icon" sizes="512x512" href="{{ asset('methodist/images/icons/android/android-launchericon-512-512.png') }}">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <meta name="apple-mobile-web-app-title" content="Connexion">
  <link rel="apple-touch-icon" href="{{ asset('methodist/images/icons/ios/512.png') }}">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="{{ asset('methodist/images/icons/android/android-launchericon-512-512.png') }}">

  <!-- CSS -->
  <link rel="stylesheet" href="{{ asset('methodist/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('methodist/css/bootstrap-icons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('methodist/css/leaflet.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
  <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
  <script src="{{ asset('methodist/js/leaflet.js') }}"></script>

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
    .pwa-header .navbar-title {
      font-size: 1.2rem;
      line-height: 1;
    }
    .pwa-header .btn {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
    }
    @media (max-width: 576px) {
      .pwa-header { height: 50px; }
      .pwa-header .navbar-title { font-size: 1rem; }
      .pwa-header .btn { width: 36px; height: 36px; }
    }

    /* ── Settings offcanvas ── */
    #offcanvasSettings .offcanvas-header {
      background-color: #f8f9fa;
      border-bottom: 1px solid #dee2e6;
    }
    #offcanvasSettings .settings-avatar {
      width: 52px;
      height: 52px;
      border-radius: 50%;
      background: #0d6efd1a;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: #0d6efd;
      margin-bottom: .75rem;
    }
    #offcanvasSettings .settings-section-label {
      font-size: .7rem;
      font-weight: 700;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: #6c757d;
      margin-bottom: .5rem;
    }

    /* ── Setup nudge toast ── */
    #setupNudge {
      position: fixed;
      /* Sits just below the sticky header */
      top: 60px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 1055;
      min-width: 280px;
      max-width: calc(100vw - 2rem);
      border-radius: .75rem;
      box-shadow: 0 4px 16px rgba(0,0,0,.12);
      cursor: pointer;
      transition: opacity .3s, transform .3s;
      /* Hidden by default; JS shows it */
      display: none;
    }
    #setupNudge.show {
      display: flex !important;
    }
    #setupNudge:hover {
      box-shadow: 0 6px 20px rgba(0,0,0,.18);
    }

    /* ── Bottom toolbar ── */
    .pwa-bottom-toolbar {
      position: fixed;
      bottom: 0;
      width: 100vw;
      height: 56px;
      background-color: #f8f9fa;
      z-index: 1030;
      border-top: 1px solid #ddd;
      display: flex;
      justify-content: space-around;
      align-items: center;
    }
    .pwa-bottom-toolbar button,
    .pwa-bottom-toolbar a {
      flex: 1;
      text-align: center;
      transition: transform 0.2s, color 0.2s;
    }
    .pwa-bottom-toolbar button:disabled {
      opacity: 0.3;
      pointer-events: none;
    }
    .pwa-bottom-toolbar button:not(:disabled):hover,
    .pwa-bottom-toolbar a:hover {
      color: #0d6efd;
      transform: scale(1.1);
    }
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

    {{-- Person icon now opens the right-side settings slideover --}}
    <button class="btn p-0" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#offcanvasSettings"
            aria-label="Open settings">
      <i class="bi bi-person fs-3"></i>
    </button>

  </header>


  <!-- ══ Left nav offcanvas ══ -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenu">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">Menu</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="list-unstyled">
        <li><a href="/"          class="d-block py-2"><i class="bi bi-house me-2"></i> Home</a></li>
        <li><a href="/lectionary" class="d-block py-2"><i class="bi bi-book me-2"></i> Lectionary</a></li>
        <li><a href="/ideas"     class="d-block py-2"><i class="bi bi-lightbulb me-2"></i> Ministry ideas</a></li>
        <li><a href="/admin"     class="d-block py-2"><i class="bi bi-lock me-2"></i> Login</a></li>
      </ul>
    </div>
  </div>


  <!-- ══ Right settings offcanvas ══ -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSettings" aria-labelledby="offcanvasSettingsLabel">

    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="offcanvasSettingsLabel">Settings</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">

      {{-- Avatar / identity area --}}
      <div class="text-center mb-4">
        <div class="settings-avatar mx-auto">
          <i class="bi bi-person-fill"></i>
        </div>
        <p class="mb-0 text-muted small" id="settingsEmailDisplay">Not signed in</p>
      </div>

      <hr class="my-3">

      {{-- Circuit section --}}
      <p class="settings-section-label">Your Circuit</p>
      <form id="userSettingsForm">

        <div class="mb-3">
          <label for="circuitSelect" class="form-label">Select your Circuit</label>
          <select class="form-select" id="circuitSelect" required>
            <option value="">Choose…</option>
            @foreach($circuits as $circuit)
              <option value="{{ $circuit->id }}">{{ $circuit->circuit }}</option>
            @endforeach
          </select>
        </div>

        <hr class="my-3">

        {{-- Account section --}}
        <p class="settings-section-label">Account</p>
        <div class="mb-3">
          <label for="userEmail" class="form-label">Email <span class="text-muted">(optional)</span></label>
          <input type="email" class="form-control" id="userEmail" placeholder="you@example.com">
        </div>

        <div class="d-grid mt-4">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check2 me-1"></i> Save Settings
          </button>
        </div>

      </form>
    </div>
  </div>


  <!-- ══ Setup nudge (shown when circuit not yet chosen) ══ -->
  <div id="setupNudge"
       class="alert alert-warning alert-dismissible mb-0 align-items-center gap-2 px-3 py-2"
       role="alert"
       aria-live="polite">
    <i class="bi bi-info-circle-fill flex-shrink-0"></i>
    <span class="flex-grow-1 small">
      <strong>One quick step:</strong> pick your circuit to personalise the app.
    </span>
    <button type="button"
            class="btn btn-warning btn-sm ms-2 flex-shrink-0"
            id="nudgeOpenSettings">
      Set up
    </button>
    <button type="button"
            class="btn-close ms-1"
            id="nudgeDismiss"
            aria-label="Dismiss"></button>
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


  @stack('scripts')
  <script src="{{ asset('methodist/js/bootstrap.min.js') }}"></script>
  <script>
    // ── Cookie helpers ──────────────────────────────────────────────
    function setCookie(name, value, days = 365) {
      const expires = new Date(Date.now() + days * 864e5).toUTCString();
      let s = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax`;
      if (location.protocol === 'https:') s += '; Secure';
      document.cookie = s;
    }

    function getCookie(name) {
      return document.cookie.split('; ').reduce((r, v) => {
        const parts = v.split('=');
        return parts[0] === name ? decodeURIComponent(parts[1]) : r;
      }, '');
    }

    // ── Nudge helpers ───────────────────────────────────────────────
    const nudge = document.getElementById('setupNudge');

    function showNudge()  { nudge.classList.add('show'); }
    function hideNudge()  { nudge.classList.remove('show'); }

    // ── Init on DOM ready ───────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {

      const circuitId = getCookie('user_circuit');
      const userEmail = getCookie('user_email');

      // Populate form from cookies
      if (circuitId) {
        const sel = document.getElementById('circuitSelect');
        if (sel) sel.value = circuitId;
      }
      if (userEmail) {
        const em = document.getElementById('userEmail');
        if (em) em.value = userEmail;
        document.getElementById('settingsEmailDisplay').textContent = userEmail;
      }

      // Show nudge if circuit not yet set
      if (!circuitId) showNudge();

      // "Set up" button inside nudge → open settings slideover
      document.getElementById('nudgeOpenSettings').addEventListener('click', () => {
        hideNudge();
        new bootstrap.Offcanvas(document.getElementById('offcanvasSettings')).show();
      });

      // Dismiss nudge without acting
      document.getElementById('nudgeDismiss').addEventListener('click', hideNudge);

      // Handle settings form submission
      document.getElementById('userSettingsForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const circuit = document.getElementById('circuitSelect').value;
        const email   = document.getElementById('userEmail').value;

        if (!circuit) {
          alert('Please select your circuit.');
          return;
        }

        setCookie('user_circuit', circuit);
        setCookie('user_email', email);

        // Update the display email in the panel header
        document.getElementById('settingsEmailDisplay').textContent = email || 'No email set';

        // Close slideover and hide nudge
        bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasSettings'))?.hide();
        hideNudge();
      });

      // ── Back button ──
      const backBtn = document.getElementById('pwaBackBtn');

      function updateBackButton() {
        const isHome = ['/', '/index.html'].includes(window.location.pathname);
        backBtn.disabled = isHome;
      }

      backBtn.addEventListener('click', () => window.history.back());
      window.addEventListener('popstate',    updateBackButton);
      window.addEventListener('pushstate',   updateBackButton);
      window.addEventListener('replacestate',updateBackButton);
      updateBackButton();
    });

    // ── Service worker ──────────────────────────────────────────────
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker
        .register("{{ url('/service-worker.js') }}", { scope: '/' })
        .then(reg => console.log('SW registered:', reg.scope))
        .catch(err => console.warn('SW registration failed:', err));
    }

    // ── PWA install prompt ──────────────────────────────────────────
    if (location.protocol === 'https:' || ['localhost','127.0.0.1'].includes(location.hostname)) {
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
        console.log('Install prompt outcome:', outcome);
        deferredPrompt = null;
        installBtn.classList.add('d-none');
      });

      window.addEventListener('appinstalled', () => {
        console.log('PWA installed');
        installBtn.classList.add('d-none');
      });
    }
  </script>
</body>
</html>