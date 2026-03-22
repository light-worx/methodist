{{--
  Drop-in replacement for the right-side offcanvas in your layout.
  The JS block at the bottom should replace the existing settings JS
  in layout.blade.php — remove the old cookie-based getCookie/setCookie
  logic for email and replace with these fetch() calls.
--}}

<!-- ══ Right settings offcanvas ══ -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSettings" aria-labelledby="offcanvasSettingsLabel">

  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasSettingsLabel">Settings</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body px-3 py-3">

    {{-- ── Circuit card ── --}}
    <div class="settings-card mb-3" id="cardCircuit">
      <p class="settings-section-label">Your circuit</p>
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

    {{-- ── Email card ── --}}
    <div class="settings-card mb-3" id="cardEmail">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <p class="settings-section-label mb-0">Email</p>
        <span id="emailBadge" class="badge d-none"></span>
      </div>

      {{-- Email input row --}}
      <div id="emailInputRow" class="d-flex align-items-center gap-2 mb-2">
        <input type="email" class="form-control form-control-sm flex-grow-1"
               id="emailInput" placeholder="you@example.com">
        <button class="btn btn-primary btn-sm px-3" id="btnSendPin">Send code</button>
      </div>

      {{-- PIN entry (hidden until code sent) --}}
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

    {{-- ── Mobile card (locked until email verified) ── --}}
    <div class="settings-card mb-3" id="cardMobile">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <p class="settings-section-label mb-0">Mobile number</p>
        <span id="mobileLockBadge" class="badge bg-secondary text-white" style="font-size:.65rem">
          Verify email first
        </span>
      </div>
      <div class="d-flex align-items-center gap-2">
        <input type="tel" class="form-control form-control-sm flex-grow-1"
               id="mobileInput" placeholder="+27 82 000 0000" disabled>
        <button class="btn btn-primary btn-sm px-3" id="btnSaveMobile" disabled>Save</button>
      </div>
    </div>

    {{-- ── Push notifications (locked until mobile added) ── --}}
    <div class="settings-card" id="cardNotif">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <p class="settings-section-label mb-0">Notifications</p>
        <span id="notifLockBadge" class="badge bg-secondary text-white" style="font-size:.65rem">
          Add mobile first
        </span>
      </div>
      <div class="d-flex flex-column gap-2" id="notifToggles">
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


