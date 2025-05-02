<div class="mt-4">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    {{-- @else
    <div class="alert alert-danger">{{ session('error') }}</div> --}}
@endif

</div>