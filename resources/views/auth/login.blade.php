@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-slate-950 px-4">
        <div class="w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 p-8 shadow-xl">
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Admin Access</p>
                <h1 class="mt-3 font-display text-3xl font-bold text-white">Login</h1>
                <p class="mt-2 text-sm text-slate-400">Sign in with an admin account to view the admin page.</p>
            </div>

            <form class="mt-8 space-y-5" method="POST" action="{{ route('login') }}">
                @csrf

                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="input-field" required autofocus />
                    @error('email')
                        <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="label">Password</label>
                    <input id="password" name="password" type="password" class="input-field" required />
                    @error('password')
                        <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-slate-400">
                        <input name="remember" type="checkbox" class="rounded border-slate-700 text-amber-500 focus:ring-amber-400" {{ old('remember') ? 'checked' : '' }} />
                        Remember me
                    </label>

                    @if (Route::has('password.request'))
                        <a class="font-medium text-amber-400 hover:text-amber-300" href="{{ route('password.request') }}">Forgot password?</a>
                    @endif
                </div>

                <button class="btn-primary w-full" type="submit">
                    Sign in
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                Customer access?
                <a href="{{ route('login') }}" class="font-semibold text-white hover:text-amber-300">Use customer login</a>
            </p>
        </div>
    </div>
@endsection
