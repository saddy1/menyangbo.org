@extends('layouts.app')
@section('title','परिवार वृक्ष')

@section('content')
@if(!$root)
  <div class="p-6 bg-white rounded-xl border text-center">
    <div class="text-lg font-semibold mb-2">कुनै डाटा भेटिएन</div>
    <div class="text-slate-600">कृपया seeder चलाउनुहोस् वा नयाँ व्यक्ति थप्नुहोस्।</div>
  </div>
  @php return; @endphp
@endif

<div x-data="treePage()" x-init="init({{ $root->id }})" class="space-y-4">
  {{-- Top controls --}}
  <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
    <div class="text-lg font-semibold">
      जरा (Root): <span class="text-emerald-600">{{ $root->display_name }}</span>
    </div>

    {{-- Live root switch without full reload --}}
    <div class="flex items-center gap-2">
      <label class="text-sm text-slate-600">जरा परिवर्तन:</label>
      <select x-ref="rootSel" class="border rounded-lg px-3 py-2">
        @foreach($allPeople as $p)
          <option value="{{ $p->id }}" @selected(request('root_id', $root->id) == $p->id)>{{ $p->display_name }}</option>
        @endforeach
      </select>
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white" @click="loadTree($refs.rootSel.value)">हेर्नुहोस्</button>
    </div>
  </div>

  {{-- Toolbar --}}
  <div class="flex items-center gap-2">
    <button class="px-3 py-1.5 rounded-lg border hover:bg-slate-50" @click="zoomIn()">＋ Zoom In</button>
    <button class="px-3 py-1.5 rounded-lg border hover:bg-slate-50" @click="zoomOut()">－ Zoom Out</button>
    <button class="px-3 py-1.5 rounded-lg border hover:bg-slate-50" @click="resetZoom()">Reset</button>
    <button class="px-3 py-1.5 rounded-lg border hover:bg-slate-50" @click="fitToScreen()">Fit</button>
    <div class="ml-auto text-xs text-slate-600 flex items-center gap-4">
      <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full" style="background:#2563eb"></span> पुरुष</span>
      <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full" style="background:#db2777"></span> महिला</span>
      <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full" style="background:#7c3aed"></span> अन्य/अज्ञात</span>
      <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full border-2 border-rose-500"></span> स्वर्गीय</span>
    </div>
  </div>

  {{-- Tree container --}}
  <div class="relative bg-white border rounded-xl shadow-sm overflow-hidden" x-ref="wrap" style="height:72vh">
    <svg x-ref="svg" class="w-full h-full block">
      <g x-ref="zoomLayer">
        <g x-ref="links"></g>
        <g x-ref="nodes"></g>
      </g>
    </svg>
  </div>

  {{-- Detail Drawer (your existing content works) --}}
  <div x-show="open" x-transition class="fixed inset-0 z-50" style="display:none">
    <div class="absolute inset-0 bg-black/30" @click="open=false"></div>
    <div class="absolute right-0 top-0 h-full w-full sm:w-[420px] bg-white shadow-xl p-5 overflow-y-auto">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold">व्यक्ति विवरण</h3>
        <button class="text-slate-500 hover:text-slate-700" @click="open=false">✕</button>
      </div>
      <template x-if="person">
        <div class="space-y-4">
          <!-- keep the same fields as before -->
          <div class="flex items-start gap-3">
            <div class="w-14 h-14 rounded-full bg-slate-200 overflow-hidden">
              <template x-if="person.photo_path"><img :src="asset(person.photo_path)" class="w-full h-full object-cover" /></template>
              <template x-if="!person.photo_path"><div class="w-full h-full flex items-center justify-center text-slate-500">👤</div></template>
            </div>
            <div>
              <div class="font-bold text-lg" x-text="person.display_name"></div>
              <div class="text-xs text-slate-600 mt-1">
                जन्म: <span x-text="dateNE(person.birth_date)"></span><br>
                मृत्यु: <span x-text="person.is_deceased ? dateNE(person.death_date) : '—'"></span>
              </div>
            </div>
          </div>
          <div><div class="text-sm font-semibold mb-1">जीवनी</div><div class="text-sm text-slate-700" x-text="person.bio || '—'"></div></div>
          <div><div class="text-sm font-semibold mb-1">अभिभावक</div>
            <ul class="list-disc ml-5 text-sm"><template x-for="pp in (person.parents || [])" :key="pp.id"><li><button class="text-emerald-700 hover:underline" @click="openPerson(pp.id)" x-text="pp.display_name"></button></li></template></ul>
          </div>
          <div><div class="text-sm font-semibold mb-1">सन्तान</div>
            <ul class="list-disc ml-5 text-sm"><template x-for="cc in (person.children || [])" :key="cc.id"><li><button class="text-emerald-700 hover:underline" @click="openPerson(cc.id)" x-text="cc.display_name"></button></li></template></ul>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>

