{{--
  Camera capture for photo inputs.
  Add data-camera to any <input type="file" name="photo">; a "📷 क्यामेरा" button appears next to it.
  When the device has more than one camera, a list lets the user pick one (remembered for next time).
  On phones/tablets an अगाडि / पछाडि (front / back) switch is shown, and 🔄 flips between them.
  The captured photo is cropped square, compressed under 500 KB, and placed into that input
  (a normal "change" event fires, so existing size checks / previews keep working).
  data-camera-autosubmit on the input submits its form right after the photo is taken.
  data-camera-label="…" replaces the button text (for tight spaces).
--}}
@once
<div id="cameraModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/70 p-4" role="dialog" aria-modal="true" aria-label="{{ \App\Support\FrontendLocale::text('क्यामेरा') }}">
  <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
    <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-3">
      <div class="font-bold text-slate-900">{{ \App\Support\FrontendLocale::text('📷 फोटो खिच्नुहोस्') }}</div>
      <button type="button" data-cam="close" class="ml-auto h-8 w-8 rounded-full text-lg leading-none text-slate-500 hover:bg-slate-100" aria-label="{{ \App\Support\FrontendLocale::text('बन्द गर्नुहोस्') }}">×</button>
    </div>

    {{-- phones: front / back switch --}}
    <div data-cam="facing" class="hidden gap-1 border-b border-slate-100 px-4 py-2">
      <button type="button" data-facing="user" class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold">{{ \App\Support\FrontendLocale::text('🤳 अगाडि (Front)') }}</button>
      <button type="button" data-facing="environment" class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold">{{ \App\Support\FrontendLocale::text('📷 पछाडि (Back)') }}</button>
    </div>

    {{-- camera source picker (only when there is more than one camera) --}}
    <div data-cam="devices" class="hidden items-center gap-2 border-b border-slate-100 px-4 py-2">
      <label for="cameraDeviceSelect" class="shrink-0 text-xs font-semibold text-slate-500">{{ \App\Support\FrontendLocale::text('क्यामेरा') }}</label>
      <select id="cameraDeviceSelect" data-cam="device"
        class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-800 focus:border-blue-500 focus:outline-none"></select>
    </div>

    <div class="relative aspect-square w-full bg-slate-900">
      <video data-cam="video" class="absolute inset-0 h-full w-full object-cover" autoplay playsinline muted></video>
      <img data-cam="still" class="absolute inset-0 hidden h-full w-full object-cover" alt="{{ \App\Support\FrontendLocale::text('खिचिएको फोटो') }}">
      {{-- face guide --}}
      <div data-cam="guide" class="pointer-events-none absolute inset-[12%] rounded-full border-2 border-dashed border-white/60"></div>
      <div data-cam="msg" class="absolute inset-0 hidden items-center justify-center p-6 text-center text-sm text-white"></div>
    </div>

    <div class="flex items-center justify-between gap-3 px-4 py-4">
      {{-- live mode --}}
      <button type="button" data-cam="flip" class="invisible flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 text-lg hover:bg-slate-50" title="{{ \App\Support\FrontendLocale::text('अर्को क्यामेरा') }}">🔄</button>
      <button type="button" data-cam="shoot" class="flex h-16 w-16 items-center justify-center rounded-full border-4 border-blue-200 bg-blue-600 text-2xl text-white shadow-lg hover:bg-blue-700" title="{{ \App\Support\FrontendLocale::text('फोटो खिच्नुहोस्') }}">📸</button>
      <span data-cam="spacer" class="h-11 w-11"></span>

      {{-- review mode --}}
      <button type="button" data-cam="retake" class="hidden flex-1 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ \App\Support\FrontendLocale::text('↺ फेरि खिच्नुहोस्') }}</button>
      <button type="button" data-cam="use" class="hidden flex-1 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700">{{ \App\Support\FrontendLocale::text('✓ यो फोटो राख्नुहोस्') }}</button>
    </div>
    <div data-cam="size" class="-mt-2 pb-3 text-center text-[11px] text-slate-400"></div>
  </div>
</div>

