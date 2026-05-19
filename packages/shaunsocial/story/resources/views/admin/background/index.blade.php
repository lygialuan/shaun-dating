@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Story Backgrounds')}}</h5>
        <a class="button admin_modal_ajax btn-filled-blue" data-url="{{route('admin.story.background.create')}}" href="javascript:void(0);">{{__('Create new')}}</a>
    </div>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th width="50">

                </th>
                <th>
                    {{__('Photo')}}
                </th>
                <th width="150">
                    {{__('Status')}}
                </th>
                <th width="150">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody id="background_list">
                @foreach ($backgrounds as $background)
                <tr data-id="{{$background->id}}">
                    <td>
                        <span role="button" class="material-symbols-outlined notranslate admin-menu-table-col-icon"> open_with </span>
                    </td>
                    <td>
                        <img width="50" src="{{$background->getPhotoUrl()}}" />                        
                    </td>
                    <td width="150">
                        <span id="status-{{$background->id}}">
                            {{$background->is_active ? __('Active') : __('Inactive')}}
                        </span>
                    </td>                          
                    <td class="actions-cell" width="150">
                        <a href="javascript:void(0);" data-id="{{$background->id}}" class="active_action" data-status="{{$background->is_active}}">{{$background->is_active ? __('Deactive') : __('Active')}}</a>
                        @if ($background->canDelete())
                            <a class="button-red admin_modal_confirm_delete" href="javascript:void(0);" data-url="{{route('admin.story.background.delete',$background->id)}}">
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
<script src="{{ asset('admin/js/story.js') }}"></script>
<script src="{{ asset('admin/js/lib/Sortable.min.js') }}"></script>
<script>
    adminStory.initBackgroundListing('{{route('admin.story.background.store_active')}}','{{route('admin.story.background.store_order')}}');
</script>
@endpush