@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{ __('Profile completion settings') }}</h5>
    </div>
    <section class="modal-card-body">
        <div class="card-content">
            <p class="text-muted mb-3">
                {{ __('You can determine the importance of each profile data here by entering all % information into the settings below. Please enter 0 if you do not want a specific section to be included in the profile completion %. Please ensure total is 100%.') }}
            </p>
            <form id="profile_completion_settings_form" method="POST" action="{{ route('admin.dating.profile_completion_settings.store') }}">
                <div id="errors"></div>
                {{ csrf_field() }}
                <div class="form-group mb-4">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', $setting->is_active ?? false) ? 'checked' : '' }}
                        >
                        <label class="form-check-label">
                            {{ __('Enable profile completion checking') }}
                        </label>
                    </div>
                </div>
                @php
                    $fields = [
                        'basic_info' => __('Basic info (%)'),
                        'about' => __('About (%)'),
                        'profile_verification' => __('Profile verification (%)'),
                        'work_education' => __('Work and education (%)'),
                        'more_about' => __('More about profile section (%)'),
                        'interests' => __('Interests (%)'),
                        'social_profiles' => __('Social media profiles (%)'),
                    ];
                @endphp
                @foreach($fields as $name => $label)
                    <div class="form-group mb-3">
                        <label class="control-label">{{ __($label) }}</label>
                        <input
                            type="number"
                            min="0"
                            max="100"
                            class="form-control"
                            name="{{ $name }}"
                            value="{{ old($name, $setting->$name ?? 0) }}"
                        >
                    </div>
                @endforeach
                <div class="form-group mt-4">
                    <button type="button" class="btn-filled-blue" id="profile_completion_settings_submit">{{__('Save Changes')}}</button>
                </div>
            </form>
        </div>
    </section>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/dating.js') }}"></script>
<script>
    adminDating.initSaveProfileSettings();
</script>
@endpush