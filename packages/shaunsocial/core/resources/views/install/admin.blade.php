@extends('shaun_core::install.layouts.master')

@section('template_title')
    {{ __('Step 4 | Create Admin Account') }}
@endsection

@section('title')
    {{ __('Create Admin Account') }}
@endsection

@section('container')
    <form method="post" action="{{ route('install.adminSave') }}" class="tabs-wrap" id="create_admin_form">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="form-group field {{ $errors->has('name') ? ' has-error ' : '' }}">
            <label for="name" class="label">
                {{ __('Name') }}
            </label>
            <input type="text" name="name" class="input" id="name" value="" />
            <div class="error-block text-red-500" style="{{ $errors->has('app_name') ? '' : 'display: none' }}">
                <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                <span id="error_name">
                        @if ($errors->has('name'))
                        {{ $errors->first('name') }}
                    @endif
                        </span>
            </div>
        </div>

        <div class="form-group field {{ $errors->has('email') ? ' has-error ' : '' }}">
            <label for="email" class="label">
                {{ __('Email') }}
            </label>
            <input type="text" name="email" class="input" id="email" value="" />
            <div class="error-block text-red-500" style="{{ $errors->has('app_name') ? '' : 'display: none' }}">
                <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                <span id="error_email">
                        @if ($errors->has('email'))
                        {{ $errors->first('email') }}
                    @endif
                        </span>
            </div>
        </div>

        <div class="form-group field {{ $errors->has('password') ? ' has-error ' : '' }}">
            <label for="password" class="label">
                {{ __('Password') }}
            </label>
            <input type="password" name="password" class="input" id="password" value="" />
            <div class="error-block text-red-500" style="{{ $errors->has('app_name') ? '' : 'display: none' }}">
                <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                <span id="error_password">
                        @if ($errors->has('password'))
                        {{ $errors->first('password') }}
                    @endif
                        </span>
            </div>
        </div>

        <div class="form-group field {{ $errors->has('confirm_password') ? ' has-error ' : '' }}">
            <label for="confirm_password" class="label">
                {{ __('Confirm Password') }}
            </label>
            <input type="password" name="confirm_password" class="input" id="confirm_password" value="" />
            <div class="error-block text-red-500" style="{{ $errors->has('app_name') ? '' : 'display: none' }}">
                <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                <span id="error_confirm_password">
                        @if ($errors->has('confirm_password'))
                        {{ $errors->first('confirm_password') }}
                    @endif
                        </span>
            </div>
        </div>

        <div class="text-center">
            <button class="button blue" onclick="validateAdminForm(); return false">
                {{ __('Save') }}
            </button>
        </div>
    </form>
@endsection

@section('scripts')
    <script type="text/javascript">
        var form = document.querySelector('#create_admin_form');
        function validateAdminForm( ) {
            var result = true;
            var data = new FormData(form);

            const xhttp = new XMLHttpRequest();
            xhttp.open("POST", "{!! route('install.adminValidate') !!}" );
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
