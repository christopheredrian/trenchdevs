@extends('layouts.home')

@section('content')

    <div class="bg-primary mt-5">

        <div id="layoutAuthentication mt-5" style="padding: 120px 0;">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header justify-content-center">
                                        <h3 class="font-weight-light my-4 text-white">{{ __('Login') }}</h3>
                                    </div>
                                    <div class="card-body">
                                        @include('admin.shared.errors')

                                        <form method="POST" action="{{ route('login') }}">

                                            @csrf
                                            <div class="form-group">
                                                <label class="small mb-1" for="email">Email</label>
                                                <input id="email" type="email"
                                                       class="form-control py-4 @error('email') is-invalid @enderror"
                                                       name="email" value="{{ old('email') }}" required
                                                       autocomplete="email" autofocus placeholder="Enter email address">
                                                @error('email')
                                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                                @enderror
                                            </div>


                                            <div class="form-group">

                                                <label class="small mb-1" for="password">Password</label>
                                                <input id="password" type="password" placeholder="Enter password"
                                                       class="form-control @error('password') is-invalid @enderror"
                                                       name="password" required autocomplete="current-password"/>

                                                @error('password')
                                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                        </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-checkbox">

                                                    <input class="custom-control-input" type="checkbox" name="remember"
                                                           id="remember" {{ old('remember') ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="remember">
                                                        {{ __('Remember Me') }}
                                                    </label>
                                                </div>

                                            </div>


                                            <div
                                                class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                                                @if (Route::has('password.request'))
                                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                                        {{ __('Forgot Your Password?') }}
                                                    </a>
                                                @endif
                                                <button type="submit" class="btn btn-primary">
                                                    {{ __('Login') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-center">
                                        <div class="small"><a href="{{ route('register') }}">Want to join our group?
                                                Sign up!</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>

        </div>
    </div>
@endsection
