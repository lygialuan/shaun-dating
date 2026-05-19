@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">    
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Themes')}}</h5>
        <a class="btn-filled-blue admin_modal_ajax dark" data-url="{{route('admin.theme.create')}}" href="javascript:void(0);">{{__('Create new')}}</a>
    </div>    
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('Name')}}
                </th>
                <th>
                    {{__('Active')}}
                </th>
                <th width="150">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody>
                @foreach ($themes as $theme)
                <tr>
                    <td>
                        {{$theme->name}}
                    </td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="is_active form-check-input" data-id="{{$theme->id}}" type="checkbox" {{ $theme->is_active ? 'checked' : ''}} value="1">
                        </div>
                    </td>
                    <td class="actions-cell">
                        @if (! $theme->isDefault())
                            <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.theme.create',$theme->id)}}">
                                {{__('Edit')}}
                            </a>
                            <a href="{{route('admin.theme.setting',['type'=>'light', 'id' => $theme->id])}}">
                                {{__('Lightmode')}}
                            </a>
                            <a href="{{route('admin.theme.setting',['type'=>'dark', 'id' => $theme->id])}}">
                                {{__('Darkmode')}}
                            </a>
                            <a class="button-red admin_modal_confirm_delete" data-url="{{route('admin.theme.delete',$theme->id)}}" href="javascript:void(0);">
                                {{__('Delete')}}
                            </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/theme.js') }}"></script>
<script>
    adminTheme.initListing('{{route('admin.theme.store_active')}}')
</script>

@endpush