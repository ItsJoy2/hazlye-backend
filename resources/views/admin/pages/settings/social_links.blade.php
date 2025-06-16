@extends('admin.layouts.app')

@section('content')
<div class="container">

    {{-- <h2>Activation Settings</h2> --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <form action="{{ route('admin.general.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf





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
