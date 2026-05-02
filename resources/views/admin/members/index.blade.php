@extends('layouts.app')
@section('title', 'वंशावली सदस्यहरुको सूची')

@section('content')
<div class="py-6" x-data="peopleDirectory()" x-init="init()">

  <div class="bg-white border rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b">
      <div class="text-xl font-bold">वंशावली सदस्यहरुको सूची</div>
      <div class="text-sm text-slate-500 mt-1">
        Total: <span class="font-semibold" x-text="total"></span>
        <span class="mx-2">•</span>
        Showing: <span class="font-semibold" x-text="rows.length"></span>
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
          @input="scheduleLoad()"
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
              <input type="checkbox" class="rounded" x-model="col.visible">
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
                    @input="scheduleLoad()"
                  >
                </template>

                <template x-if="col.filter === 'select_member_type'">
                  <select class="w-full border rounded-md px-2 py-1 bg-white"
                          x-model="filters[col.key]" @change="load()">
                    <option value="">All</option>
                    <template x-for="t in memberTypes" :key="'mt-'+t">
                      <option :value="t" x-text="t"></option>
                    </template>
                  </select>
                </template>

                <template x-if="col.filter === 'select_alive'">
                  <select class="w-full border rounded-md px-2 py-1 bg-white"
                          x-model="filters[col.key]" @change="load()">
                    <option value="">All</option>
                    <option value="alive">Alive</option>
                    <option value="deceased">Deceased</option>
                  </select>
                </template>

                <template x-if="col.filter === 'select_gender'">
                  <select class="w-full border rounded-md px-2 py-1 bg-white"
                          x-model="filters[col.key]" @change="load()">
                    <option value="">All</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                    <option value="unknown">Unknown</option>
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
              <td class="px-4 py-6 text-slate-500" colspan="50">Loading members...</td>
            </tr>
          </template>

          <template x-if="!loading && rows.length===0">
            <tr>
              <td class="px-4 py-6 text-slate-500" colspan="50">No results</td>
            </tr>
          </template>

          <template x-for="(row, idx) in rows" :key="row.id">
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

                  <template x-if="col.key==='gender'">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                      :class="genderClass(row.gender)"
                      x-text="genderLabel(row.gender)"
                    ></span>
                  </template>

                  <template x-if="col.key==='grandfather_name'">
                    <span x-text="row.grandfather_name || '—'"></span>
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
        <select class="border rounded-lg px-2 py-1" x-model.number="perPage" @change="page=1; load()">
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
          <option :value="200">200</option>
        </select>
      </div>

      <div class="text-sm text-slate-600">
        Page <span class="font-semibold" x-text="page"></span> /
        <span class="font-semibold" x-text="totalPages()"></span>
      </div>

      <div class="flex gap-2">
        <button class="border rounded-lg px-3 py-1.5 hover:bg-slate-50"
                :disabled="page<=1"
                @click="goPage(page-1)">
          Prev
        </button>
        <button class="border rounded-lg px-3 py-1.5 hover:bg-slate-50"
                :disabled="page>=totalPages()"
                @click="goPage(page+1)">
          Next
        </button>
      </div>
    </div>
  </div>

</div>

<script>
function peopleDirectory() {
  return {
    loading: true,
    rows: [],
    total: 0,
    lastPage: 1,
    q: '',
    filters: {
      member_no: '',
      display_name: '',
      pusta: '',
      gender: '',
      father_name: '',
      grandfather_name: '',
      mother_name: '',
      member_type: '',
      spouse_name: '',
      alive_status: '',
    },
    sortKey: 'id',
    sortDir: 'desc',
    columnsOpen: false,
    page: 1,
    perPage: 50,
    memberTypes: [],
    debounce: null,

    columns: [
      { key:'sn',            label:'न.',           visible:true,  sortable:false, filter:null },
      { key:'member_no',     label:'सदस्य न.',     visible:true,  sortable:true,  filter:'text' },
      { key:'display_name',  label:'सदस्यको नाम',  visible:true,  sortable:true,  filter:'text' },
      { key:'pusta',         label:'पुस्ता',      visible:true,  sortable:true,  filter:'text' },
      { key:'gender',        label:'लिङ्ग',       visible:true,  sortable:true,  filter:'select_gender' },
      { key:'grandfather_name', label:'बाजेको नाम', visible:true, sortable:false, filter:'text' },
      { key:'father_name',   label:'बुबाको नाम',   visible:true,  sortable:false, filter:'text' },
      { key:'mother_name',   label:'आमाको नाम',    visible:true,  sortable:false, filter:'text' },
      { key:'member_type',   label:'सदस्यको प्रकार',visible:true, sortable:true,  filter:'select_member_type' },
      { key:'spouse_name',   label:'दम्पतीको नाम', visible:true,  sortable:false, filter:'text' },
      { key:'total_children',label:'बच्चाको संख्या',visible:true, sortable:true,  filter:null },
      { key:'alive_status',  label:'जीवित स्थिति', visible:true,  sortable:false, filter:'select_alive' },
    ],

    visibleColumns() { return this.columns.filter(c => c.visible); },
    memberUrl(id) { return `{{ url('/member') }}/${id}`; },

    async init() { await this.load(); },

    scheduleLoad() {
      clearTimeout(this.debounce);
      this.debounce = setTimeout(() => {
        this.page = 1;
        this.load();
      }, 250);
    },

    params() {
      const params = new URLSearchParams();
      params.set('page', this.page);
      params.set('per_page', this.perPage);
      params.set('sort', this.sortKey);
      params.set('dir', this.sortDir);
      if (this.q.trim()) params.set('q', this.q.trim());

      for (const [key, value] of Object.entries(this.filters)) {
        if (!value) continue;
        params.set(key, value);
      }
      return params;
    },

    async load() {
      this.loading = true;
      try {
        const res = await fetch(`{{ route('admin.people.directory.all') }}?${this.params()}`, {
          headers: { 'Accept': 'application/json' }
        });
        const json = await res.json();
        this.rows = Array.isArray(json.rows) ? json.rows : [];
        this.total = Number(json.count || 0);
        this.page = Number(json.page || 1);
        this.lastPage = Number(json.last_page || 1);
        this.memberTypes = Array.isArray(json.member_types) ? json.member_types : [];
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
      this.load();
    },

    toggleSort(key) {
      if (['father_name', 'grandfather_name', 'mother_name', 'spouse_name', 'alive_status'].includes(key)) return;
      if (this.sortKey === key) this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      else { this.sortKey = key; this.sortDir = 'asc'; }
      this.page = 1;
      this.load();
    },

    totalPages() { return Math.max(1, this.lastPage); },

    goPage(next) {
      const target = Math.min(this.totalPages(), Math.max(1, next));
      if (target === this.page) return;
      this.page = target;
      this.load();
    },

    genderLabel(g) {
      return { male:'Male', female:'Female', other:'Other', unknown:'Unknown' }[g] || '—';
    },

    genderClass(g) {
      return {
        male: 'bg-blue-50 text-blue-700',
        female: 'bg-pink-50 text-pink-700',
        other: 'bg-purple-50 text-purple-700',
        unknown: 'bg-slate-50 text-slate-600',
      }[g] || 'bg-slate-50 text-slate-600';
    },
  }
}
</script>
@endsection
