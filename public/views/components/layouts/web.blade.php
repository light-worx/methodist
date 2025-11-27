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

    /* Header */
    .pwa-header {
    height: 56px; /* typical mobile header height */
    background-color: #fff;
    border-bottom: 1px solid #ddd;
    position: relative;
    }

    /* Ensure title stays centered even if buttons differ in width */
    .pwa-header .navbar-title {
    font-size: 1.2rem;
    line-height: 1;
    }

    /* Make buttons uniform */
    .pwa-header .btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    }

    /* On small screens: slightly reduce size */
    @media (max-width: 576px) {
    .pwa-header {
        height: 50px;
    }

    .pwa-header .navbar-title {
        font-size: 1rem;
    }

    .pwa-header .btn {
        width: 36px;
        height: 36px;
    }
    }


    /* Bottom toolbar */
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

  <!-- Header -->
  <header class="pwa-header d-flex align-items-center justify-content-between px-3 py-2">
    <button class="menu-btn btn p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
        <i class="bi bi-list fs-3"></i>
    </button>

    <div class="flex-grow-1 text-center position-relative">
        <span class="navbar-title fw-semibold">{{ $pageName ?? 'Connexion' }}</span>
    </div>

    <button class="action-btn btn p-0" type="button" onclick="new bootstrap.Modal(document.getElementById('userSettingsModal')).show()">
        <i class="bi bi-person fs-3"></i>
    </button>
  </header>

  <!-- Offcanvas menu -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenu">
      <div class="offcanvas-header">
          <h5 class="offcanvas-title">Menu</h5>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body">
          <ul class="list-unstyled">
              <li><a href="/" class="d-block py-2"><i class="bi bi-house me-2"></i> Home</a></li>
              <li><a href="/lectionary" class="d-block py-2"><i class="bi bi-book me-2"></i> Lectionary</a></li>
              <li><a href="/ideas" class="d-block py-2"><i class="bi bi-lightbulb me-2"></i> Ministry ideas</a></li>
              <li><a href="/admin" class="d-block py-2"><i class="bi bi-lock me-2"></i> Login</a></li>
          </ul>
      </div>
  </div>

  <!-- Main content -->
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

  <!-- Bottom toolbar -->
  <nav class="pwa-bottom-toolbar shadow-sm">
      <button class="btn btn-link text-dark" id="pwaBackBtn" title="Back" disabled>
          <i class="bi bi-arrow-left fs-4"></i>
      </button>
      <a class="btn btn-link text-dark" href="/" id="pwaHomeBtn" title="Home">
          <i class="bi bi-house fs-4"></i>
      </a>
      <a class="btn btn-link text-dark" href="/ideas" id="pwaHomeBtn" title="Ideas">
          <i class="bi bi-lightbulb fs-4"></i>
      </a>
  </nav>

    <!-- User Settings Modal -->
    <div class="modal fade" id="userSettingsModal" tabindex="-1" aria-labelledby="userSettingsLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userSettingsLabel">Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="userSettingsForm">
                <div class="mb-3">
                    <label for="circuitSelect" class="form-label">Select your Circuit</label>
                    <select class="form-select" id="circuitSelect" required>
                    <option value="">Choose...</option>
                    @foreach($circuits as $circuit)
                        <option value="{{ $circuit->id }}">{{ $circuit->circuit }}</option>
                    @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="userEmail" class="form-label">Email (optional)</label>
                    <input type="email" class="form-control" id="userEmail" placeholder="you@example.com">
                </div>
                <button type="submit" class="btn btn-primary w-100">Save Settings</button>
                </form>
            </div>
            </div>
        </div>
    </div>
    @stack('scripts')
    <!-- JS -->
    <script src="{{ asset('methodist/js/bootstrap.min.js') }}"></script>
    <script>
        // --- Cookie helpers ---
        function setCookie(name, value, days = 365) {
            const expires = new Date(Date.now() + days * 864e5).toUTCString();
            let cookieStr = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax`;
            if (location.protocol === 'https:') cookieStr += '; Secure';
            document.cookie = cookieStr;
        }

        function getCookie(name) {
            return document.cookie.split('; ').reduce((r, v) => {
                const parts = v.split('=');
                return parts[0] === name ? decodeURIComponent(parts[1]) : r;
            }, '');
        }

        // --- Load existing settings ---
        document.addEventListener('DOMContentLoaded', function () {
            const circuitId = getCookie('user_circuit');
            const userEmail = getCookie('user_email');

            if (circuitId) {
                const select = document.getElementById('circuitSelect');
                if (select) select.value = circuitId;
            }
            if (userEmail) {
                const email = document.getElementById('userEmail');
                if (email) email.value = userEmail;
            }

            // If circuit not set, show modal automatically
            if (!circuitId) {
                const settingsModal = new bootstrap.Modal(document.getElementById('userSettingsModal'));
                settingsModal.show();
            }

            // Handle form submission
            document.getElementById('userSettingsForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const selectedCircuit = document.getElementById('circuitSelect').value;
                const email = document.getElementById('userEmail').value;

                if (selectedCircuit) {
                setCookie('user_circuit', selectedCircuit);
                setCookie('user_email', email);
                bootstrap.Modal.getInstance(document.getElementById('userSettingsModal')).hide();
                } else {
                alert('Please select your circuit.');
                }
            });
        });
        
        // --- Service worker registration ---
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register("{{ url('/service-worker.js') }}", { scope: '/' })
            .then(reg => console.log('ServiceWorker registered:', reg.scope))
            .catch(err => console.log('ServiceWorker registration failed:', err));
        }

        // --- PWA install prompt ---
        if (location.protocol === "https:" || location.hostname === "localhost" || location.hostname === "127.0.0.1") {
            let deferredPrompt;
            const installBtn = document.getElementById("installPwaBtn");

            window.addEventListener("beforeinstallprompt", (e) => {
                e.preventDefault();
                deferredPrompt = e;
                installBtn.classList.remove("d-none");
            });

            installBtn.addEventListener("click", async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log(`User response to install prompt: ${outcome}`);
                    deferredPrompt = null;
                    installBtn.classList.add("d-none");
                }
            });

            window.addEventListener("appinstalled", () => {
                console.log("PWA installed successfully");
                installBtn.classList.add("d-none");
            });
        }

        // --- Simple back/home toolbar logic ---
        document.addEventListener("DOMContentLoaded", () => {
            const backBtn = document.getElementById('pwaBackBtn');

            function updateBackButton() {
            const isHome = window.location.pathname === '/' || window.location.pathname === '/index.html';
            backBtn.disabled = isHome;
            }

            backBtn.addEventListener('click', () => {
            window.history.back();
            });

            window.addEventListener('popstate', updateBackButton);
            window.addEventListener('pushstate', updateBackButton);
            window.addEventListener('replacestate', updateBackButton);

            updateBackButton();
        });
    </script>
</body>
</html>
