<header class="modal-card-head">
    <p class="modal-card-title">{{__('Edit Page Info')}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="layout_page_form" method="post" action="{{ route('admin.layout.store')}}" onsubmit="return false;">
            <div id="errors"></div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $page->id }}" />            
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" readonly value="{{$page->getTranslatedAttributeValue('title') }}" type="text">
                <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$page->getTable(),'title',$page->id])}}">{{__('Translate')}}</a>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Meta keywords')}}</label>
                <input class="form-control" name="meta_keywords" value="{{$page->meta_keywords }}" type="text">
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Meta description')}}</label>
                <input class="form-control" name="meta_description" value="{{$page->meta_description }}" type="text">
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="layout_page_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminLayout.initEditPage();
</script>