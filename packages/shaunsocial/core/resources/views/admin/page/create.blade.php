@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Create New Page')}}</h5>
    </div>
    <div class="admin-card-body">
        @include('shaun_core::admin.partial.error')
        <form id="page_form" method="post" action="{{ route('admin.page.store')}}">
            {{ csrf_field() }}
            <input type="hidden" id="id" name="id" value="{{ $page->id }}" />
            @if ($page->id)
                <div class="form-group">
                    <label class="control-label">{{__('Language')}}</label>
                        <select id="language" name="language" class="form-select">
                            @foreach($languages as $item)
                                <option @if ($item->key == $language) selected @endif value="{{$item->key}}">{{$item->name}}</option>
                            @endforeach
                        </select>
                    <p class="help">
                        {{__('Select a language to translate for page title, page content, meta keywords and meta description only.')}}
                    </p>
                </div>
            @endif
            <div class="form-group">
                <label class="control-label">{{__('Title')}}</label>
                <div class="control">
                    <input class="form-control" name="title" value="{{old('title', $layoutPage ? $layoutPage->getTranslatedAttributeValue('title',$language) : '')}}" type="text">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Slug')}}</label>
                <div class="control">
                    <input class="form-control" name="slug" id="slug" value="{{old('slug',$page->slug)}}" type="text">
                    <p class="help" id="slug_link">
                    </p>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Content')}}</label>
                <div class="control">
                    <textarea id="content" name="content">{{old('content',$page->getTranslatedAttributeValue('content',$language))}}</textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Meta keywords')}}</label>
                <div class="control">
                    <input class="form-control" name="meta_keywords" value="{{old('meta_keywords', $layoutPage ? $layoutPage->meta_keywords : '')}}" type="text">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label">{{__('Meta description')}}</label>
                <div class="control">
                    <input class="form-control" name="meta_description" value="{{old('meta_description',$layoutPage ? $layoutPage->meta_description : '')}}" type="text">
                </div>
            </div>

            @include('shaun_core::admin.partial.role.access',['fieldName'=> 'role_access','values'=>old('role_access',$page->getRoleAccess())])

            <div class="form-actions">
                <button type="submit" class="btn-filled-blue">
                    {{__('Submit')}}
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/lib/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('admin/js/page.js') }}"></script>
<script>
    adminPage.initCreate('{{ route('admin.page.create')}}', '{{route('web.page.detail',['slug' =>'123'])}}');
</script>
@endpush