{{-- ══ Settings styles (add to your <style> block in layout.blade.php) ══ --}}
<style>
  .settings-card {
    background: var(--bs-light, #f8f9fa);
    border: 1px solid var(--bs-border-color, #dee2e6);
    border-radius: .5rem;
    padding: .875rem 1rem;
  }
  .settings-section-label {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #6c757d;
    margin-bottom: 0;
  }
  .settings-card.locked {
    opacity: .55;
    pointer-events: none;
  }
</style>


@push('scripts')
<script>
(function () {
  // ── State ────────────────────────────────────────────────────────
  let prefs = {};   // populated from GET /api/preferences on panel open

  // ── Helpers ──────────────────────────────────────────────────────
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

  async function api(path, body = null) {
    const opts = {
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
      },
      credentials: 'same-origin',
    };
    if (body) {
      opts.method = 'POST';
      opts.body   = JSON.stringify(body);
    } else {
      opts.method = 'GET';
    }
    const res  = await fetch('/api' + path, opts);
    const data = await res.json();
    if (!res.ok) throw new Error(data.error ?? 'Something went wrong.');
    return data;
  }

  // ── Render UI from prefs state ───────────────────────────────────
  function renderPrefs() {
    // Circuit
    if (prefs.circuit_id) {
      el('circuitSelect').value = prefs.circuit_id;
    }

    // Email badge
    const badge = el('emailBadge');
    badge.classList.remove('d-none', 'bg-warning', 'bg-success', 'text-dark', 'text-white');
    if (prefs.email && prefs.email_verified) {
      badge.textContent = 'Verified';
      badge.classList.add('bg-success', 'text-white');
      el('emailInput').value     = prefs.email;
      el('emailInput').disabled  = true;
      el('btnSendPin').disabled  = true;
      hide('pinRow');
    } else if (prefs.email && !prefs.email_verified) {
      badge.textContent = 'Unverified';
      badge.classList.add('bg-warning', 'text-dark');
      el('emailInput').value = prefs.email;
    }
    show('emailBadge');

    // Mobile — unlocked once email verified
    const mobileUnlocked = prefs.email_verified;
    el('mobileInput').disabled  = !mobileUnlocked;
    el('btnSaveMobile').disabled = !mobileUnlocked;
    el('cardMobile').classList.toggle('locked', !mobileUnlocked);
    if (mobileUnlocked) hide('mobileLockBadge');
    if (prefs.mobile) el('mobileInput').value = prefs.mobile;

    // Notifications — unlocked once mobile added
    const notifUnlocked = mobileUnlocked && !!prefs.mobile;
    el('cardNotif').classList.toggle('locked', !notifUnlocked);
    if (notifUnlocked) hide('notifLockBadge');
    document.querySelectorAll('.notif-toggle').forEach(t => {
      t.disabled = !notifUnlocked;
      if (prefs[t.dataset.key] !== undefined) t.checked = prefs[t.dataset.key];
    });
  }

  // ── Load prefs when offcanvas opens ─────────────────────────────
  document.getElementById('offcanvasSettings')
    .addEventListener('show.bs.offcanvas', async () => {
      try {
        prefs = await api('/preferences');
        renderPrefs();
      } catch (e) {
        console.warn('Could not load preferences', e);
      }
    });

  // ── Save circuit ─────────────────────────────────────────────────
  el('btnSaveCircuit').addEventListener('click', async () => {
    const circuit_id = el('circuitSelect').value;
    if (!circuit_id) { alert('Please choose a circuit.'); return; }
    try {
      prefs = { ...prefs, ...(await api('/preferences', { circuit_id })) };
      // Hide the nudge banner if it's still showing
      document.getElementById('setupNudge')?.classList.remove('show');
    } catch (e) {
      alert(e.message);
    }
  });

  // ── Send PIN ─────────────────────────────────────────────────────
  el('btnSendPin').addEventListener('click', async () => {
    const email = el('emailInput').value.trim();
    if (!email) { setFeedback('Please enter an email address.'); return; }
    el('btnSendPin').disabled = true;
    try {
      await api('/preferences/send-pin', { email });
      el('pinEmailDisplay').textContent = email;
      show('pinRow');
      hide('emailInputRow');
      setFeedback('Code sent — check your inbox.', 'success');
    } catch (e) {
      setFeedback(e.message);
    } finally {
      el('btnSendPin').disabled = false;
    }
  });

  // ── Resend PIN ───────────────────────────────────────────────────
  el('btnResendPin').addEventListener('click', async () => {
    const email = el('emailInput').value.trim();
    try {
      await api('/preferences/send-pin', { email });
      setFeedback('New code sent.', 'success');
    } catch (e) {
      setFeedback(e.message);
    }
  });

  // ── Change email (reset flow) ────────────────────────────────────
  el('btnChangeEmail').addEventListener('click', () => {
    hide('pinRow');
    show('emailInputRow');
    el('emailInput').value    = '';
    el('emailInput').disabled = false;
    el('btnSendPin').disabled = false;
  });

  // ── Verify PIN ───────────────────────────────────────────────────
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

  // ── Save mobile ──────────────────────────────────────────────────
  el('btnSaveMobile').addEventListener('click', async () => {
    const mobile = el('mobileInput').value.trim();
    try {
      prefs = { ...prefs, ...(await api('/preferences', { mobile })) };
      renderPrefs();
      setFeedback('Mobile number saved.', 'success');
    } catch (e) {
      setFeedback(e.message);
    }
  });

  // ── Notification toggles ─────────────────────────────────────────
  document.querySelectorAll('.notif-toggle').forEach(toggle => {
    toggle.addEventListener('change', async function () {
      try {
        await api('/preferences', { [this.dataset.key]: this.checked });
      } catch (e) {
        this.checked = !this.checked;   // revert on failure
        setFeedback(e.message);
      }
    });
  });

})();
</script>
@endpush