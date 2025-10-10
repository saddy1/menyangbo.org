@extends('layouts.app')
@section('content')
 <div class="min-h-screen flex flex-col">
        <!-- Header -->
 @error('error')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror


        <div
            class="min-h-screen flex items-center justify-center bg-gradient-to-r from-slate-100 via-blue-100 to-slate-200 p-4">
            <div class="w-full max-w-md bg-white p-6 rounded-2xl shadow-lg border border-slate-200">

                <!-- Heading -->
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-blue-900">Admin Login</h1>
                    <p class="text-sm text-slate-600 mt-1">
                        Use your admin email and password to access the dashboard.
                    </p>
                </div>

                <!-- Login Form -->
                <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium mb-1 text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full rounded-lg border border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 text-sm"
                            placeholder="admin@example.com" />
                        @error('email')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium mb-1 text-slate-700">Password</label>
                        <input type="password" name="password" required
                            class="w-full rounded-lg border border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 text-sm"
                            placeholder="••••••••" />
                        @error('password')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full bg-blue-700 text-white py-2 rounded-lg hover:bg-blue-800 transition shadow-md">
                        Sign In
                    </button>
                </form>
            </div>
        </div>

  

    </div>
@endsection