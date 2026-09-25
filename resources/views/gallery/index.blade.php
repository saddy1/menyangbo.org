@extends('layouts.app')

@section('title', \App\Support\FrontendLocale::text('फोटो ग्यालेरी'))
@section('meta_description', \App\Support\FrontendLocale::text('मेन्याङ्बो कल्याणकारी संघको फोटो ग्यालेरी'))

@section('content')
<h1 class="text-2xl font-bold text-slate-900 mb-6">{{ \App\Support\FrontendLocale::text('फोटो ग्यालेरी') }}</h1>
@php
    $images = $media->map(fn($img) => [
        'src' => $img->url,
        'alt' => $img->title ?: pathinfo($img->name, PATHINFO_FILENAME),
    ])->values();
@endphp

<div x-data="publicGallery({{ \Illuminate\Support\Js::from($images) }})"
     @keydown.right.window="nextImage()"
     @keydown.left.window="prevImage()"
     @keydown.escape.window="closeLightbox()"
     @keydown.plus.window="lightboxOpen && zoomIn()"
     @keydown.equal.window="lightboxOpen && zoomIn()"
     @keydown.minus.window="lightboxOpen && zoomOut()"
     @keydown.digit0.window="lightboxOpen && resetZoom()">

    @if($media->count())
        <section class="columns-2 sm:columns-3 lg:columns-4 gap-3 sm:gap-4">
            @foreach($media as $i => $image)
                <button type="button"
                        class="mb-3 sm:mb-4 inline-block w-full break-inside-avoid overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-lg"
                        @click="openLightbox({{ $i }})">
                    <img src="{{ $image->url }}"
                        alt="{{ $image->title ?: pathinfo($image->name, PATHINFO_FILENAME) }}"
                         loading="lazy"
                         class="w-full object-cover transition duration-500 hover:scale-105">
                </button>
            @endforeach
        </section>

        <div class="mt-8">
            {{ $media->links() }}
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
            <div class="text-4xl mb-3 text-slate-300">🖼️</div>
            <h2 class="font-bold text-slate-700">{{ \App\Support\FrontendLocale::text('No photos yet') }}</h2>
            <p class="text-sm text-slate-400 mt-1">{{ \App\Support\FrontendLocale::text('Admin बाट फोटो upload गरेपछि यहाँ देखिनेछ।') }}</p>
        </div>
    @endif

    <div x-show="lightboxOpen"
         x-transition.opacity
         class="fixed inset-0 z-[9999] bg-slate-950/95 backdrop-blur flex items-center justify-center p-4 overflow-hidden"
         style="display:none;">
        <!-- Close button -->
        <button type="button"
                class="absolute right-4 top-4 z-20 grid h-10 w-10 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20 transition"
                @click="closeLightbox()"
                title="{{ \App\Support\FrontendLocale::text('Close') }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- Zoom controls -->
        <div class="absolute left-4 top-4 z-20 flex flex-col gap-2">
            <button type="button"
                    class="grid h-10 w-10 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20 transition"
                    @click="zoomIn()"
                    :disabled="zoom >= maxZoom"
                    :class="{ 'opacity-50 cursor-not-allowed': zoom >= maxZoom }"
                    title="Zoom in (+)">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
            <button type="button"
                    class="grid h-10 w-10 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20 transition"
                    @click="zoomOut()"
                    :disabled="zoom <= 1"
                    :class="{ 'opacity-50 cursor-not-allowed': zoom <= 1 }"
                    title="Zoom out (-)">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                </svg>
            </button>
            <button type="button"
                    class="grid h-10 w-10 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20 transition"
                    @click="resetZoom()"
                    :disabled="zoom === 1"
                    :class="{ 'opacity-50 cursor-not-allowed': zoom === 1 }"
                    title="Reset zoom">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
        </div>

        <!-- Zoom indicator -->
        <div class="absolute left-4 bottom-4 z-20 rounded-full bg-white/10 px-3 py-1 text-xs text-white/70 backdrop-blur">
            <span x-text="Math.round(zoom * 100)"></span>%
        </div>

        <!-- Image container with zoom -->
        <div class="flex items-center justify-center w-full h-full overflow-hidden"
             @wheel.prevent="handleWheel"
             @mousemove="handlePan"
             @mouseup="endPan"
             @mouseleave="endPan">
            <img :src="images[currentIndex]?.src"
                 :alt="images[currentIndex]?.alt"
                 class="rounded-2xl object-contain shadow-2xl cursor-move"
                 :style="{
                     maxHeight: '86vh',
                     maxWidth: '92vw',
                     transform: `scale(${zoom}) translate(${panOffset.x}px, ${panOffset.y}px)`,
                     cursor: zoom > 1 ? (isPanning ? 'grabbing' : 'grab') : 'zoom-in',
                     transition: isPanning ? 'none' : 'transform 0.2s ease-out'
                 }"
                 @mousedown="startPan"
                 @click.stop>
        </img>

        <!-- Previous button -->
        <button type="button"
                class="absolute left-3 sm:left-6 top-1/2 z-10 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20 transition"
                @click.stop="prevImage()"
                title="Previous image">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        <!-- Next button -->
        <button type="button"
                class="absolute right-3 sm:right-6 top-1/2 z-10 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20 transition"
                @click.stop="nextImage()"
                title="Next image">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

        <!-- Image title -->
        <div class="absolute bottom-14 left-1/2 max-w-[90vw] -translate-x-1/2 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur pointer-events-none">
            <span x-text="images[currentIndex]?.alt"></span>
        </div>

        <!-- Image counter -->
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-white/10 px-3 py-1 text-xs text-white/70">
            <span x-text="currentIndex + 1"></span> / <span x-text="images.length"></span>
        </div>
    </div>
