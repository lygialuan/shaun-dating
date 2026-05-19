@if ($errors->any())
    @foreach ($errors->all() as $error)
        <div class="admin-message message-error" role="alert">{{ $error }}</div>
    @endforeach
@endif