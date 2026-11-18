@extends('admin.layout')
@php
  $np = fn ($n) => \App\Http\Controllers\Admin\PrintTreeController::toNepaliDigits($n);
  $startLabel = $root ? $root->display_name : 'पुस्ता ' . $np($startPusta) . ' — सबै सदस्य';
  $rangeLabel = 'पुस्ता ' . $np($startPusta) . ' → ' . $np($toPusta);
@endphp
@section('title', $startLabel . ' — Print Tree')
@section('content')
  <style>
    .print-tree-page { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif; color: #0f172a; margin: 0 -1.5rem; background: #eef2f7; }
    .shell { display: grid; grid-template-columns: 330px minmax(0, 1fr); min-height: calc(100vh - 4rem); }
    .panel { background: #fff; padding: 20px; position: sticky; top: 4rem; height: calc(100vh - 4rem); overflow: auto; border-right: 1px solid #e2e8f0; }
    .panel h1 { font-size: 1.25rem; margin: 8px 0 4px; font-weight: 800; }
    .panel label, .panel .hint { color: #475569; font-size: .85rem; }
    .group { margin-top: 16px; padding-top: 14px; border-top: 1px solid #e2e8f0; }
    .group h2 { margin: 0 0 8px; font-size: .75rem; letter-spacing: .08em; text-transform: uppercase; color: #64748b; font-weight: 700; }
    .panel select, .panel input[type=number] { width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 10px; font-size: .9rem; background: #fff; }
    .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .seg { display: grid; grid-template-columns: 1fr 1fr; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; }
    .seg label { display: block; text-align: center; padding: 8px 4px; cursor: pointer; font-weight: 600; font-size: .82rem; color: #334155; }
    .seg input { position: absolute; opacity: 0; }
    .seg input:checked + span { color: #fff; }
    .seg label:has(input:checked) { background: #2563eb; color: #fff; }
    .seg label:has(input:focus-visible) { outline: 2px solid #1d4ed8; outline-offset: -2px; }
    .stepper { display: grid; grid-template-columns: 38px 1fr 38px; gap: 6px; }
    .btn { border: 0; border-radius: 8px; padding: 9px 12px; font-weight: 700; cursor: pointer; font-size: .85rem; }
    .btn-primary { background: #2563eb; color: #fff; } .btn-primary:hover { background: #1d4ed8; }
    .btn-secondary { background: #fff; color: #334155; border: 1px solid #cbd5e1; } .btn-secondary:hover { background: #f8fafc; }
    .stats { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .stat { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 10px; font-size: .8rem; color: #475569; }
    .stat strong { display: block; font-size: 1.05rem; color: #0f172a; }
    .info { margin-top: 10px; padding: 10px; border-radius: 8px; font-size: .82rem; background: #f1f5f9; color: #334155; }
    .info.warn { background: #fef3c7; color: #92400e; }
    .info.ok   { background: #dcfce7; color: #166534; }
    .hidden { display: none !important; }

    .preview-shell { padding: 20px; min-width: 0; overflow: auto; }
    .preview-shell h2 { margin: 0 0 4px; font-size: 1rem; font-weight: 800; }
    .preview-shell .sub { margin: 0 0 14px; color: #64748b; font-size: .85rem; }

    #pages { display: grid; gap: 8mm; justify-content: start; }
    .page { box-sizing: border-box; break-inside: avoid; background: #fff; box-shadow: 0 6px 30px rgba(15,23,42,.15); overflow: hidden; display: flex; flex-direction: column; }
    .page-head { display: flex; justify-content: space-between; align-items: baseline; gap: 4mm; height: 8mm; font-size: 3.2mm; color: #334155; border-bottom: .3mm solid #cbd5e1; margin-bottom: 2mm; white-space: nowrap; overflow: hidden; }
    .page-head strong { color: #0f172a; font-size: 3.6mm; }
    .page-svg { display: block; outline: .25mm dashed #cbd5e1; }

    @media (max-width: 1100px) {
      .shell { grid-template-columns: 1fr; }
      .panel { position: static; height: auto; }
    }

    @media print {
      html, body { background: #fff !important; margin: 0 !important; padding: 0 !important; }
      body > header, #sidebar, #sidebarBackdrop, .panel, .preview-shell h2, .preview-shell .sub { display: none !important; }
      main, .print-tree-page, .shell, .preview-shell {
        display: block !important; padding: 0 !important; margin: 0 !important; min-height: 0 !important;
        background: none !important; overflow: visible !important; border: 0 !important;
      }
      #pages { display: block !important; zoom: 1 !important; }
      .page { box-shadow: none !important; break-after: page; page-break-after: always; }
      .page:last-child { break-after: auto; page-break-after: auto; }
      .page-svg { outline-color: #e2e8f0; }
      * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
  </style>

  {{-- The tree is drawn once here and reused on every page via <use> --}}
  <svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs><g id="treeMaster"><g id="treeBase"></g><g id="treeCards"></g></g></defs></svg>

  <div class="print-tree-page">
  <div class="shell">
    <aside class="panel">
      <a href="{{ route('admin.print-tree.form') }}" style="color:#2563eb;text-decoration:none;font-size:.85rem;">&larr; Change start / पुस्ता</a>
      <h1>Print Tree</h1>

      <div class="stats" style="margin-top:10px;">
        <div class="stat"><strong>{{ $totalNodes }}</strong>Members</div>
        <div class="stat"><strong>{{ $actualDepth }}</strong>Generations</div>
      </div>
      <div class="hint" style="margin-top:8px;">
        Start: <strong style="color:#0f172a">{{ $startLabel }}</strong><br>
        Range: <strong style="color:#0f172a">{{ $rangeLabel }}</strong>
      </div>

      <div class="group">
        <h2>Paper</h2>
        <div class="row2">
          <div>
            <label for="paperSize">Size</label>
            <select id="paperSize">
              <option value="A4" selected>A4</option>
              <option value="A3">A3</option>
              <option value="A2">A2</option>
              <option value="A1">A1</option>
              <option value="A0">A0</option>
              <option value="FLEX">Custom / Banner</option>
            </select>
          </div>
          <div>
            <label for="orientation">Orientation</label>
            <select id="orientation">
              <option value="landscape" selected>Landscape</option>
              <option value="portrait">Portrait</option>
            </select>
          </div>
        </div>
        <div id="flexFields" class="row2 hidden" style="margin-top:8px;">
          <div><label for="flexWidth">Width (mm)</label><input id="flexWidth" type="number" min="200" value="1500"></div>
          <div><label for="flexHeight">Height (mm)</label><input id="flexHeight" type="number" min="200" value="900"></div>
        </div>
      </div>

      <div class="group">
        <h2>Layout</h2>
        <div class="seg" role="radiogroup" aria-label="Layout">
          <label><input type="radio" name="mode" value="fit"> Fit on 1 page</label>
          <label><input type="radio" name="mode" value="tile" checked> Split across pages</label>
        </div>

        <div id="tileFields" style="margin-top:10px;">
          <label for="cols">Pages across</label>
          <div class="stepper">
            <button type="button" class="btn btn-secondary" id="colsMinus" aria-label="Fewer pages">−</button>
            <input id="cols" type="number" min="1" max="200" value="1" style="text-align:center;font-weight:700;">
            <button type="button" class="btn btn-secondary" id="colsPlus" aria-label="More pages">+</button>
          </div>
          <button type="button" class="btn btn-secondary" id="readableBtn" style="width:100%;margin-top:8px;">Make text readable</button>
        </div>
        <div id="sizeInfo" class="info"></div>
      </div>

      <div class="group">
        <h2>Lines</h2>
        <label for="lineWidth">Connection line thickness</label>
        <select id="lineWidth">
          <option value="1">Thin</option>
          <option value="1.6" selected>Normal</option>
          <option value="2.5">Bold</option>
          <option value="4">Extra bold</option>
        </select>
      </div>

      <div class="group">
        <h2>Preview zoom</h2>
        <input id="screenScale" type="range" min="5" max="100" value="35" style="width:100%" aria-label="Preview zoom">
        <div class="hint" id="screenScaleLabel">35%</div>
      </div>

      <div class="group">
        <h2>Print</h2>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <button class="btn btn-primary" id="printBtn" type="button">Print / Save PDF</button>
          <button class="btn btn-secondary" id="downloadSvgBtn" type="button">Download SVG</button>
        </div>
        <div class="info" style="margin-top:10px;">
          In the print dialog:<br>
          • Paper = same size as chosen here<br>
          • Margins = <strong>None</strong>, Scale = <strong>100%</strong><br>
          • Tick <strong>Background graphics</strong>
        </div>
      </div>
    </aside>

    <main class="preview-shell">
      <h2>{{ $startLabel }} — {{ $rangeLabel }}</h2>
      <p class="sub" id="pageSummary"></p>
      <div id="pages"></div>
    </main>
  </div>
  </div>

  <script>
    const forest     = {{ Illuminate\Support\Js::from($forest) }};
    const startPusta = {{ (int) $startPusta }};
    const TITLE      = 'मेन्याङ्बो कल्याणकारी संघ — वंशावली';
    const SUBTITLE   = {{ Illuminate\Support\Js::from($startLabel . ' · ' . $rangeLabel . ' · ' . $totalNodes . ' members · ' . now()->format('Y-m-d')) }};
    const NP = '०१२३४५६७८९';
    const toNp = n => String(n).replace(/\d/g, d => NP[d]);

    const $ = id => document.getElementById(id);
    const paperPresets = { A4: [297, 210], A3: [420, 297], A2: [594, 420], A1: [841, 594], A0: [1189, 841] };
    const PAGE_MARGIN_MM = 7;   // inner white margin each page keeps (printers can't print to the edge)
    const HEAD_MM        = 10;  // header strip height incl. gap
    const READABLE_CARD_MM = 42; // card width on paper that reads comfortably

    const cfg = { nodeWidth: 192, nodeHeight: 62, childGap: 22, treeGap: 46, levelGap: 100,
                  marginX: 36, marginY: 34, cornerRadius: 12, barWidth: 6, avatarCx: 20 };

    // ── helpers ──
    function svgEl(tag, attrs = {}, txt = null) {
      const el = document.createElementNS('http://www.w3.org/2000/svg', tag);
      for (const [k, v] of Object.entries(attrs)) el.setAttribute(k, v);
      if (txt !== null) el.textContent = txt;
      return el;
    }
    const genderColor = g => g === 'male' ? '#2563eb' : g === 'female' ? '#db2777' : '#64748b';
    const estimateW = (t, fs, fw = 400) => Math.ceil(String(t || '').length * fs * (fw >= 700 ? 0.62 : 0.55));
    function clip(text, maxW, fs, fw = 400) {
      let s = String(text || '');
      if (estimateW(s, fs, fw) <= maxW) return s;
      while (s.length > 1 && estimateW(s + '…', fs, fw) > maxW) s = s.slice(0, -1);
      return s + '…';
    }

    // ── layout ──
    function measure(node) {
      const kids = node.children || [];
      node._sw = kids.length
        ? Math.max(cfg.nodeWidth, kids.map(measure).reduce((a, b) => a + b, 0) + cfg.childGap * (kids.length - 1))
        : cfg.nodeWidth;
      return node._sw;
    }
    const rowY = d => cfg.marginY + d * (cfg.nodeHeight + cfg.levelGap);
    function place(node, left, depth, yOff) {
      node._x = left + (node._sw - cfg.nodeWidth) / 2;
      node._y = yOff + rowY(depth);
      const kids = node.children || [];
      const total = kids.reduce((a, c) => a + c._sw, 0) + cfg.childGap * Math.max(0, kids.length - 1);
      let cursor = left + (node._sw - total) / 2;
      kids.forEach(c => { place(c, cursor, depth + 1, yOff); cursor += c._sw + cfg.childGap; });
    }
    function maxDepth(n) { return Math.max(n.depth, ...(n.children || []).map(maxDepth)); }

    // ── drawing ──
    function drawCard(g0, node) {
      const p = node.person, spouses = node.spouses || [], color = genderColor(p.gender);
      const W = cfg.nodeWidth, H = cfg.nodeHeight, R = cfg.cornerRadius, bg = p.is_deceased ? '#fff4f4' : '#ffffff';
      const textMaxW = W - cfg.avatarCx * 2 - 18;
      const g = svgEl('g', { transform: `translate(${node._x}, ${node._y})` });

      g.appendChild(svgEl('rect', { x: 0, y: 0, width: W, height: H, rx: R, fill: bg }));
      g.appendChild(svgEl('path', { d: `M${R} 0 H${cfg.barWidth + R} V${H} H${R} A${R} ${R} 0 0 1 0 ${H - R} V${R} A${R} ${R} 0 0 1 ${R} 0 Z`, fill: color }));
      g.appendChild(svgEl('rect', { x: 0, y: 0, width: W, height: H, rx: R, fill: 'none', stroke: '#64748b', 'stroke-width': 1, 'vector-effect': 'non-scaling-stroke' }));

      const avX = cfg.avatarCx + cfg.barWidth + 4, avY = H / 2;
      g.appendChild(svgEl('circle', { cx: avX, cy: avY, r: 11, fill: color, 'fill-opacity': '0.15' }));
      g.appendChild(svgEl('circle', { cx: avX, cy: avY - 4, r: 4, fill: color }));
      g.appendChild(svgEl('path', { d: `M${avX - 5} ${avY + 8} Q${avX} ${avY + 3} ${avX + 5} ${avY + 8}`, fill: 'none', stroke: color, 'stroke-width': '1.8', 'stroke-linecap': 'round' }));

      const tx = cfg.barWidth + cfg.avatarCx * 2 + 10;
      const nameStr = (p.is_deceased ? '† ' : '') + clip(p.display_name, textMaxW, 13, 700);
      const hasNp = p.display_name_np && p.display_name_np !== p.display_name;
      const spouseStr = spouses.length ? '♥ ' + spouses.map(s => s.display_name).join(', ') : null;
      const meta = [];
      if (p.pusta) meta.push(`P${p.pusta}`);
      if (p.birth_year) meta.push(p.is_deceased && p.death_year ? `${p.birth_year}–${p.death_year}` : `b.${p.birth_year}`);
      else if (p.is_deceased && p.death_year) meta.push(`d.${p.death_year}`);
      if (p.member_no) meta.push(`#${p.member_no}`);
      const metaStr = meta.length ? clip(meta.join(' · '), textMaxW, 9, 600) : null;

      const lines = [[nameStr, 13, 700, '#0f172a']];
      if (hasNp)     lines.push([clip(p.display_name_np, textMaxW, 9), 9, 400, '#475569']);
      if (metaStr)   lines.push([metaStr, 8.5, 600, '#64748b']);
      if (spouseStr) lines.push([clip(spouseStr, textMaxW, 8.5), 8.5, 400, '#be185d']);
      const ys = lines.length === 4 ? [19, 32, 44, 56] : lines.length === 3 ? [20, 34, 48] : lines.length === 2 ? [25, 41] : [35];
      lines.forEach(([t, fs, fw, fill], i) => g.appendChild(svgEl('text', { x: tx, y: ys[i], 'font-size': fs, 'font-weight': fw, fill }, t)));

      if (p.is_deceased) g.appendChild(svgEl('circle', { cx: W - 8, cy: 8, r: 4, fill: '#ef4444' }));
      g0.appendChild(g);
      boxes.push([node._x, node._y, W, H]);
      cards.push([node._x, node._y, W, H, g]);
    }

    // One <path> for all connections → crisp in PDF and never dropped by printers
    function connectorPath(node, d = []) {
      const kids = node.children || [];
      if (!kids.length) return d;
      const pCX = node._x + cfg.nodeWidth / 2, pBY = node._y + cfg.nodeHeight, joinY = pBY + cfg.levelGap / 2;
      d.push(`M${pCX} ${pBY}V${joinY}`); boxes.push([pCX, pBY, 0, joinY - pBY]);
      const xs = kids.map(c => c._x + cfg.nodeWidth / 2);
      if (xs.length > 1) { d.push(`M${Math.min(...xs)} ${joinY}H${Math.max(...xs)}`); boxes.push([Math.min(...xs), joinY, Math.max(...xs) - Math.min(...xs), 0]); }
      kids.forEach((c, i) => { d.push(`M${xs[i]} ${joinY}V${c._y}`); boxes.push([xs[i], joinY, 0, c._y - joinY]); connectorPath(c, d); });
      return d;
    }

    let L = { width: 800, height: 200 }, boxes = [], depthMax = 0, bandTops = [0], cards = [];
    const BAND_GAP = 70;

    function buildTree() {
      const master = $('treeBase'), cardLayer = $('treeCards');
      master.innerHTML = ''; cardLayer.innerHTML = ''; boxes = []; cards = [];
      if (!forest.length) {
        master.appendChild(svgEl('text', { x: 40, y: 100, 'font-size': 24, fill: '#94a3b8' }, 'No members in this range.'));
        return;
      }
      const roots = JSON.parse(JSON.stringify(forest));
      roots.forEach(measure);
      depthMax = Math.max(...roots.map(maxDepth));

      // Many separate families (whole पुस्ता): wrap them into bands stacked
      // top-to-bottom so the overall shape matches the paper, not one endless row.
      const bandH  = rowY(depthMax) + cfg.nodeHeight + 20;
      const totalW = roots.reduce((a, r) => a + r._sw + cfg.treeGap, 0);
      const { cw, ch } = contentBox();
      const k = Math.max(1, Math.round(Math.sqrt(totalW / (bandH * (cw / ch)))));
      const target = Math.max(...roots.map(r => r._sw), totalW / k);
      const bands = [[]];
      let rowW = 0;
      roots.forEach(r => {
        if (rowW && rowW + r._sw > target) { bands.push([]); rowW = 0; }
        bands[bands.length - 1].push(r); rowW += r._sw + cfg.treeGap;
      });

      let y0 = 0, maxW = 0; bandTops = [];
      bands.forEach(band => {
        bandTops.push(y0);
        let cursor = cfg.marginX;
        band.forEach(r => { place(r, cursor, 0, y0); cursor += r._sw + cfg.treeGap; });
        maxW = Math.max(maxW, cursor - cfg.treeGap + cfg.marginX);
        y0 += bandH + BAND_GAP;
      });
      L = { width: maxW, height: y0 - BAND_GAP };

      bandTops.forEach((bt, b) => {
        if (b > 0) master.appendChild(svgEl('line', { x1: 0, y1: bt - BAND_GAP / 2, x2: L.width, y2: bt - BAND_GAP / 2, stroke: '#94a3b8', 'stroke-width': 1, 'vector-effect': 'non-scaling-stroke' }));
        for (let d = 1; d <= depthMax; d++) {
          const top = bt + rowY(d);
          master.appendChild(svgEl('line', { x1: 0, y1: top - 22, x2: L.width, y2: top - 22, stroke: '#e2e8f0', 'stroke-dasharray': '6 6', 'vector-effect': 'non-scaling-stroke' }));
        }
      });

      master.appendChild(svgEl('path', {
        id: 'connectors', d: roots.flatMap(r => connectorPath(r)).join(''),
        fill: 'none', stroke: '#334155', 'stroke-width': $('lineWidth').value,
        'stroke-linecap': 'square', 'vector-effect': 'non-scaling-stroke',
      }));
      (function drawAll(list) { list.forEach(n => { drawCard(cardLayer, n); drawAll(n.children || []); }); })(roots);
    }

    // ── pages ──
    function paperDims() {
      if ($('paperSize').value === 'FLEX') return [Math.max(200, +$('flexWidth').value || 1500), Math.max(200, +$('flexHeight').value || 900)];
      const [a, b] = paperPresets[$('paperSize').value];
      return $('orientation').value === 'portrait' ? [b, a] : [a, b];
    }
    const mode = () => document.querySelector('input[name=mode]:checked').value;
    function contentBox() { const [w, h] = paperDims(); return { w, h, cw: w - 2 * PAGE_MARGIN_MM, ch: h - 2 * PAGE_MARGIN_MM - HEAD_MM }; }

    function readableCols() {
      const { cw } = contentBox();
      return Math.max(1, Math.ceil(L.width / (cw * cfg.nodeWidth / READABLE_CARD_MM)));
    }

    function renderPages() {
      const { w, h, cw, ch } = contentBox();
      const fit = mode() === 'fit';
      $('tileFields').classList.toggle('hidden', fit);

      let cols = 1, rows = 1, unitsPerMm, tileW, tileH;
      if (fit) {
        unitsPerMm = Math.max(L.width / cw, L.height / ch);
        tileW = L.width; tileH = L.height;
      } else {
        cols = Math.max(1, Math.min(200, parseInt($('cols').value, 10) || 1));
        $('cols').value = cols;
        unitsPerMm = L.width / (cols * cw);
        tileW = cw * unitsPerMm; tileH = ch * unitsPerMm;
        rows = Math.max(1, Math.ceil(L.height / tileH));
      }
      const paperName = $('paperSize').value === 'FLEX' ? `${w}×${h} mm` : $('paperSize').value;
      const cardMm = cfg.nodeWidth / unitsPerMm;

      // info box
      const info = $('sizeInfo');
      const readable = cardMm >= READABLE_CARD_MM * 0.8;
      info.className = 'info ' + (readable ? 'ok' : 'warn');
      info.innerHTML = (fit ? '<strong>1 page</strong>' : `<strong>Grid ${cols} across × ${rows} down</strong>`) + '<br>'
        + `Each card ≈ ${cardMm.toFixed(cardMm < 10 ? 1 : 0)} mm wide on paper — `
        + (readable ? 'text is readable.' : `text is too small. ${fit ? 'Choose “Split across pages”.' : 'Press “Make text readable”.'}`);

      // @page size
      let s = $('dynamicPrintPageStyle');
      if (!s) { s = document.createElement('style'); s.id = 'dynamicPrintPageStyle'; document.head.appendChild(s); }
      s.textContent = `@media print { @page { size: ${w}mm ${h}mm; margin: 0; } }`;

      const pages = $('pages');
      pages.style.gridTemplateColumns = `repeat(${cols}, ${w}mm)`;
      pages.innerHTML = '';
      if (cols * rows > 2000) { pages.innerHTML = '<p>Too many pages. Reduce “Pages across”, use bigger paper, or start from one person.</p>'; return; }

      const used = [];
      for (let r = 0; r < rows; r++) for (let c = 0; c < cols; c++) {
        const x0 = c * tileW, y0 = r * tileH;
        if (fit || boxes.some(([x, y, bw, bh]) => x <= x0 + tileW && x + bw >= x0 && y <= y0 + tileH && y + bh >= y0)) used.push([r, c]);
      }
      const total = used.length, skipped = cols * rows - total;
      info.innerHTML += skipped ? `<br>${skipped} empty page${skipped > 1 ? 's are' : ' is'} skipped — leave a gap there when taping.` : '';
      $('pageSummary').textContent = `${total} × ${paperName} ${$('orientation').value}`
        + (total > 1 ? ' — tape pages together using the row / column number on each page.' : '');

      used.forEach(([r, c], i) => {
        const n = i + 1;
        const page = document.createElement('div');
        page.className = 'page';
        page.style.cssText = `width:${w}mm;height:${h - 1}mm;padding:${PAGE_MARGIN_MM}mm;`;
        const head = document.createElement('div');
        head.className = 'page-head';
        head.innerHTML = `<span><strong></strong> &nbsp;<span class="st"></span></span><span>${total > 1 ? `Page ${n}/${total} · row ${r + 1}, col ${c + 1}` : ''}</span>`;
        head.querySelector('strong').textContent = TITLE;
        head.querySelector('.st').textContent = SUBTITLE;
        page.appendChild(head);

        const vb = fit ? `0 0 ${L.width} ${L.height}` : `${c * tileW} ${r * tileH} ${tileW} ${tileH}`;
        const svg = svgEl('svg', { class: 'page-svg', viewBox: vb, preserveAspectRatio: fit ? 'xMidYMid meet' : 'xMinYMin meet', role: 'img', 'aria-label': `Family tree, page ${n}` });
        svg.style.width = `${cw}mm`; svg.style.height = `${ch}mm`;
        // shared lines layer + only the cards that fall on this page (keeps printing fast)
        svg.appendChild(svgEl('use', { href: fit ? '#treeMaster' : '#treeBase' }));
        if (!fit) {
          const [vx, vy] = [c * tileW, r * tileH];
          cards.forEach(([x, y, bw, bh, el]) => {
            if (x <= vx + tileW && x + bw >= vx && y <= vy + tileH && y + bh >= vy) svg.appendChild(el.cloneNode(true));
          });
        }
        // पुस्ता labels on the left edge of every page
        const lx = (fit ? 0 : c * tileW) + 6;
        for (const bt of bandTops) for (let d = 0; d <= depthMax; d++) {
          const top = bt + rowY(d);
          svg.appendChild(svgEl('text', { x: lx, y: top - 6, 'font-size': 11, 'font-weight': 800, fill: '#94a3b8' }, `पुस्ता ${toNp(startPusta + d)}`));
        }
        page.appendChild(svg);
        page.style.gridRow = r + 1; page.style.gridColumn = c + 1;
        pages.appendChild(page);
      });
    }

    function updateZoom() {
      $('pages').style.zoom = +$('screenScale').value / 100;
      $('screenScaleLabel').textContent = `${$('screenScale').value}%`;
    }

    function downloadSvg() {
      const src = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${L.width} ${L.height}" width="${L.width}" height="${L.height}" font-family="sans-serif">`
                + `<rect width="100%" height="100%" fill="#fff"/>${$('treeMaster').innerHTML}`
                + bandTops.flatMap(bt => Array.from({ length: depthMax + 1 }, (_, d) => `<text x="6" y="${bt + rowY(d) - 6}" font-size="11" font-weight="800" fill="#94a3b8">पुस्ता ${toNp(startPusta + d)}</text>`)).join('')
                + `</svg>`;
      const url = URL.createObjectURL(new Blob([src], { type: 'image/svg+xml;charset=utf-8' }));
      const a = document.createElement('a');
      a.href = url; a.download = `family-tree-pusta-${startPusta}-{{ (int) $toPusta }}.svg`;
      document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
    }

    // ── wire up ──
    ['paperSize', 'orientation', 'flexWidth', 'flexHeight'].forEach(id => $(id).addEventListener('input', () => {
      $('flexFields').classList.toggle('hidden', $('paperSize').value !== 'FLEX');
      buildTree();
      renderPages();
    }));
    $('cols').addEventListener('input', renderPages);
    document.querySelectorAll('input[name=mode]').forEach(r => r.addEventListener('change', renderPages));
    $('colsMinus').addEventListener('click', () => { $('cols').value = Math.max(1, +$('cols').value - 1); renderPages(); });
    $('colsPlus').addEventListener('click',  () => { $('cols').value = +$('cols').value + 1; renderPages(); });
    $('readableBtn').addEventListener('click', () => { $('cols').value = readableCols(); renderPages(); });
    $('lineWidth').addEventListener('change', () => { const c = $('connectors'); if (c) c.setAttribute('stroke-width', $('lineWidth').value); });
    $('screenScale').addEventListener('input', updateZoom);
    $('printBtn').addEventListener('click', () => window.print());
    $('downloadSvgBtn').addEventListener('click', downloadSvg);

    buildTree();
    $('cols').value = readableCols();   // start at a readable size
    if (readableCols() > 200) document.querySelector('input[name=mode][value=fit]').checked = true;
    renderPages();
    updateZoom();
  </script>
@endsection
