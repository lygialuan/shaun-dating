<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>
<section class="modal-card-body">        
    <div class="card-content">
        <form id="create_category_form" method="post" action="{{ route('admin.group.category.store')}}">
            <div id="errors">
            </div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $category->id }}" />
            <div class="form-group">
                <label class="control-label">{{__('Name')}}</label>
                <input class="form-control" name="name" @if ($category->id) readonly @endif value="{{$category->getTranslatedAttributeValue('name') }}" type="text">
                @if ($category->id)
                    <a class="help admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$category->getTable(),'name',$category->id])}}">{{__('Translate')}}</a>
                @else
                    <p class="help">
                        {{__('*You can add translation language after creating.')}}
                    </p>
                @endif
            </div>
            @if (count($categoryParents) && count($category->childs) === 0)
                <div id="parent_content" class="form-group">
                    <label class="control-label">{{__('Parent')}}</label>
                    <select id="parent_id" name="parent_id" class="form-select">
                        <option value="0"></option>
                        @foreach($categoryParents as $categoryParent)
                            <option @if ($categoryParent->id == $category->parent_id) selected @endif value="{{$categoryParent->id}}">{{$categoryParent->getTranslatedAttributeValue('name')}}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="form-group">
                <label class="control-label">{{__('Active')}}</label>        
                <div class="form-check form-switch">
                    <input name="is_active" class="form-check-input" type="checkbox" {{ $category->is_active ? 'checked' : ''}} value="1">
                </div>
            </div>
        </form>
    </div>
</section>
<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="create_category_submit">{{__('Submit')}}</button>
    <button class="btn-filled-white modal-close">{{__('Cancel')}}</button>
</footer>
<script>
    adminGroup.initCreateCategory();
</script>
