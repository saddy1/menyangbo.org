{{-- resources/views/tree/index.blade.php --}}
@extends('layouts.app')
@section('title', 'परिवार वृक्ष')

@section('content')
  @if (!$root)
    <div class="p-6 bg-white rounded-xl border text-center">
      <div class="text-lg font-semibold mb-2">कुनै डाटा भेटिएन</div>
      <div class="text-slate-600">कृपया seeder चलाउनुहोस् वा नयाँ व्यक्ति थप्नुहोस्।</div>
    </div>
    @php return; @endphp
  @endif

  <div x-data="treePage()" x-init="init({{ $root->id }})" class="space-y-4 p-4 sm:p-15 lg:p-20">
    <!-- Heading + Root -->
    <div>
      <h2 class="text-xl sm:text-2xl font-semibold">थिन्दोलुङ खोॽयाहाङ मेन्याङबो वंशावली</h2>
    </div>

    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
      <div class="text-base sm:text-lg font-semibold">
        जरा (Root): <span class="text-emerald-600 break-words">{{ $root->display_name }}</span>
      </div>

      <!-- Root switch -->
      <div class="flex items-center gap-2 flex-wrap">
        <label class="text-sm text-slate-600">जरा परिवर्तन:</label>
        <select x-ref="rootSel" class="border rounded-lg px-3 py-2 max-w-[60vw] sm:max-w-none">
          @foreach ($allPeople as $p)
            <option value="{{ $p->id }}" @selected(request('root_id', $root->id) == $p->id)>{{ $p->display_name }}</option>
          @endforeach
        </select>
        <button class="px-3 py-2 rounded-lg bg-emerald-600 text-white text-sm" @click="loadTree($refs.rootSel.value)">
          हेर्नुहोस्
        </button>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar -mx-1 px-1">
      <button class="shrink-0 px-3 py-1.5 rounded-lg border hover:bg-slate-50 text-sm" @click="zoomIn()">＋ Zoom In</button>
      <button class="shrink-0 px-3 py-1.5 rounded-lg border hover:bg-slate-50 text-sm" @click="zoomOut()">－ Zoom Out</button>
      <button class="shrink-0 px-3 py-1.5 rounded-lg border hover:bg-slate-50 text-sm" @click="fitToScreen()">Fit</button>

      <div class="ml-auto text-xs text-slate-600 flex items-center gap-3 sm:gap-4">
        <span class="inline-flex items-center gap-1 shrink-0"><span class="w-3 h-3 rounded-full" style="background:#2563eb"></span> पुरुष</span>
        <span class="inline-flex items-center gap-1 shrink-0"><span class="w-3 h-3 rounded-full" style="background:#db2777"></span> महिला</span>
        <span class="inline-flex items-center gap-1 shrink-0"><span class="w-3 h-3 rounded-full" style="background:#7c3aed"></span> अन्य/अज्ञात</span>
        <span class="inline-flex items-center gap-1 shrink-0"><span class="w-3 h-3 rounded-full border-2 border-rose-500"></span> स्वर्गीय</span>
      </div>
    </div>

    <!-- Tree container -->
    <div class="relative bg-white border rounded-xl shadow-sm overflow-hidden w-full" x-ref="wrap">
      <!-- height: mobile -> dynamic viewport; md+ -> fixed 72vh -->
      <div class="md:hidden" style="height: calc(100dvh - 8rem);"></div>
      <div class="hidden md:block" style="height: 72vh;"></div>

      <svg x-ref="svg" class="absolute inset-0 w-full h-full">
        <g x-ref="zoomLayer">
          <g x-ref="links"></g>
          <g x-ref="nodes"></g>
        </g>
      </svg>
    </div>

    <!-- Detail Drawer -->
    <div x-show="open" x-transition class="fixed inset-0 z-50" style="display:none">
      <div class="absolute inset-0 bg-black/30" @click="open=false"></div>
      <div class="absolute right-0 top-0 h-full w-full sm:w-[420px] bg-white shadow-xl p-5 overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">व्यक्ति विवरण</h3>
          <button class="text-slate-500 hover:text-slate-700" @click="open=false">✕</button>
        </div>

        <template x-if="person">
          <div class="space-y-4">
            <div class="flex flex-col sm:flex-row items-start gap-4 p-4 bg-white rounded-xl shadow-sm border border-slate-200">
              <!-- Profile Image -->
              <div class="w-20 h-20 rounded-full bg-slate-200 overflow-hidden flex-shrink-0">
                <template x-if="person.photo_path">
                  <img :src="asset(person.photo_path)" class="w-full h-full object-cover" />
                </template>
                <template x-if="!person.photo_path">
                  <div class="w-full h-full flex items-center justify-center text-slate-500 text-2xl">👤</div>
                </template>
              </div>

              <!-- Details -->
              <div class="flex-1">
                <div class="font-bold text-xl text-slate-800 mb-2" x-text="person.display_name"></div>

                <div class="grid grid-cols-2 gap-4">
                  <div class="text-slate-600 text-sm">
                    <div class="mb-1">
                      <span class="font-medium text-slate-700">जन्म:</span>
                      <span x-text="dateNE(person.birth_date)"></span>
                    </div>
                    <div>
                      <span class="font-medium text-slate-700">मृत्यु:</span>
                      <span x-text="person.is_deceased ? dateNE(person.death_date) : '—'"></span>
                    </div>
                  </div>

                  <div class="px-4 sm:text-right md:text-right text-slate-600 text-sm">
                    <div class="font-semibold mb-1">पुस्ता</div>
                    <div class="text-lg font-bold text-slate-800" x-text="(person.pusta) || '—'"></div>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <div class="text-sm font-semibold mb-1">जीवनी</div>
              <div class="text-sm text-slate-700" x-text="person.bio || '—'"></div>
            </div>

            <div>
              <div class="text-sm font-semibold mb-1">अभिभावक</div>
              <ul class="list-disc ml-5 text-sm">
                <template x-for="pp in (person.parents || [])" :key="pp.id">
                  <li><button class="text-emerald-700 hover:underline" @click="openPerson(pp.id)" x-text="pp.display_name"></button></li>
                </template>
              </ul>
            </div>

            <div>
              <div class="text-sm font-semibold mb-1">सन्तान</div>
              <ul class="list-disc ml-5 text-sm">
                <template x-for="cc in (person.children || [])" :key="cc.id">
                  <li><button class="text-emerald-700 hover:underline" @click="openPerson(cc.id)" x-text="cc.display_name"></button></li>
                </template>
              </ul>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>

  {{-- D3 v7 --}}
  <script src="https://cdn.jsdelivr.net/npm/d3@7"></script>
  <script>
    function treePage() {
      const colorByGender = g => ({ male:'#2563eb', female:'#db2777', other:'#7c3aed', unknown:'#64748b' }[g] || '#64748b');
      const map = {'0':'०','1':'१','2':'२','3':'३','4':'४','5':'५','6':'६','7':'७','8':'८','9':'९','-':'-'};
      const toNE = (s='') => String(s||'').replace(/[0-9-]/g, d => map[d] ?? d);

      return {
        open:false, person:null,
        svg:null, wrap:null, zoomLayer:null, linksG:null, nodesG:null, zoomBehavior:null,

        init(rootId){
          this.wrap = this.$refs.wrap;
          this.svg = d3.select(this.$refs.svg);
          this.zoomLayer = d3.select(this.$refs.zoomLayer);
          this.linksG = d3.select(this.$refs.links);
          this.nodesG = d3.select(this.$refs.nodes);

          this.zoomBehavior = d3.zoom().scaleExtent([0.2, 3]).on('zoom', (ev) => this.zoomLayer.attr('transform', ev.transform));
          this.svg.call(this.zoomBehavior).on('dblclick.zoom', null);

          this.loadTree(rootId);
          window.addEventListener('resize', () => this.fitToScreen());
        },

        async loadTree(rootId){
          try{
            const res = await fetch(`/tree-json?root_id=${rootId}`);
            if(!res.ok) throw new Error(`HTTP ${res.status}`);
            const rootData = await res.json();
            if(!rootData || !rootData.id){ this.clear(); return; }
            this.drawTree(rootData);
          }catch(e){
            console.error(e);
            alert('ग्राफ लोड हुन सकेन।');
          }
        },

        clear(){
          this.linksG.selectAll('*').remove();
          this.nodesG.selectAll('*').remove();
        },

        drawTree(rootData){
          this.clear();

          const root = d3.hierarchy(rootData, d => d.children);
          const treeLayout = d3.tree()
            .nodeSize([80, 140])
            .separation((a,b) => (a.parent === b.parent ? 1 : 1.5));
          treeLayout(root);

          const linkGen = d3.linkVertical().x(d => d.x).y(d => d.y);

          this.linksG.selectAll('path')
            .data(root.links())
            .enter().append('path')
            .attr('fill','none')
            .attr('stroke','#cbd5e1')
            .attr('stroke-width',1.5)
            .attr('d', linkGen);

          const node = this.nodesG.selectAll('g.node')
  .data(root.descendants())
  .enter().append('g')
  .attr('class','node')
  .attr('transform', d => `translate(${d.x},${d.y})`)
  .style('cursor','pointer');

// Make the group clickable (keyboard accessible too)
node.attr('tabindex', 0)
    .on('click', (_, d) => this.openPerson(d.data.id))
    .on('keydown', (ev, d) => { if (ev.key === 'Enter' || ev.key === ' ') this.openPerson(d.data.id) });

// Add a transparent hitbox so the whole node is easy to click/tap
node.append('circle')
  .attr('r', 22)                  // bigger than the visible circle
  .attr('fill', 'transparent')    // invisible
  .attr('stroke', 'transparent')
  .style('pointer-events', 'all'); // receives events

// Visible circle (DO NOT disable pointer events here)
node.append('circle')
  .attr('r', 16)
  .attr('fill', d => colorByGender(d.data.gender))
  .attr('stroke', d => d.data.is_deceased ? '#f43f5e' : '#e2e8f0')
  .attr('stroke-width', d => d.data.is_deceased ? 3 : 1.5);

// Pusta inside circle
node.append('text')
  .attr('text-anchor','middle')
  .attr('dy','0.35em')
  .attr('font-size','10px')
  .attr('font-weight','700')
  .attr('fill','#ffffff')
  .text(d => d.data.pusta ? 'पु.'+ d.data.pusta : '')
  .attr('display', d => d.data.pusta ? null : 'none');

// Name below
node.append('text')
  .attr('y', 30)
  .attr('text-anchor','middle')
  .attr('font-size','11px')
  .attr('fill','#0f172a')
  .text(d => d.data.name.length > 16 ? d.data.name.slice(0,15)+'…' : d.data.name);

// Spouse below
node.append('text')
  .attr('y', 45)
  .attr('text-anchor','middle')
  .attr('font-size','10px')
  .attr('fill','#475569')
  .text(d => (d.data.spouses && d.data.spouses.length)
    ? ('+' + (d.data.spouses[0].length>16 ? d.data.spouses[0].slice(0,15)+'…' : d.data.spouses[0]))
    : '');

          this.$nextTick(() => this.fitToScreen());
        },

        zoomIn(){ this.svg.transition().duration(200).call(this.zoomBehavior.scaleBy, 1.2); },
        zoomOut(){ this.svg.transition().duration(200).call(this.zoomBehavior.scaleBy, 0.8); },
        resetZoom(){ this.svg.transition().duration(250).call(this.zoomBehavior.transform, d3.zoomIdentity); },

        fitToScreen(){
          const g = this.$refs.zoomLayer;
          if(!g) return;
          const bbox = g.getBBox();
          if (!isFinite(bbox.width) || bbox.width === 0) return this.resetZoom();

          const rect = this.wrap.getBoundingClientRect();
          const w = rect.width, h = rect.height;
          const scale = Math.min(1.0, 0.9 * Math.min(w / bbox.width, h / bbox.height));
          const tx = (w - bbox.width * scale) / 2 - bbox.x * scale;
          const ty = (h - bbox.height * scale) / 2 - bbox.y * scale;
          this.svg.transition().duration(350).call(
            this.zoomBehavior.transform,
            d3.zoomIdentity.translate(tx, ty).scale(scale)
          );
        },

        asset(p){ return p ? `/${p}` : ''; },
        dateNE(d){ return d ? toNE(new Date(d).toISOString().slice(0,10)) : '—'; },

        async openPerson(id){
          try{
            const r = await fetch(`/person/${id}`);
            if(!r.ok) throw new Error(`HTTP ${r.status}`);
            this.person = await r.json();
            this.open = true;
          }catch(e){
            console.error('person fetch failed', e);
            alert('लोड हुन सकेन।');
          }
        }
      }
    }
  </script>
@endsection
