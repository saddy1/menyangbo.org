{{--
  Camera capture for photo inputs.
  Add data-camera to any <input type="file" name="photo">; a "📷 क्यामेरा" button appears next to it.
  The captured photo is cropped square, compressed under 500 KB, and placed into that input
  (a normal "change" event fires, so existing size checks / previews keep working).
  data-camera-autosubmit on the input submits its form right after the photo is taken.
--}}
@once
<div id="cameraModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/70 p-4" role="dialog" aria-modal="true" aria-label="क्यामेरा">
  <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
    <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-3">
      <div class="font-bold text-slate-900">📷 फोटो खिच्नुहोस्</div>
      <button type="button" data-cam="close" class="ml-auto h-8 w-8 rounded-full text-lg leading-none text-slate-500 hover:bg-slate-100" aria-label="बन्द गर्नुहोस्">×</button>
    </div>

    <div class="relative aspect-square w-full bg-slate-900">
      <video data-cam="video" class="absolute inset-0 h-full w-full object-cover" autoplay playsinline muted></video>
      <img data-cam="still" class="absolute inset-0 hidden h-full w-full object-cover" alt="खिचिएको फोटो">
      {{-- face guide --}}
      <div data-cam="guide" class="pointer-events-none absolute inset-[12%] rounded-full border-2 border-dashed border-white/60"></div>
      <div data-cam="msg" class="absolute inset-0 hidden items-center justify-center p-6 text-center text-sm text-white"></div>
    </div>

    <div class="flex items-center justify-between gap-3 px-4 py-4">
      {{-- live mode --}}
      <button type="button" data-cam="flip" class="flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 text-lg hover:bg-slate-50" title="अगाडि / पछाडि क्यामेरा">🔄</button>
      <button type="button" data-cam="shoot" class="flex h-16 w-16 items-center justify-center rounded-full border-4 border-blue-200 bg-blue-600 text-2xl text-white shadow-lg hover:bg-blue-700" title="फोटो खिच्नुहोस्">📸</button>
      <span data-cam="spacer" class="h-11 w-11"></span>

      {{-- review mode --}}
      <button type="button" data-cam="retake" class="hidden flex-1 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">↺ फेरि खिच्नुहोस्</button>
      <button type="button" data-cam="use" class="hidden flex-1 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700">✓ यो फोटो राख्नुहोस्</button>
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

  let stream = null, facing = 'user', target = null, blob = null;
  const canStream = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);

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
    try {
      stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: facing, width: { ideal: 1280 }, height: { ideal: 1280 } }, audio: false,
      });
      video.srcObject = stream;
      video.style.transform = facing === 'user' ? 'scaleX(-1)' : '';
      message('');
    } catch (e) {
      message(e.name === 'NotAllowedError'
        ? 'क्यामेरा प्रयोग गर्ने अनुमति दिइएन। ब्राउजरको सेटिङमा अनुमति दिनुहोस्।'
        : 'क्यामेरा खोल्न सकिएन। यो उपकरणमा क्यामेरा नहुन सक्छ।');
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
  }

  function reset() {
    blob = null;
    if (still.src) URL.revokeObjectURL(still.src);
    still.removeAttribute('src');
    show([still], false); show([video], true);
    show(live, true); show(review, false);
    sizeEl.textContent = '';
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
    if (status) status.textContent = `✓ क्यामेराको फोटो तयार (${Math.round(b.size / 1024)} KB)`;

    if (input.hasAttribute('data-camera-autosubmit') && input.form) input.form.requestSubmit();
  }

  $('close').addEventListener('click', close);
  modal.addEventListener('click', e => { if (e.target === modal) close(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) close(); });
  $('flip').addEventListener('click', () => { facing = facing === 'user' ? 'environment' : 'user'; start(); });
  $('retake').addEventListener('click', () => { reset(); if (!stream) start(); });

  $('shoot').addEventListener('click', async () => {
    if (!stream || !video.videoWidth) return;
    const b = await squareJpeg(video, video.videoWidth, video.videoHeight, facing === 'user');
    if (!b) { message('फोटो 500 KB भित्र मिलाउन सकिएन। फेरि प्रयास गर्नुहोस्।'); return; }
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
    btn.innerHTML = '📷 क्यामेराबाट फोटो खिच्नुहोस्';
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
