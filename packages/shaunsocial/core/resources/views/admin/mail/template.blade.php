@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-grid-block-cont">
    <div class="form-body {{count($languages) > 1 ? 'form-body-group' : 'form-body-single'}}">
        @include('shaun_core::admin.partial.error')
        @foreach ($languages as $language)
            <div class="admin-card">
                <div class="admin-card-top">
                    <h5 class="admin-card-top-title">{{$language->name}}</h5>
                </div>
                <div class="admin-card-body">          
                    <form id="mail_template_form" method="post" action="{{ route('admin.mail.store_template')}}">
                        {{ csrf_field() }}
                        <input type="hidden" name="language" value="{{$language->key}}"/>
                        <input type="hidden" name="id" value="{{$template->id}}"/>
                        <div class="form-group">
                            <label class="control-label">{{__('Subject')}}</label>
                            <input name="subject" value="{{old('subject',$template ? $template->getTranslatedAttributeValue('subject',$language->key) : '')}}" type="text" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{__('Available Placeholders')}}</label>
                            <div>{{$template->vars}}</div>                      
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{__('Content')}}</label>
                            <textarea name="content" class="form-control content_textarea">{{old('content',$template ? $template->getTranslatedAttributeValue('content',$language->key) : '')}}</textarea>
                        </div>  
                        <div class="form-actions">
                            <button type="submit" class="btn-filled-blue">{{ __('Submit') }}</button>
                        </div>   
                    </form>
                </div> 
            </div>
        @endforeach
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/lib/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('admin/js/mail.js') }}"></script>
<script>
    adminMail.initCreate('{{ route('admin.mail.index')}}');
</script>
@endpush