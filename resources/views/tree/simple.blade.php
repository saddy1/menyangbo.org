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

        .link {
            fill: none;
            stroke-width: 1.3;
        }

        .tree-canvas {
            background-color: #f8fafc;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 22px 22px;
        }
        .tree-legend {
            position: absolute;
            left: 12px;
            bottom: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 7px 12px;
            background: rgba(255,255,255,.92);
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            box-shadow: 0 4px 14px rgba(15,23,42,.06);
            font-size: 11px;
            font-weight: 600;
            color: #334155;
            backdrop-filter: blur(4px);
        }
        .tree-legend span { display: inline-flex; align-items: center; gap: 5px; }
        .tree-legend i { width: 11px; height: 11px; border-radius: 50%; display: inline-block; }
        @media (max-width: 640px) {
            .tree-legend { border-radius: 12px; right: 12px; gap: 6px 10px; font-size: 10px; }
        }

        .hover-card .pc-member {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            border-radius: 999px;
            padding: 1px 7px;
            background: #eef2ff;
            color: #3730a3;
            font-size: 10px;
            font-weight: 800;
        }
        .hover-card .pc-cols {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .hover-card .pc-col-title {
            font-size: 10px;
            font-weight: 800;
            padding: 2px 5px;
            border-radius: 5px;
            margin-bottom: 2px;
        }
        .hover-card .pc-num {
            flex: 0 0 16px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            font-weight: 800;
            color: #fff;
        }
        .hover-card .pc-sub {
            display: block;
            font-size: 9px;
            font-weight: 700;
            color: #94a3b8;
        }

        /* Popup card — fixed in screen coords, compact & clearly bordered */
        .hover-card {
            position: fixed;
            z-index: 50;
            pointer-events: auto;
            animation: popIn 0.12s ease;
            background: #fff;
            border: 1px solid #c7d2fe;
            border-top: 3px solid #4f46e5;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(79,70,229,0.14), 0 2px 8px rgba(0,0,0,0.08);
        }
        @keyframes popIn {
            from { opacity: 0; transform: scale(0.95) translateY(-4px); }
            to   { opacity: 1; transform: scale(1)    translateY(0);     }
        }
        .hover-card .pc-section {
            border: 1px solid #e0e7ff;
            border-radius: 7px;
            padding: 5px 8px;
            margin-top: 6px;
            background: #f5f7ff;
        }
        .hover-card .pc-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #818cf8;
            margin-bottom: 3px;
        }
        .hover-card .pc-item {
            display: block;
            width: 100%;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #1e1b4b;
            padding: 3px 5px;
            border-radius: 5px;
            background: none;
            border: none;
            cursor: pointer;
            transition: background 0.1s;
        }
        .hover-card .pc-item:hover { background: #e0e7ff; }
        .hover-card .pc-row {
            display: flex;
            align-items: center;
            gap: 5px;
            width: 100%;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #1e1b4b;
            padding: 3px 5px;
            border-radius: 5px;
            background: none;
            border: none;
            cursor: pointer;
            transition: background 0.1s;
        }
        .hover-card .pc-row:hover { background: #e0e7ff; }
        .hover-card .pc-tag {
            flex: 0 0 auto;
            min-width: 36px;
            text-align: center;
            border-radius: 999px;
            padding: 1px 5px;
            font-size: 9px;
            font-weight: 800;
        }
        .hover-card .pc-gender {
            flex: 0 0 18px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 8px;
            font-weight: 800;
        }
        .hover-card .pc-tag-blue { background:#dbeafe; color:#1d4ed8; }
        .hover-card .pc-tag-pink { background:#fce7f3; color:#be185d; }
        .hover-card .pc-tag-amber { background:#fef3c7; color:#b45309; }
        .hover-card .pc-tag-slate { background:#e2e8f0; color:#475569; }
        .hover-card .pc-name {
            min-width: 0;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .hover-card .pc-empty {
            font-size: 10px;
            color: #c7d2fe;
            padding: 1px 4px;
        }
        .tree-node-photo {
            pointer-events: none;
        }

        @media print {
            body > header,
            body > footer,
            main > *:not(.tree-print-export) {
                display: none !important;
            }
            main {
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .tree-print-export {
                display: flex !important;
                flex-direction: column;
                padding: 0;
                background: #fff;
                width: 100vw;
                height: 100vh;
                overflow: hidden;
                break-after: avoid;
                page-break-after: avoid;
            }
            .tree-print-header {
                flex: 0 0 auto;
                padding: 8mm 10mm 4mm;
                text-align: center;
                border-bottom: 1px solid #e2e8f0;
            }
            .tree-print-header h1 {
                margin: 0;
                font-size: 18px;
                line-height: 1.25;
                font-weight: 900;
                color: #0f172a;
            }
            .tree-print-header p {
                margin: 2mm 0 0;
                font-size: 10px;
                color: #475569;
            }
            .tree-print-svg-wrap {
                flex: 1 1 auto;
                min-height: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 5mm 7mm 8mm;
            }
            .tree-print-export svg {
                max-width: 100vw;
                max-height: 100%;
                width: 100%;
                height: 100%;
                display: block;
            }
        }
    </style>

    <div x-data="treeUI({{ (int) $rootId }}, {{ auth()->check() && auth()->user()->isAdmin() ? 'true' : 'false' }}, {{ request()->boolean('export') ? 'true' : 'false' }})" x-init="init()" class="w-full">

        {{-- ── TOP BAR ── --}}
        <div class="topbar-wrap flex items-center gap-3 px-4 py-3 border-b bg-white sticky top-0 z-20">
            <div class="flex items-center gap-2 text-sm text-slate-700">
                <span class="font-semibold">जुम नियन्त्रण</span>
                <button class="w-9 h-9 rounded-full border flex items-center justify-center hover:bg-slate-50"
                    @click="zoomOut()">−</button>
                <button class="w-9 h-9 rounded-full border flex items-center justify-center hover:bg-slate-50"
                    @click="zoomIn()">+</button>
                <button class="w-9 h-9 rounded-full border flex items-center justify-center hover:bg-slate-50"
                    @click="fit()">⟳</button>
            </div>

            <div class="flex items-center gap-2 text-sm text-slate-700">
                <span>Tree level :</span>
                <select class="border rounded-lg px-2 py-1" x-model="depth" @change="reload()">
                    <template x-for="d in [2,5,10,15,20,30]" :key="d">
                        <option :value="d" x-text="d"></option>
                    </template>
                </select>
            </div>

            <div class="flex items-center gap-2 text-sm text-slate-700">
                <span class="font-semibold">Pusta :</span>
                <button class="w-9 h-9 rounded-full border hover:bg-slate-50 flex items-center justify-center"
                    title="Previous pusta" @click="pustaDown()">▼</button>
                <span class="min-w-[44px] text-center font-bold text-slate-800" x-text="currentPusta || '—'"></span>
                <button class="w-9 h-9 rounded-full border hover:bg-slate-50 flex items-center justify-center"
                    title="Next pusta" @click="pustaUp()">▲</button>
            </div>

            <div class="flex-1 text-center text-slate-700 font-semibold min-w-[120px]">
                <span x-text="'Level ' + levelText"></span>
            </div>

            <button type="button" @click="toggleUnconnected()"
                class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-800 hover:bg-amber-100"
                title="रुखमा नजोडिएका सदस्यहरू">
                नजोडिएका सदस्य
                <span class="rounded-full bg-amber-500 px-2 py-0.5 text-[11px] font-bold text-white"
                    x-text="unconnectedCount === null ? '…' : unconnectedCount"></span>
            </button>

            <div class="searchBox relative w-[360px] ml-auto">
                <input type="text" class="w-full border rounded-lg px-3 py-2 text-sm"
                    placeholder="Search Members by ID/Name..."
                    @input="onSearch($event.target.value)"
                    @focus="searchOpen=true"
                    @keydown.escape="searchOpen=false" />
                <div x-show="searchOpen" x-cloak
                    class="absolute mt-1 w-full bg-white border rounded-lg shadow-lg max-h-72 overflow-auto z-50">
                    <template x-for="r in results" :key="r.id">
                        <button class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 flex items-center gap-2"
                            @click="selectResult(r)">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-white text-xs font-bold"
                                :style="`background:${genderColor(r.gender)}`">
                                <span x-text="genderLetter(r.gender)"></span>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-semibold truncate">
                                    <span x-text="r.display_name"></span>
                                    <span class="text-slate-500 font-normal" x-show="r.member_number" x-text="'(#'+r.member_number+')'"></span>
                                </span>
                                <span class="block text-[11px] text-slate-500 truncate" x-show="r.father_name">
                                    बुबा: <span x-text="r.father_name"></span>
                                </span>
                            </span>
                            <span class="ml-auto text-slate-500 text-xs" x-text="r.pusta ? ('पु.'+r.pusta) : ''"></span>
                        </button>
                    </template>
                    <div x-show="results.length===0" class="px-3 py-2 text-sm text-slate-500">No results</div>
                </div>
            </div>
        </div>

        {{-- ── ADMIN EXPORT BAR ── --}}
        <div x-show="canExport && exportOpen" x-cloak
            class="px-4 py-3 bg-slate-900 text-white border-b border-slate-800 flex flex-wrap items-center gap-3">
            <div class="font-bold text-sm flex items-center gap-2">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-blue-600">
                    ↓
                </span>
                Tree Download
            </div>
            <div class="text-xs text-slate-300">
                Uses current root, selected level, photos, spouses, and compact tree layout.
            </div>
            <div class="ml-auto flex flex-wrap items-center gap-2">
                <select x-model="exportPaper" class="rounded-lg border border-white/10 bg-white/10 px-2 py-2 text-xs font-bold text-white">
                    <option class="text-slate-900" value="A4">A4</option>
                    <option class="text-slate-900" value="A3">A3</option>
                    <option class="text-slate-900" value="A2">A2</option>
                    <option class="text-slate-900" value="A1">A1</option>
                    <option class="text-slate-900" value="A0">A0</option>
                    <option class="text-slate-900" value="CUSTOM">Custom / Flex</option>
                </select>
                <select x-model="exportOrientation" class="rounded-lg border border-white/10 bg-white/10 px-2 py-2 text-xs font-bold text-white">
                    <option class="text-slate-900" value="landscape">Landscape</option>
                    <option class="text-slate-900" value="portrait">Portrait</option>
                </select>
                <select x-model.number="exportScale" class="rounded-lg border border-white/10 bg-white/10 px-2 py-2 text-xs font-bold text-white" title="PNG quality">
                    <option class="text-slate-900" value="2">PNG 2x</option>
                    <option class="text-slate-900" value="4">PNG 4x</option>
                    <option class="text-slate-900" value="6">PNG 6x</option>
                    <option class="text-slate-900" value="8">PNG 8x</option>
                </select>
                <template x-if="exportPaper === 'CUSTOM'">
                    <div class="flex items-center gap-1 text-xs">
                        <input type="number" x-model.number="customWidthMm" min="200" max="10000"
                            class="w-20 rounded-lg border border-white/10 bg-white/10 px-2 py-2 text-xs font-bold text-white"
                            placeholder="Width">
                        <span class="text-slate-400">×</span>
                        <input type="number" x-model.number="customHeightMm" min="200" max="10000"
                            class="w-20 rounded-lg border border-white/10 bg-white/10 px-2 py-2 text-xs font-bold text-white"
                            placeholder="Height">
                        <span class="text-slate-300">mm</span>
                    </div>
                </template>
                <button type="button" @click="downloadTreeSvg()" :disabled="exportBusy"
                    class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 border border-white/10 text-xs font-bold">
                    Download SVG
                </button>
                <button type="button" @click="downloadTreePng()" :disabled="exportBusy"
                    class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/15 border border-white/10 text-xs font-bold">
                    Download PNG
                </button>
                <button type="button" @click="printTreePdf()" :disabled="exportBusy"
                    class="px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-xs font-bold">
                    Print / Save PDF
                </button>
                <span x-show="exportBusy" class="text-xs text-blue-200">Preparing...</span>
                <button type="button" @click="exportOpen=false"
                    class="px-3 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-xs font-bold">
                    Hide
                </button>
            </div>
        </div>

        <div x-show="canExport && !exportOpen" x-cloak class="fixed bottom-5 right-5 z-40">
            <button type="button" @click="exportOpen=true"
                class="rounded-full bg-slate-900 text-white shadow-xl px-4 py-3 text-sm font-bold hover:bg-slate-800">
                ↓ Tree Export
            </button>
        </div>

        {{-- ── SVG CANVAS ── --}}
        <div class="relative w-full" style="height: calc(100vh - 64px);">
            <svg x-ref="svg" class="tree-canvas absolute inset-0 w-full h-full"></svg>

            <div class="tree-legend">
                <span><i :style="`background:${COLORS.male}`"></i>छोरा / पुरुष</span>
                <span><i :style="`background:${COLORS.female}`"></i>छोरी</span>
                <span><i :style="`background:${COLORS.buhari}`"></i>बुहारी</span>
                <span><i :style="`background:${COLORS.unknown}`"></i>अज्ञात</span>
            </div>
        </div>

        {{-- ── UNCONNECTED MEMBERS DRAWER ── --}}
        <div x-show="unconnectedOpen" x-cloak class="fixed inset-0 z-40 bg-slate-900/20" @click="unconnectedOpen=false"></div>
        <aside x-show="unconnectedOpen" x-cloak
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            class="fixed right-0 top-0 z-50 h-full w-[380px] max-w-full bg-white shadow-2xl border-l border-slate-200 flex flex-col">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-2">
                <div class="min-w-0">
                    <div class="font-bold text-slate-900">नजोडिएका सदस्यहरू</div>
                    <div class="text-[11px] text-slate-500">मुख्य वंशावली रुखमा नजोडिएका नामहरू</div>
                </div>
                <button type="button" @click="unconnectedOpen=false"
                    class="ml-auto w-8 h-8 rounded-full hover:bg-slate-100 text-slate-500 text-lg leading-none">×</button>
            </div>

            <div class="px-4 pt-3 flex gap-2">
                <button type="button" @click="unconnectedTab='roots'"
                    class="flex-1 rounded-lg px-3 py-2 text-xs font-bold border"
                    :class="unconnectedTab==='roots' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                    छुट्टै परिवार (<span x-text="unconnected.roots.length"></span>)
                </button>
                <button type="button" @click="unconnectedTab='isolated'"
                    class="flex-1 rounded-lg px-3 py-2 text-xs font-bold border"
                    :class="unconnectedTab==='isolated' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                    एक्लो नाम (<span x-text="unconnected.isolated.length"></span>)
                </button>
            </div>
            <p class="px-4 pt-2 text-[11px] leading-snug text-slate-500"
                x-text="unconnectedTab==='roots'
                    ? 'बुबा/आमा नजोडिएका तर छोराछोरी भएका — यिनको परिवार मुख्य रुखमा जोड्न बाँकी छ।'
                    : 'न बुबा/आमा, न छोराछोरी जोडिएका — कुनै नाता नभएका नामहरू।'"></p>

            <div class="px-4 pt-2">
                <input type="text" x-model="unconnectedFilter" placeholder="नाम वा सदस्य नं. खोज्नुहोस्…"
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
            </div>

            <div class="flex-1 overflow-y-auto px-2 py-2">
                <template x-if="unconnectedLoading">
                    <div class="px-3 py-6 text-center text-sm text-slate-400">Loading…</div>
                </template>
                <template x-for="u in filteredUnconnected()" :key="u.id">
                    <div class="flex items-center gap-3 rounded-xl px-2 py-2 hover:bg-slate-50">
                        <span class="flex-none w-9 h-9 rounded-full text-white text-xs font-bold flex items-center justify-center"
                            :style="`background:${genderColor(u.gender)}`" x-text="genderLetter(u.gender)"></span>
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-sm text-slate-900 truncate" x-text="u.display_name"></div>
                            <div class="text-[11px] text-slate-500 truncate">
                                <span x-text="u.member_no || ('#'+u.id)"></span>
                                <span x-show="u.pusta" x-text="' · पु.'+u.pusta"></span>
                                <span x-show="u.spouse_names.length" x-text="' · जीवनसाथी: '+u.spouse_names.join(', ')"></span>
                            </div>
                        </div>
                        <div class="flex-none flex gap-1">
                            <button type="button" @click="unconnectedOpen=false; setRootById(u.id)"
                                class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-white">रुख</button>
                            <a :href="isAdmin ? editUrl(u.id) : memberUrl(u.id)"
                                class="rounded-lg bg-slate-900 px-2 py-1 text-[11px] font-bold text-white hover:bg-slate-700"
                                x-text="isAdmin ? 'जोड्नुहोस्' : 'विवरण'"></a>
                        </div>
                    </div>
                </template>
                <template x-if="!unconnectedLoading && filteredUnconnected().length===0">
                    <div class="px-3 py-6 text-center text-sm text-slate-400">कुनै नाम छैन</div>
                </template>
            </div>
        </aside>

        {{-- ── COMPACT POPUP ── --}}
        <div
            x-show="hoverOpen"
            x-cloak
            class="hover-card"
            :style="`left:${hoverX}px; top:${hoverY}px; width:${isMobile?'230px':'280px'}; max-height:58vh; overflow-y:auto; overflow-x:hidden;`"
            @mouseenter="hoverLock=true"
            @mouseleave="hoverLock=false; closeHoverSoon()">

            <template x-if="hoverLoading">
                <div style="padding:10px 12px; font-size:10px; color:#94a3b8;">Loading…</div>
            </template>

            <template x-if="hoverPerson && !hoverLoading">
                <div style="padding:10px;">

                    {{-- Header --}}
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                        <div style="width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:12px; font-weight:800; flex-shrink:0; overflow:hidden; box-shadow:0 0 0 2px #fff, 0 0 0 4px var(--ring);"
                            :style="`background:${personColor(hoverPerson)}; --ring:${personColor(hoverPerson)}55`">
                            <template x-if="hoverPerson.photo_path">
                                <img :src="assetUrl(hoverPerson.photo_path)" alt="" style="width:100%; height:100%; object-fit:cover;">
                            </template>
                            <span x-show="!hoverPerson.photo_path" x-text="genderLetter(hoverPerson.gender)"></span>
                        </div>
                        <div style="min-width:0;">
                            <div style="font-size:12.5px; font-weight:800; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:210px;"
                                x-text="hoverPerson.display_name"></div>
                            <div style="font-size:10px; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:210px;"
                                 x-text="[hoverPerson.display_name_np, hoverPerson.display_name_limbu].filter(Boolean).join(' / ')"></div>
                            <div style="display:flex; flex-wrap:wrap; align-items:center; gap:4px; margin-top:3px;">
                                <span class="pc-member" title="सदस्य नम्बर">सदस्य नं. <span x-text="hoverPerson.member_no || ('#'+hoverPerson.id)"></span></span>
                                <span class="pc-tag pc-tag-slate" x-show="hoverPerson.pusta" x-text="'पु.'+hoverPerson.pusta"></span>
                                <span class="pc-tag" x-show="hoverPerson.is_buhari" style="background:#fef3c7; color:#b45309;">बुहारी</span>
                                <span class="pc-tag" x-show="hoverPerson.birth && !hoverPerson.is_buhari"
                                    :class="childTagClass(hoverPerson.gender)" x-text="hoverPerson.birth?.label"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:4px; margin-bottom:8px;">
                        <button style="border:1px solid #e2e8f0; border-radius:6px; padding:3px 0; font-size:10px; font-weight:600; color:#374151; background:#fff; cursor:pointer;"
                            onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'"
                            @click="setRootById(hoverPerson.id)">Root</button>
                        <button style="border:none; border-radius:6px; padding:3px 0; font-size:10px; font-weight:600; color:#fff; background:#1e293b; cursor:pointer;"
                            onmouseover="this.style.background='#334155'" onmouseout="this.style.background='#1e293b'"
                            @click="openPerson(hoverPerson.id)">More</button>
                    </div>

                    {{-- Family --}}
                    <div class="pc-section">
                        <div class="pc-label">परिवार / Family</div>
                        <template x-if="!hoverPerson.father && !hoverPerson.mother && !hoverPerson.grandfather">
                            <span class="pc-empty">—</span>
                        </template>
                        <template x-for="pp in familyRows(hoverPerson)" :key="pp.key">
                            <button class="pc-row" @click="setRootById(pp.person.id)">
                                <span class="pc-gender" :style="`background:${genderColor(pp.person.gender)}`" x-text="genderLetter(pp.person.gender)"></span>
                                <span class="pc-name" x-text="compactName(pp.person)"></span>
                            </button>
                        </template>
                    </div>

                    {{-- Spouses --}}
                    <div class="pc-section">
                        <div class="pc-label">Wife / Husband</div>
                        <template x-if="(hoverPerson.spouses||[]).length===0">
                            <span class="pc-empty">—</span>
                        </template>
                        <template x-for="ss in (hoverPerson.spouses||[])" :key="ss.id">
                            <button class="pc-row" @click="setRootById(ss.id)">
                                <span class="pc-gender" :style="`background:${genderColor(ss.gender)}`" x-text="genderLetter(ss.gender)"></span>
                                <span class="pc-name" x-text="ss.display_name"></span>
                                <span class="pc-sub" style="margin-left:auto;" x-text="ss.member_no || ''"></span>
                            </button>
                        </template>
                    </div>

                    {{-- Children: sons and daughters in separate columns --}}
                    <div class="pc-section">
                        <div class="pc-label">छोराछोरी / Children</div>
                        <template x-if="(hoverPerson.children||[]).length===0">
                            <span class="pc-empty">—</span>
                        </template>
                        <div class="pc-cols" x-show="(hoverPerson.children||[]).length">
                            <template x-for="col in childColumns(hoverPerson)" :key="col.key">
                                <div style="min-width:0;">
                                    <div class="pc-col-title" :class="col.className" x-text="`${col.title} (${col.items.length})`"></div>
                                    <template x-if="col.items.length===0">
                                        <span class="pc-empty">—</span>
                                    </template>
                                    <template x-for="cc in col.items" :key="cc.id">
                                        <button class="pc-row" style="align-items:flex-start;" @click="setRootById(cc.id)"
                                            :title="[cc.birth?.label, cc.member_no].filter(Boolean).join(' · ')">
                                            <span class="pc-num" :style="`background:${genderColor(cc.gender)}`" x-text="cc.birth ? npDigits(cc.birth.rank) : '•'"></span>
                                            <span style="min-width:0;">
                                                <span class="pc-name" style="display:block;" x-text="cc.display_name"></span>
                                                <span class="pc-sub" x-text="[cc.birth?.word, cc.member_no].filter(Boolean).join(' · ')"></span>
                                            </span>
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </template>
        </div>

    </div><!-- /x-data -->

    <div id="treePrintExport" class="tree-print-export hidden"></div>

    <script src="https://cdn.jsdelivr.net/npm/d3@7"></script>
    <script>
    function treeUI(initialRootId, canExport = false, exportMode = false) {
        return {

            /* ── helpers ── */
            NE2EN: { '०':'0','१':'1','२':'2','३':'3','४':'4','५':'5','६':'6','७':'7','८':'8','९':'9' },
            toENdigits(s){ return String(s||'').replace(/[०-९]/g, d => this.NE2EN[d]||d); },

            COLORS: { male:'#2563eb', female:'#ec4899', buhari:'#f59e0b', unknown:'#7c3aed' },
            genderColor(g){ return this.COLORS[g==='male'||g==='female' ? g : 'unknown']; },
            personColor(p){ return p?.is_buhari ? this.COLORS.buhari : this.genderColor(p?.gender); },
            genderLetter(g){ return g==='male'?'M': g==='female'?'F':'?'; },
            npDigits(n){ return String(n ?? '').replace(/[0-9]/g, d => '०१२३४५६७८९'[d]); },
            memberUrl(id){ return @json(url('/member')) + '/' + id; },
            editUrl(id){ return @json(url('/admin/persons')) + '/' + id + '/edit'; },
            childColumns(p){
                const kids = p?.children || [];
                const cols = [
                    { key:'sons', title:'छोरा', className:'pc-tag-blue', items: kids.filter(c => c.gender === 'male') },
                    { key:'daughters', title:'छोरी', className:'pc-tag-pink', items: kids.filter(c => c.gender === 'female') },
                ];
                const others = kids.filter(c => c.gender !== 'male' && c.gender !== 'female');
                if (others.length) cols.push({ key:'others', title:'सन्तान', className:'pc-tag-slate', items: others });
                return cols;
            },
            assetUrl(path){ return path ? @json(asset('')) + String(path).replace(/^\/+/, '') : ''; },
            parentLabel(p){ return `${p.gender === 'male' ? 'Father' : p.gender === 'female' ? 'Mother' : 'Parent'}: ${p.display_name}`; },
            spouseLabel(p){ return `${p.gender === 'male' ? 'Husband' : p.gender === 'female' ? 'Wife' : 'Spouse'}: ${p.display_name}`; },
            compactName(p){
                if (!p) return '—';
                const extra = [p.display_name_np, p.display_name_limbu].filter(Boolean).join(' / ');
                return extra ? `${p.display_name} (${extra})` : p.display_name;
            },
            familyRows(p){
                const rows = [];
                if (p?.grandfather) rows.push({ key:'grandfather', label:'बाजे', className:'pc-tag-amber', person:p.grandfather });
                if (p?.father) rows.push({ key:'father', label:'बुबा', className:'pc-tag-blue', person:p.father });
                if (p?.mother) rows.push({ key:'mother', label:'आमा', className:'pc-tag-pink', person:p.mother });
                return rows;
            },
            childRelationLabel(g){
                return g === 'male' ? 'छोरा' : (g === 'female' ? 'छोरी' : 'सन्तान');
            },
            childTagClass(g){
                return g === 'male' ? 'pc-tag-blue' : (g === 'female' ? 'pc-tag-pink' : 'pc-tag-slate');
            },

            /* ── state ── */
            isMobile: window.matchMedia('(max-width:640px)').matches || window.matchMedia('(pointer:coarse)').matches,

            rootId:       initialRootId || 0,
            depth:        5,
            levelText:    0,
            currentPusta: null,
            canExport:    !!canExport,
            isAdmin:      !!canExport,

            // unconnected members drawer
            unconnectedOpen:    false,
            unconnectedLoading: false,
            unconnectedTab:     'roots',
            unconnectedFilter:  '',
            unconnectedCount:   null,
            unconnected:        { roots: [], isolated: [] },
            exportOpen:   !!(canExport && exportMode),
            exportBusy:   false,
            exportPaper:  'A4',
            exportOrientation: 'landscape',
            exportScale: 6,
            customWidthMm: 1500,
            customHeightMm: 900,
            treeRootData: null,

            // search
            results:    [],
            searchOpen: false,
            _searchTimer: null,

            // hover / popup
            hoverOpen:    false,
            hoverPerson:  null,
            hoverLoading: false,
            hoverLock:    false,
            hoverX:       0,      // screen-px from viewport left
            hoverY:       0,      // screen-px from viewport top
            _hoverTimer:  null,
            _hoverDelayTimer: null,
            isPanning: false,
            suppressHoverUntil: 0,

            // the SVG <g class="node"> element currently being shown
            _activeNodeEl: null,

            personCache: {},

            // d3 refs
            svg:  null,
            g:    null,
            zoom: null,

            /* ═══════════════════════════════
               INIT
            ═══════════════════════════════ */
            init() {
                window.addEventListener('resize', () => {
                    this.isMobile = window.matchMedia('(max-width:640px)').matches ||
                                    window.matchMedia('(pointer:coarse)').matches;
                    this.fit();
                    this._placePopup(); // reposition popup on resize
                });
                window.addEventListener('scroll', () => {
                    this._cancelHoverDelay();
                    this.suppressHoverUntil = Date.now() + 900;
                    this._closePopup();
                }, true);

                this.svg = d3.select(this.$refs.svg);
                this.$refs.svg.style.touchAction = 'manipulation';

                /* defs */
                const defs = this.svg.append('defs');

                defs.append('marker')
                    .attr('id','arrow').attr('viewBox','0 -5 10 10')
                    .attr('refX',10).attr('refY',0)
                    .attr('markerWidth',6).attr('markerHeight',6)
                    .attr('orient','auto')
                    .append('path').attr('d','M0,-5L10,0L0,5').attr('fill','#94a3b8');

                const male = defs.append('symbol').attr('id','icon-male').attr('viewBox','0 0 24 24');
                male.append('circle').attr('cx','12').attr('cy','7').attr('r','4').attr('fill','#fff');
                male.append('path').attr('d','M6 22v-5c0-3 3-5 6-5s6 2 6 5v5').attr('fill','#fff');

                const female = defs.append('symbol').attr('id','icon-female').attr('viewBox','0 0 24 24');
                female.append('circle').attr('cx','12').attr('cy','7').attr('r','4').attr('fill','#fff');
                female.append('path').attr('d','M5 11c2-3 5-4 7-4s5 1 7 4v1c0 2-1 4-2 5v5H7v-5c-1-1-2-3-2-5z').attr('fill','#fff');

                const unk = defs.append('symbol').attr('id','icon-unk').attr('viewBox','0 0 24 24');
                unk.append('text').attr('x','12').attr('y','16').attr('text-anchor','middle')
                   .attr('font-size','14').attr('font-weight','800').attr('fill','#fff').text('?');

                this.g = this.svg.append('g');

                /* zoom — fires on every zoom/pan, including smooth transitions */
                this.zoom = d3.zoom()
                    .scaleExtent([0.03, 5])
                    .filter(ev => {
                        // block zoom when pointer is directly on a node circle
                        if (ev?.target?.closest?.('g.node')) return false;
                        return true;
                    })
                    .on('start', () => {
                        this.isPanning = true;
                        this._cancelHoverDelay();
                        this.suppressHoverUntil = Date.now() + 900;
                        this._closePopup();
                    })
                    .on('zoom', (ev) => {
                        this.g.attr('transform', ev.transform);
                        // re-anchor popup to the active node every frame
                        this._placePopup();
                    })
                    .on('end', () => {
                        this.isPanning = false;
                        this.suppressHoverUntil = Date.now() + 600;
                    });

                this.svg.call(this.zoom).on('dblclick.zoom', null);
                this.svg.on('wheel.zoom', null);

                // close popup when clicking empty canvas
                this.svg.on('click', () => {
                    if (!this.hoverLock) this._closePopup();
                });

                this.reload();
                this.loadUnconnected();
            },

            /* ═══════════════════════════════
               UNCONNECTED MEMBERS
            ═══════════════════════════════ */
            async loadUnconnected() {
                this.unconnectedLoading = true;
                try {
                    const r = await fetch(@json(route('tree.unconnected')));
                    const data = await r.json();
                    this.unconnected = { roots: data.roots || [], isolated: data.isolated || [] };
                    this.unconnectedCount = this.unconnected.roots.length + this.unconnected.isolated.length;
                } finally {
                    this.unconnectedLoading = false;
                }
            },

            toggleUnconnected() {
                this.unconnectedOpen = !this.unconnectedOpen;
                if (this.unconnectedOpen) this._closePopup();
            },

            filteredUnconnected() {
                const list = this.unconnected[this.unconnectedTab] || [];
                const q = this.unconnectedFilter.trim().toLowerCase();
                if (!q) return list;
                return list.filter(u => [u.display_name, u.display_name_np, u.member_no, u.id]
                    .some(v => String(v || '').toLowerCase().includes(q)));
            },

            /* ═══════════════════════════════
               DATA / DRAW
            ═══════════════════════════════ */
            async reload() {
                const url = new URL(@json(route('tree.json')), window.location.origin);
                url.searchParams.set('root_id', this.rootId);
                url.searchParams.set('depth',   this.depth);
                const res  = await fetch(url);
                const data = await res.json();
                this.draw(data);
                if (data?.pusta) this.currentPusta = String(data.pusta);
            },

            draw(data) {
                this.treeRootData = data || null;
                this.g.selectAll('*').remove();
                this._closePopup();
                if (!data?.id) return;

                const root   = d3.hierarchy(data, d => d.children);
                // couples are ~100px wide, so give them extra room next to their siblings
                const hasSpouse = d => (d.data.spouses?.length || d.data.spouse) ? 1 : 0;
                const layout = d3.tree()
                    .nodeSize([118, 112])
                    .separation((a, b) => (1 + 0.3 * (hasSpouse(a) + hasSpouse(b))) * (a.parent === b.parent ? 1 : 1.15));
                layout(root);

                this.levelText = (d3.max(root.descendants(), d => d.depth) || 0) + 1;

                /* links — start below the parent's labels, end above the child's flag */
                this.g.selectAll('path.link')
                    .data(root.links()).enter()
                    .append('path').attr('class','link')
                    .attr('stroke', d => this.genderColor(d.target.data.gender))
                    .attr('stroke-opacity', 0.35)
                    .attr('marker-end','url(#arrow)')
                    .attr('d', d => {
                        const sx = d.source.x, sy = d.source.y + 50;
                        const tx = d.target.x, ty = d.target.y - 39;
                        const my = (sy + ty) / 2;
                        return `M${sx},${sy} C${sx},${my} ${tx},${my} ${tx},${ty}`;
                    });

                /* nodes */
                const node = this.g.selectAll('g.node')
                    .data(root.descendants()).enter()
                    .append('g').attr('class','node')
                    .attr('transform', d => `translate(${d.x},${d.y})`);

                const renderMini = (g, person) => {
                    const hoverRing = g.append('circle')
                        .attr('r', 22)
                        .attr('fill', 'none')
                        .attr('stroke', this.personColor(person))
                        .attr('stroke-width', 2.5)
                        .attr('opacity', 0);

                    g.style('cursor','pointer')
                     .on('mouseenter', (ev) => {
                         hoverRing.attr('opacity', 0.45);
                         if (!this.isMobile) this._showPopup(person.id, ev.currentTarget);
                     })
                     .on('mouseleave', () => {
                         hoverRing.attr('opacity', 0);
                         this._cancelHoverDelay();
                         if (!this.isMobile) this.closeHoverSoon();
                     })
                     .on('pointerdown', (ev) => {
                         if (!this.isMobile) return;
                         ev.preventDefault(); ev.stopPropagation();
                         this._showPopup(person.id, ev.currentTarget);
                     })
                     .on('click', (ev) => {
                         if (this.isMobile) return;
                         ev.stopPropagation();
                         this.setRootById(person.id);
                     });

                    const photoId = `clip-${String(person.id).replace(/[^a-zA-Z0-9_-]/g, '')}-${Math.random().toString(36).slice(2)}`;
                    g.append('clipPath').attr('id', photoId)
                        .append('circle').attr('r', 16);

                    if (person.photo_path) {
                        g.append('circle').attr('r',18)
                         .attr('fill', '#fff')
                         .attr('stroke', this.personColor(person)).attr('stroke-width',2.5)
                         .attr('filter','drop-shadow(0 1px 2px rgba(15,23,42,.18))');

                        g.append('image')
                         .attr('class', 'tree-node-photo')
                         .attr('href', this.assetUrl(person.photo_path))
                         .attr('x', -16).attr('y', -16).attr('width', 32).attr('height', 32)
                         .attr('clip-path', `url(#${photoId})`)
                         .attr('preserveAspectRatio', 'xMidYMid slice');

                        g.append('circle')
                         .attr('cx', 12).attr('cy', 12).attr('r', 7)
                         .attr('fill', this.personColor(person))
                         .attr('stroke', '#fff').attr('stroke-width', 1.5);

                        g.append('text')
                         .attr('x', 12).attr('y', 15)
                         .attr('text-anchor','middle')
                         .attr('font-size','7px')
                         .attr('font-weight','800')
                         .attr('fill','#fff')
                         .text(this.genderLetter(person.gender));
                    } else {
                        g.append('circle').attr('r',18)
                         .attr('fill', this.personColor(person))
                         .attr('stroke','#fff').attr('stroke-width',3)
                         .attr('filter','drop-shadow(0 1px 2px rgba(15,23,42,.18))');

                        g.append('text')
                         .attr('text-anchor','middle')
                         .attr('y',5)
                         .attr('font-size','12px')
                         .attr('font-weight','900')
                         .attr('fill','#fff')
                         .text(this.genderLetter(person.gender));
                    }

                    g.append('text')
                     .attr('text-anchor','middle').attr('y',32)
                     .attr('font-size','9.5px').attr('font-weight','700').attr('fill','#0f172a')
                     .attr('stroke','#f8fafc').attr('stroke-width',3).attr('paint-order','stroke')
                     .text(() => {
                         const nm = person.name || person.display_name || '';
                         return nm.length > 13 ? nm.slice(0,12)+'…' : nm;
                     });

                    g.append('text')
                     .attr('text-anchor','middle').attr('y',44)
                     .attr('font-size','8px').attr('fill', person.is_buhari ? '#b45309' : '#64748b')
                     .attr('stroke','#f8fafc').attr('stroke-width',3).attr('paint-order','stroke')
                     .text(() => [person.is_buhari ? 'बुहारी' : '', person.pusta ? ('पु.'+person.pusta) : '']
                         .filter(Boolean).join(' · '));

                    // birth-order flag: छोरा १ · जेठो / छोरी २ · माहिली
                    if (person.birth?.label) {
                        const tone = {
                            male:   ['#dbeafe', '#93c5fd', '#1d4ed8'],
                            female: ['#fce7f3', '#f9a8d4', '#be185d'],
                        }[person.gender] || ['#ede9fe', '#c4b5fd', '#6d28d9'];
                        const flag = g.append('g').attr('class','birth-flag').attr('transform','translate(0,-31)');
                        const rect = flag.append('rect')
                            .attr('rx', 7.5).attr('ry', 7.5)
                            .attr('fill', tone[0])
                            .attr('stroke', tone[1]).attr('stroke-width', 0.8);
                        const label = flag.append('text')
                            .attr('text-anchor','middle').attr('y', 3)
                            .attr('font-size','8.5px').attr('font-weight','800')
                            .attr('fill', tone[2])
                            .text(person.birth.label);
                        const w = (label.node().getComputedTextLength?.() || person.birth.label.length * 5.5) + 12;
                        rect.attr('x', -w / 2).attr('y', -7.5).attr('width', w).attr('height', 15);
                    }
                };

                node.filter(d => d.data.type !== 'union').each((d, i, nodes) => {
                    const wrap = d3.select(nodes[i]);
                    const main = {
                        id:           String(d.data.id),
                        name:         d.data.name,
                        display_name: d.data.display_name,
                        gender:       d.data.gender,
                        pusta:        d.data.pusta,
                        photo_path:   d.data.photo_path,
                        member_no:    d.data.member_no,
                        is_buhari:    d.data.is_buhari,
                        birth:        d.data.birth,
                    };

                    const rawSpouses = d.data.spouses ||
                        (d.data.spouse ? [d.data.spouse] : []);

                    const spouses = rawSpouses.map(s => ({
                        id:           String(s.id),
                        name:         s.name,
                        display_name: s.display_name,
                        gender:       s.gender,
                        pusta:        s.pusta,
                        photo_path:   s.photo_path,
                        member_no:    s.member_no,
                        is_buhari:    s.is_buhari,
                    }));

                    if (spouses.length === 0) {
                        renderMini(wrap, main);
                        return;
                    }

                    // Always show main + first spouse at fixed ±32 (same as original)
                    const extraCount = spouses.length - 1;

                    wrap.append('line')
                        .attr('x1', -31).attr('y1', 0).attr('x2', 31).attr('y2', 0)
                        .attr('stroke', '#f59e0b').attr('stroke-opacity', 0.55).attr('stroke-width', 2);
                    wrap.append('circle').attr('r', 3.5)
                        .attr('fill', '#fff').attr('stroke', '#f59e0b').attr('stroke-width', 1.5);

                    renderMini(wrap.append('g').attr('transform', 'translate(-32,0)'), main);
                    renderMini(wrap.append('g').attr('transform', 'translate(32,0)'), spouses[0]);

                    // "+N" overflow badge for additional spouses
                    if (extraCount > 0) {
                        const bx = 74;
                        wrap.append('line')
                            .attr('x1', 33).attr('y1', 0).attr('x2', bx - 14).attr('y2', 0)
                            .attr('stroke', '#94a3b8').attr('stroke-width', 1.2)
                            .attr('stroke-dasharray', '3,2');

                        const badge = wrap.append('g')
                            .attr('transform', `translate(${bx},0)`)
                            .style('cursor', 'pointer');

                        badge.append('circle')
                            .attr('r', 13)
                            .attr('fill', '#f1f5f9')
                            .attr('stroke', '#94a3b8')
                            .attr('stroke-width', 1.5)
                            .attr('stroke-dasharray', '3,2');

                        badge.append('text')
                            .attr('text-anchor', 'middle').attr('y', 4)
                            .attr('font-size', '9px').attr('font-weight', '800')
                            .attr('fill', '#475569')
                            .text(`+${extraCount}`);

                        badge.append('text')
                            .attr('text-anchor', 'middle').attr('y', 26)
                            .attr('font-size', '8px').attr('fill', '#94a3b8')
                            .text('more');

                        badge
                            .on('mouseenter', (ev) => {
                                badge.select('circle').attr('fill', '#e2e8f0');
                                if (!this.isMobile) this._showPopup(main.id, ev.currentTarget);
                            })
                            .on('mouseleave', () => {
                                badge.select('circle').attr('fill', '#f1f5f9');
                                this._cancelHoverDelay();
                                if (!this.isMobile) this.closeHoverSoon();
                            })
                            .on('pointerdown', (ev) => {
                                if (!this.isMobile) return;
                                ev.preventDefault(); ev.stopPropagation();
                                this._showPopup(main.id, ev.currentTarget);
                            })
                            .on('click', (ev) => {
                                if (this.isMobile) return;
                                ev.stopPropagation();
                                this.setRootById(main.id);
                            });
                    }
                });

                this.fit();
            },

            /* ═══════════════════════════════
               ZOOM helpers
            ═══════════════════════════════ */
            fit() {
                const bbox = this.g.node()?.getBBox?.();
                if (!bbox?.width) return;
                const w = this.$refs.svg.clientWidth;
                const h = this.$refs.svg.clientHeight;
                const scale = Math.min(1.0, 0.92 * Math.min(w/bbox.width, h/bbox.height));
                const tx = (w - bbox.width*scale)/2 - bbox.x*scale;
                const ty = 80 - bbox.y*scale;
                this.svg.transition().duration(250)
                    .call(this.zoom.transform, d3.zoomIdentity.translate(tx,ty).scale(scale));
            },
            zoomOut() { this.zoomAtCenter(0.80); },
            zoomIn()  { this.zoomAtCenter(1.25); },
            zoomAtCenter(factor) {
                const w = this.$refs.svg.clientWidth || window.innerWidth;
                const h = this.$refs.svg.clientHeight || window.innerHeight;
                this.svg.transition().duration(150)
                    .call(this.zoom.scaleBy, factor, [w / 2, h / 2]);
            },

            /* ═══════════════════════════════
               POPUP — core positioning logic
               
               Strategy: store the SVG <g.node> element.
               On every zoom event (and on resize) read its getBoundingClientRect()
               which is ALWAYS in current screen pixels — no manual math needed.
            ═══════════════════════════════ */

            /**
             * Place (or reposition) the popup next to _activeNodeEl.
             * Called: on hover, on every zoom frame, on resize.
             */
            _placePopup() {
                if (!this.hoverOpen || !this._activeNodeEl) return;

                const rect    = this._activeNodeEl.getBoundingClientRect();
                const cardW   = this.isMobile ? 230 : 280;
                const cardH   = Math.min(window.innerHeight * 0.58, 420);
                const GAP     = 12; // px gap between node circle and popup edge
                const vw      = window.innerWidth;
                const vh      = window.innerHeight;

                // Prefer: right of node
                let x = rect.right + GAP;
                // If it would overflow the right edge, flip to left
                if (x + cardW > vw - 8) x = rect.left - cardW - GAP;
                // Last-resort: pin to left edge
                if (x < 8) x = 8;

                // Vertically: align top of card with top of node circle
                let y = rect.top - 10;
                if (y + cardH > vh - 8) y = vh - cardH - 8;
                if (y < 8) y = 8;

                this.hoverX = Math.round(x);
                this.hoverY = Math.round(y);
            },

            _cancelHoverDelay() {
                clearTimeout(this._hoverTimer);
                clearTimeout(this._hoverDelayTimer);
            },

            async _showPopup(id, nodeEl) {
                this._cancelHoverDelay();
                if (this.isPanning || Date.now() < this.suppressHoverUntil) return;
                this._activeNodeEl = nodeEl; // remember which node we're anchored to
                this.hoverLoading  = true;
                this.hoverOpen     = true;
                this._placePopup();           // position immediately (before fetch)

                try {
                    this.hoverPerson = await this.fetchPerson(id);
                } finally {
                    this.hoverLoading = false;
                    this._placePopup(); // reposition after card renders (height may change)
                }
            },

            _closePopup() {
                this._cancelHoverDelay();
                this.hoverOpen     = false;
                this.hoverLock     = false;
                this._activeNodeEl = null;
            },

            closeHoverSoon() {
                clearTimeout(this._hoverTimer);
                this._hoverTimer = setTimeout(() => {
                    if (this.hoverLock) return;
                    this._closePopup();
                }, 200);
            },

            /* ═══════════════════════════════
               DATA helpers
            ═══════════════════════════════ */
            async fetchPerson(id) {
                if (this.personCache[id]) return this.personCache[id];
                const url = new URL(@json(url('/person')) + `/${id}`, window.location.origin);
                const r   = await fetch(url);
                const data = await r.json();
                this.personCache[id] = data;
                return data;
            },

            setRootById(id) {
                if (!id) return;
                this._closePopup();
                this.rootId = parseInt(id);
                this.reload();
            },

            openPerson(id) {
                window.location.href = @json(url('/member')) + '/' + id;
            },

            /* ═══════════════════════════════
               SEARCH
            ═══════════════════════════════ */
            onSearch(term) {
                clearTimeout(this._searchTimer);
                this._searchTimer = setTimeout(async () => {
                    const t = (term||'').trim();
                    if (!t) { this.searchOpen=false; this.results=[]; return; }
                    const url = new URL(@json(route('people.search')), window.location.origin);
                    url.searchParams.set('term', t);
                    const r = await fetch(url);
                    const rows = await r.json();
                    this.results    = Array.isArray(rows) ? rows : [];
                    this.searchOpen = true;
                }, 200);
            },

            selectResult(r) {
                this.searchOpen = false;
                if (!r?.id) return;
                this.rootId      = parseInt(r.id);
                this.currentPusta = r.pusta || this.currentPusta;
                this.reload();
            },

            /* ═══════════════════════════════
               PUSTA navigation
            ═══════════════════════════════ */
            async loadByPusta(pustaNumber) {
                const url = new URL(@json(route('people.firstByPusta')), window.location.origin);
                url.searchParams.set('pusta', String(pustaNumber));
                const r      = await fetch(url);
                const person = await r.json();
                if (person?.id) {
                    this.rootId      = parseInt(person.id);
                    this.currentPusta = person.pusta || String(pustaNumber);
                    this.reload();
                }
            },

            pustaUp() {
                const p = parseInt(this.toENdigits(this.currentPusta||'0'), 10);
                if (!p) return;
                this.loadByPusta(p + 1);
            },

            pustaDown() {
                const p = parseInt(this.toENdigits(this.currentPusta||'0'), 10);
                if (!p || p <= 1) return;
                this.loadByPusta(p - 1);
            },

            /* ═══════════════════════════════
               ADMIN EXPORT
            ═══════════════════════════════ */
            paperDimensionsMm() {
                const presets = {
                    A4: [210, 297],
                    A3: [297, 420],
                    A2: [420, 594],
                    A1: [594, 841],
                    A0: [841, 1189],
                };
                if (this.exportPaper === 'CUSTOM') {
                    return {
                        width: Math.max(200, Math.min(10000, parseInt(this.customWidthMm || 1500, 10))),
                        height: Math.max(200, Math.min(10000, parseInt(this.customHeightMm || 900, 10))),
                    };
                }
                const base = presets[this.exportPaper] || presets.A4;
                const portrait = this.exportOrientation === 'portrait';
                return portrait
                    ? { width: Math.min(base[0], base[1]), height: Math.max(base[0], base[1]) }
                    : { width: Math.max(base[0], base[1]), height: Math.min(base[0], base[1]) };
            },

            collectPustas(node, values = []) {
                if (!node) return values;
                const p = parseInt(this.toENdigits(node.pusta || ''), 10);
                if (p) values.push(p);
                (node.children || []).forEach(child => this.collectPustas(child, values));
                return values;
            },

            printHeaderText() {
                const pustas = this.collectPustas(this.treeRootData, []);
                const min = pustas.length ? Math.min(...pustas) : null;
                const max = pustas.length ? Math.max(...pustas) : null;
                if (min && max && min !== max) {
                    return `पुस्ता ${min} देखि ${max} सम्म वंशावली रुख चित्र`;
                }
                if (min) {
                    return `पुस्ता ${min} को वंशावली रुख चित्र`;
                }
                return 'वंशावली रुख चित्र';
            },

            escapeHtmlText(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            },

            async inlineSvgImages(root) {
                const images = Array.from(root.querySelectorAll('image'));
                await Promise.all(images.map(async img => {
                    const href = img.getAttribute('href') || img.getAttributeNS('http://www.w3.org/1999/xlink', 'href');
                    if (!href || href.startsWith('data:')) return;
                    try {
                        const res = await fetch(href, { cache: 'force-cache' });
                        if (!res.ok) return;
                        const blob = await res.blob();
                        const dataUrl = await new Promise((resolve, reject) => {
                            const reader = new FileReader();
                            reader.onload = () => resolve(reader.result);
                            reader.onerror = reject;
                            reader.readAsDataURL(blob);
                        });
                        img.setAttribute('href', dataUrl);
                        img.setAttributeNS('http://www.w3.org/1999/xlink', 'href', dataUrl);
                    } catch (e) {
                        // Keep original href if embedding fails.
                    }
                }));
            },

            async buildExportSvgString() {
                const gNode = this.g?.node?.();
                const svgNode = this.$refs.svg;
                if (!gNode || !svgNode) return '';

                const bbox = gNode.getBBox();
                const pad = 42;
                const width = Math.max(300, Math.ceil(bbox.width + pad * 2));
                const height = Math.max(220, Math.ceil(bbox.height + pad * 2));

                const exportSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                exportSvg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
                exportSvg.setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
                exportSvg.setAttribute('width', String(width));
                exportSvg.setAttribute('height', String(height));
                exportSvg.setAttribute('viewBox', `0 0 ${width} ${height}`);
                exportSvg.setAttribute('role', 'img');
                exportSvg.setAttribute('aria-label', 'Menyanbo family tree');

                const style = document.createElementNS('http://www.w3.org/2000/svg', 'style');
                style.textContent = `
                    .link{fill:none;stroke-width:1.3;marker-end:url(#arrow)}
                    text{font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
                    image{image-rendering:auto}
                `;
                exportSvg.appendChild(style);

                const bg = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                bg.setAttribute('width', '100%');
                bg.setAttribute('height', '100%');
                bg.setAttribute('fill', '#ffffff');
                exportSvg.appendChild(bg);

                const defs = svgNode.querySelector('defs');
                if (defs) exportSvg.appendChild(defs.cloneNode(true));

                const clonedGroup = gNode.cloneNode(true);
                clonedGroup.querySelectorAll('path.link').forEach(path => {
                    path.setAttribute('fill', 'none');
                    path.setAttribute('stroke-width', '1.3');
                    path.setAttribute('marker-end', 'url(#arrow)');
                });
                clonedGroup.querySelectorAll('text').forEach(text => {
                    text.setAttribute('font-family', 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif');
                });
                clonedGroup.setAttribute('transform', `translate(${pad - bbox.x},${pad - bbox.y})`);
                await this.inlineSvgImages(clonedGroup);
                exportSvg.appendChild(clonedGroup);

                return new XMLSerializer().serializeToString(exportSvg);
            },

            exportFileName(ext) {
                const date = new Date().toISOString().slice(0, 10);
                return `menyanbo-tree-root-${this.rootId}-level-${this.depth}-${date}.${ext}`;
            },

            async downloadTreeSvg() {
                this.exportBusy = true;
                try {
                    const svg = await this.buildExportSvgString();
                    if (!svg) return;
                    const blob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = this.exportFileName('svg');
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    URL.revokeObjectURL(url);
                } finally {
                    this.exportBusy = false;
                }
            },

            async downloadTreePng() {
                this.exportBusy = true;
                try {
                    const svg = await this.buildExportSvgString();
                    if (!svg) return;

                    const match = svg.match(/<svg[^>]*width="(\d+)"[^>]*height="(\d+)"/);
                    const width = match ? parseInt(match[1], 10) : 1600;
                    const height = match ? parseInt(match[2], 10) : 900;
                    const scale = Math.max(2, Math.min(8, parseInt(this.exportScale || 6, 10)));
                    const maxPixels = 9000;
                    const finalScale = Math.min(scale, maxPixels / Math.max(width, height));

                    const blob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
                    const url = URL.createObjectURL(blob);
                    const img = new Image();
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        canvas.width = Math.ceil(width * finalScale);
                        canvas.height = Math.ceil(height * finalScale);
                        const ctx = canvas.getContext('2d');
                        ctx.imageSmoothingEnabled = true;
                        ctx.imageSmoothingQuality = 'high';
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                        URL.revokeObjectURL(url);
                        canvas.toBlob((pngBlob) => {
                            this.exportBusy = false;
                            if (!pngBlob) return;
                            const pngUrl = URL.createObjectURL(pngBlob);
                            const a = document.createElement('a');
                            a.href = pngUrl;
                            a.download = this.exportFileName('png');
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            URL.revokeObjectURL(pngUrl);
                        }, 'image/png');
                    };
                    img.onerror = () => {
                        URL.revokeObjectURL(url);
                        this.exportBusy = false;
                    };
                    img.src = url;
                } catch (e) {
                    this.exportBusy = false;
                }
            },

            async printTreePdf() {
                this.exportBusy = true;
                try {
                    const svg = await this.buildExportSvgString();
                    if (!svg) return;
                    const dims = this.paperDimensionsMm();
                    let style = document.getElementById('treePrintPageStyle');
                    if (!style) {
                        style = document.createElement('style');
                        style.id = 'treePrintPageStyle';
                        document.head.appendChild(style);
                    }
                    style.textContent = `@media print{@page{size:${dims.width}mm ${dims.height}mm;margin:0}html,body{width:${dims.width}mm;height:${dims.height}mm;overflow:hidden}.tree-print-export{display:flex!important;flex-direction:column;width:${dims.width}mm!important;height:${dims.height}mm!important;overflow:hidden;break-after:avoid;page-break-after:avoid}.tree-print-header{padding:8mm 10mm 4mm;text-align:center;border-bottom:1px solid #e2e8f0}.tree-print-header h1{font-size:${dims.width > 500 ? 28 : 18}px}.tree-print-svg-wrap{flex:1 1 auto;min-height:0;display:flex;align-items:center;justify-content:center;padding:5mm 7mm 8mm}.tree-print-svg-wrap svg{width:100%!important;height:100%!important;max-width:100%!important;max-height:100%!important;display:block}}`;

                    const holder = document.getElementById('treePrintExport');
                    const title = this.printHeaderText();
                    const rootName = this.treeRootData?.display_name || this.treeRootData?.name || '';
                    holder.innerHTML = `
                        <div class="tree-print-header">
                            <h1>${this.escapeHtmlText(title)}</h1>
                            <p>Root: ${this.escapeHtmlText(rootName)} · Level ${this.depth} · Printed: ${new Date().toISOString().slice(0, 10)}</p>
                        </div>
                        <div class="tree-print-svg-wrap">${svg}</div>
                    `;
                    const printedSvg = holder.querySelector('svg');
                    if (printedSvg) {
                        printedSvg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
                        printedSvg.style.width = '100%';
                        printedSvg.style.height = '100%';
                    }
                    requestAnimationFrame(() => window.print());
                } finally {
                    setTimeout(() => { this.exportBusy = false; }, 300);
                }
            },
        };
    }
    </script>
@endsection
