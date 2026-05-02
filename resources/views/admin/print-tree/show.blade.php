@extends('admin.layout')
@section('title', ($root?->display_name ?? 'Family Tree') . ' — Print Tree')
@section('content')
  <style>
    :root {
      --paper-width-mm: 297;
      --paper-height-mm: 210;
      --screen-scale: 0.22;
      --tree-width-px: 1600;
      --tree-height-px: 900;
      --header-space-mm: 32;
      --content-space-mm: 164;
    }

    * { box-sizing: border-box; }
    .print-tree-page {
      font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: #eef2f7;
      color: #0f172a;
      margin: 0 -1.5rem;
    }

    .shell {
      display: grid;
      grid-template-columns: 340px minmax(0, 1fr);
      min-height: calc(100vh - 4rem);
    }

    .panel {
      background: #ffffff;
      color: #0f172a;
      padding: 22px;
      position: sticky;
      top: 4rem;
      height: calc(100vh - 4rem);
      overflow: auto;
      border-right: 1px solid #e2e8f0;
    }

    .panel h1 {
      font-size: 1.3rem;
      line-height: 1.2;
      margin: 0 0 6px;
      font-weight: 800;
      color: #0f172a;
    }

    .panel p,
    .panel label,
    .panel .hint {
      color: #475569;
      font-size: 0.9rem;
    }

    .toolbar-group {
      margin-top: 18px;
      padding-top: 16px;
      border-top: 1px solid #e2e8f0;
    }

    .toolbar-group h2 {
      margin: 0 0 10px;
      font-size: 0.82rem;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #64748b;
    }

    .panel select,
    .panel input {
      width: 100%;
      border: 1px solid #cbd5e1;
      background: #fff;
      color: #0f172a;
      border-radius: 10px;
      padding: 10px 12px;
      font-size: 0.93rem;
      outline: none;
    }

    .panel input[type="range"] {
      padding: 0;
      background: transparent;
      border: 0;
    }

    .field-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .btn-row {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .btn {
      border: 0;
      border-radius: 10px;
      padding: 10px 14px;
      font-weight: 700;
      cursor: pointer;
      transition: 160ms ease;
      font-size: 0.9rem;
    }

    .btn-primary { background: #2563eb; color: #fff; }
    .btn-primary:hover { background: #1d4ed8; }

    .btn-secondary {
      background: #fff;
      color: #334155;
      border: 1px solid #cbd5e1;
    }
    .btn-secondary:hover { background: #f8fafc; }

    .stat-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .stat {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 10px;
    }

    .stat strong {
      display: block;
      font-size: 1.05rem;
      color: #0f172a;
      margin-bottom: 3px;
    }

    .preview-shell {
      padding: 24px;
      min-width: 0;
      overflow: auto;
    }

    .preview-topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      margin-bottom: 16px;
      flex-wrap: wrap;
    }

    .preview-topbar h2 {
      margin: 0;
      font-size: 1.05rem;
      font-weight: 800;
      color: #0f172a;
    }

    .preview-topbar p {
      margin: 3px 0 0;
      color: #64748b;
      font-size: 0.9rem;
    }

    .legend {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .legend span {
      background: #fff;
      border: 1px solid #dbe3ef;
      border-radius: 999px;
      padding: 6px 11px;
      font-size: 0.8rem;
      color: #334155;
    }

    .paper-stage {
      background:
        linear-gradient(90deg, rgba(148, 163, 184, 0.08) 1px, transparent 1px),
        linear-gradient(rgba(148, 163, 184, 0.08) 1px, transparent 1px),
        #dfe7f1;
      background-size: 24px 24px, 24px 24px, auto;
      border: 1px solid #dbe3ef;
      border-radius: 20px;
      padding: 24px;
      min-height: calc(100vh - 130px);
      overflow: auto;
    }

    .paper {
      width: calc(var(--paper-width-mm) * 1mm);
      min-height: calc(var(--paper-height-mm) * 1mm);
      background: #fff;
      margin: 0 auto;
      box-shadow: 0 18px 60px rgba(15, 23, 42, 0.16);
      border-radius: 10px;
      overflow: hidden;
      transform-origin: top center;
      transform: scale(var(--screen-scale));
      margin-bottom: calc((1 - var(--screen-scale)) * var(--paper-height-mm) * 1mm * -1);
    }

    .paper.fit-page .svg-shell svg {
      width: 100%;
      height: 100%;
    }

    .paper.actual-size .svg-shell svg {
      width: calc(var(--tree-width-px) * 1px);
      height: calc(var(--tree-height-px) * 1px);
    }

    .paper-header {
      padding: 14mm 16mm 6mm;
      border-bottom: 1px solid #e2e8f0;
    }

    .paper-header h3 {
      margin: 0;
      font-size: 18px;
      font-weight: 800;
      color: #0f172a;
    }

    .paper-header p {
      margin: 5px 0 0;
      color: #475569;
      font-size: 11px;
    }

    .svg-shell {
      padding: 8mm 8mm 12mm;
      overflow: visible;
    }

    .paper.fit-page .svg-shell {
      height: calc(var(--content-space-mm) * 1mm);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .svg-shell svg {
      display: block;
      shape-rendering: geometricPrecision;
      text-rendering: geometricPrecision;
      max-width: 100%;
      max-height: 100%;
    }

    .hidden { display: none !important; }

    @media (max-width: 1100px) {
      .shell { grid-template-columns: 1fr; }
      .panel { position: static; height: auto; }
      .paper { transform: none; width: 100%; min-height: auto; margin-bottom: 0; }
    }

    @media print {
      body { background: #fff; }
      body > header, #sidebar, #sidebarBackdrop, .panel, .preview-topbar, .legend, .paper-stage { display: none !important; }
      main, .print-tree-page, .shell, .preview-shell { display: block !important; padding: 0 !important; margin: 0 !important; min-height: 0 !important; }
      .print-only { display: block !important; }
      .paper {
        width: calc(var(--print-width-mm) * 1mm);
        min-height: calc(var(--print-height-mm) * 1mm);
        box-shadow: none;
        border-radius: 0;
        margin: 0;
        transform: none !important;
      }
      .paper.fit-page .svg-shell svg { width: 100% !important; height: 100% !important; }
      .paper.actual-size .svg-shell svg {
        width: calc(var(--tree-width-px) * 1px) !important;
        height: calc(var(--tree-height-px) * 1px) !important;
      }
    }

    .print-only { display: none; }
  </style>
  <div class="print-tree-page">
  <div class="shell">
    <aside class="panel">
      <div>
        <a href="{{ route('admin.print-tree.form') }}" style="color:#2563eb;text-decoration:none;font-size:.88rem;">&larr; Change root / pusta</a>
        <h1 style="margin-top:8px;">Print Tree</h1>
        <p style="font-size:.85rem;">Sharp SVG output for print, PDF, or large-format media.</p>
      </div>

      <div class="toolbar-group">
        <h2>Current Tree</h2>
        <div class="stat-grid">
          <div class="stat">
            <strong>{{ $totalNodes }}</strong>
            <span>Members</span>
          </div>
          <div class="stat">
            <strong>{{ $actualDepth }}</strong>
            <span>Depth</span>
          </div>
        </div>
        <div class="hint" style="margin-top:8px;font-size:.82rem;">
          Root: <strong style="color:#0f172a;">{{ $root?->display_name }}</strong><br>
          Requested: <strong style="color:#0f172a;">{{ $maxPusta }}</strong> pustas
        </div>
      </div>

      <div class="toolbar-group">
        <h2>Paper</h2>
        <label for="paperSize">Paper / Media Size</label>
        <select id="paperSize">
          <option value="A4" selected>A4 PDF</option>
          <option value="A3">A3</option>
          <option value="A2">A2</option>
          <option value="A1">A1</option>
          <option value="A0">A0</option>
          <option value="FLEX">Flex / Banner (Custom)</option>
        </select>

        <div class="field-grid" style="margin-top:10px;">
          <div>
            <label for="orientation">Orientation</label>
            <select id="orientation">
              <option value="landscape" selected>Landscape</option>
              <option value="portrait">Portrait</option>
            </select>
          </div>
          <div>
            <label for="printMode">Print Mode</label>
            <select id="printMode">
              <option value="fit" selected>Fit One Page</option>
              <option value="actual">Full Size</option>
            </select>
          </div>
        </div>

        <div id="flexFields" class="field-grid hidden" style="margin-top:10px;">
          <div>
            <label for="flexWidth">Width (mm)</label>
            <input id="flexWidth" type="number" min="200" value="1500">
          </div>
          <div>
            <label for="flexHeight">Height (mm)</label>
            <input id="flexHeight" type="number" min="200" value="900">
          </div>
        </div>
      </div>

      <div class="toolbar-group">
        <h2>Preview Zoom</h2>
        <input id="screenScale" type="range" min="8" max="80" value="22">
        <div class="hint" id="screenScaleLabel" style="margin-top:6px;">22%</div>
      </div>

      <div class="toolbar-group">
        <h2>Actions</h2>
        <div class="btn-row">
          <button class="btn btn-primary" id="printBtn" type="button">Save PDF / Print</button>
          <button class="btn btn-secondary" id="downloadSvgBtn" type="button">Download Tree SVG</button>
        </div>
        <div class="hint" style="margin-top:8px;font-size:.8rem;">
          For one-page PDF: keep <strong style="color:#0f172a;">A4 Landscape Fit One Page</strong>, then choose <strong style="color:#0f172a;">Save as PDF</strong> in print dialog.
        </div>
      </div>
    </aside>

    <main class="preview-shell">
      <div class="preview-topbar">
        <div>
          <h2>{{ $root?->display_name }} Family Tree</h2>
          <p>Compact SVG tree — spouses shown inside each node card.</p>
        </div>
        <div class="legend">
          <span>🔵 Blue = Male</span>
          <span>🩷 Pink = Female</span>
          <span>♥ Spouse inside card</span>
          <span>† = Deceased</span>
        </div>
      </div>

      <div class="paper-stage">
        <div class="paper fit-page" id="paperPreview">
          <div class="paper-header">
            <h3>मेन्याङ्बो कल्याणकारी संघ — वंशावली</h3>
            <p>
              Root: {{ $root?->display_name }}
              &nbsp;|&nbsp; {{ $maxPusta }} पुस्ता requested
              &nbsp;|&nbsp; {{ $totalNodes }} members
              &nbsp;|&nbsp; Printed: {{ now()->format('Y-m-d') }}
            </p>
          </div>
          <div class="svg-shell">
            <svg id="treeSvg" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Printable family tree"></svg>
          </div>
        </div>
      </div>

      <div class="print-only">
        <div class="paper fit-page" id="printPaper">
          <div class="paper-header">
            <h3>मेन्याङ्बो कल्याणकारी संघ — वंशावली</h3>
            <p>
              Root: {{ $root?->display_name }}
              &nbsp;|&nbsp; {{ $maxPusta }} पुस्ता requested
              &nbsp;|&nbsp; {{ $totalNodes }} members
              &nbsp;|&nbsp; Printed: {{ now()->format('Y-m-d') }}
            </p>
          </div>
          <div class="svg-shell">
            <svg id="printTreeSvg" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Printable family tree"></svg>
          </div>
        </div>
      </div>
    </main>
  </div>
  </div>

  <script>
    const treeData = {{ Illuminate\Support\Js::from($treeData) }};

    const paperSizeEl       = document.getElementById('paperSize');
    const orientationEl     = document.getElementById('orientation');
    const printModeEl       = document.getElementById('printMode');
    const screenScaleEl     = document.getElementById('screenScale');
    const screenScaleLabelEl= document.getElementById('screenScaleLabel');
    const flexFieldsEl      = document.getElementById('flexFields');
    const flexWidthEl       = document.getElementById('flexWidth');
    const flexHeightEl      = document.getElementById('flexHeight');
    const paperPreviewEl    = document.getElementById('paperPreview');
    const printPaperEl      = document.getElementById('printPaper');
    const treeSvgEl         = document.getElementById('treeSvg');
    const printTreeSvgEl    = document.getElementById('printTreeSvg');

    const paperPresets = {
      A4: { width: 297, height: 210 },
      A3: { width: 420, height: 297 },
      A2: { width: 594, height: 420 },
      A1: { width: 841, height: 594 },
      A0: { width: 1189, height: 841 },
    };

    // ── Compact layout config ──────────────────────────────────────────────
    const cfg = {
      nodeWidth:    192,   // card width (spouse is INSIDE, no side-pill)
      nodeHeight:   62,    // compact card height
      childGap:     22,    // horizontal gap between sibling subtrees
      levelGap:     100,   // vertical gap between generations
      marginX:      36,
      marginY:      20,
      cornerRadius: 13,
      barWidth:     6,     // left gender colour bar
      avatarCx:     20,    // avatar centre-x
    };

    // ── Helpers ────────────────────────────────────────────────────────────
    function getPaperDimensions() {
      if (paperSizeEl.value === 'FLEX') {
        return {
          width:  Math.max(200, parseInt(flexWidthEl.value  || '1500', 10)),
          height: Math.max(200, parseInt(flexHeightEl.value || '900',  10)),
        };
      }
      const preset = paperPresets[paperSizeEl.value] || paperPresets.A4;
      return orientationEl.value === 'portrait'
        ? { width: Math.min(preset.width, preset.height), height: Math.max(preset.width, preset.height) }
        : { width: Math.max(preset.width, preset.height), height: Math.min(preset.width, preset.height) };
    }

    function genderColor(g) {
      return g === 'male' ? '#2563eb' : g === 'female' ? '#db2777' : '#64748b';
    }

    function estimateW(text, fs, fw = 400) {
      return Math.ceil(String(text || '').length * fs * (fw >= 700 ? 0.62 : 0.55));
    }

    function clip(text, maxW, fs, fw = 400) {
      let s = String(text || '');
      if (estimateW(s, fs, fw) <= maxW) return s;
      while (s.length > 1 && estimateW(s + '…', fs, fw) > maxW) s = s.slice(0, -1);
      return s + '…';
    }

    function svgEl(tag, attrs = {}, txt = null) {
      const el = document.createElementNS('http://www.w3.org/2000/svg', tag);
      for (const [k, v] of Object.entries(attrs)) el.setAttribute(k, v);
      if (txt !== null) el.textContent = txt;
      return el;
    }

    // ── Measure & Place (all nodes same width — no spouse pill) ───────────
    function measure(node) {
      const children  = node.children || [];
      const selfW     = cfg.nodeWidth;
      if (!children.length) { node._sw = selfW; return selfW; }
      const childW    = children.map(measure).reduce((a, b) => a + b, 0)
                      + cfg.childGap * Math.max(0, children.length - 1);
      node._sw = Math.max(selfW, childW);
      return node._sw;
    }

    function place(node, left, depth, rows) {
      const children  = node.children || [];
      const selfW     = cfg.nodeWidth;
      const sw        = node._sw || selfW;

      node._x  = left + (sw - selfW) / 2;
      node._y  = cfg.marginY + depth * (cfg.nodeHeight + cfg.levelGap);
      rows[depth] = Math.max(rows[depth] || 0, node._y + cfg.nodeHeight);

      if (!children.length) return;

      const totalChildW = children.reduce((a, c) => a + c._sw, 0)
                        + cfg.childGap * Math.max(0, children.length - 1);
      let cursor = left + (sw - totalChildW) / 2;
      children.forEach(child => {
        place(child, cursor, depth + 1, rows);
        cursor += child._sw + cfg.childGap;
      });
    }

    function buildLayout(root) {
      measure(root);
      const rows = [];
      place(root, cfg.marginX, 0, rows);
      return {
        width:  root._sw + cfg.marginX * 2,
        height: Math.max(...rows, 0) + cfg.marginY + 20,
      };
    }

    // ── Draw single compact card ───────────────────────────────────────────
    function drawCard(svg, node) {
      const p       = node.person;
      const spouses = node.spouses || [];
      const color   = genderColor(p.gender);
      const W = cfg.nodeWidth, H = cfg.nodeHeight, R = cfg.cornerRadius;
      const textMaxW = W - cfg.avatarCx * 2 - 18;   // ~136 px usable

      const g = svgEl('g', { transform: `translate(${node._x}, ${node._y})` });

      // Card background
      g.appendChild(svgEl('rect', {
        x: 0, y: 0, width: W, height: H, rx: R,
        fill: p.is_deceased ? '#fff4f4' : '#ffffff',
        stroke: '#d1d5db', 'stroke-width': '1.1',
      }));

      // Left gender bar (two rects trick to keep right edge square)
      g.appendChild(svgEl('rect', { x: 0, y: 0, width: cfg.barWidth + R, height: H, fill: color }));
      g.appendChild(svgEl('rect', { x: cfg.barWidth, y: 0, width: R, height: H, fill: p.is_deceased ? '#fff4f4' : '#ffffff' }));

      // Avatar circle
      const avX = cfg.avatarCx + cfg.barWidth + 4;
      const avY = H / 2;
      g.appendChild(svgEl('circle', { cx: avX, cy: avY, r: 11, fill: color, opacity: '0.15' }));
      g.appendChild(svgEl('circle', { cx: avX, cy: avY - 4, r: 4, fill: color }));
      g.appendChild(svgEl('path', {
        d: `M${avX - 5} ${avY + 8} Q${avX} ${avY + 3} ${avX + 5} ${avY + 8}`,
        fill: 'none', stroke: color, 'stroke-width': '1.8', 'stroke-linecap': 'round',
      }));

      const tx = cfg.barWidth + cfg.avatarCx * 2 + 10;  // text x start ~48

      // ── Build text lines ──────────────────────────────────────────────
      // Decide what goes on each line based on available data
      const nameStr  = (p.is_deceased ? '† ' : '') + clip(p.display_name, textMaxW, 13, 700);
      const hasNp    = p.display_name_np && p.display_name_np !== p.display_name;
      const spouseStr = spouses.length
        ? '♥ ' + clip(spouses.map(s => s.display_name).join(', '), textMaxW - 10, 9)
        : null;

      const metaParts = [];
      if (p.pusta)      metaParts.push(`P${p.pusta}`);
      if (p.birth_year) metaParts.push(p.is_deceased && p.death_year ? `${p.birth_year}–${p.death_year}` : `b.${p.birth_year}`);
      else if (p.is_deceased && p.death_year) metaParts.push(`d.${p.death_year}`);
      if (p.member_no)  metaParts.push(`#${p.member_no}`);
      const metaStr = metaParts.length ? clip(metaParts.join(' · '), textMaxW, 9, 600) : null;

      // 3-row layout inside 62px card: rows at y≈19, 34, 50
      // Row 1: name (always)
      g.appendChild(svgEl('text', {
        x: tx, y: 20, 'font-size': '13', 'font-weight': '700', fill: '#0f172a',
      }, nameStr));

      // Row 2: nepali name OR meta (whichever exists first)
      let usedRow2 = false;
      if (hasNp) {
        g.appendChild(svgEl('text', {
          x: tx, y: 33, 'font-size': '9', fill: '#64748b',
        }, clip(p.display_name_np, textMaxW, 9)));
        usedRow2 = true;
      } else if (metaStr) {
        g.appendChild(svgEl('text', {
          x: tx, y: 33, 'font-size': '9', 'font-weight': '600', fill: '#475569',
        }, metaStr));
        usedRow2 = true;
      }

      // Row 3: meta (if row2 was np) OR spouse
      if (hasNp && metaStr) {
        g.appendChild(svgEl('text', {
          x: tx, y: 45, 'font-size': '8.5', 'font-weight': '600', fill: '#94a3b8',
        }, metaStr));
        // Spouse squeezes in at y=56 if present — tight but readable
        if (spouseStr) {
          g.appendChild(svgEl('text', {
            x: tx, y: 57, 'font-size': '8.5', fill: '#be185d',
          }, clip(spouseStr, textMaxW, 8.5)));
        }
      } else if (usedRow2 && spouseStr) {
        g.appendChild(svgEl('text', {
          x: tx, y: 46, 'font-size': '9', fill: '#be185d',
        }, spouseStr));
      } else if (!usedRow2 && spouseStr) {
        g.appendChild(svgEl('text', {
          x: tx, y: 33, 'font-size': '9', fill: '#be185d',
        }, spouseStr));
      }

      // Deceased red dot in top-right corner
      if (p.is_deceased) {
        g.appendChild(svgEl('circle', { cx: W - 8, cy: 8, r: 4, fill: '#ef4444' }));
      }

      svg.appendChild(g);
    }

    // ── Draw connector lines ───────────────────────────────────────────────
    function drawConnections(svg, node) {
      const children = node.children || [];
      if (!children.length) return;

      const pCX = node._x + cfg.nodeWidth / 2;
      const pBY = node._y + cfg.nodeHeight;
      const joinY = pBY + 26;

      svg.appendChild(svgEl('line', { x1: pCX, y1: pBY, x2: pCX, y2: joinY, stroke: '#94a3b8', 'stroke-width': '1.5' }));

      const cCXs = children.map(c => c._x + cfg.nodeWidth / 2);
      if (cCXs.length > 1) {
        svg.appendChild(svgEl('line', {
          x1: Math.min(...cCXs), y1: joinY,
          x2: Math.max(...cCXs), y2: joinY,
          stroke: '#94a3b8', 'stroke-width': '1.5',
        }));
      }

      children.forEach(child => {
        const cCX = child._x + cfg.nodeWidth / 2;
        svg.appendChild(svgEl('line', { x1: cCX, y1: joinY, x2: cCX, y2: child._y, stroke: '#94a3b8', 'stroke-width': '1.5' }));
        drawConnections(svg, child);
      });
    }

    // ── Generation band labels ─────────────────────────────────────────────
    function drawGenBands(svg, width, depthTops) {
      depthTops.forEach((top, depth) => {
        if (depth > 0) {
          svg.appendChild(svgEl('line', {
            x1: 10, y1: top - 16, x2: width - 10, y2: top - 16,
            stroke: '#e2e8f0', 'stroke-width': '1', 'stroke-dasharray': '5 5',
          }));
        }
        svg.appendChild(svgEl('text', {
          x: 12, y: top - 4, 'font-size': '10', 'font-weight': '800', fill: '#cbd5e1',
        }, `Gen ${depth + 1}`));
      });
    }

    function collectDepthTops(node, rows = {}) {
      rows[node.depth] = Math.min(rows[node.depth] ?? 1e9, node._y);
      (node.children || []).forEach(c => collectDepthTops(c, rows));
      return rows;
    }

    // ── Full render ────────────────────────────────────────────────────────
    function renderTree(svgTarget) {
      if (!treeData) {
        svgTarget.setAttribute('viewBox', '0 0 800 200');
        svgTarget.appendChild(svgEl('text', { x: 40, y: 100, 'font-size': '24', fill: '#94a3b8' }, 'No tree data.'));
        return;
      }

      const root   = JSON.parse(JSON.stringify(treeData));
      const layout = buildLayout(root);
      const depthTops = Object.entries(collectDepthTops(root))
        .sort((a, b) => +a[0] - +b[0])
        .map(([, y]) => y);

      svgTarget.innerHTML = '';
      svgTarget.setAttribute('viewBox', `0 0 ${layout.width} ${layout.height}`);
      svgTarget.setAttribute('width',  layout.width);
      svgTarget.setAttribute('height', layout.height);
      document.documentElement.style.setProperty('--tree-width-px',  String(layout.width));
      document.documentElement.style.setProperty('--tree-height-px', String(layout.height));

      drawGenBands(svgTarget, layout.width, depthTops);
      drawConnections(svgTarget, root);
      (function drawAll(node) {
        drawCard(svgTarget, node);
        (node.children || []).forEach(drawAll);
      })(root);
    }

    // ── Controls ───────────────────────────────────────────────────────────
    function updatePaperMode() {
      const fit = printModeEl.value === 'fit';
      paperPreviewEl.classList.toggle('fit-page',    fit);
      paperPreviewEl.classList.toggle('actual-size', !fit);
      printPaperEl.classList.toggle('fit-page',    fit);
      printPaperEl.classList.toggle('actual-size', !fit);
    }

    function updatePaperSize() {
      flexFieldsEl.classList.toggle('hidden', paperSizeEl.value !== 'FLEX');
      const dims = getPaperDimensions();
      document.documentElement.style.setProperty('--paper-width-mm',   String(dims.width));
      document.documentElement.style.setProperty('--paper-height-mm',  String(dims.height));
      const hdr = 30, content = Math.max(60, dims.height - hdr - 12);
      document.documentElement.style.setProperty('--header-space-mm',  String(hdr));
      document.documentElement.style.setProperty('--content-space-mm', String(content));
      let s = document.getElementById('dynamicPrintPageStyle');
      if (!s) { s = document.createElement('style'); s.id = 'dynamicPrintPageStyle'; document.head.appendChild(s); }
      s.textContent = `@media print { @page { size: ${dims.width}mm ${dims.height}mm; margin: 0; } }`;
    }

    function updateScreenScale() {
      const v = Math.max(0.08, +screenScaleEl.value / 100);
      document.documentElement.style.setProperty('--screen-scale', String(v));
      screenScaleLabelEl.textContent = `${screenScaleEl.value}%`;
    }

    function downloadSvg() {
      const src  = new XMLSerializer().serializeToString(treeSvgEl);
      const blob = new Blob([src], { type: 'image/svg+xml;charset=utf-8' });
      const url  = URL.createObjectURL(blob);
      const a    = document.createElement('a');
      a.href = url; a.download = `family-tree-{{ $root?->id ?? 'root' }}.svg`;
      document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
    }

    paperSizeEl.addEventListener('change', updatePaperSize);
    orientationEl.addEventListener('change', updatePaperSize);
    printModeEl.addEventListener('change', updatePaperMode);
    screenScaleEl.addEventListener('input', updateScreenScale);
    flexWidthEl.addEventListener('input', updatePaperSize);
    flexHeightEl.addEventListener('input', updatePaperSize);
    document.getElementById('printBtn').addEventListener('click', () => window.print());
    document.getElementById('downloadSvgBtn').addEventListener('click', downloadSvg);

    renderTree(treeSvgEl);
    renderTree(printTreeSvgEl);
    updatePaperSize();
    updatePaperMode();
    updateScreenScale();
  </script>
@endsection
