@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Languages')}}</h5>
        <a class="btn-filled-blue admin_modal_ajax dark" data-url="{{route('admin.language.create')}}" href="javascript:void(0);">{{__('Create new')}}</a>
    </div>    
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('Name')}}
                </th>
                <th>
                    {{__('Key')}}
                </th>
                <th>
                    {{__('RTL')}}
                </th>
                <th>
                    {{__('Default')}}
                </th>
                <th>
                    {{__('Active')}}
                </th>
                <th width="150">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody>
                @foreach ($languages as $language)
                <tr>
                    <td>
                        {{$language->getTranslatedAttributeValue('name')}}
                    </td>
                    <td>
                        {{$language->key}}
                    </td>
                    <td>
                        @if ($language->is_rtl)
                            {{__('Yes')}}
                        @else
                            {{__('No')}}
                        @endif
                    </td>
                    <td>                     
                        @if ($language->is_default)
                            {{__('Yes')}}
                        @else
                            {{__('No')}}
                        @endif
                    </td>
                    <td>                     
                        @if ($language->is_active)
                            {{__('Yes')}}
                        @else
                            {{__('No')}}
                        @endif
                    </td>
                    <td class="actions-cell">
                        <a class="admin_modal_ajax" href="javascript:void(0)" data-url="{{route('admin.language.create',$language->id)}}">
                            {{__('Edit')}}
                        </a>
                        @if ($language->canDelete())
                            <a class="button-red admin_modal_confirm_delete" href="javascript:void(0)" data-url="{{route('admin.language.delete',$language->id)}}">
                                {{__('Delete')}}
                            </a>
                        @endif
                        <a href="{{route('admin.language.edit_phrase',$language->id)}}">
                            {{__('Edit phrase')}}
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
<script src="{{ asset('admin/js/translation.js') }}"></script>
<script src="{{ asset('admin/js/language.js') }}"></script>
<script>
    adminLanguage.initListing();
</script>
@endpush