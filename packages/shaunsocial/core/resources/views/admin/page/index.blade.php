@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Pages')}}</h5>
        <a class="btn-filled-blue" href="{{route('admin.page.create')}}">{{__('Create new')}}</a>
    </div>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('Title')}}
                </th>
                <th>
                    {{__('Slug')}}
                </th>
                <th>
                    {{__('Action')}}
                </th>
            </thead>
            <tbody>
                @foreach ($pages as $page)
                <tr>
                    <td>
                        {{$page->getLayout()->getTranslatedAttributeValue('title')}}
                    </td>
                    <td>
                        {{$page->slug}}
                    </td>
                    <td class="actions-cell" width="150">
                        <a target="_blank" href="{{$page->getHref()}}">
                            {{__('View')}}
                        </a>
                        <a href="{{route('admin.page.create',$page->id)}}">
                            {{__('Edit')}}
                        </a>
                        <a class="button-red admin_modal_confirm_delete" href="javascript:void(0);" data-url="{{route('admin.page.delete',$page->id)}}">
                            {{__('Delete')}}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $pages->links('shaun_core::admin.partial.paginate') }}
    </div>
</div>
@stop