{{-- resources/views/tree/index.blade.php --}}
@extends('layouts.app')
@section('title', \App\Support\FrontendLocale::text('परिवार वृक्ष'))

@section('meta_description', \App\Support\FrontendLocale::text('मेन्याङ्बो वंशको परिवार वृक्षमा पुस्ता र पारिवारिक सम्बन्धहरू हेर्नुहोस्।'))

@section('content')
  @if (!$root)
    <div class="p-6 bg-white rounded-xl border text-center">
      <div class="text-lg font-semibold mb-2">{{ \App\Support\FrontendLocale::text('कुनै डाटा भेटिएन') }}</div>
      <div class="text-slate-600">{{ \App\Support\FrontendLocale::text('कृपया seeder चलाउनुहोस् वा नयाँ व्यक्ति थप्नुहोस्।') }}</div>
    </div>
    @php return; @endphp
  @endif

  <div
    x-data="treePage(@json($pustas->values()))"
    x-init="init({{ (int)$root->id }})"
    class="space-y-5 p-4 sm:p-10 lg:p-14"
  >
    <!-- Heading -->
    <div>
      <h2 class="text-xl sm:text-2xl font-semibold">{{ \App\Support\FrontendLocale::text('थिन्दोलुङ खोॽयाहाङ मेन्याङबो वंशावली') }}</h2>
      <div class="text-sm sm:text-base mt-1">
        {{ \App\Support\FrontendLocale::text('हालको जरा:') }}
        <span class="text-emerald-600 font-medium" x-text="currentRootName || '{{ $root->display_name }}'"></span>
      </div>
    </div>

    <!-- Controls box -->
    <div class="rounded-xl border bg-white p-4 shadow-sm">
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">

        <!-- Pusta -->
        <div>
          <label class="block text-xs text-slate-600 mb-1">{{ \App\Support\FrontendLocale::text('पुस्ता (ने/अं)') }}</label>
          <input
            x-ref="pustaInput"
            type="text"
            placeholder="{{ \App\Support\FrontendLocale::text('उदा: ५ / 5') }}"
            class="w-full rounded-lg border px-3 py-2 text-sm"
            @keyup.enter="loadPeopleByPusta()"
          >
        </div>

        <!-- Realtime name search -->
        <div>
          <label class="block text-xs text-slate-600 mb-1">{{ \App\Support\FrontendLocale::text('Search name (AJAX)') }}</label>
          <input
            type="text"
            class="w-full rounded-lg border px-3 py-2 text-sm"
            placeholder="Type name…"
            @input="onSearchKey($event.target.value)"
          >
        </div>

        <!-- Root select -->
        <div class="sm:col-span-2">
          <label class="block text-xs text-slate-600 mb-1">{{ \App\Support\FrontendLocale::text('जरा परिवर्तन:') }}</label>
          <div class="flex gap-2 flex-wrap">
            <select x-ref="rootSel" class="border rounded-lg px-3 py-2 grow min-w-[240px]">
              @foreach ($allPeople as $p)
                <option value="{{ $p->id }}" @selected(request('root_id', $root->id) == $p->id)>
                  {{ $p->display_name }}
                </option>
              @endforeach
            </select>

            <button class="px-4 py-2 rounded-lg bg-slate-700 text-white text-sm" @click="loadPeopleByPusta()">
              {{ \App\Support\FrontendLocale::text('सूची देखाउने') }}
            </button>

            <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm" @click="drawFromSelect()">
              {{ \App\Support\FrontendLocale::text('हेर्नुहोस्') }}
            </button>
          </div>

          <!-- status -->
          <div class="mt-2 text-xs">
            <span x-show="loading" class="text-slate-500" x-cloak>{{ \App\Support\FrontendLocale::text('खोज्दै…') }}</span>
            <span x-show="error" class="text-rose-600" x-text="error" x-cloak></span>
            <span x-show="!loading && info" class="text-slate-500" x-text="info" x-cloak></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Control row -->
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar -mx-1 px-1">
      <button class="shrink-0 px-3 py-1.5 rounded-lg border hover:bg-slate-50 text-sm" @click="zoomIn()">{{ \App\Support\FrontendLocale::text('＋ Zoom In') }}</button>
      <button class="shrink-0 px-3 py-1.5 rounded-lg border hover:bg-slate-50 text-sm" @click="zoomOut()">{{ \App\Support\FrontendLocale::text('－ Zoom Out') }}</button>
      <button class="shrink-0 px-3 py-1.5 rounded-lg border hover:bg-slate-50 text-sm" @click="fitToScreen()">{{ \App\Support\FrontendLocale::text('Fit') }}</button>

      <button class="shrink-0 px-3 py-1.5 rounded-lg border hover:bg-slate-50 text-sm" @click="pan(0,-140)">↑</button>
      <button class="shrink-0 px-3 py-1.5 rounded-lg border hover:bg-slate-50 text-sm" @click="pan(-140,0)">←</button>
      <button class="shrink-0 px-3 py-1.5 rounded-lg border hover:bg-slate-50 text-sm" @click="pan(140,0)">→</button>
      <button class="shrink-0 px-3 py-1.5 rounded-lg border hover:bg-slate-50 text-sm" @click="pan(0,140)">↓</button>

      <button class="shrink-0 px-3 py-1.5 rounded-lg border hover:bg-slate-50 text-sm" @click="prevPusta()">{{ \App\Support\FrontendLocale::text('← Prev पुस्ता') }}</button>
      <button class="shrink-0 px-3 py-1.5 rounded-lg border hover:bg-slate-50 text-sm" @click="nextPusta()">{{ \App\Support\FrontendLocale::text('Next पुस्ता →') }}</button>

      <div class="ml-auto text-xs text-slate-600 flex items-center gap-3 sm:gap-4">
        <span class="inline-flex items-center gap-1 shrink-0">
          <span class="w-3 h-3 rounded-full" style="background:#2563eb"></span> {{ \App\Support\FrontendLocale::text('पुरुष') }}
        </span>
        <span class="inline-flex items-center gap-1 shrink-0">
          <span class="w-3 h-3 rounded-full" style="background:#db2777"></span> {{ \App\Support\FrontendLocale::text('महिला') }}
        </span>
        <span class="inline-flex items-center gap-1 shrink-0">
          <span class="w-3 h-3 rounded-full" style="background:#7c3aed"></span> {{ \App\Support\FrontendLocale::text('अन्य/अज्ञात') }}
        </span>
        <span class="inline-flex items-center gap-1 shrink-0">
          <span class="w-3 h-3 rounded-full border-2 border-rose-500"></span> {{ \App\Support\FrontendLocale::text('स्वर्गीय') }}
        </span>
      </div>
    </div>

    <!-- Tree container (scrollable!) -->
    <div class="relative bg-white border rounded-xl shadow-sm overflow-auto w-full" x-ref="wrap">
      <div class="md:hidden" style="height: calc(100dvh - 12rem);"></div>
      <div class="hidden md:block" style="height: 72vh;"></div>

      <svg x-ref="svg" class="absolute inset-0 w-full h-full">
        <g x-ref="zoomLayer">
          <g x-ref="links"></g>
          <g x-ref="nodes"></g>
        </g>
      </svg>
    </div>

    <!-- Drawer -->
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-50" style="display:none">
      <div class="absolute inset-0 bg-black/30" @click="open=false"></div>

      <div class="absolute right-0 top-0 h-full w-full sm:w-[420px] bg-white shadow-xl p-5 overflow-y-auto"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-x-full opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="translate-x-full opacity-0"
      >
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">{{ \App\Support\FrontendLocale::text('व्यक्ति विवरण') }}</h3>
          <button class="text-slate-500 hover:text-slate-700" @click="open=false">✕</button>
        </div>

        <template x-if="person">
          <div class="space-y-4">
            <div class="flex items-start gap-4 p-4 bg-white rounded-xl shadow-sm border border-slate-200">
              <div class="w-20 h-20 rounded-full bg-slate-200 overflow-hidden flex-shrink-0">
                <template x-if="person.photo_path">
                  <img :src="asset(person.photo_path)" class="w-full h-full object-cover" />
                </template>
                <template x-if="!person.photo_path">
                  <div class="w-full h-full flex items-center justify-center text-slate-500 text-2xl">👤</div>
                </template>
              </div>

              <div class="flex-1">
                <div class="font-bold text-xl text-slate-800 mb-2" x-text="person.display_name"></div>

                <div class="grid grid-cols-2 gap-4">
                  <div class="text-slate-600 text-sm">
                    <div class="mb-1">
                      <span class="font-medium text-slate-700">{{ \App\Support\FrontendLocale::text('जन्म:') }}</span>
                      <span x-text="dateNE(person.birth_date)"></span>
                    </div>
                    <div>
                      <span class="font-medium text-slate-700">{{ \App\Support\FrontendLocale::text('मृत्यु:') }}</span>
                      <span x-text="person.is_deceased ? dateNE(person.death_date) : '—'"></span>
                    </div>
                  </div>
                  <div class="text-right text-slate-600 text-sm">
                    <div class="font-semibold mb-1">{{ \App\Support\FrontendLocale::text('पुस्ता') }}</div>
                    <div class="text-lg font-bold text-slate-800" x-text="person.pusta || '—'"></div>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <div class="text-sm font-semibold mb-1">{{ \App\Support\FrontendLocale::text('जीवनी') }}</div>
              <div class="text-sm text-slate-700" x-text="person.bio || '—'"></div>
            </div>

            <div>
              <div class="text-sm font-semibold mb-1">{{ \App\Support\FrontendLocale::text('अभिभावक') }}</div>
              <ul class="list-disc ml-5 text-sm">
                <template x-for="pp in (person.parents || [])" :key="pp.id">
                  <li>
                    <button class="text-emerald-700 hover:underline" @click="openPerson(pp.id)" x-text="pp.display_name"></button>
                  </li>
                </template>
              </ul>
            </div>

            <div>
              <div class="text-sm font-semibold mb-1">{{ \App\Support\FrontendLocale::text('सन्तान') }}</div>
              <ul class="list-disc ml-5 text-sm">
                <template x-for="cc in (person.children || [])" :key="cc.id">
                  <li>
                    <button class="text-emerald-700 hover:underline" @click="openPerson(cc.id)" x-text="cc.display_name"></button>
                  </li>
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
    function treePage(pustasList = []) {
      const genderColor = g => ({ male:'#2563eb', female:'#db2777', other:'#7c3aed', unknown:'#64748b' }[g] || '#64748b');

      const NE2EN = {'०':'0','१':'1','२':'2','३':'3','४':'4','५':'5','६':'6','७':'7','८':'8','९':'9'};
      const EN2NE = {'0':'०','1':'१','2':'२','3':'३','4':'४','5':'५','6':'६','7':'७','8':'८','9':'९'};
      const toEN = s => (String(s||'')).replace(/[०-९]/g, d => NE2EN[d] || d);
      const toNE = s => (String(s||'')).replace(/[0-9]/g, d => EN2NE[d] || d);

      return {
        // state
        loading:false, error:'', info:'', currentRootName:null,

        // drawer
        open:false, person:null,

        // d3 refs
        svg:null, wrap:null, zoomLayer:null, linksG:null, nodesG:null, zoomBehavior:null,

        // debounced search
        _timer:null,
        _doSearch:null,

        // pustas list
        pustas: (pustasList || []).map(v => String(v)),

        init(initialRootId){
          this.wrap = this.$refs.wrap;
          this.svg = d3.select(this.$refs.svg);
          this.zoomLayer = d3.select(this.$refs.zoomLayer);
          this.linksG = d3.select(this.$refs.links);
          this.nodesG = d3.select(this.$refs.nodes);

          this.zoomBehavior = d3.zoom()
            .scaleExtent([0.2, 3])
            .on('zoom', (ev) => this.zoomLayer.attr('transform', ev.transform));

          this.svg.call(this.zoomBehavior).on('dblclick.zoom', null);

          this.loadTree(initialRootId);
          window.addEventListener('resize', () => this.fitToScreen());
        },

        debounce(fn, wait = 250){
          return (...args) => {
            clearTimeout(this._timer);
            this._timer = setTimeout(() => fn(...args), wait);
          };
        },

        genderIcon(g){
          if(g === 'male') return '♂';
          if(g === 'female') return '♀';
          return '⚧';
        },

        // --- pusta helpers ---
        currentPusta(){
          const raw = (this.$refs.pustaInput?.value || '').trim();
          const pEN = toEN(raw).replace(/\D/g,'');
          return pEN || null;
        },

        async nextPusta(){
          const cur = this.currentPusta();
          if(!cur || !this.pustas.length) return;

          const i = this.pustas.indexOf(String(cur));
          if(i === -1 || i >= this.pustas.length - 1) return;

          const nxt = this.pustas[i+1];
          this.$refs.pustaInput.value = toNE(nxt);
          await this.loadPeopleByPusta();
        },

        async prevPusta(){
          const cur = this.currentPusta();
          if(!cur || !this.pustas.length) return;

          const i = this.pustas.indexOf(String(cur));
          if(i <= 0) return;

          const prv = this.pustas[i-1];
          this.$refs.pustaInput.value = toNE(prv);
          await this.loadPeopleByPusta();
        },

        // --- realtime search ---
        onSearchKey(value){
          const term = (value || '').trim();

          if(!this._doSearch){
            this._doSearch = this.debounce(async (t) => {
              if(!t){
                this.info = '';
                return;
              }
              try{
                this.error = '';
                const url = new URL(@json(\App\Support\FrontendLocale::route('people.search')), window.location.origin);
                url.searchParams.set('term', t);

                const r = await fetch(url);
                if(!r.ok) throw new Error('HTTP ' + r.status);
                const rows = await r.json();

                const sel = this.$refs.rootSel;
                sel.innerHTML = '';

                if(Array.isArray(rows) && rows.length){
                  for(const p of rows){
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = (p.display_name || ('ID '+p.id)) + (p.pusta ? ` (पु.${p.pusta})` : '');
                    sel.appendChild(opt);
                  }
                  this.info = `${rows.length} result(s)`;
                }else{
                  const opt = document.createElement('option');
                  opt.value = '';
                  opt.textContent = @js(\App\Support\FrontendLocale::text('No results'));
                  sel.appendChild(opt);
                  this.info = @js(\App\Support\FrontendLocale::text('No results'));
                }
              }catch(e){
                console.error(e);
                this.error = 'Search failed';
              }
            }, 250);
          }

          this._doSearch(term);
        },

        // --- load people by pusta ---
        async loadPeopleByPusta(){
          this.error=''; this.info=''; this.loading=true;

          const raw = (this.$refs.pustaInput?.value || '').trim();
          const pustaEN = toEN(raw).replace(/\D/g,'');

          if(!pustaEN){
            this.loading=false;
            this.error = @js(\App\Support\FrontendLocale::text('पुस्ता भर्नुहोस् (उदा: ५ वा 5)'));
            return;
          }

          try{
            const url = new URL(@json(\App\Support\FrontendLocale::route('people.byPusta')), window.location.origin);
            url.searchParams.set('pusta', pustaEN);

            const r = await fetch(url);
            if(!r.ok) throw new Error('HTTP '+r.status);
            const people = await r.json();

            const sel = this.$refs.rootSel;
            sel.innerHTML = '';

            if(Array.isArray(people) && people.length){
              for(const p of people){
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.display_name || ('ID '+p.id);
                sel.appendChild(opt);
              }
              this.info = @js(\App\Support\FrontendLocale::text('पुस्ता ')) + toNE(pustaEN) + ' : '+ people.length + @js(\App\Support\FrontendLocale::text(' व्यक्ति भेटिए।'));
            }else{
              const opt = document.createElement('option');
              opt.value = '';
              opt.textContent = @js(\App\Support\FrontendLocale::text('व्यक्ति भेटिएन'));
              sel.appendChild(opt);
              this.info = @js(\App\Support\FrontendLocale::text('पुस्ता ')) + toNE(pustaEN) + @js(\App\Support\FrontendLocale::text(' : व्यक्ति भेटिएन।'));
            }
          }catch(e){
            console.error(e);
            this.error = @js(\App\Support\FrontendLocale::text('लोड गर्न सकिएन'));
          }finally{
            this.loading=false;
          }
        },

        drawFromSelect(){
          const sel = this.$refs.rootSel;
          const id = sel?.value;
          const label = sel?.selectedOptions?.[0]?.textContent || null;

          if(!id){
            this.error = @js(\App\Support\FrontendLocale::text('कृपया सूचीबाट व्यक्ति छान्नुहोस्।'));
            return;
          }

          this.error = '';
          this.currentRootName = label;
          this.loadTree(id);
          this.$nextTick(() => this.wrap?.scrollIntoView({behavior:'smooth', block:'start'}));
        },

        async loadTree(rootId){
          try{
            const url = new URL(@json(\App\Support\FrontendLocale::route('tree.json')), window.location.origin);
            url.searchParams.set('root_id', rootId);

            const res = await fetch(url);
            if(!res.ok) throw new Error(`HTTP ${res.status}`);

            const rootData = await res.json();
            if(!rootData || !rootData.id){
              this.clear();
              return;
            }
            this.drawTree(rootData);
          }catch(e){
            console.error(e);
            alert(@js(\App\Support\FrontendLocale::text('ग्राफ लोड हुन सकेन।')));
          }
        },

        clear(){
          this.linksG.selectAll('*').remove();
          this.nodesG.selectAll('*').remove();
        },

        drawTree(rootData){
          this.clear();

          // Arrow defs
          this.svg.select('defs').remove();
          const defs = this.svg.append('defs');
          defs.append('marker')
            .attr('id', 'arrowhead')
            .attr('viewBox', '0 -5 10 10')
            .attr('refX', 10)
            .attr('refY', 0)
            .attr('markerWidth', 6)
            .attr('markerHeight', 6)
            .attr('orient', 'auto')
            .append('path')
            .attr('d', 'M0,-5L10,0L0,5')
            .attr('fill', '#cbd5e1');

          const root = d3.hierarchy(rootData, d => d.children);

          // more spacing to avoid “diminished”
          const treeLayout = d3.tree()
            .nodeSize([120, 200])
            .separation((a,b) => (a.parent === b.parent ? 1 : 1.4));

          treeLayout(root);

          const linkGen = d3.linkVertical().x(d => d.x).y(d => d.y);

          // links
          this.linksG.selectAll('path')
            .data(root.links())
            .enter().append('path')
            .attr('fill','none')
            .attr('stroke','#cbd5e1')
            .attr('stroke-width',1.5)
            .attr('marker-end', 'url(#arrowhead)')
            .attr('d', linkGen)
            .attr('opacity', 0)
            .transition().duration(300).attr('opacity',1);

          // nodes
          const node = this.nodesG.selectAll('g.node')
            .data(root.descendants())
            .enter().append('g')
            .attr('class','node')
            .attr('transform', d => `translate(${d.x},${d.y - 10})`)
            .style('cursor','pointer');

          node.attr('tabindex', 0)
            .on('click', (_, d) => this.openPerson(d.data.id))
            .on('keydown', (ev, d) => { if (ev.key === 'Enter' || ev.key === ' ') this.openPerson(d.data.id) });

          node.attr('opacity', 0).transition().duration(250).attr('opacity', 1);

          // hitbox
          node.append('circle')
            .attr('r', 26)
            .attr('fill', 'transparent')
            .attr('stroke', 'transparent')
            .style('pointer-events', 'all');

          // visible circle
          node.append('circle')
            .attr('r', 16)
            .attr('fill', d => genderColor(d.data.gender))
            .attr('stroke', d => d.data.is_deceased ? '#f43f5e' : '#e2e8f0')
            .attr('stroke-width', d => d.data.is_deceased ? 3 : 1.5);

          // gender icon above
          node.append('text')
            .attr('text-anchor','middle')
            .attr('y', -22)
            .attr('font-size','12px')
            .attr('font-weight','800')
            .attr('fill','#0f172a')
            .text(d => this.genderIcon(d.data.gender));

          // pusta inside circle
          node.append('text')
            .attr('text-anchor','middle')
            .attr('dy','0.35em')
            .attr('font-size','10px')
            .attr('font-weight','700')
            .attr('fill','#ffffff')
            .text(d => d.data.pusta ? @js(\App\Support\FrontendLocale::text('पु.')) + d.data.pusta : '')
            .attr('display', d => d.data.pusta ? null : 'none');

          // text background card
          node.append('rect')
            .attr('x', -80)
            .attr('y', 18)
            .attr('width', 160)
            .attr('height', 46)
            .attr('rx', 12)
            .attr('fill', '#ffffff')
            .attr('stroke', '#e2e8f0');

          // name
          node.append('text')
            .attr('y', 38)
            .attr('text-anchor','middle')
            .attr('font-size','11px')
            .attr('font-weight','700')
            .attr('fill','#0f172a')
            .text(d => (d.data.name || '').length > 18 ? d.data.name.slice(0,17)+'…' : d.data.name);

          // spouse
          node.append('text')
            .attr('y', 54)
            .attr('text-anchor','middle')
            .attr('font-size','10px')
            .attr('fill','#475569')
            .text(d => (d.data.spouses && d.data.spouses.length)
              ? ('+' + ((d.data.spouses[0] || '').length > 18 ? d.data.spouses[0].slice(0,17)+'…' : d.data.spouses[0]))
              : '');

          this.$nextTick(() => this.fitToScreen());
        },

        zoomIn(){ this.svg.transition().duration(200).call(this.zoomBehavior.scaleBy, 1.2); },
        zoomOut(){ this.svg.transition().duration(200).call(this.zoomBehavior.scaleBy, 0.8); },

        pan(dx, dy){
          const t = d3.zoomTransform(this.$refs.svg);
          const next = d3.zoomIdentity.translate(t.x + dx, t.y + dy).scale(t.k);
          this.svg.transition().duration(150).call(this.zoomBehavior.transform, next);
        },

        resetZoom(){
          this.svg.transition().duration(250).call(this.zoomBehavior.transform, d3.zoomIdentity);
        },

        fitToScreen(){
          const g = this.$refs.zoomLayer;
          if(!g) return;

          const bbox = g.getBBox();
          if (!isFinite(bbox.width) || bbox.width === 0) return this.resetZoom();

          const rect = this.wrap.getBoundingClientRect();
          const w = rect.width, h = rect.height;

          const scale = Math.min(1.0, 0.92 * Math.min(w / bbox.width, h / bbox.height));
          const tx = (w - bbox.width * scale) / 2 - bbox.x * scale;
          const ty = (h - bbox.height * scale) / 2 - bbox.y * scale;

          this.svg.transition().duration(350).call(
            this.zoomBehavior.transform,
            d3.zoomIdentity.translate(tx, ty).scale(scale)
          );
        },

        asset(p){ return p ? `/${String(p).replace(/^\/+/, '')}` : ''; },

        dateNE(d){
          if(!d) return '—';
          const s = String(d).slice(0,10);
          return s.replace(/[0-9]/g, ch => EN2NE[ch] ?? ch);
        },

        async openPerson(id){
          try{
            const url = new URL(@json(url('/person')) + `/${id}`, window.location.origin);
            const r = await fetch(url);
            if(!r.ok) throw new Error(`HTTP ${r.status}`);

            this.person = await r.json();
            this.open = true;
          }catch(e){
            console.error('person fetch failed', e);
            alert(@js(\App\Support\FrontendLocale::text('लोड हुन सकेन।')));
          }
        }
      };
    }
  </script>
@endsection
