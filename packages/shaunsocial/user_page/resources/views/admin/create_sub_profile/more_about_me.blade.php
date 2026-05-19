<header class="modal-card-head">
    <p class="modal-card-title">{{$title}}</p>
</header>

<section class="modal-card-body">        
    <div class="card-content">
        <div id="about_selected_preview" class="mb-2 text-sm"></div>
        <div class="modal-body">
            <input type="text" class="form-control mb-3" placeholder="Search">
            <div class="row">
                <div class="col-4 border-end" style="max-height:300px; overflow:auto;">
                    <ul class="list-group list-group-flush" id="category_list"></ul>
                </div>
                <div class="col-8" style="max-height:300px; overflow:auto;" id="about_list">
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="modal-card-foot">
    <button class="btn-filled-blue" id="about_submit">{{__('Submit')}}</button>
</footer>

<script>
(function () {
    const rawData = @json($attributesMoreAbout);

    const transformedData = rawData.map(attr => ({
        type: attr.id,
        name: attr.name,
        multiple: attr.allow_multiple == 1,
        icon: attr.icon, 
        items: (attr.attribute_values || []).map(v => ({
            id: v.id,
            name: v.name
        }))
    }));

    const nameMap = {};
    const iconMap = {};

    rawData.forEach(attr => {
        (attr.attribute_values || []).forEach(v => {
            nameMap[v.id] = v.name;
            iconMap[v.id] = attr.icon;
        });
    });

    adminUserPage.initMultiSelectModal({
        input: '#selected_about_me',
        list: '#about_list',
        categoryItem: '#category_list .list-group-item', 
        searchInput: '.modal-body input[type="text"]',
        submitBtn: '#about_submit',
        previewOutput: '#about_preview',
        livePreview: '#about_selected_preview',
        data: transformedData,
        nameMap: nameMap,
        iconMap: iconMap 
    });
})();
</script>