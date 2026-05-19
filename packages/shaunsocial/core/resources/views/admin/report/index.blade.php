@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Reports')}}</h5>
    </div>
    <p class="admin-card-help">{{__('This page lists all of the reports your users have sent in regarding inappropriate content, system abuse, spam, and so forth. You can use the search field to look for reports that contain a particular word or phrase. Very old reports are periodically deleted by the system.')}}</p>
    <form method="get">
        <div class="admin-card-bar">
            <select name="category_id" class="form-select">
                <option value="">{{__('All category')}}</option>
                @foreach ($categories as $category)
                    <option @if ($category->id == $categoryId) selected @endif value="{{$category->id}}">{{$category->getTranslatedAttributeValue('name')}}</option>
                @endforeach
            </select>
            <div class="admin-card-search-bar-wrap">
                <span class="material-symbols-outlined notranslate admin-card-search-bar-icon"> search </span>
                <input type="text" value="{{$name}}" name="name" class="admin-card-search-bar-input form-control" placeholder="{{__('Search')}}"/>
            </div>
            <button type="submit" class="btn-filled-blue" href="javascript:void(0);" id="searchUser">{{__('Search')}}</button>
        </div>
    </form>
    <form method="post" id="report_form" action="{{route('admin.report.multi_delete')}}">
        {{ csrf_field() }}
        <div class="admin-card-body table-responsive">
            <table class="admin-table table table-hover">
                <thead>
                    <th width="30">
                        <input class="form-check-input col-check check_all" type="checkbox" value="" id="">
                    </th>
                    <th>
                        {{__('User')}}
                    </th>
                    <th>
                        {{__('Subject')}}
                    </th>
                    <th>
                        {{__('Category')}}
                    </th>
                    <th>
                        {{__('Date')}}
                    </th>
                    <th>
                        {{__('Reason')}}
                    </th>
                    <th>
                        {{__('Action')}}
                    </th>
                </thead>
                <tbody>
                    @foreach ($reports as $report)
                        @if ($report->checkExits())
                            <tr>
                                <td width="30">
                                    <input class="form-check-input col-check check_item" type="checkbox" name="ids[]" value="{{$report->id}}">
                                </td>
                                <td>
                                    @if ($report->user_id)
                                        <a href="{{$report->getUser()->getHref()}}">{{$report->getUser()->getTitle()}}</a>
                                    @else
                                        {{__('Ai')}}
                                    @endif
                                </td>
                                <td>
                                    <a href="{{$report->getSubject()->getAdminHref()}}">{{$report->getSubject()->getTitle()}}</a>
                                </td>
                                <td>
                                    {{$report->getCategory()->getTranslatedAttributeValue('name')}}
                                </td>
                                <td>
                                    {{$report->created_at->setTimezone(auth()->user()->timezone)->diffForHumans()}}
                                </td>
                                <td>
                                    {{$report->reason}}
                                </td>
                                <td class="actions-cell">
                                    <a href="{{$report->getSubject()->getAdminHref()}}">{{__('View content')}}</a>
                                    <a href="javascript:void(0);" class="red admin_modal_confirm_delete" data-content="{{__('Do you want to delete this report?')}}" data-url="{{route('admin.report.delete',$report->id)}}">
                                        {{__('Delete')}}
                                    </a>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            {{ $reports->withQueryString()->links('shaun_core::admin.partial.paginate') }}
            <div class="form-actions">
                <button type="button" id="delete_button" class="btn-filled-red">
                    {{__('Delete')}}
                </button>
            </div>
        </div>
    </form>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/report.js') }}"></script>
<script>
    adminCore.initCheckAll();
    adminReport.initListing()
    adminTranslate.add({
        'confirm_delete_report' : '{{addslashes(__('Are you sure you want to delete these reports?'))}}',
    });
</script>
@endpush