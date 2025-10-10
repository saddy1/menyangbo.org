@extends('layouts.app')
@section('title','धन्यवाद')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12 bg-gradient-to-br from-blue-50 via-white to-purple-50">
  <div class="max-w-md w-full">
    <!-- Main Card -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden transform transition-all duration-300 hover:scale-105">
      <!-- Decorative Header -->
      <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-2"></div>
      
      <!-- Content -->
      <div class="p-8 md:p-10">
        <!-- Icon Container -->
        <div class="mb-6 relative">
          <div class="w-20 h-20 mx-auto bg-gradient-to-br from-blue-100 to-purple-100 rounded-full flex items-center justify-center animate-bounce">
            <span class="text-5xl">🙏</span>
          </div>
          <!-- Decorative Circles -->
          <div class="absolute top-0 left-1/2 transform -translate-x-1/2 w-24 h-24 bg-blue-200 rounded-full opacity-20 animate-ping"></div>
        </div>
        
        <!-- Thank You Text -->
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3 text-center">
          धन्यवाद!
        </h1>
        
        <!-- Message -->
        <p class="text-gray-600 text-base md:text-lg text-center leading-relaxed mb-8">
          तपाईंको सुझावको लागि धन्यवाद। तपाईंको प्रतिक्रिया हाम्रो लागि अमूल्य छ।
        </p>
        
        <!-- Button -->
        <div class="text-center">
          <a href="{{ url('/') }}" 
             class="inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-blue-300">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            मुख्य पृष्ठ
          </a>
        </div>
      </div>
      
      <!-- Decorative Footer -->
      <div class="h-1 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500"></div>
    </div>
    
    <!-- Additional Info (Optional) -->
    <p class="text-center text-sm text-gray-500 mt-6">
      हामी तपाईंको प्रतिक्रियालाई गम्भीरतापूर्वक लिन्छौं
    </p>
  </div>
</div>

<style>
@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

@keyframes ping {
  75%, 100% {
    transform: translate(-50%, 0) scale(1.5);
    opacity: 0;
  }
}

.animate-bounce {
  animation: bounce 2s ease-in-out infinite;
}

.animate-ping {
  animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style>
@endsection