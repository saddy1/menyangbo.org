{{-- resources/views/tree/simple.blade.php --}}
@extends('layouts.app')
@section('title', 'वंशावली')

@section('content')
    <style>
        .topbar-wrap {
            flex-wrap: wrap;
        }

        @media (max-width: 640px) {
            .searchBox {
                width: 100% !important;
            }
        }

        [x-cloak] {
            display: none !important;
        }

        /* (optional) link style if you want consistent look */
        .link {
            fill: none;
            stroke: #94a3b8;
            stroke-width: 1.2;
        }
    </style>

    <div x-data="treeUI({{ (int) $rootId }})" x-init="init()" class="w-full">

        <!-- Top Bar -->
        <div class="topbar-wrap flex items-center gap-3 px-4 py-3 border-b bg-white sticky top-0 z-30">
            <!-- Zoom -->
            <div class="flex items-center gap-2 text-sm text-slate-700">
                <span class="font-semibold">जुम नियन्त्रण</span>
                <button class="w-9 h-9 rounded-full border flex items-center justify-center hover:bg-slate-50"
                    @click="zoomOut()">−</button>
                <button class="w-9 h-9 rounded-full border flex items-center justify-center hover:bg-slate-50"
                    @click="zoomIn()">+</button>
                <button class="w-9 h-9 rounded-full border flex items-center justify-center hover:bg-slate-50"
                    @click="fit()">⟳</button>
            </div>

            <!-- Generation -->
            <div class="flex items-center gap-2 text-sm text-slate-700">
                <span>Generation :</span>
                <select class="border rounded-lg px-2 py-1" x-model="depth" @change="reload()">
                    <template x-for="d in [2,3,4,5]" :key="d">
                        <option :value="d" x-text="d"></option>
                    </template>
                </select>
            </div>

            <!-- Pusta controls -->
            <div class="flex items-center gap-2 text-sm text-slate-700">
                <span class="font-semibold">Pusta :</span>
                <button class="w-9 h-9 rounded-full border hover:bg-slate-50 flex items-center justify-center"
                    title="Previous pusta" @click="pustaDown()">▼</button>
                <span class="min-w-[44px] text-center font-bold text-slate-800" x-text="currentPusta || '—'"></span>
                <button class="w-9 h-9 rounded-full border hover:bg-slate-50 flex items-center justify-center"
                    title="Next pusta" @click="pustaUp()">▲</button>
            </div>

            <!-- Level center -->
            <div class="flex-1 text-center text-slate-700 font-semibold min-w-[120px]">
                <span x-text="'Level ' + levelText"></span>
            </div>

            <!-- Search -->
            <div class="searchBox relative w-[360px] ml-auto">
                <input type="text" class="w-full border rounded-lg px-3 py-2 text-sm"
                    placeholder="Search Members by ID/Name..." @input="onSearch($event.target.value)"
                    @focus="searchOpen=true" @keydown.escape="searchOpen=false" />
                <div x-show="searchOpen" x-cloak
                    class="absolute mt-1 w-full bg-white border rounded-lg shadow-lg max-h-72 overflow-auto">
                    <template x-for="r in results" :key="r.id">
                        <button class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 flex items-center gap-2"
                            @click="selectResult(r)">
                            <span
                                class="inline-flex items-center justify-center w-7 h-7 rounded-full text-white text-xs font-bold"
                                :style="`background:${genderColor(r.gender)}`">
                                <span x-text="genderLetter(r.gender)"></span>
                            </span>
                            <span class="font-semibold" x-text="r.display_name"></span>
                            <span class="text-slate-500" x-text="'(#'+r.id+')'"></span>
                            <span class="ml-auto text-slate-500 text-xs" x-text="r.pusta ? ('पु.'+r.pusta) : ''"></span>
                        </button>
                    </template>

                    <div x-show="results.length===0" class="px-3 py-2 text-sm text-slate-500">No results</div>
                </div>
            </div>
        </div>

        <!-- Canvas -->
        <div class="relative w-full" style="height: calc(100vh - 64px);">
            <svg x-ref="svg" class="absolute inset-0 w-full h-full bg-white"></svg>
        </div>

        <!-- Hover / Tap Panel -->
        <div x-show="hoverOpen" x-cloak class="fixed z-50" :class="isMobile ? 'right-2 top-[120px]' : ''"
            :style="isMobile ? '' : `left:${hoverX}px; top:${hoverY}px;`" @mouseenter="hoverLock=true"
            @mouseleave="hoverLock=false; closeHoverSoon()">

            <div class="bg-white border shadow-xl rounded-2xl text-xs sm:text-sm p-3 sm:p-4 max-w-[92vw] overflow-auto"
                :class="isMobile ? 'w-[210px] max-h-[55vh]' : 'w-[360px] max-h-[70vh]'">

                <template x-if="hoverPerson">
                    <div>
                        <div class="flex items-start gap-2">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-white font-extrabold"
                                :style="`background:${genderColor(hoverPerson.gender)}`">
                                <span class="text-sm" x-text="genderLetter(hoverPerson.gender)"></span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-slate-900 truncate" :class="isMobile ? 'text-sm' : 'text-base'"
                                    x-text="hoverPerson.display_name"></div>

                                <div class="text-slate-600 text-[11px] sm:text-xs">
                                    <span x-text="'ID: '+hoverPerson.id"></span>
                                    <span class="mx-2">•</span>
                                    <span x-text="hoverPerson.pusta ? ('Pusta: '+hoverPerson.pusta) : 'Pusta: —'"></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <button class="rounded-xl border px-2 py-2 text-xs hover:bg-slate-50"
                                @click="setRootById(hoverPerson.id)">Root</button>

                            <button class="rounded-xl bg-slate-900 text-white px-2 py-2 text-xs hover:bg-slate-800"
                                @click="openPerson(hoverPerson.id)">More</button>
                        </div>

                        <div class="mt-3 border rounded-xl p-2">
                            <div class="text-[11px] sm:text-xs font-semibold text-slate-700 mb-2">Parents</div>

                            <template x-if="(hoverPerson.parents||[]).length===0">
                                <div class="text-[11px] sm:text-xs text-slate-500">—</div>
                            </template>

                            <template x-for="pp in (hoverPerson.parents||[])" :key="pp.id">
                                <button class="block w-full text-left px-2 py-1 rounded-lg hover:bg-slate-50"
                                    @click="setRootById(pp.id)">
                                    <span class="font-semibold text-[12px] sm:text-sm" x-text="pp.display_name"></span>
                                </button>
                            </template>
                        </div>

                        <div class="mt-3 border rounded-xl p-2">
                            <div class="text-[11px] sm:text-xs font-semibold text-slate-700 mb-2">Children</div>

                            <template x-if="(hoverPerson.children||[]).length===0">
                                <div class="text-[11px] sm:text-xs text-slate-500">—</div>
                            </template>

                            <template x-for="cc in (hoverPerson.children||[])" :key="cc.id">
                                <button class="block w-full text-left px-2 py-1 rounded-lg hover:bg-slate-50"
                                    @click="setRootById(cc.id)">
                                    <span class="font-semibold text-[12px] sm:text-sm" x-text="cc.display_name"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="hoverLoading">
                    <div class="p-6 text-slate-600">Loading…</div>
                </template>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/d3@7"></script>
    <script>
        function treeUI(initialRootId) {
            return {
                isMobile: window.matchMedia("(max-width: 640px)").matches || window.matchMedia("(pointer: coarse)").matches,

                NE2EN: {
                    '०': '0',
                    '१': '1',
                    '२': '2',
                    '३': '3',
                    '४': '4',
                    '५': '5',
                    '६': '6',
                    '७': '7',
                    '८': '8',
                    '९': '9'
                },
                toENdigits(s) {
                    return String(s || '').replace(/[०-९]/g, d => this.NE2EN[d] || d);
                },

                rootId: initialRootId || 0,
                depth: 2,
                levelText: 0,
                currentPusta: null,

                results: [],
                searchOpen: false,
                _t: null,

                hoverOpen: false,
                hoverPerson: null,
                hoverX: 0,
                hoverY: 0,
                hoverLock: false,
                _hoverTimer: null,
                hoverLoading: false,

                personCache: {},

                svg: null,
                g: null,
                zoom: null,

                genderColor(g) {
                    if (g === "male") return "#2563eb";
                    if (g === "female") return "#ef4444";
                    return "#7c3aed";
                },
                genderLetter(g) {
                    if (g === "male") return "M";
                    if (g === "female") return "F";
                    return "?";
                },

                init() {
                    window.addEventListener("resize", () => {
                        this.isMobile = window.matchMedia("(max-width: 640px)").matches || window.matchMedia(
                            "(pointer: coarse)").matches;
                        this.fit();
                    });

                    this.svg = d3.select(this.$refs.svg);
                    this.$refs.svg.style.touchAction = "manipulation";

                    const defs = this.svg.append("defs");
                    defs.append("marker")
                        .attr("id", "arrow").attr("viewBox", "0 -5 10 10")
                        .attr("refX", 10).attr("refY", 0)
                        .attr("markerWidth", 6).attr("markerHeight", 6)
                        .attr("orient", "auto")
                        .append("path").attr("d", "M0,-5L10,0L0,5").attr("fill", "#94a3b8");

                    const male = defs.append("symbol").attr("id", "icon-male").attr("viewBox", "0 0 24 24");
                    male.append("circle").attr("cx", "12").attr("cy", "7").attr("r", "4").attr("fill", "#fff");
                    male.append("path").attr("d", "M6 22v-5c0-3 3-5 6-5s6 2 6 5v5").attr("fill", "#fff");

                    const female = defs.append("symbol").attr("id", "icon-female").attr("viewBox", "0 0 24 24");
                    female.append("circle").attr("cx", "12").attr("cy", "7").attr("r", "4").attr("fill", "#fff");
                    female.append("path").attr("d", "M5 11c2-3 5-4 7-4s5 1 7 4v1c0 2-1 4-2 5v5H7v-5c-1-1-2-3-2-5z").attr(
                        "fill", "#fff");

                    const unk = defs.append("symbol").attr("id", "icon-unk").attr("viewBox", "0 0 24 24");
                    unk.append("text").attr("x", "12").attr("y", "16").attr("text-anchor", "middle")
                        .attr("font-size", "14").attr("font-weight", "800").attr("fill", "#fff").text("?");

                    this.g = this.svg.append("g");

                    this.zoom = d3.zoom()
                        .scaleExtent([0.3, 3])
                        .filter((ev) => {
                            const t = ev?.target;
                            if (t && t.closest && t.closest("g.node")) return false;
                            return true;
                        })
                        .on("zoom", (ev) => this.g.attr("transform", ev.transform));

                    this.svg.call(this.zoom).on("dblclick.zoom", null);
                    this.svg.on("wheel.zoom", null);

                    this.reload();
                },

                async reload() {
                    const url = new URL(@json(route('tree.json')), window.location.origin);
                    url.searchParams.set("root_id", this.rootId);
                    url.searchParams.set("depth", this.depth);

                    const res = await fetch(url);
                    const data = await res.json();

                    this.draw(data);
                    this.currentPusta = (data && data.pusta) ? String(data.pusta) : this.currentPusta;
                },

                /* ===============================
                   ✅ FIXED DRAW FOR MARRIAGE
                =============================== */
                draw(data) {
                    this.g.selectAll("*").remove();
                    if (!data || !data.id) return;

                    const root = d3.hierarchy(data, d => d.children);

                    // wider spacing to support couple nodes
                    const layout = d3.tree().nodeSize([220, 150]);
                    layout(root);

                    this.levelText = d3.max(root.descendants(), d => d.depth) || 0;

                    // links
                    this.g.selectAll("path.link")
                        .data(root.links())
                        .enter()
                        .append("path")
                        .attr("class", "link")
                        .attr("fill", "none")
                        .attr("stroke", "#94a3b8")
                        .attr("stroke-width", 1.2)
                        .attr("marker-end", "url(#arrow)")
                        .attr("d", d3.linkVertical().x(d => d.x).y(d => d.y));

                    const node = this.g.selectAll("g.node")
                        .data(root.descendants())
                        .enter()
                        .append("g")
                        .attr("class", "node")
                        .attr("transform", d => `translate(${d.x},${d.y})`);

                    // helper to render a single mini node
                    const renderMini = (g, person) => {
                        g.style("cursor", "pointer")
                            .on("mouseenter", (ev) => {
                                if (!this.isMobile) this.hoverShow(ev, person.id);
                            })
                            .on("mousemove", (ev) => {
                                if (!this.isMobile) this.hoverMove(ev);
                            })
                            .on("mouseleave", () => {
                                if (!this.isMobile) this.closeHoverSoon();
                            })
                            .on("pointerdown", (ev) => {
                                if (!this.isMobile) return;
                                ev.preventDefault();
                                ev.stopPropagation();
                                this.hoverShow(ev, person.id);
                            })
                            .on("touchstart", (ev) => {
                                if (!this.isMobile) return;
                                ev.preventDefault();
                                ev.stopPropagation();
                                this.hoverShow(ev, person.id);
                            })
                            .on("click", (ev) => {
                                if (this.isMobile) return;
                                ev.stopPropagation();
                                this.setRootById(person.id);
                            });

                        g.append("circle")
                            .attr("r", 14)
                            .attr("fill", this.genderColor(person.gender))
                            .attr("stroke", "#fff")
                            .attr("stroke-width", 3);

                        g.append("use")
                            .attr("href", person.gender === "male" ? "#icon-male" : (person.gender === "female" ?
                                "#icon-female" : "#icon-unk"))
                            .attr("x", -9).attr("y", -9).attr("width", 18).attr("height", 18);

                        g.append("text")
                            .attr("text-anchor", "middle")
                            .attr("y", 30)
                            .attr("font-size", "10px")
                            .attr("font-weight", "700")
                            .attr("fill", "#0f172a")
                            .text(() => {
                                const nm = (person.name || person.display_name || "");
                                return nm.length > 14 ? (nm.slice(0, 13) + "…") : nm;
                            });

                        g.append("text")
                            .attr("text-anchor", "middle")
                            .attr("y", 44)
                            .attr("font-size", "9px")
                            .attr("fill", "#64748b")
                            .text(() => person.pusta ? ("पु." + person.pusta) : "");
                    };

                   // PERSON nodes (single OR married in same row)
node.filter(d => d.data.type !== "union")
  .each((d, i, nodes) => {
    const wrap = d3.select(nodes[i]);

    const main = {
      id: String(d.data.id),
      name: d.data.name,
      display_name: d.data.display_name,
      gender: d.data.gender,
      pusta: d.data.pusta
    };

    // ✅ robust spouse pick (supports multiple JSON shapes)
    const spouseRaw =
      d.data.spouse ||
      d.data.spouse2 ||
      d.data.partner ||
      (d.data.couple && (d.data.couple.right || d.data.couple.left && d.data.couple.left.id !== main.id ? d.data.couple.left : null)) ||
      null;

    const spouse = spouseRaw && spouseRaw.id ? {
      id: String(spouseRaw.id),
      name: spouseRaw.name,
      display_name: spouseRaw.display_name,
      gender: spouseRaw.gender,
      pusta: spouseRaw.pusta
    } : null;

    // DEBUG (optional): check in browser console
    // console.log("node", main.id, main.name, "spouse?", spouse);

    if (!spouse) {
      renderMini(wrap, main);
      return;
    }

    const dx = 45;

    wrap.append("line")
      .attr("x1", -dx + 1).attr("y1", 0)
      .attr("x2",  dx - 1).attr("y2", 0)
      .attr("stroke", "#94a3b8")
      .attr("stroke-width", 1.6);

    wrap.append("text")
      .attr("x", 0).attr("y", 4)
      .attr("text-anchor", "middle")
      .attr("font-size", "14px")
      .attr("font-weight", "900")
      .attr("fill", "#64748b")
      .text("+");

    const leftG  = wrap.append("g").attr("transform", `translate(${-dx},0)`);
    const rightG = wrap.append("g").attr("transform", `translate(${dx},0)`);

    renderMini(leftG, main);
    renderMini(rightG, spouse);
  });


                   

                    this.fit();
                },

                fit() {
                    const bbox = this.g.node()?.getBBox?.();
                    if (!bbox || !bbox.width) return;

                    const w = this.$refs.svg.clientWidth;
                    const h = this.$refs.svg.clientHeight;

                    const scale = Math.min(1.0, 0.92 * Math.min(w / bbox.width, h / bbox.height));
                    const tx = (w - bbox.width * scale) / 2 - bbox.x * scale;
                    const ty = 80 - bbox.y * scale;

                    this.svg.transition().duration(250)
                        .call(this.zoom.transform, d3.zoomIdentity.translate(tx, ty).scale(scale));
                },

                zoomIn() {
                    this.svg.transition().duration(150).call(this.zoom.scaleBy, 1.2);
                },
                zoomOut() {
                    this.svg.transition().duration(150).call(this.zoom.scaleBy, 0.85);
                },

                async fetchPerson(id) {
                    if (this.personCache[id]) return this.personCache[id];
                    const url = new URL(@json(url('/person')) + `/${id}`, window.location.origin);
                    const r = await fetch(url);
                    const data = await r.json();
                    this.personCache[id] = data;
                    return data;
                },

                hoverMove(ev) {
                    if (this.isMobile) return;
                    const vw = window.innerWidth,
                        vh = window.innerHeight;
                    const cardW = 360,
                        cardH = 420,
                        pad = 10;

                    let x = (ev?.clientX ?? 0) + 14;
                    let y = (ev?.clientY ?? 0) + 14;

                    if (x + cardW + pad > vw) x = vw - cardW - pad;
                    if (x < pad) x = pad;

                    if (y + cardH + pad > vh) y = vh - cardH - pad;
                    if (y < pad) y = pad;

                    this.hoverX = x;
                    this.hoverY = y;
                },

                async hoverShow(ev, id) {
                    clearTimeout(this._hoverTimer);
                    this.hoverLoading = true;
                    this.hoverMove(ev);
                    this.hoverOpen = true;
                    try {
                        this.hoverPerson = await this.fetchPerson(id);
                    } finally {
                        this.hoverLoading = false;
                    }
                },

                closeHoverSoon() {
                    clearTimeout(this._hoverTimer);
                    this._hoverTimer = setTimeout(() => {
                        if (this.hoverLock) return;
                        this.hoverOpen = false;
                    }, 180);
                },

                setRootById(id) {
                    if (!id) return;
                    this.hoverOpen = false;
                    this.rootId = parseInt(id);
                    this.reload();
                },

                openPerson(id) {
                    window.location.href = @json(url('/member')) + '/' + id;
                },

                onSearch(term) {
                    clearTimeout(this._t);
                    this._t = setTimeout(async () => {
                        const t = (term || "").trim();
                        if (!t) {
                            this.searchOpen = false;
                            this.results = [];
                            return;
                        }

                        const url = new URL(@json(route('people.search')), window.location.origin);
                        url.searchParams.set("term", t);

                        const r = await fetch(url);
                        const rows = await r.json();
                        this.results = Array.isArray(rows) ? rows : [];
                        this.searchOpen = true;
                    }, 200);
                },

                selectResult(r) {
                    this.searchOpen = false;
                    if (!r?.id) return;
                    this.rootId = parseInt(r.id);
                    this.currentPusta = r.pusta || this.currentPusta;
                    this.reload();
                },

                async loadByPusta(pustaNumber) {
                    const url = new URL(@json(route('people.firstByPusta')), window.location.origin);
                    url.searchParams.set("pusta", String(pustaNumber));
                    const r = await fetch(url);
                    const person = await r.json();
                    if (person && person.id) {
                        this.rootId = parseInt(person.id);
                        this.currentPusta = person.pusta || String(pustaNumber);
                        this.reload();
                    }
                },

                pustaUp() {
                    const p = parseInt(this.toENdigits(this.currentPusta || "0"), 10);
                    if (!p) return;
                    this.loadByPusta(p + 1);
                },

                pustaDown() {
                    const p = parseInt(this.toENdigits(this.currentPusta || "0"), 10);
                    if (!p || p <= 1) return;
                    this.loadByPusta(p - 1);
                },
            }
        }
    </script>
@endsection
