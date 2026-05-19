var adminUserPage = (function () {
    var loading = false;

    var initCategoriesList = function (orderCategoriesListUrl) {
        new Sortable(document.getElementById("user_page_categories_list"), {
            animation: 150,
            filter: ".admin_modal_ajax",
            onUpdate: function (e) {
                $.ajax({
                    type: "POST",
                    url: orderCategoriesListUrl,
                    data: { orders: this.toArray() },
                    dataType: "json",
                });
            },
        });

        $(".menu_child").each(function () {
            new Sortable(document.getElementById($(this).attr("id")), {
                animation: 150,
                filter: ".admin_modal_ajax",
                onUpdate: function (e) {
                    $.ajax({
                        type: "POST",
                        url: orderCategoriesListUrl,
                        data: { orders: this.toArray() },
                        dataType: "json",
                    });
                },
            });
        });
    };

    var initCreateCategory = function () {
        loading = false;
        $("#create_category_submit").unbind("click");
        $("#create_category_submit").click(function () {
            if (loading) {
                return;
            }
            loading = true;
            $.ajax({
                type: "POST",
                url: $("#create_category_form").attr("action"),
                data: $("#create_category_form").serialize(),
                dataType: "json",
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        $("#create_category_form #errors").html("");
                        adminCore.setMessageError(
                            $("#create_category_form #errors"),
                            data.messages
                        );
                    }
                },
            }).done(function (data) {
                loading = false;
            });
        });
    };

    var initCreateFeaturePackage = function() {
        loading = false
        $('#package_submit').click(function(){        
            var form = $('#package_form');
            var formData = new FormData(form[0]);

            if (loading) {
                return
            }
            loading = true

            $.ajax({ 
                type: 'POST', 
                url: $('#package_form').attr('action'), 
                data: formData, 
                dataType: 'json',
                contentType: false,
                processData: false,
                cache: false,
                success: function (data) {
                    if (data.status){
                        location.reload();
                    }
                    else{
                    $('#package_form #errors').html('');
                        adminCore.setMessageError($('#package_form #errors'),data.messages);
                    }
                }
            }).done(function( data ) {
                loading = false
            });
        });
    }
    var initFeaturePackageListing = function(storeOrderUrl) {
        new Sortable(document.getElementById('package_list'), {
            animation: 150,
            filter: '.active_action',
            onUpdate: function (e) {
              $.ajax({ 
                  type: 'POST', 
                  url: storeOrderUrl, 
                  data: {orders: this.toArray()}, 
                  dataType: 'json',
                  success: function (data) {
    
                  }
              });
            }
        });
    }
    var initListing = function() {
        $('#delete_button').click(function(e) {
           if ($('.check_item:checked').length == 0) {            
             return;
           }
 
           adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_delete_user'), function(){
             $('#action').val('delete');
             $('#user_form').submit();
           });
        });
        $('#active_button').click(function(e) {
           if ($('.check_item:checked').length == 0) {
             return;
           }
 
           adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_active_user'), function(){
             $('#action').val('active');
             $('#user_form').submit();
           });
        });
    }

    var initMultiSelectModal = function (config) {
        let selected = $(config.input).val();
        try {
            selected = JSON.parse(selected || '{}');
        } catch (e) {
            selected = {};
        }
        if (Array.isArray(selected)) selected = {};

        renderCategories();
        let currentType = config.data[0]?.type;
        load(currentType);
        updateLivePreview();

        $(document).off('click', config.categoryItem).on('click', config.categoryItem, function () {
            save();

            updateLivePreview();

            $(config.categoryItem).removeClass('active');
            $(this).addClass('active');
            currentType = $(this).data('type');
            $(config.searchInput).val('');
            load(currentType);
        });

        $(document).off('input', config.searchInput).on('input', config.searchInput, function () {
            let keyword = $(this).val().toLowerCase().trim();
            let category = config.data.find(c => c.type === currentType);
            let list = category ? category.items : [];
            let filtered = list.filter(item =>
                item.name.toLowerCase().includes(keyword)
            );
            if (filtered.length === 0) {
                renderEmpty(keyword);
            } else {
                render(filtered);
            }
        });

        $(document).off('change', config.list + ' .form-check-input')
            .on('change', config.list + ' .form-check-input', function () {
                save();
                updateLivePreview();
            });

        $(document).off('click', config.submitBtn).on('click', config.submitBtn, function () {
            save();
            $(config.input).val(JSON.stringify(selected));

            if (config.previewOutput) {
                renderPreview(config.input, config.previewOutput);
            }

            $('.modal-close').click();
        });

        function renderCategories() {
            let html = '';
            config.data.forEach((cat, index) => {
                let active = index === 0 ? 'active' : '';

                html += `
                    <li class="list-group-item ${active}" data-type="${cat.type}">
                        ${cat.name}
                    </li>
                `;
            });
            $('#category_list').html(html);
        }

        function load(type) {
            let category = config.data.find(c => c.type === type);
            render(category ? category.items : []);
        }

        function render(list) {
            let html = '';
            let category = config.data.find(c => c.type === currentType);
            let isMultiple = category?.multiple ?? true;

            list.forEach(item => {
                let checked = selected[currentType] && selected[currentType].includes(String(item.id)) ? 'checked' : '';
                let inputType = isMultiple ? 'checkbox' : 'radio';

                html += `
                    <div class="form-check">
                        <input class="form-check-input"
                            type="${inputType}"
                            name="select_${currentType}"
                            value="${item.id}"
                            ${checked}>
                        <label class="form-check-label">${item.name}</label>
                    </div>
                `;
            });

            $(config.list).html(html);
        }

        function renderEmpty(keyword) {
            $(config.list).html(`
                <div class="text-muted text-center py-3">
                    No results found ${keyword ? `for "<b>${keyword}</b>"` : ''}
                </div>
            `);
        }

        function save() {
            let category = config.data.find(c => c.type === currentType);
            let isMultiple = category?.multiple ?? true;

            if (!selected[currentType]) {
                selected[currentType] = [];
            }

            selected[currentType] = [];

            $(config.list + ' .form-check-input:checked').each(function () {
                selected[currentType].push($(this).val());
            });

            if (!isMultiple && selected[currentType].length > 1) {
                selected[currentType] = [selected[currentType][0]];
            }
        }

        function updateLivePreview() {
            if (config.livePreview) {
                $(config.input).val(JSON.stringify(selected));
                renderPreview(config.input, config.livePreview);
            }
        }

        function renderPreview(input, output) {
            let raw = $(input).val();
            let data = {};

            try {
                data = JSON.parse(raw || '{}');
            } catch (e) {
                console.warn("Selected data JSON error:", raw);
                data = {};
            }

            let html = '';

            Object.keys(data).forEach(type => {
                let category = config.data.find(c => String(c.type) === String(type));
                let categoryName = category ? category.name : type;
                let icon = category?.icon || '';

                if (Array.isArray(data[type]) && data[type].length) {
                    let itemsNames = data[type].map(id => config.nameMap?.[id] || id).join(', ');

                    html += `
                        <div class="mb-1 d-flex align-items-start gap-1">
                            ${icon ? `<img src="${icon}" width="14" height="14">` : ''}
                            <span class="flex-shrink-0">${categoryName}:</span>
                            <span class="flex-grow-1">${itemsNames}</span>
                        </div>
                    `;
                }
            });

            $(output).html(html);
        }
    };

    var initCreateSubProfile = function () {
        loading = false;
        $("#create_sub_profile_submit").unbind("click");
        $("#create_sub_profile_submit").click(function () {
            if (loading) {
                return;
            }
            loading = true;

            let formData = new FormData(document.getElementById("create_sub_profile_form"));

            $.ajax({
                type: "POST",
                url: $("#create_sub_profile_form").attr("action"),
                data: formData,
                processData: false, 
                contentType: false, 
                dataType: "json",
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        $("#create_sub_profile_form #errors").html("");
                        adminCore.setMessageError(
                            $("#create_sub_profile_form #errors"),
                            data.messages
                        );
                    }
                },
            }).done(function (data) {
                loading = false;
            });
        });
    };

    var initUploadPhotos = function () {
        loading = false;
        $("#create_upload_photos_submit").unbind("click");
        $("#create_upload_photos_submit").click(function () {
            if (loading) {
                return;
            }
            loading = true;

            let formData = new FormData(document.getElementById("create_upload_photos_form"));

            $.ajax({
                type: "POST",
                url: $("#create_upload_photos_form").attr("action"),
                data: formData,
                processData: false, 
                contentType: false, 
                dataType: "json",
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        $("#create_upload_photos_form #errors").html("");
                        adminCore.setMessageError(
                            $("#create_upload_photos_form #errors"),
                            data.messages
                        );
                    }
                },
            }).done(function (data) {
                loading = false;
            });
        });
    };

    var initHandleLocation = function (stateUrl, cityUrl) {
        $('#country').on('change', function () {
            let countryId = $(this).val();
            if (!countryId) return;
            $.ajax({
                type: "POST",
                url: stateUrl,
                data: { country_id: countryId }, 
                dataType: "json",
                success: function (res) {
                    let html = '<option value="">Select</option>';
                    if (res.status && res.states.length) {
                        res.states.forEach(item => {
                            html += `<option value="${item.id}">${item.name}</option>`;
                        });
                        $('#state').html(html);
                        $("#state_select").show();
                    }
                }
            });
        });

        $('#state').on('change', function () {
            let stateId = $(this).val();
            if (!stateId) return;
            $.ajax({
                type: "POST",
                url: cityUrl,
                data: { state_id: stateId },
                dataType: "json",
                success: function (res) {
                    let html = '<option value="">Select</option>';
                    if (res.status && res.cities.length) {
                        res.cities.forEach(item => {
                            html += `<option value="${item.id}">${item.name}</option>`;
                        });
                        $('#city').html(html);
                        $("#city_select").show();
                    }
                }
            });
        });
    };

    return {
        initCategoriesList: initCategoriesList,
        initCreateCategory: initCreateCategory,
        initCreateFeaturePackage: initCreateFeaturePackage,
        initFeaturePackageListing: initFeaturePackageListing,
        initListing: initListing,
        initMultiSelectModal: initMultiSelectModal,
        initCreateSubProfile: initCreateSubProfile,
        initHandleLocation: initHandleLocation,
        initUploadPhotos: initUploadPhotos
    };
})();
