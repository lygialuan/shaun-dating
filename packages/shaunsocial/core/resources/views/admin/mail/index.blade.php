@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Mail Templates')}}</h5>
    </div>
    <p class="admin-card-help">{{__('Various notification emails are sent to your members as they interact with the community.')}}</p>
    <div class="admin-card-bar">
        <div class="admin-card-search-bar-wrap">
            <span class="material-symbols-outlined notranslate admin-card-search-bar-icon"> search </span>
            <input type="text" id="search" class="admin-card-search-bar-input form-control" placeholder="{{__('Search')}}"/>
        </div>
    </div>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover template_list">
            <thead>
                <th>
                    {{__('Templates')}}
                </th>
                <th>
                    {{__('Action')}}
                </th>
            </thead>
            <tbody>
                @foreach ($templates as $template)
                <tr>
                    <td class="template_name">
                        {{__($template->getKeyNameTranslate())}}
                    </td>
                    <td class="actions-cell">
                        <a href="{{route('admin.mail.template',$template->id)}}">
                            {{__('Edit')}}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@stop

@push('scripts-body')
<script src="{{ asset('admin/js/mail.js') }}"></script>
<script>
    adminMail.initListing();
</script>
@endpush