<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        {{-- {{ $generalSettings->app_name ?? 'Larabel' }} --}}
    </title>
    <link rel="stylesheet" href="{{ asset('assets/admin/auth/style.css') }}">
    {{-- <link rel="icon" href="{{ Storage::url($generalSettings->favicon) ?? asset('default_favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ Storage::url($generalSettings->favicon) ?? asset('default_favicon.ico') }}"> --}}
</head>

<body>
    <div class="wrapper">
        <div class="logo">
            @if(isset($generalSettings->favicon))
        <img src="{{ Storage::url($generalSettings->favicon) }}" alt="Favicon"">
    @endif
        </div>
        <div style="text-align: center" class="text-center mt-4 name">
            Admin Login
        </div>
        @if(session('error'))
        <div style="color: red" class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <form class="p-3 mt-3" method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="form-field d-flex align-items-center">
                <span class="far fa-user"></span>
                <input type="email" name="email" id="email" placeholder="Email" required>
            </div>
            <div class="form-field d-flex align-items-center">
                <span class="fas fa-key"></span>
                <input type="password" name="password" id="pwd" placeholder="Password" required>
            </div>
            <button type="submit" class="btn mt-3">Login</button>
        </form>
    </div>
</body>
</html>
