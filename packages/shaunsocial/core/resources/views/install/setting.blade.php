@extends('shaun_core::install.layouts.master')

@section('template_title')
    {{ __('Step 3 | Site Settings') }}
@endsection

@section('title')
    {{ __('Site Setting') }}
@endsection

@section('container')
    <form method="post" action="{{ route('install.settingSave') }}" class="tabs-wrap" id="setting_form">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        @foreach($settings as $setting)
            <?php $key =  str_replace('.', '_', $setting->key);
            if($setting->key == 'site.url' && empty($setting->value)){
                $setting->value = url('/');
            }
            ?>
            <div style="@if ($setting->key == 'site.url') display:none; @endif" class="form-group field {{ $errors->has($key) ? ' has-error ' : '' }}">
                <label for="name" class="label">
                    {{ __($setting->name) }}
                </label>
                @include('shaun_core::admin.partial.setting.field')
                <div class="error-block text-red-500" style="{{ $errors->has('app_name') ? '' : 'display: none' }}">
                    <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                    <span id="error_{{ $key }}">
                        @if ($errors->has($key))
                            {{ $errors->first($key) }}
                        @endif
                        </span>
                </div>
            </div>
        @endforeach

        <div class="text-center">
            <button class="button blue" onclick="validateSettingForm(); return false">
                {{ __('Next') }}
            </button>
        </div>
    </form>
@endsection

@section('scripts')
    <script type="text/javascript">
        var form = document.querySelector('#setting_form');
        function validateSettingForm( ) {
            var result = true;
            var data = new FormData(form);

            const xhttp = new XMLHttpRequest();
            xhttp.open("POST", "{!! route('install.settingValidate') !!}" );
            xhttp.responseType = 'json';
            xhttp.onload = function () {
                if (xhttp.readyState === xhttp.DONE) {
                    if (xhttp.status === 200) {
                        var jsonResult = xhttp.response;
                        var elems = document.querySelectorAll('.form-group');
                        var elems1 = document.querySelectorAll('.error-block');

                        [].forEach.call(elems, function(el) {
                            el.classList.remove("has-error");
                        });

                        [].forEach.call(elems1, function(el) {
                            el.style.display = "none";
                        });

                        if (jsonResult != null){
                            for (let name in jsonResult) {
                                let element = document.querySelector('#error_' + name);
                                if (element){

                                    if ( Array.isArray( jsonResult[name] ) ){
                                        element.innerHTML = jsonResult[name][0];
                                    } else {
                                        element.innerHTML = jsonResult[name];
                                    }

                                    element.closest('.error-block').style.display = "block";
                                    element.closest('.form-group').classList.add("has-error");
                                } else {
                                    let errorElement = document.querySelector('#fatal_error');
                                    errorElement.innerHTML = jsonResult[name];

                                    errorElement.closest('.error-block').style.display = "block";
                                    errorElement.closest('.form-group').classList.add("has-error");
                                }
                            }
                            result = false;
                        } else {
                            form.submit();
                        }
                    }
                }
                return result;
            }

            xhttp.send(data);
        }
    </script>
@endsection