{{-- D3 v7 --}}
<script src="https://cdn.jsdelivr.net/npm/d3@7"></script>
<script>
function treePage(){
  // color by gender
  const colorByGender = g => ({ male:'#2563eb', female:'#db2777', other:'#7c3aed', unknown:'#64748b' }[g] || '#64748b');
  // Nepali digits (for dates if you want to show them on nodes later)
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
      // optional: reload on window resize to get better fit
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
        console.error(e); alert('ग्राफ लोड हुन सकेन।');
      }
    },

    clear(){
      this.linksG.selectAll('*').remove();
      this.nodesG.selectAll('*').remove();
    },

    drawTree(rootData){
      this.clear();

      // Build hierarchy and tree layout
      const root = d3.hierarchy(rootData, d => d.children);
      // Set node separation and size; y grows per depth
      const {clientWidth: w, clientHeight: h} = this.wrap;
      const treeLayout = d3.tree().nodeSize([80, 140]).separation((a,b) => (a.parent === b.parent ? 1 : 1.5));
      treeLayout(root);

      // LINKS (elbow)
      const linkGen = d3.linkVertical()
        .x(d => d.x)
        .y(d => d.y);

      this.linksG.selectAll('path')
        .data(root.links())
        .enter().append('path')
        .attr('fill','none')
        .attr('stroke','#cbd5e1') // slate-300
        .attr('stroke-width',1.5)
        .attr('d', linkGen);

      // NODES
      const node = this.nodesG.selectAll('g.node')
        .data(root.descendants())
        .enter().append('g')
        .attr('class','node')
        .attr('transform', d => `translate(${d.x},${d.y})`);

      // Node shape
      node.append('circle')
        .attr('r', 16)
        .attr('fill', d => colorByGender(d.data.gender))
        .attr('stroke', d => d.data.is_deceased ? '#f43f5e' : '#e2e8f0')
        .attr('stroke-width', d => d.data.is_deceased ? 3 : 1.5)
        .style('cursor','pointer')
        .on('click', (_, d) => this.openPerson(d.data.id));

      // Name label
      node.append('text')
        .attr('y', 30)
        .attr('text-anchor','middle')
        .attr('font-size','11px')
        .attr('fill','#0f172a')
        .text(d => d.data.name.length > 16 ? d.data.name.slice(0,15)+'…' : d.data.name);

      // (Optional) small spouses line
      node.append('text')
        .attr('y', 45)
        .attr('text-anchor','middle')
        .attr('font-size','10px')
        .attr('fill','#475569')
        .text(d => (d.data.spouses && d.data.spouses.length) ? ('+' + (d.data.spouses[0].length>16? d.data.spouses[0].slice(0,15)+'…' : d.data.spouses[0])) : '');

      // Center & fit
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

      const w = this.$refs.wrap.clientWidth, h = this.$refs.wrap.clientHeight;
      const scale = Math.min(1.0, 0.9 * Math.min(w / bbox.width, h / bbox.height));
      const tx = (w - bbox.width * scale) / 2 - bbox.x * scale;
      const ty = (h - bbox.height * scale) / 2 - bbox.y * scale;
      this.svg.transition().duration(350).call(this.zoomBehavior.transform, d3.zoomIdentity.translate(tx, ty).scale(scale));
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
