
<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="role_form" method="post" action="{{ route('admin.role.store')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $role->id }}" />
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" value="{{$role->name}}" type="text">
            </div>
            @if(!in_array($role->id, config('shaun_core.role.id')))
                <div class="form-group">
                    <label class="control-label">{{__('Moderator')}}</label>
                    <div class="form-check form-switch">
                        <input name="is_moderator" class="form-check-input" id="is_moderator" type="checkbox" {{ $role->is_moderator ? 'checked' : ''}} value="1">
                    </div>                     
                </div>
            @endif
            @if (! $role->id)
                <div class="form-group">
                    <label class="control-label">{{__('Inherit')}}</label>
                    <select name="inherit" class="form-select">
                        @foreach($roles as $role)
                            <option value="{{$role->id}}">{{$role->name}}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="role_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminRole.initCreate();
</script>