@extends('layouts.app')
@section('title', 'वंशावली सदस्यहरुको सूची')

@section('content')
<div class="py-6" x-data="peopleDirectory()" x-init="init()">

  <div class="bg-white border rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b">
      <div class="text-xl font-bold">वंशावली सदस्यहरुको सूची</div>
      <div class="text-sm text-slate-500 mt-1">
        Total: <span class="font-semibold" x-text="allRows.length"></span>
        <span class="mx-2">•</span>
        Showing: <span class="font-semibold" x-text="filteredRows.length"></span>
      </div>
    </div>

    {{-- Search bar + show/hide columns --}}
    <div class="p-4 flex flex-col md:flex-row gap-3 items-stretch md:items-center">
      <div class="flex-1 relative">
        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔍</div>
        <input
          type="text"
          class="w-full border rounded-lg pl-10 pr-3 py-2"
          placeholder="Search in Gen. Code, सदस्य न., सदस्यको नाम, बुबाको नाम, सदस्यको प्रकार ..."
          x-model="q"
          @input="apply()"
        >
      </div>

      <div class="flex gap-2">
        <button
          class="border rounded-lg px-4 py-2 text-blue-600 hover:bg-blue-50"
          @click="columnsOpen = !columnsOpen"
        >
          SHOW / HIDE COLUMNS
        </button>

        <button
          class="border rounded-lg px-4 py-2 hover:bg-slate-50"
          @click="resetFilters()"
        >
          Reset
        </button>
      </div>
    </div>

    {{-- Columns panel --}}
    <div class="px-4 pb-4" x-show="columnsOpen" x-cloak>
      <div class="border rounded-xl p-3 bg-slate-50">
        <div class="text-sm font-semibold text-slate-700 mb-2">Columns</div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
          <template x-for="col in columns" :key="col.key">
            <label class="inline-flex items-center gap-2">
              <input type="checkbox" class="rounded" x-model="col.visible" @change="render()">
              <span x-text="col.label"></span>
            </label>
          </template>
        </div>
      </div>
    </div>

    {{-- Table --}}
    <div class="overflow-auto">
      <table class="min-w-[1200px] w-full text-sm">
        <thead class="bg-slate-100 text-slate-700">
          <tr>
            <template x-for="col in visibleColumns()" :key="col.key">
              <th
                class="text-left px-4 py-3 whitespace-nowrap select-none"
                :class="col.sortable ? 'cursor-pointer hover:bg-slate-200' : ''"
                @click="col.sortable ? toggleSort(col.key) : null"
              >
                <span x-text="col.label"></span>
                <span class="ml-1 text-xs" x-show="sortKey===col.key" x-text="sortDir==='asc' ? '▲' : '▼'"></span>
              </th>
            </template>
            <th class="px-4 py-3 text-right whitespace-nowrap">Action</th>
          </tr>

          {{-- Filter row like screenshot --}}
          <tr class="bg-blue-50/70">
            <template x-for="col in visibleColumns()" :key="'f-'+col.key">
              <th class="px-3 py-2">
                <template x-if="col.filter === 'text'">
                  <input
                    class="w-full border rounded-md px-2 py-1 bg-white"
                    :placeholder="col.label"
                    x-model="filters[col.key]"
                    @input="apply()"
                  >
                </template>

                <template x-if="col.filter === 'select_member_type'">
                  <select class="w-full border rounded-md px-2 py-1 bg-white"
                          x-model="filters[col.key]" @change="apply()">
                    <option value="">All</option>
                    <template x-for="t in memberTypes" :key="'mt-'+t">
                      <option :value="t" x-text="t"></option>
                    </template>
                  </select>
                </template>

                <template x-if="col.filter === 'select_alive'">
                  <select class="w-full border rounded-md px-2 py-1 bg-white"
                          x-model="filters[col.key]" @change="apply()">
                    <option value="">All</option>
                    <option value="alive">Alive</option>
                    <option value="deceased">Deceased</option>
                  </select>
                </template>

                <template x-if="!col.filter">
                  <div></div>
                </template>
              </th>
            </template>
            <th class="px-4 py-2"></th>
          </tr>
        </thead>

        <tbody>
          <template x-if="loading">
            <tr>
              <td class="px-4 py-6 text-slate-500" colspan="50">Loading all people...</td>
            </tr>
          </template>

          <template x-if="!loading && filteredRows.length===0">
            <tr>
              <td class="px-4 py-6 text-slate-500" colspan="50">No results</td>
            </tr>
          </template>

          <template x-for="(row, idx) in pagedRows()" :key="row.id">
            <tr class="border-t hover:bg-slate-50">
              <template x-for="col in visibleColumns()" :key="row.id+'-'+col.key">
                <td class="px-4 py-3 whitespace-nowrap">
                  <template x-if="col.key==='sn'">
                    <span x-text="idx + 1 + (page-1)*perPage"></span>
                  </template>

                  <template x-if="col.key==='member_no'">
                    <a class="text-blue-600 hover:underline"
                       :href="memberUrl(row.id)"
                       x-text="row.member_no || '—'"></a>
                  </template>

                  <template x-if="col.key==='display_name'">
                    <span class="font-semibold text-slate-900" x-text="row.display_name || '—'"></span>
                  </template>

                  <template x-if="col.key==='pusta'">
                    <span x-text="row.pusta || '—'"></span>
                  </template>

                  <template x-if="col.key==='father_name'">
                    <span x-text="row.father_name || '—'"></span>
                  </template>

                  <template x-if="col.key==='mother_name'">
                    <span x-text="row.mother_name || '—'"></span>
                  </template>

                  <template x-if="col.key==='member_type'">
                    <span x-text="row.member_type || '—'"></span>
                  </template>

                  <template x-if="col.key==='spouse_name'">
                    <span x-text="row.spouse_name || '—'"></span>
                  </template>

                  <template x-if="col.key==='total_children'">
                    <span x-text="(row.total_children ?? '—')"></span>
                  </template>

                  <template x-if="col.key==='alive_status'">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                      :class="row.is_deceased ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700'"
                      x-text="row.is_deceased ? 'Deceased' : 'Alive'"
                    ></span>
                  </template>

                </td>
              </template>

              <td class="px-4 py-3 text-right">
                <a class="inline-flex items-center px-3 py-1.5 rounded-lg border hover:bg-slate-50"
                   :href="memberUrl(row.id)">
                  View
                </a>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    {{-- Pagination (client-side) --}}
    <div class="p-4 flex flex-col md:flex-row items-center gap-3 justify-between border-t bg-white">
      <div class="flex items-center gap-2 text-sm">
        <span class="text-slate-600">Rows:</span>
        <select class="border rounded-lg px-2 py-1" x-model.number="perPage" @change="page=1; render()">
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
          <option :value="200">200</option>
          <option :value="500">500</option>
          <option :value="999999">ALL</option>
        </select>
      </div>

      <div class="text-sm text-slate-600">
        Page <span class="font-semibold" x-text="page"></span> /
        <span class="font-semibold" x-text="totalPages()"></span>
      </div>

      <div class="flex gap-2">
        <button class="border rounded-lg px-3 py-1.5 hover:bg-slate-50"
                :disabled="page<=1"
                @click="page=Math.max(1,page-1)">
          Prev
        </button>
        <button class="border rounded-lg px-3 py-1.5 hover:bg-slate-50"
                :disabled="page>=totalPages()"
                @click="page=Math.min(totalPages(),page+1)">
          Next
        </button>
      </div>
    </div>
  </div>

