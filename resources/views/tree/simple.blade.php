{{-- resources/views/tree/simple.blade.php --}}
@extends('layouts.app')
@section('title', 'वंशावली')

@section('content')
<style>
  .topbar-wrap{flex-wrap:wrap;}
  @media (max-width: 640px){
    .searchBox{width:100%!important;}
  }
  [x-cloak]{display:none!important;}
</style>

<div x-data="treeUI({{ (int)$rootId }})" x-init="init()" class="w-full">

  <!-- Top Bar -->
  <div class="topbar-wrap flex items-center gap-3 px-4 py-3 border-b bg-white sticky top-0 z-30">

    <!-- Zoom -->
    <div class="flex items-center gap-2 text-sm text-slate-700">
      <span class="font-semibold">जुम नियन्त्रण</span>
      <button class="w-9 h-9 rounded-full border flex items-center justify-center hover:bg-slate-50" @click="zoomOut()">−</button>
      <button class="w-9 h-9 rounded-full border flex items-center justify-center hover:bg-slate-50" @click="zoomIn()">+</button>
      <button class="w-9 h-9 rounded-full border flex items-center justify-center hover:bg-slate-50" @click="fit()">⟳</button>
    </div>

    <!-- Generation -->
    <div class="flex items-center gap-2 text-sm text-slate-700">
      <span>Generation :</span>
      <select class="border rounded-lg px-2 py-1" x-model="depth" @change="reload()">
        <template x-for="d in [2,3,4,5,6,7,8,9,10]" :key="d">
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
      <input
        type="text"
        class="w-full border rounded-lg px-3 py-2 text-sm"
        placeholder="Search Members by ID/Name..."
        @input="onSearch($event.target.value)"
        @focus="searchOpen=true"
        @keydown.escape="searchOpen=false"
      />
      <div x-show="searchOpen" x-cloak class="absolute mt-1 w-full bg-white border rounded-lg shadow-lg max-h-72 overflow-auto">
        <template x-for="r in results" :key="r.id">
          <button class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 flex items-center gap-2"
                  @click="selectResult(r)">
            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-white text-xs font-bold"
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

  <!-- Hover / Tap Panel
       Desktop: follows mouse (clamped)
       Mobile: fixed small right sidebar (doesn't hide tree) -->
  <div x-show="hoverOpen" x-cloak
       class="fixed z-50"
       :class="isMobile ? 'right-2 top-[120px]' : ''"
       :style="isMobile ? '' : `left:${hoverX}px; top:${hoverY}px;`"
       @mouseenter="hoverLock=true"
       @mouseleave="hoverLock=false; closeHoverSoon()">

    <div class="bg-white border shadow-xl rounded-2xl text-xs sm:text-sm
                p-3 sm:p-4
                max-w-[92vw] overflow-auto"
         :class="isMobile ? 'w-[210px] max-h-[55vh]' : 'w-[360px] max-h-[70vh]'">

      <template x-if="hoverPerson">
        <div>
          <!-- Header -->
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

              <div class="text-slate-500 text-[11px] sm:text-xs mt-1" x-show="!isMobile">
                <template x-if="hoverPerson.birth_date"><span x-text="'Born: '+hoverPerson.birth_date"></span></template>
                <template x-if="hoverPerson.death_date"><span class="ml-2" x-text="'Died: '+hoverPerson.death_date"></span></template>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-3 grid grid-cols-2 gap-2">
            <button class="rounded-xl border px-2 py-2 text-xs hover:bg-slate-50"
                    @click="setRootById(hoverPerson.id)">
              Root
            </button>

            <button class="rounded-xl bg-slate-900 text-white px-2 py-2 text-xs hover:bg-slate-800"
                    @click="openPerson(hoverPerson.id)">
              More
            </button>
          </div>

          <!-- Parents -->
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

          <!-- Children -->
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

          <div class="mt-2 text-[10px] text-slate-400" x-show="!isMobile">
            Tip: Click a parent/child to jump and make it root.
          </div>
        </div>
      </template>

      <template x-if="hoverLoading">
        <div class="animate-pulse">
          <div class="h-4 w-32 bg-slate-200 rounded"></div>
          <div class="h-3 w-44 bg-slate-200 rounded mt-2"></div>
          <div class="h-8 bg-slate-200 rounded-xl mt-4"></div>
          <div class="h-28 bg-slate-200 rounded-xl mt-3"></div>
        </div>
      </template>

    </div>
  </div>

  <!-- Full Detail Side Panel (View More) -->
  <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/30" @click="modalOpen=false"></div>

    <div class="absolute right-0 top-0 h-full w-full sm:w-[460px] bg-white shadow-xl p-5 overflow-y-auto">
      <div class="flex items-center justify-between mb-4">
        <div class="text-lg font-semibold">Person Detail</div>
        <button class="text-slate-500 hover:text-slate-700" @click="modalOpen=false">✕</button>
      </div>

      <template x-if="person">
        <div class="space-y-4">

          <div class="p-4 border rounded-2xl">
            <div class="flex items-start gap-3">
              <div class="w-14 h-14 rounded-full flex items-center justify-center text-white font-extrabold"
                   :style="`background:${genderColor(person.gender)}`">
                <span x-text="genderLetter(person.gender)"></span>
              </div>

              <div class="flex-1">
                <div class="text-xl font-bold" x-text="person.display_name"></div>

                <div class="text-sm text-slate-600 mt-1">
                  <span x-text="'ID: '+person.id"></span>
                  <span class="mx-2">•</span>
                  <span x-text="person.pusta ? ('Pusta: '+person.pusta) : 'Pusta: —'"></span>
                </div>

                <div class="text-sm text-slate-600 mt-1">
                  <template x-if="person.birth_date"><span x-text="'Birth: '+person.birth_date"></span></template>
                  <template x-if="person.death_date"><span class="ml-3" x-text="'Death: '+person.death_date"></span></template>
                  <template x-if="person.is_deceased">
                    <span class="ml-3 inline-flex items-center px-2 py-0.5 rounded-full bg-red-50 text-red-700 text-xs font-semibold">Deceased</span>
                  </template>
                </div>

                <template x-if="person.bio">
                  <div class="mt-3 text-sm text-slate-700 whitespace-pre-line" x-text="person.bio"></div>
                </template>

                <div class="mt-3 flex gap-2">
                  <button class="flex-1 rounded-xl border px-3 py-2 text-sm hover:bg-slate-50"
                          @click="setRootById(person.id)">
                    Make Root
                  </button>
                  <button class="flex-1 rounded-xl bg-slate-900 text-white px-3 py-2 text-sm hover:bg-slate-800"
                          @click="modalOpen=false">
                    Close
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="p-4 border rounded-2xl">
            <div class="text-sm font-semibold mb-2">Parents</div>
            <template x-if="(person.parents||[]).length===0">
              <div class="text-sm text-slate-500">—</div>
            </template>
            <template x-for="pp in (person.parents||[])" :key="pp.id">
              <button class="block w-full text-left px-3 py-2 rounded-xl hover:bg-slate-50"
                      @click="setRootById(pp.id)">
                <span class="font-semibold" x-text="pp.display_name"></span>
                <span class="text-slate-500 text-xs" x-text="' (#'+pp.id+')'"></span>
              </button>
            </template>
          </div>

          <div class="p-4 border rounded-2xl">
            <div class="text-sm font-semibold mb-2">Children</div>
            <template x-if="(person.children||[]).length===0">
              <div class="text-sm text-slate-500">—</div>
            </template>
            <template x-for="cc in (person.children||[])" :key="cc.id">
              <button class="block w-full text-left px-3 py-2 rounded-xl hover:bg-slate-50"
                      @click="setRootById(cc.id)">
                <span class="font-semibold" x-text="cc.display_name"></span>
                <span class="text-slate-500 text-xs" x-text="' (#'+cc.id+')'"></span>
              </button>
            </template>
          </div>

        </div>
      </template>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/d3@7"></script>
<script>
function treeUI(initialRootId){
  return {
    // device
    isMobile: window.matchMedia("(max-width: 640px)").matches || window.matchMedia("(pointer: coarse)").matches,

    // Nepali digits => English digits
    NE2EN: {'०':'0','१':'1','२':'2','३':'3','४':'4','५':'5','६':'6','७':'7','८':'8','९':'9'},
    toENdigits(s){ return String(s||'').replace(/[०-९]/g, d => this.NE2EN[d] || d); },

    rootId: initialRootId || 0,
    depth: 5,
    levelText: 0,
    currentPusta: null,

    // search
    results: [],
    searchOpen: false,
    _t: null,

    // full detail
    modalOpen:false,
    person:null,

    // hover/tap panel
    hoverOpen:false,
    hoverPerson:null,
    hoverX: 0,
    hoverY: 0,
    hoverLock:false,
    _hoverTimer:null,
    hoverLoading:false,

    // cache
    personCache:{},

    // d3
    svg:null,
    g:null,
    zoom:null,

    genderColor(g){
      if(g === "male") return "#2563eb";
      if(g === "female") return "#ef4444";
      return "#7c3aed";
    },
    genderLetter(g){
      if(g === "male") return "M";
      if(g === "female") return "F";
      return "?";
    },

    init(){
      // update mobile flag
      window.addEventListener("resize", () => {
        this.isMobile = window.matchMedia("(max-width: 640px)").matches || window.matchMedia("(pointer: coarse)").matches;
        this.fit();
      });

      this.svg = d3.select(this.$refs.svg);
      // helps touch on mobile
      this.$refs.svg.style.touchAction = "manipulation";

      const defs = this.svg.append("defs");
      defs.append("marker")
        .attr("id","arrow").attr("viewBox","0 -5 10 10")
        .attr("refX",10).attr("refY",0)
        .attr("markerWidth",6).attr("markerHeight",6)
        .attr("orient","auto")
        .append("path").attr("d","M0,-5L10,0L0,5").attr("fill","#94a3b8");

      const male = defs.append("symbol").attr("id","icon-male").attr("viewBox","0 0 24 24");
      male.append("circle").attr("cx","12").attr("cy","7").attr("r","4").attr("fill","#fff");
      male.append("path").attr("d","M6 22v-5c0-3 3-5 6-5s6 2 6 5v5").attr("fill","#fff");

      const female = defs.append("symbol").attr("id","icon-female").attr("viewBox","0 0 24 24");
      female.append("circle").attr("cx","12").attr("cy","7").attr("r","4").attr("fill","#fff");
      female.append("path").attr("d","M5 11c2-3 5-4 7-4s5 1 7 4v1c0 2-1 4-2 5v5H7v-5c-1-1-2-3-2-5z").attr("fill","#fff");

      const unk = defs.append("symbol").attr("id","icon-unk").attr("viewBox","0 0 24 24");
      unk.append("text").attr("x","12").attr("y","16").attr("text-anchor","middle")
        .attr("font-size","14").attr("font-weight","800").attr("fill","#fff").text("?");

      this.g = this.svg.append("g");

      // zoom: ignore events starting on nodes (so tap works)
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

    async reload(){
      const url = new URL(@json(route('tree.json')), window.location.origin);
      url.searchParams.set("root_id", this.rootId);
      url.searchParams.set("depth", this.depth);

      const res = await fetch(url);
      const data = await res.json();
      this.draw(data);

      this.currentPusta = (data && data.pusta) ? String(data.pusta) : this.currentPusta;
    },

    draw(data){
      this.g.selectAll("*").remove();
      if(!data || !data.id) return;

      const root = d3.hierarchy(data, d => d.children);
      const layout = d3.tree().nodeSize([95, 150]);
      layout(root);

      this.levelText = d3.max(root.descendants(), d => d.depth) || 0;

      // links
      this.g.selectAll("path.link")
        .data(root.links())
        .enter()
        .append("path")
        .attr("class","link")
        .attr("fill","none")
        .attr("stroke","#94a3b8")
        .attr("stroke-width",1.2)
        .attr("marker-end","url(#arrow)")
        .attr("d", d3.linkVertical().x(d => d.x).y(d => d.y));

      // nodes
      const node = this.g.selectAll("g.node")
        .data(root.descendants())
        .enter()
        .append("g")
        .attr("class","node")
        .attr("transform", d => `translate(${d.x},${d.y})`)
        .style("cursor","pointer")

        // Desktop hover => open panel
        .on("mouseenter", (ev, d) => { if(!this.isMobile) this.hoverShow(ev, d.data.id); })
        .on("mousemove", (ev) => { if(!this.isMobile) this.hoverMove(ev); })
        .on("mouseleave", () => { if(!this.isMobile) this.closeHoverSoon(); })

        // Mobile tap => open right panel (do NOT change root)
        .on("pointerdown", (ev, d) => {
          if(!this.isMobile) return;
          ev.preventDefault();
          ev.stopPropagation();
          this.hoverShow(ev, d.data.id);
        })
        .on("touchstart", (ev, d) => {
          if(!this.isMobile) return;
          ev.preventDefault();
          ev.stopPropagation();
          this.hoverShow(ev, d.data.id);
        })

        // Desktop click => change root
        .on("click", (ev, d) => {
          if(this.isMobile) return;
          this.setRootById(d.data.id);
        });

      node.append("circle")
        .attr("r", 14)
        .attr("fill", d => this.genderColor(d.data.gender))
        .attr("stroke", "#fff")
        .attr("stroke-width", 3);

      node.append("use")
        .attr("href", d => d.data.gender === "male" ? "#icon-male" : (d.data.gender === "female" ? "#icon-female" : "#icon-unk"))
        .attr("x", -9).attr("y", -9).attr("width", 18).attr("height", 18);

      node.append("text")
        .attr("text-anchor","middle")
        .attr("y", 30)
        .attr("font-size","10px")
        .attr("font-weight","700")
        .attr("fill", "#0f172a")
        .text(d => (d.data.name || "").length > 14 ? (d.data.name.slice(0,13)+"…") : d.data.name);

      node.append("text")
        .attr("text-anchor","middle")
        .attr("y", 44)
        .attr("font-size","9px")
        .attr("fill", "#64748b")
        .text(d => d.data.pusta ? ("पु." + d.data.pusta) : "");

      this.fit();
    },

    fit(){
      const bbox = this.g.node()?.getBBox?.();
      if(!bbox || !bbox.width) return;

      const w = this.$refs.svg.clientWidth;
      const h = this.$refs.svg.clientHeight;

      const scale = Math.min(1.0, 0.92 * Math.min(w / bbox.width, h / bbox.height));
      const tx = (w - bbox.width * scale) / 2 - bbox.x * scale;
      const ty = 80 - bbox.y * scale;

      this.svg.transition().duration(250)
        .call(this.zoom.transform, d3.zoomIdentity.translate(tx, ty).scale(scale));
    },

    zoomIn(){ this.svg.transition().duration(150).call(this.zoom.scaleBy, 1.2); },
    zoomOut(){ this.svg.transition().duration(150).call(this.zoom.scaleBy, 0.85); },

    // person fetching (cached)
    async fetchPerson(id){
      if(this.personCache[id]) return this.personCache[id];
      const url = new URL(@json(url('/person')) + `/${id}`, window.location.origin);
      const r = await fetch(url);
      const data = await r.json();
      this.personCache[id] = data;
      return data;
    },

    // Desktop only: clamp position so it never goes outside screen
    hoverMove(ev){
      if(this.isMobile) return; // mobile sidebar is fixed
      const vw = window.innerWidth;
      const vh = window.innerHeight;

      const cardW = 360;
      const cardH = 420;
      const pad = 10;

      let x = (ev?.clientX ?? 0) + 14;
      let y = (ev?.clientY ?? 0) + 14;

      if (x + cardW + pad > vw) x = vw - cardW - pad;
      if (x < pad) x = pad;

      if (y + cardH + pad > vh) y = vh - cardH - pad;
      if (y < pad) y = pad;

      this.hoverX = x;
      this.hoverY = y;
    },

    async hoverShow(ev, id){
      clearTimeout(this._hoverTimer);
      this.hoverLoading = true;

      // position only for desktop
      this.hoverMove(ev);

      this.hoverOpen = true;

      try{
        const p = await this.fetchPerson(id);
        this.hoverPerson = p;
      }finally{
        this.hoverLoading = false;
      }
    },

    closeHoverSoon(){
      clearTimeout(this._hoverTimer);
      this._hoverTimer = setTimeout(() => {
        if(this.hoverLock) return;
        this.hoverOpen = false;
      }, 180);
    },

    // root switching
    setRootById(id){
      if(!id) return;
      this.hoverOpen = false;
      this.modalOpen = false;
      this.rootId = parseInt(id);
      this.reload();
    },

    // view more panel
    async openPerson(id){
      const p = await this.fetchPerson(id);
      this.person = p;
      this.modalOpen = true;
    },

    // search
    onSearch(term){
      clearTimeout(this._t);
      this._t = setTimeout(async () => {
        const t = (term || "").trim();
        if(!t){
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

    selectResult(r){
      this.searchOpen = false;
      if(!r?.id) return;
      this.rootId = parseInt(r.id);
      this.currentPusta = r.pusta || this.currentPusta;
      this.reload();
    },

    // pusta navigation
    async loadByPusta(pustaNumber){
      const url = new URL(@json(route('people.firstByPusta')), window.location.origin);
      url.searchParams.set("pusta", String(pustaNumber));
      const r = await fetch(url);
      const person = await r.json();
      if(person && person.id){
        this.rootId = parseInt(person.id);
        this.currentPusta = person.pusta || String(pustaNumber);
        this.reload();
      }
    },

    pustaUp(){
      const p = parseInt(this.toENdigits(this.currentPusta || "0"), 10);
      if(!p) return;
      this.loadByPusta(p + 1);
    },

    pustaDown(){
      const p = parseInt(this.toENdigits(this.currentPusta || "0"), 10);
      if(!p || p <= 1) return;
      this.loadByPusta(p - 1);
    },
  }
}
</script>
@endsection
