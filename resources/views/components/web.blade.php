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
      border-left: none;
      border-right: none;
      border-top: none;
    }
    #setupNudge.show { display: flex !important; }
    @media (max-width: 576px) {
      #setupNudge { top: 50px; }
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

    <!-- Person icon opens the right-side settings slideover -->
    <button class="btn p-0" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#offcanvasSettings"
            aria-label="Open settings">
      <i class="bi bi-person fs-3"></i>
    </button>

  </header>


  <!-- ══ Setup nudge (shown only when circuit not yet chosen) ══ -->
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

    <div class="offcanvas-body">

      <!-- Avatar / identity area -->
      <div class="text-center mb-4">
        <div class="settings-avatar mx-auto">
          <i class="bi bi-person-fill"></i>
        </div>
        <p class="mb-0 text-muted small" id="settingsEmailDisplay">Not signed in</p>
      </div>

      <hr class="my-3">

      <form id="userSettingsForm">

        <p class="settings-section-label">Your Circuit</p>
        <div class="mb-3">
          <label for="circuitSelect" class="form-label">Select your Circuit</label>
          <select class="form-select" id="circuitSelect" required>
            <option value="">Choose...</option>
            @foreach($circuits as $circuit)
              <option value="{{ $circuit->id }}">{{ $circuit->circuit }}</option>
            @endforeach
          </select>
        </div>

        <hr class="my-3">

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
  <script src="{{ asset('js/bootstrap.min.js') }}"></script>
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

    // ── On DOM ready ────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {

      const circuitId = getCookie('user_circuit');
      const userEmail = getCookie('user_email');

      // Pre-fill form from cookies
      if (circuitId) {
        const sel = document.getElementById('circuitSelect');
        if (sel) sel.value = circuitId;
      }
      if (userEmail) {
        const em = document.getElementById('userEmail');
        if (em) em.value = userEmail;
        document.getElementById('settingsEmailDisplay').textContent = userEmail;
      }

      // Show nudge if circuit not yet chosen
      if (!circuitId) {
        document.getElementById('setupNudge').classList.add('show');
      }

      // "Set up" in nudge → open settings slideover
      document.getElementById('nudgeOpenSettings').addEventListener('click', () => {
        document.getElementById('setupNudge').classList.remove('show');
        new bootstrap.Offcanvas(document.getElementById('offcanvasSettings')).show();
      });

      // Dismiss nudge without acting
      document.getElementById('nudgeDismiss').addEventListener('click', () => {
        document.getElementById('setupNudge').classList.remove('show');
      });

      // Save settings
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

        document.getElementById('settingsEmailDisplay').textContent = email || 'No email set';

        bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasSettings'))?.hide();
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

    // ── Service worker ──────────────────────────────────────────────
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker
        .register("{{ url('/service-worker.js') }}", { scope: '/' })
        .then(reg => console.log('SW registered:', reg.scope))
        .catch(err => console.warn('SW registration failed:', err));
    }

    // ── PWA install prompt ──────────────────────────────────────────
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
  </script>
</body>
</html>