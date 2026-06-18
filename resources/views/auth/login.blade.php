<x-guest-layout>
    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label for="password" class="form-label mb-0">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-muted-link" href="{{ route('password.request') }}">
                        Forgot password?
                    </a>
                @endif
            </div>
            <input id="password" type="password" class="form-control mt-1 @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="mb-4 form-check">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label class="form-check-label text-muted" for="remember_me">Remember me</label>
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary btn-lg">
                Log In
            </button>
        </div>

        <div class="text-center mt-3">
            <span class="text-muted">Don't have an account?</span>
            <a href="{{ route('register') }}" class="text-muted-link fw-semibold ms-1">Register</a>
        </div>
    </form>
</x-guest-layout>
