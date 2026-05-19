@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{ __('Create sub-profile') }}</h5>
    </div>
    <div class="admin-message message-notice admin-message-task p-3 mb-3" role="alert">
        <div class="mb-1">
            {{__('Please set the following commands to run in crontab about every 1 minute')}}:
        </div>
        <ul class="mb-1">
            <li>{{__('Requires command line utility (VPS)')}}: "cd {{base_path()}} && php artisan schedule:run >> /dev/null 2>&1"</li>
            <li>{{__('Requires command line utility (cPanel)')}}: "/usr/local/bin/php {{base_path()}}/artisan schedule:run >/dev/null 2>&1"</li>
        </ul>
    </div>
    <section class="modal-card-body">
        <div class="card-content">
            <form id="create_sub_profile_form" method="POST" action="{{ route('admin.user_page.create_sub_profile.store') }}">
                <div id="errors"></div>
                {{ csrf_field() }}
                <div class="form-group">
                    <label class="control-label">{{__('Number of Users')}}</label>
                    <input class="form-control" name="number_of_users" type="number" max="50">
                </div>
                <div class="form-group">
                    <label class="control-label">{{__('Choose User Role')}}</label>            
                    <select name="expire_role_id" class="form-select">
                        @foreach($roles as $role)
                            <option value="{{$role->id}}">{{$role->name}}</option>
                        @endforeach
                    </select>    
                </div>
                <div class="form-group">
                    <label class="control-label">{{__('Gender')}}</label>            
                    <select name="gender_id" class="form-select">
                        <option value="">{{__('Prefer not to say')}}</option>
                        @foreach($genders as $id => $name)
                            <option value="{{$id}}">{{$name}}</option>
                        @endforeach
                    </select>             
                </div>
                <div class="form-group">
                    <label class="control-label">{{ __('Age random') }}</label>
                    <div class="d-flex gap-2">
                        <input class="form-control" name="from_age" type="number" placeholder="From age" min="18">
                        <input class="form-control" name="to_age" type="number" placeholder="To age" max="80">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label">{{__('Sub-profile of')}}</label>
                    <div class="control">
                        <input type="text" style="display: none;" id="{{ md5('admin.user_page.create_sub_profile.auto_suggest') }}" name="user_id" class="setting_input_text" />
                        @include('shaun_core::admin.partial.utility.autocompplete_user', ['value' => old('id'), 'id' => 'admin.user_page.create_sub_profile.auto_suggest', 'multiple' => false])
                    </div>
                </div>
                <div class="form-group">
                    <div class="d-flex align-items-center mb-1">
                        <label class="control-label mb-0 me-1">{{ __('More About Me') }}</label>
                        <a href="#" class="admin_modal_ajax " data-url="{{route('admin.user_page.create_sub_profile.more_about_me')}}">
                            <span class="material-symbols-outlined notranslate">edit</span>
                        </a>
                    </div>
                    <div class="text-muted" id="about_preview"></div>
                    <input type="hidden" name="about_me" id="selected_about_me">
                </div>
                <div class="form-group">
                    <div class="d-flex align-items-center mb-1">
                        <label class="control-label mb-0 me-1">{{ __('Interest') }}</label>
                        <a href="#" class="admin_modal_ajax" data-url="{{route('admin.user_page.create_sub_profile.interest')}}">
                            <span class="material-symbols-outlined notranslate">edit</span>
                        </a>
                    </div>
                    <div id="interest_preview"></div>
                    <input type="hidden" name="interests" id="selected_interests">
                </div>
                <div class="form-group">
                    <label class="mb-2">{{__('Country')}}</label>
                    <select name="country_id" id="country" class="form-select">
                        <option value="">{{__('Select')}}</option>
                        @foreach($countries as $id => $country)
                            <option value="{{$country->id}}">{{$country->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" id="state_select" style="display:none">
                    <label class="mb-2">{{__('State')}}</label>
                    <select name="state_id" id="state" class="form-select">
                        <option value="">{{__('Select')}}</option>
                    </select>
                </div>
                <div class="form-group" id="city_select" style="display:none">
                    <label class="mb-2">{{__('City')}}</label>
                    <select name="city_id" id="city" class="form-select">
                        <option value="">{{__('Select')}}</option>
                    </select>
                </div>
                <span>
                    {{ __("Currently, system only have :maleTotal male profile pictures (:maleUsed used, :maleRemain remaining) and :femaleTotal female profile pictures (:femaleUsed used, :femaleRemain remaining). The rest will only have default profile picture.", [
                        'maleTotal'   => $maleTotal,
                        'femaleTotal' => $femaleTotal,
                        'maleUsed'    => $maleUsed,
                        'femaleUsed'  => $femaleUsed,
                        'maleRemain'  => $maleTotal - $maleUsed,
                        'femaleRemain'=> $femaleTotal - $femaleUsed,
                    ]) }}
                </span>
                <div class="form-group mt-4">
                    <button type="button" class="btn-filled-blue" id="create_sub_profile_submit">{{__('Save Changes')}}</button>
                    <a class="btn-filled-blue admin_modal_ajax" href="javascript:void(0)" data-url="{{route('admin.user_page.create_sub_profile.upload_photos')}}">
                        {{__('Upload More Fake Photos')}}
                    </a>
                </div>
            </form>
        </div>
    </section>
</div>
@stop

@push('scripts-body')
<script src="{{asset('admin/js/lib/jquery-autocomplete/autocomplete.min.js')}}"></script>
<link rel="stylesheet" href="{{asset('admin/js/lib/jquery-autocomplete/autocomplete.css')}}">
<script src="{{ asset('admin/js/user_page.js') }}"></script>
<script src="{{ asset('admin/js/input_upload.js') }}"></script>
<script>
    adminUserPage.initCreateSubProfile();
    adminUserPage.initHandleLocation("{{ route('admin.user_page.create_sub_profile.state') }}", "{{ route('admin.user_page.create_sub_profile.city') }}");
    adminInputUpload.initMultiUpload('#uploadMulti');
    adminTranslate.add({
        'error_limit_photos' : '{{addslashes(__('You can upload up to 10 photos at a time.'))}}',
    });
</script>
@endpush