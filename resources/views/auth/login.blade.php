@extends('auth.layouts.app')
@section('title', __('auth/login.title'))
@section('content')
    <div class="content d-flex justify-content-center align-items-center">
        <div class="card login-form">
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="icon-reading icon-2x text-secondary border-secondary border-3 rounded-pill p-3 mb-3 mt-1"></i>
                    <h5 class="mb-0">{{__('auth/login.title')}}</h5>
                    <span class="d-block text-muted">{{__('auth/login.credentials')}}</span>
                </div>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="form-group form-group-feedback form-group-feedback-left">
                        <input id="email" type="email"
                               class="form-control @error('email') is-invalid @enderror" name="email"
                               value="{{ old('email') }}" required autocomplete="email" autofocus
                               placeholder="{{__('auth/login.email')}}">
                        <div class="form-control-feedback">
                            <i class="icon-user text-muted"></i>
                        </div>
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group form-group-feedback form-group-feedback-left">
                        <input id="password" type="password"
                               class="form-control @error('password') is-invalid @enderror" name="password"
                               required autocomplete="current-password" placeholder="{{__('auth/login.password')}}">
                        <div class="form-control-feedback">
                            <i class="icon-lock2 text-muted"></i>
                        </div>
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" name="remember" class="custom-control-input"
                                   id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember" class="custom-control-label">{{__('auth/login.remember')}}</label>
                        </label>
                        <a href="{{route('password.request')}}"
                           class="ml-auto text-primary">{{__('auth/login.forgot')}}</a>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">{{__('auth/login.sign-in')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
