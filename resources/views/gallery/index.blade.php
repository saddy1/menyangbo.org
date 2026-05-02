@extends('layouts.app')

@section('title', 'फोटो ग्यालेरी')
@section('meta_description', 'मेन्याङ्बो कल्याणकारी संघको फोटो ग्यालेरी')

@section('content')
@php
    $images = $media->map(fn($img) => [
        'src' => $img->url,
        'alt' => $img->title ?: pathinfo($img->name, PATHINFO_FILENAME),
    ])->values();
@endphp

<div x-data="publicGallery({{ \Illuminate\Support\Js::from($images) }})"
     @keydown.right.window="nextImage()"
     @keydown.left.window="prevImage()"
     @keydown.escape.window="closeLightbox()">

    @if($media->count())
        <section class="columns-2 sm:columns-3 lg:columns-4 gap-3 sm:gap-4">
            @foreach($media as $i => $image)
                <button type="button"
                        class="mb-3 sm:mb-4 inline-block w-full break-inside-avoid overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-lg"
                        @click="openLightbox({{ $i }})">
                    <img src="{{ $image->url }}"
                        alt="{{ $image->name }}"
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
            <h1 class="font-bold text-slate-700">No photos yet</h1>
            <p class="text-sm text-slate-400 mt-1">Admin बाट फोटो upload गरेपछि यहाँ देखिनेछ।</p>
        </div>
    @endif

    <div x-show="lightboxOpen"
         x-transition.opacity
         class="fixed inset-0 z-[9999] bg-slate-950/95 backdrop-blur flex items-center justify-center p-4"
         style="display:none;">
        <button type="button"
                class="absolute right-4 top-4 z-10 grid h-10 w-10 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20"
                @click="closeLightbox()">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <button type="button"
                class="absolute left-3 sm:left-6 top-1/2 z-10 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20"
                @click.stop="prevImage()">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        <img :src="images[currentIndex]?.src"
             :alt="images[currentIndex]?.alt"
             class="max-h-[86vh] max-w-[92vw] rounded-2xl object-contain shadow-2xl"
             @click.stop>

        <div class="absolute bottom-14 left-1/2 max-w-[90vw] -translate-x-1/2 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur">
            <span x-text="images[currentIndex]?.alt"></span>
        </div>

        <button type="button"
                class="absolute right-3 sm:right-6 top-1/2 z-10 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20"
                @click.stop="nextImage()">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

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
        openLightbox(index) {
            this.currentIndex = index;
            this.lightboxOpen = true;
            document.body.style.overflow = 'hidden';
        },
        closeLightbox() {
            this.lightboxOpen = false;
            document.body.style.overflow = '';
        },
        nextImage() {
            if (!this.lightboxOpen || this.images.length === 0) return;
            this.currentIndex = (this.currentIndex + 1) % this.images.length;
        },
        prevImage() {
            if (!this.lightboxOpen || this.images.length === 0) return;
            this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        },
    };
}
</script>
@endsection
