@extends('shaun_core::install.layouts.master')

@section('template_title')
    {{ __('Step 2 | Environment Settings') }}
@endsection

@section('title')
    <i class="fa fa-magic fa-fw" aria-hidden="true"></i>
    {!! __('Step 2 | Environment Settings') !!}
@endsection

@section('container')
    <div class="tabs tabs-full">

        <input id="tab1" type="radio" name="tabs" class="tab-input" checked />
        <label for="" class="tab-label">
            <i class="mdi mdi-cog mdi-36px" aria-hidden="true"></i>
            <br />
            {{ __('Environment') }}
        </label>

        <input id="tab2" type="radio" name="tabs" class="tab-input" />
        <label for="" class="tab-label">
            <i class="mdi mdi-database mdi-36px" aria-hidden="true"></i>
            <br />
            {{ __('Database') }}
        </label>

        <form method="post" action="{{ route('install.environmentSaveWizard') }}" class="tabs-wrap" id="environment_form">
            <div class="tab" id="tab1content">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="form-group field {{ $errors->has('app_admin_prefix') ? ' has-error ' : '' }}">
                    <label for="app_url" class="label">
                        {{ __('Admin Prefix') }}
                    </label>
                    <input type="text" name="app_admin_prefix" class="input" id="app_admin_prefix" value="admin" placeholder="{{ __('Admin Prefix') }}" />
                    <p class="help">
                        {{__('Please enter the admin prefix name, it will be the Admin URL of your social website. Ex: your site is at www.domain.com, you enter "myadmin" into the "Admin prefix" -> to access the admin you will enter the url www.domain.com/myadmin')}}
                    </p>
                    <div class="error-block text-red-500" style="{{ $errors->has('app_admin_prefix') ? '' : 'display: none' }}">
                        <span id="error_app_admin_prefix">
                            @if ($errors->has('app_admin_prefix'))
                                {{ $errors->first('app_admin_prefix') }}
                            @endif
                        </span>
                    </div>
                </div>

                <div class="text-center">
                    <button class="button blue" onclick="validateAction('environment', 'tab2');return false">
                        {{ __('Setup Database') }}
                        <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="tab" id="tab2content">

                <div class="form-group field {{ $errors->has('database_connection') ? ' has-error ' : '' }}">
                    <label class="label" for="database_connection">
                        {{ __('Database Connection') }}
                    </label>
                    <select name="database_connection" id="database_connection" class="input">
                        <option value="mysql" selected>{{ __('mysql') }}</option>
                        <option value="sqlite">{{ __('sqlite') }}</option>
                        <option value="sqlsrv">{{ __('sqlsrv') }}</option>
                    </select>
                    <div class="error-block text-red-500" style="{{ $errors->has('database_connection') ? '' : 'display: none' }}">
                        <span id="error_database_connection">
                            @if ($errors->has('database_connection'))
                                {{ $errors->first('database_connection') }}
                            @endif
                            </span>
                    </div>
                </div>

                <div class="form-group field {{ $errors->has('database_hostname') ? ' has-error ' : '' }}">
                    <label class="label" for="database_hostname">
                        {{ __('Database Host') }}
                    </label>
                    <input type="text" name="database_hostname" class="input" id="database_hostname" value="127.0.0.1" placeholder="{{ __('Database Host') }}" />
                    <div class="error-block text-red-500" style="{{ $errors->has('database_hostname') ? '' : 'display: none' }}">
                        <span id="error_database_hostname">
                            @if ($errors->has('database_hostname'))
                                {{ $errors->first('database_hostname') }}
                            @endif
                            </span>
                    </div>
                </div>

                <div class="form-group field {{ $errors->has('database_port') ? ' has-error ' : '' }}">
                    <label class="label" for="database_port">
                        {{ __('Database Port') }}
                    </label>
                    <input type="number" name="database_port" class="input" id="database_port" value="3306" placeholder="{{ __('Database Port') }}" />
                    <div class="error-block text-red-500" style="{{ $errors->has('database_port') ? '' : 'display: none' }}">
                        <span id="error_database_port">
                            @if ($errors->has('database_port'))
                                {{ $errors->first('database_port') }}
                            @endif
                            </span>
                    </div>
                </div>

                <div class="form-group field {{ $errors->has('database_name') ? ' has-error ' : '' }}">
                    <label class="label" for="database_name">
                        {{ __('Database Name') }}
                    </label>
                    <input type="text" name="database_name" class="input" id="database_name" value="" placeholder="{{ __('Database Name') }}" />
                    <div class="error-block text-red-500" style="{{ $errors->has('database_name') ? '' : 'display: none' }}">
                        <span id="error_database_name">
                            @if ($errors->has('database_name'))
                                {{ $errors->first('database_name') }}
                            @endif
                            </span>
                    </div>
                </div>

                <div class="form-group field">
                    <label class="label" for="database_prefix">
                        {{ __('Table Prefix') }}
                    </label>
                    <input type="text" name="database_prefix" class="input" id="database_prefix" value="" placeholder="{{ __('Table Prefix') }}" />
                </div>

                <div class="form-group field {{ $errors->has('database_username') ? ' has-error ' : '' }}">
                    <label class="label" for="database_username">
                        {{ __('Database Username') }}
                    </label>
                    <input type="text" name="database_username" class="input" id="database_username" value="" placeholder="{{ __('Database Username') }}" />
                    <div class="error-block text-red-500" style="{{ $errors->has('database_username') ? '' : 'display: none' }}">
                        <span id="error_database_username">
                            @if ($errors->has('database_username'))
                                {{ $errors->first('database_username') }}
                            @endif
                            </span>
                    </div>
                </div>

                <div class="form-group field {{ $errors->has('database_password') ? ' has-error ' : '' }}">
                    <label class="label" for="database_password">
                        {{ __('Database Password') }}
                    </label>
                    <input type="password" name="database_password" class="input" id="database_password" value="" placeholder="{{ __('Database Password') }}" />
                    <div class="error-block text-red-500" style="{{ $errors->has('database_password') ? '' : 'display: none' }}">
                        <span id="error_database_password">
                            @if ($errors->has('database_password'))
                                {{ $errors->first('database_password') }}
                            @endif
                            </span>
                    </div>
                </div>

                <div class="text-center">
                    <button class="button blue" onclick="validateAction('database', '');return false">
                        {{ __('Install') }}
                        <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="form-group margin-top-2">
                <div class="error-block text-red-500" style="display: none">
                    <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                    <span id="fatal_error"></span>
                </div>
            </div>
        </form>

    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        var form = document.querySelector('#environment_form');

        function checkEnvironment(val) {
            var element=document.getElementById('environment_text_input');
            if(val=='other') {
                element.style.display='block';
            } else {
                element.style.display='none';
            }
        }
        function showDatabaseSettings() {
            validate("environment", "tab2");
        }
        function showApplicationSettings() {
            validate("database", 'tab3');
            document.getElementById('tab3').checked = true;
        }
        function validateAction( action, nextTab ) {
            var result = true;
            var data = new FormData(form);
            data.append("action", action);

            const xhttp = new XMLHttpRequest();
            xhttp.open("POST", "{!! route('install.environmentValidate') !!}" );
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
                            if (nextTab !== ''){
                                document.getElementById(nextTab).checked = true;
                            } else {
                                form.submit();
                            }

                        }
                    }
                }
                return result;
            }

            xhttp.send(data);
        }
    </script>
@endsection
