@extends('layouts.app')
@section('title', $page->meta_title ?: $page->title)
@section('meta_description', $page->meta_description ?: \App\Support\Seo::description($page->content ?: $page->title))

@section('content')
<div class="max-w-3xl mx-auto">
  <div class="mb-6">
    <h1 class="text-3xl font-bold text-slate-800">{{ $page->title }}</h1>
  </div>

  @if($page->content)
    <div class="prose prose-slate max-w-none bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8">
      {!! $page->content !!}
    </div>
  @else
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center text-slate-400">
      {{ \App\Support\FrontendLocale::text('सामग्री उपलब्ध छैन।') }}
    </div>
  @endif
</div>
@endsection
