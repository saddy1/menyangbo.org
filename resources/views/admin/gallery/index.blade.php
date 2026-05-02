@extends('admin.layout')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Media Gallery</h2>
        <p class="text-sm text-gray-500 mt-1">Manage all uploaded images. These can be used in notices, events, and pages.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-[#1a5632]/20 text-[#1a5632] rounded-xl p-4 text-sm font-bold flex items-center gap-3">
            <span class="w-6 h-6 bg-[#1a5632] text-white rounded-full flex items-center justify-center shrink-0">✓</span>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-600 rounded-xl p-4 text-sm font-bold">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

  {{-- Drag & Drop Upload Zone --}}
<div class="mb-10 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
    
    <form id="galleryUploadForm" action="{{ route('admin.gallery.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-4 flex items-center justify-between gap-3 flex-wrap">
            <div>
                <label class="block text-sm font-bold text-gray-800">Upload Gallery Photos</label>
                <p class="text-xs text-gray-400 mt-1">Choose multiple JPG, PNG, or WEBP images. Max 5MB each.</p>
            </div>
            <button id="manualUploadBtn" type="submit"
                    class="hidden px-4 py-2 rounded-xl bg-[#1a5632] text-white text-sm font-bold hover:bg-[#124326]">
                Upload photos
            </button>
        </div>

        <div id="galleryDropZone"
             class="relative flex flex-col items-center justify-center w-full min-h-52 border-2 border-dashed border-gray-300 bg-gray-50 hover:bg-gray-100 rounded-2xl transition-colors duration-200">
            
            <div class="flex flex-col items-center justify-center pt-5 pb-6 pointer-events-none">
                <svg id="uploadIcon" class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                
                {{-- Loading Spinner --}}
                <svg id="uploadSpinner" style="display: none;" class="animate-spin w-10 h-10 mb-3 text-[#1a5632]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>

                <p id="uploadTitle" class="mb-2 text-sm text-gray-500"><span class="font-bold text-[#1a5632]">Click to select</span> or drag and drop photos</p>
                <p id="uploadHint" class="text-xs text-gray-400">After selecting, add a title for each photo and confirm upload.</p>
                <p id="uploadingText" class="text-sm font-bold text-[#1a5632]" style="display: none;">Uploading photos...</p>
            </div>
            
            <input id="galleryFileInput" type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
        </div>

        <div id="selectedPhotoList" class="hidden mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"></div>
    </form>
</div>

    {{-- Gallery Grid --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-6 border-b pb-4">Previously Uploaded Media</h3>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @forelse($media as $image)
                <div class="group relative aspect-square rounded-xl overflow-hidden border border-gray-200 bg-gray-50 shadow-sm hover:shadow-md transition-all">
                    {{-- Image --}}
                    <img src="{{ $image->url }}" alt="{{ $image->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    
                    {{-- Hover Overlay --}}
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-center p-2">
                        <p class="text-white text-xs text-center font-bold mb-1 truncate w-full px-2">{{ $image->title ?: $image->name }}</p>
                        <p class="text-white/60 text-[10px] text-center mb-2 truncate w-full px-2">{{ $image->name }}</p>
                        <p class="text-white/70 text-[10px] mb-3">{{ number_format($image->size / 1024, 1) }} KB</p>
                        
                        {{-- Delete Button --}}
                        <form action="{{ route('admin.media.destroy', $image->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this image? If it is used in a notice, it will break the image link.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-full shadow-lg transform hover:scale-110 transition-all" title="Delete Image">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center">
                    <div class="text-4xl mb-3 text-gray-300">🖼️</div>
                    <p class="text-gray-500 font-medium">Your gallery is empty.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $media->links() }}
        </div>
    </div>

</div>
<script>
(() => {
    const form = document.getElementById('galleryUploadForm');
    const input = document.getElementById('galleryFileInput');
    const dropZone = document.getElementById('galleryDropZone');
    const uploadIcon = document.getElementById('uploadIcon');
    const uploadSpinner = document.getElementById('uploadSpinner');
    const uploadTitle = document.getElementById('uploadTitle');
    const uploadHint = document.getElementById('uploadHint');
    const uploadingText = document.getElementById('uploadingText');
    const manualUploadBtn = document.getElementById('manualUploadBtn');
    const selectedPhotoList = document.getElementById('selectedPhotoList');

    function setUploading() {
        manualUploadBtn.classList.add('hidden');
        input.style.pointerEvents = 'none';
        dropZone.classList.add('border-[#1a5632]', 'bg-green-50');
        dropZone.classList.remove('border-gray-300', 'bg-gray-50');
        uploadIcon.style.display = 'none';
        uploadSpinner.style.display = '';
        uploadTitle.style.display = 'none';
        uploadHint.style.display = 'none';
        uploadingText.style.display = '';
    }

    function titleFromFilename(filename) {
        return filename.replace(/\.[^.]+$/, '').replace(/[_-]+/g, ' ').trim();
    }

    function renderSelectedFiles() {
        if (!input.files || input.files.length === 0) return;
        const count = input.files.length;
        manualUploadBtn.classList.remove('hidden');
        selectedPhotoList.classList.remove('hidden');
        selectedPhotoList.innerHTML = Array.from(input.files).map((file, index) => {
            const title = titleFromFilename(file.name);
            return `
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                    <div class="flex items-center gap-3">
                        <div class="h-14 w-14 rounded-lg bg-white border border-gray-200 overflow-hidden shrink-0">
                            <img data-preview-index="${index}" alt="" class="h-full w-full object-cover">
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-xs font-semibold text-gray-600">${file.name}</div>
                            <div class="text-[11px] text-gray-400">${(file.size / 1024).toFixed(1)} KB</div>
                        </div>
                    </div>
                    <label class="block text-xs font-bold text-gray-600 mt-3 mb-1">Photo Title</label>
                    <input type="text" name="titles[]" value="${title.replace(/"/g, '&quot;')}"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a5632]/20 focus:border-[#1a5632]"
                           placeholder="What is this photo about?">
                </div>
            `;
        }).join('');

        Array.from(input.files).forEach((file, index) => {
            const img = selectedPhotoList.querySelector(`img[data-preview-index="${index}"]`);
            if (!img) return;
            const reader = new FileReader();
            reader.onload = e => { img.src = e.target.result; };
            reader.readAsDataURL(file);
        });
    }

    input.addEventListener('change', renderSelectedFiles);

    form.addEventListener('submit', event => {
        if (!input.files || input.files.length === 0) return;
        const count = input.files.length;
        if (!confirm(`Upload ${count} selected photo${count === 1 ? '' : 's'} with these titles?`)) {
            event.preventDefault();
            return;
        }
        setUploading();
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, event => {
            event.preventDefault();
            dropZone.classList.add('border-[#1a5632]', 'bg-green-50');
            dropZone.classList.remove('border-gray-300', 'bg-gray-50');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, event => {
            event.preventDefault();
            dropZone.classList.remove('border-[#1a5632]', 'bg-green-50');
            dropZone.classList.add('border-gray-300', 'bg-gray-50');
        });
    });

    dropZone.addEventListener('drop', event => {
        input.files = event.dataTransfer.files;
        renderSelectedFiles();
    });
})();
</script>
@endsection