</div>

{{-- Alpine (only if not already in admin.layout) --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
function peopleDirectory() {
  return {
    loading: true,

    // all loaded rows
    allRows: [],
    filteredRows: [],

    // global search
    q: '',

    // column filters
    filters: {
      member_no: '',
      display_name: '',
      pusta: '',
      father_name: '',
      mother_name: '',
      member_type: '',
      spouse_name: '',
      alive_status: '',
    },

    // sort
    sortKey: 'id',
    sortDir: 'desc',

    // UI
    columnsOpen: false,
    page: 1,
    perPage: 50,
    memberTypes: [],

    // Column config
    columns: [
      { key:'sn',            label:'न.',           visible:true,  sortable:false, filter:null },
      { key:'member_no',     label:'सदस्य न.',     visible:true,  sortable:true,  filter:'text' },
      { key:'display_name',  label:'सदस्यको नाम',  visible:true,  sortable:true,  filter:'text' },
      { key:'pusta',         label:'पुस्ता',      visible:true,  sortable:true,  filter:'text' },
      { key:'father_name',   label:'बुबाको नाम',   visible:true,  sortable:true,  filter:'text' },
      { key:'mother_name',   label:'आमाको नाम',    visible:true,  sortable:true,  filter:'text' },
      { key:'member_type',   label:'सदस्यको प्रकार',visible:true, sortable:true,  filter:'select_member_type' },
      { key:'spouse_name',   label:'दम्पतीको नाम', visible:true,  sortable:true,  filter:'text' },
      { key:'total_children',label:'बच्चाको संख्या',visible:true, sortable:true,  filter:null },
      { key:'alive_status',  label:'जीवित स्थिति', visible:true,  sortable:false, filter:'select_alive' },
    ],

    visibleColumns() {
      return this.columns.filter(c => c.visible);
    },

    memberUrl(id) {
      return `{{ url('/member') }}/${id}`;
    },

    async init() {
      this.loading = true;
      try {
        const res = await fetch(`{{ route('admin.people.directory.all') }}`);
        const json = await res.json();
        this.allRows = Array.isArray(json.rows) ? json.rows : [];

        // build member type list
        const set = new Set();
        for (const r of this.allRows) {
          if (r.member_type && String(r.member_type).trim()) set.add(String(r.member_type).trim());
        }
        this.memberTypes = Array.from(set).sort((a,b)=>a.localeCompare(b));

        this.apply();
      } finally {
        this.loading = false;
      }
    },

    resetFilters() {
      this.q = '';
      for (const k in this.filters) this.filters[k] = '';
      this.sortKey = 'id';
      this.sortDir = 'desc';
      this.page = 1;
      this.apply();
    },

    toggleSort(key) {
      if (this.sortKey === key) {
        this.sortDir = (this.sortDir === 'asc') ? 'desc' : 'asc';
      } else {
        this.sortKey = key;
        this.sortDir = 'asc';
      }
      this.render();
    },

    // normalize for searching (Nepali/English)
    norm(v) {
      return String(v ?? '')
        .toLowerCase()
        .replace(/\s+/g,' ')
        .trim();
    },

    // does row match filters?
    rowMatches(row) {
      // global search across common columns
      const q = this.norm(this.q);
      if (q) {
        const hay = this.norm([
          row.member_no,
          row.display_name,
          row.pusta,
          row.father_name,
          row.mother_name,
          row.member_type,
          row.spouse_name,
          row.id
        ].join(' | '));

        if (!hay.includes(q)) return false;
      }

      // per-column filters
      const f = this.filters;

      if (f.member_no && !this.norm(row.member_no).includes(this.norm(f.member_no))) return false;
      if (f.display_name && !this.norm(row.display_name).includes(this.norm(f.display_name))) return false;
      if (f.pusta && !this.norm(row.pusta).includes(this.norm(f.pusta))) return false;
      if (f.father_name && !this.norm(row.father_name).includes(this.norm(f.father_name))) return false;
      if (f.mother_name && !this.norm(row.mother_name).includes(this.norm(f.mother_name))) return false;

      if (f.member_type && this.norm(row.member_type) !== this.norm(f.member_type)) return false;

      if (f.spouse_name && !this.norm(row.spouse_name).includes(this.norm(f.spouse_name))) return false;

      if (f.alive_status) {
        if (f.alive_status === 'alive' && row.is_deceased) return false;
        if (f.alive_status === 'deceased' && !row.is_deceased) return false;
      }

      return true;
    },

    apply() {
      // filter
      this.filteredRows = this.allRows.filter(r => this.rowMatches(r));
      this.page = 1;
      this.render();
    },

    render() {
      // sort
      const key = this.sortKey;
      const dir = this.sortDir;

      const numKeys = new Set(['id','total_children']);
      const getVal = (r) => {
        if (key === 'alive_status') return r.is_deceased ? 1 : 0;
        return r[key];
      };

      this.filteredRows.sort((a,b) => {
        let va = getVal(a);
        let vb = getVal(b);

        if (numKeys.has(key)) {
          va = Number(va ?? 0);
          vb = Number(vb ?? 0);
          return dir === 'asc' ? (va - vb) : (vb - va);
        }

        va = this.norm(va);
        vb = this.norm(vb);
        if (va < vb) return dir === 'asc' ? -1 : 1;
        if (va > vb) return dir === 'asc' ? 1 : -1;
        return 0;
      });
    },

    totalPages() {
      if (this.perPage >= 999999) return 1;
      return Math.max(1, Math.ceil(this.filteredRows.length / this.perPage));
    },

    pagedRows() {
      if (this.perPage >= 999999) return this.filteredRows;
      const start = (this.page - 1) * this.perPage;
      return this.filteredRows.slice(start, start + this.perPage);
    },
  }
}
</script>
@endsection
