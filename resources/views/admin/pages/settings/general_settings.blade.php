@extends('admin.layouts.app')

@section('content')
<div class="container">

    {{-- <h2>Activation Settings</h2> --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <form action="{{ route('admin.general.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf


        {{-- <div class="mb-3">
            <label for="activation_amount">Activation Amount ($)</label>
            <input type="number" step="0.01" id="activation_amount" name="activation_amount" value="{{ old('activation_amount', $generalSettings->activation_amount) }}" required class="form-control">
        </div>


        <div class="mb-3">
            <label for="bonus_token">Bonus Token</label>
            <input type="number" step="0.01" id="bonus_token" name="bonus_token" value="{{ old('bonus_token', $generalSettings->bonus_token) }}" required class="form-control">
        </div>

        <h2>Withdraw Settings</h2>

        <div class="mb-3">
            <label for="min_withdraw">Minimum Amount ($)</label>
            <input type="number" step="0.01" id="min_withdraw" name="min_withdraw" value="{{ old('min_withdraw', $generalSettings->min_withdraw) }}" required class="form-control">
        </div>

        <div class="mb-3">
            <label for="max_withdraw">Maximum Amount ($)</label>
            <input type="number" step="0.01" id="max_withdraw" name="max_withdraw" value="{{ old('max_withdraw', $generalSettings->max_withdraw) }}" required class="form-control">
        </div> --}}

        <h2>App Settings</h2>

        <div class="mb-3">
            <label for="app_name">App Name</label>
            <input type="text" id="app_name" name="app_name" value="{{ old('app_name', $generalSettings->app_name) }}" required class="form-control">
        </div>


        <div class="mb-3">
            <label for="favicon">Favicon(200x200px)</label>
            <input type="file" id="favicon" name="favicon" class="form-control">
            @if($generalSettings->favicon)
                <img src="{{ asset('public/storage/' . str_replace('public/', '', $generalSettings->favicon)) }}" alt="Current Favicon" style="max-width: 32px; max-height: 32px;">
            @endif
        </div>


        <div class="mb-3">
            <label for="logo">Logo(300x45px)</label>
            <input type="file" id="logo" name="logo" class="form-control">
            @if($generalSettings->logo)
                <img src="{{ asset('public/storage/' . str_replace('public/', '', $generalSettings->logo)) }}" alt="Current Logo" style="max-width: 300px; max-height: 45px;">
            @endif
        </div>


        <h2 class="mb-4">Social Links</h2>

        <div class="form-group">
            <label for="facebook_url">Facebook URL</label>
            <input type="url" class="form-control" id="facebook_url" name="facebook_url" value="{{ old('facebook_url', $generalSettings->facebook_url ?? '') }}" placeholder="https://www.facebook.com/">
        </div>

        <div class="form-group">
            <label for="twitter_url">Twitter URL</label>
            <input type="url" class="form-control" id="twitter_url" name="twitter_url" value="{{ old('twitter_url', $generalSettings->twitter_url ?? '') }}" placeholder="https://twitter.com/">
        </div>

        <div class="form-group">
            <label for="instagram_url">Instagram URL</label>
            <input type="url" class="form-control" id="instagram_url" name="instagram_url" value="{{ old('instagram_url', $generalSettings->instagram_url ?? '') }}" placeholder="https://www.instagram.com/">
        </div>

        <div class="form-group">
            <label for="youtube_url">YouTube URL</label>
            <input type="url" class="form-control" id="youtube_url" name="youtube_url" value="{{ old('youtube_url', $generalSettings->youtube_url ?? '') }}" placeholder="https://www.youtube.com/">
        </div>

        <div class="form-group">
            <label for="linkedin_url">LinkedIn URL</label>
            <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url', $generalSettings->linkedin_url ?? '') }}" placeholder="https://www.linkedin.com/">
        </div>

        <div class="form-group">
            <label for="tiktok_url">TikTok URL</label>
            <input type="url" class="form-control" id="tiktok_url" name="tiktok_url" value="{{ old('tiktok_url', $generalSettings->tiktok_url ?? '') }}" placeholder="https://www.tiktok.com/">
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