</div>

<script>
function publicGallery(images) {
    return {
        images,
        lightboxOpen: false,
        currentIndex: 0,
        zoom: 1,
        maxZoom: 4,
        zoomStep: 0.2,
        isPanning: false,
        panStart: { x: 0, y: 0 },
        panOffset: { x: 0, y: 0 },

        openLightbox(index) {
            this.currentIndex = index;
            this.lightboxOpen = true;
            this.resetZoom();
            document.body.style.overflow = 'hidden';
        },

        closeLightbox() {
            this.lightboxOpen = false;
            document.body.style.overflow = '';
        },

        nextImage() {
            if (!this.lightboxOpen || this.images.length === 0) return;
            this.currentIndex = (this.currentIndex + 1) % this.images.length;
            this.resetZoom();
        },

        prevImage() {
            if (!this.lightboxOpen || this.images.length === 0) return;
            this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
            this.resetZoom();
        },

        zoomIn() {
            if (this.zoom < this.maxZoom) {
                this.zoom = Math.min(this.zoom + this.zoomStep, this.maxZoom);
            }
        },

        zoomOut() {
            if (this.zoom > 1) {
                this.zoom = Math.max(this.zoom - this.zoomStep, 1);
                if (this.zoom === 1) this.panOffset = { x: 0, y: 0 };
            }
        },

        resetZoom() {
            this.zoom = 1;
            this.panOffset = { x: 0, y: 0 };
        },

        handleWheel(e) {
            if (!this.lightboxOpen) return;
            const delta = e.deltaY > 0 ? -1 : 1;
            if (delta > 0) this.zoomIn();
            else this.zoomOut();
        },

        startPan(e) {
            if (this.zoom <= 1) return;
            this.isPanning = true;
            this.panStart = { x: e.clientX, y: e.clientY };
            e.target.style.cursor = 'grabbing';
        },

        handlePan(e) {
            if (!this.isPanning || this.zoom <= 1) return;
            const dx = e.clientX - this.panStart.x;
            const dy = e.clientY - this.panStart.y;
            this.panOffset.x += dx * 0.5;
            this.panOffset.y += dy * 0.5;
            this.panStart = { x: e.clientX, y: e.clientY };
        },

        endPan(e) {
            if (this.isPanning) {
                this.isPanning = false;
                e.target.style.cursor = 'grab';
            }
        },
    };
}
</script>
@endsection