<script>
(() => {
  const MAX_BYTES = 500 * 1024;
  const SIDE = 800; // output is SIDE × SIDE px

  const modal = document.getElementById('cameraModal');
  const $ = name => modal.querySelector(`[data-cam="${name}"]`);
  const video = $('video'), still = $('still'), msg = $('msg'), sizeEl = $('size');
  const live = ['flip', 'shoot', 'spacer', 'guide'].map($);
  const review = ['retake', 'use'].map($);

  const deviceBar = $('devices'), deviceSelect = $('device'), flipBtn = $('flip');
  const STORE_KEY = 'menyanbo.cameraDeviceId';

  const facingBar = $('facing');
  // Phones and tablets (touch screen) get the front/back switch
  const isMobile = window.matchMedia('(pointer: coarse)').matches || /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
  let facing = 'user';

  let stream = null, target = null, blob = null, mirror = true;
  let devices = [];
  let deviceId = null;
  try { deviceId = localStorage.getItem(STORE_KEY); } catch (e) {}
  const canStream = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);

  function remember(id) {
    deviceId = id || null;
    try { id ? localStorage.setItem(STORE_KEY, id) : localStorage.removeItem(STORE_KEY); } catch (e) {}
  }

  // Show the picker / 🔄 only when there is a choice to make
  function deviceUI() {
    const reviewing = !!blob;
    const many = devices.length > 1;
    // Phones: front/back switch; the full list only when there are extra lenses (e.g. wide / zoom)
    const showFacing = isMobile && !reviewing;
    const showList = many && !reviewing && (!isMobile || devices.length > 2);
    facingBar.classList.toggle('hidden', !showFacing);
    facingBar.classList.toggle('flex', showFacing);
    deviceBar.classList.toggle('hidden', !showList);
    deviceBar.classList.toggle('flex', showList);
    flipBtn.classList.toggle('invisible', !(isMobile || many));
    facingBar.querySelectorAll('[data-facing]').forEach(b => {
      const on = b.dataset.facing === facing;
      b.className = 'flex-1 rounded-lg px-3 py-2 text-sm font-semibold ' +
        (on ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200');
    });
  }

  function setFacing(f) {
    facing = f;
    remember(null); // front/back chosen → let the phone pick that side's main camera
    start();
  }

  async function listDevices() {
    if (!navigator.mediaDevices?.enumerateDevices) return;
    try {
      devices = (await navigator.mediaDevices.enumerateDevices()).filter(d => d.kind === 'videoinput');
    } catch (e) {
      devices = [];
    }
    const current = stream?.getVideoTracks()[0]?.getSettings().deviceId || deviceId;
    deviceSelect.innerHTML = devices.map((d, i) =>
      `<option value="${d.deviceId}">${(d.label || `क्यामेरा ${i + 1}`).replace(/[<>&"]/g, '')}</option>`).join('');
    if (current && devices.some(d => d.deviceId === current)) deviceSelect.value = current;
    deviceUI();
  }

  function show(els, on) { els.forEach(el => el.classList.toggle('hidden', !on)); }
  function message(text) {
    msg.textContent = text || '';
    msg.classList.toggle('hidden', !text);
    msg.classList.toggle('flex', !!text);
  }
  function stop() {
    stream?.getTracks().forEach(t => t.stop());
    stream = null;
  }

  async function start() {
    stop();
    message('क्यामेरा खुल्दैछ…');
    const size = { width: { ideal: 1280 }, height: { ideal: 1280 } };
    try {
      try {
        stream = await navigator.mediaDevices.getUserMedia({
          video: deviceId ? { deviceId: { exact: deviceId }, ...size } : { facingMode: { exact: facing }, ...size }, audio: false,
        });
      } catch (e) {
        if (e.name === 'NotAllowedError') throw e;
        // saved camera is gone, or no camera on that side (e.g. laptop) → closest available
        remember(null);
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: facing, ...size }, audio: false });
      }
      video.srcObject = stream;
      // Mirror the preview (and photo) for front / laptop cameras, not for a phone's back camera
      const facingMode = stream.getVideoTracks()[0]?.getSettings().facingMode;
      if (facingMode === 'user' || facingMode === 'environment') facing = facingMode;
      mirror = facingMode !== 'environment';
      deviceUI();
      video.style.transform = mirror ? 'scaleX(-1)' : '';
      message('');
      listDevices(); // labels are only available once permission is granted
    } catch (e) {
      message(e.name === 'NotAllowedError'
        ? @js(\App\Support\FrontendLocale::text('क्यामेरा प्रयोग गर्ने अनुमति दिइएन। ब्राउजरको सेटिङमा अनुमति दिनुहोस्।'))
        : @js(\App\Support\FrontendLocale::text('क्यामेरा खोल्न सकिएन। यो उपकरणमा क्यामेरा नहुन सक्छ।')));
    }
  }

  // Square centre-crop, then lower JPEG quality (and size) until it fits under 500 KB
  async function squareJpeg(source, srcW, srcH, mirror) {
    const side = Math.min(srcW, srcH);
    let out = Math.min(SIDE, side);
    for (let attempt = 0; attempt < 4; attempt++) {
      const canvas = document.createElement('canvas');
      canvas.width = canvas.height = out;
      const ctx = canvas.getContext('2d');
      if (mirror) { ctx.translate(out, 0); ctx.scale(-1, 1); }
      ctx.drawImage(source, (srcW - side) / 2, (srcH - side) / 2, side, side, 0, 0, out, out);
      for (const q of [0.9, 0.8, 0.7, 0.6, 0.5]) {
        const b = await new Promise(r => canvas.toBlob(r, 'image/jpeg', q));
        if (b && b.size <= MAX_BYTES) return b;
      }
      out = Math.round(out * 0.75);
    }
    return null;
  }

  function review_(b) {
    blob = b;
    still.src = URL.createObjectURL(b);
    show([still], true); show([video], false);
    show(live, false); show(review, true);
    sizeEl.textContent = `${Math.round(b.size / 1024)} KB · JPEG`;
    deviceUI();
  }

  function reset() {
    blob = null;
    if (still.src) URL.revokeObjectURL(still.src);
    still.removeAttribute('src');
    show([still], false); show([video], true);
    show(live, true); show(review, false);
    sizeEl.textContent = '';
    deviceUI();
  }

  function open(input) {
    target = input;
    reset();
    modal.classList.remove('hidden'); modal.classList.add('flex');
    start();
  }

  function close() {
    stop(); reset();
    modal.classList.add('hidden'); modal.classList.remove('flex');
    target = null;
  }

  function place(input, b) {
    const file = new File([b], `camera-${Date.now()}.jpg`, { type: 'image/jpeg' });
    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    input.dispatchEvent(new Event('change', { bubbles: true }));

    const status = input._cameraStatus;
    if (status) status.textContent = `✓ ${@js(\App\Support\FrontendLocale::text('खिचिएको फोटो'))} (${Math.round(b.size / 1024)} KB)`;

    if (input.hasAttribute('data-camera-autosubmit') && input.form) input.form.requestSubmit();
  }

  $('close').addEventListener('click', close);
  modal.addEventListener('click', e => { if (e.target === modal) close(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) close(); });
  // 🔄 = phones: front ⇄ back; computers: next camera in the list
  facingBar.querySelectorAll('[data-facing]').forEach(b => b.addEventListener('click', () => setFacing(b.dataset.facing)));
  flipBtn.addEventListener('click', () => {
    if (isMobile) { setFacing(facing === 'user' ? 'environment' : 'user'); return; }
    if (devices.length < 2) return;
    const i = devices.findIndex(d => d.deviceId === deviceSelect.value);
    remember(devices[(i + 1) % devices.length].deviceId);
    deviceSelect.value = deviceId;
    start();
  });
  deviceSelect.addEventListener('change', () => { remember(deviceSelect.value); start(); });
  navigator.mediaDevices?.addEventListener?.('devicechange', () => { if (!modal.classList.contains('hidden')) listDevices(); });
  $('retake').addEventListener('click', () => { reset(); if (!stream) start(); });

  $('shoot').addEventListener('click', async () => {
    if (!stream || !video.videoWidth) return;
    const b = await squareJpeg(video, video.videoWidth, video.videoHeight, mirror);
    if (!b) { message(@js(\App\Support\FrontendLocale::text('फोटो 500 KB भित्र मिलाउन सकिएन। फेरि प्रयास गर्नुहोस्।'))); return; }
    stop();
    review_(b);
  });

  $('use').addEventListener('click', () => {
    if (!blob || !target) return;
    const input = target, b = blob;
    close();
    place(input, b);
  });

  // No live camera API (e.g. plain http on a phone): use the phone's own camera app instead
  const fallback = document.createElement('input');
  fallback.type = 'file';
  fallback.accept = 'image/*';
  fallback.setAttribute('capture', 'user');
  fallback.addEventListener('change', async () => {
    const file = fallback.files?.[0];
    const input = fallback._target;
    fallback.value = '';
    if (!file || !input) return;
    const img = new Image();
    img.onload = async () => {
      const b = await squareJpeg(img, img.naturalWidth, img.naturalHeight, false);
      URL.revokeObjectURL(img.src);
      if (b) place(input, b);
    };
    img.src = URL.createObjectURL(file);
  });

  document.querySelectorAll('input[type="file"][data-camera]').forEach(input => {
    const row = document.createElement('div');
    row.className = 'mt-1.5 flex flex-wrap items-center gap-2';
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100';
    btn.textContent = input.dataset.cameraLabel || @js(\App\Support\FrontendLocale::text('📷 क्यामेराबाट फोटो खिच्नुहोस्'));
    const status = document.createElement('span');
    status.className = 'text-[11px] font-semibold text-green-700';
    input._cameraStatus = status;
    input.addEventListener('change', () => { status.textContent = ''; }); // place() sets it again after its own change event

    btn.addEventListener('click', () => {
      if (canStream) open(input);
      else { fallback._target = input; fallback.click(); }
    });
    row.append(btn, status);
    input.insertAdjacentElement('afterend', row);
  });
})();
</script>
@endonce
