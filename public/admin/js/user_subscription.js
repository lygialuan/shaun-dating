var adminUserSubscription = function () {
    var loading = false
    var initCreatePackage = function (){
        loading = false
        $('#subscription_package_submit').click(function(){        
            if (loading) {
                return
            }
            loading = true

            $.ajax({ 
                type: 'POST', 
                url: $('#subscription_package_form').attr('action'), 
                data: $('#subscription_package_form').serialize(), 
                dataType: 'json',
                success: function (data) {
                    if (data.status){
                        location.reload();
                    }
                    else{
                        $('#subscription_package_form #errors').html('');
                        adminCore.setMessageError($('#subscription_package_form #errors'),data.messages);
                    }
                }
            }).done(function( data ) {
                loading = false
            });
        });
        $('#isShowBadge').change(function(){
            if ($(this).prop('checked')) {
                $('.edit_badge_content').show();
            } else {
                $('.edit_badge_content').hide();
            }
        })
    }
    var initCreatePlan = function (){
        loading = false
        $('#subscription_plan_submit').click(function(){        
            if (loading) {
                return
            }
            loading = true

            $.ajax({ 
                type: 'POST', 
                url: $('#subscription_plan_form').attr('action'), 
                data: $('#subscription_plan_form').serialize(), 
                cache: false,
                success: function (data) {
                    if (data.status){
                        location.reload();
                    }
                    else{
                        $('#subscription_plan_form #errors').html('');
                        adminCore.setMessageError($('#subscription_plan_form #errors'),data.messages);
                    }
                }
            }).done(function( data ) {
                loading = false
            });
        });
    }

    var initComparePackages = function(storeOrderUrl) {
        new Sortable(document.getElementById('compare_packages_list'), {
            animation: 150,
            filter: '.active_action',
            onUpdate: function (e) {
                var checkboxIds = this.toArray();

                // Reorder Header Package Table
                var tableHeaderRow = document.getElementById('compareTableHeader');
                var tableHeaders = tableHeaderRow.querySelectorAll('.package-column');
    
                var reorderedTableHeaders = [];
                checkboxIds.forEach(function (checkboxId) {
                    let tableHeader = tableHeaderRow.querySelector('.package-column[data-id="' + checkboxId + '"]');
                    reorderedTableHeaders.push(tableHeader);
                });
    
                tableHeaders.forEach(function (header) {
                    tableHeaderRow.removeChild(header);
                });
    
                reorderedTableHeaders.forEach(function (header) {
                    tableHeaderRow.appendChild(header);
                });
    
                // Reorder Body Package Table
                var tbody = document.getElementById('table-body');
                var rows = tbody.querySelectorAll('.compare-row:not(.compare-row_blank)');

                rows.forEach(function (row) {
                    let cells = row.querySelectorAll('td.compare-row-column');
                    let reorderedCells = [];

                    checkboxIds.forEach(function (checkboxId) {
                        let cell = row.querySelector('td[data-id="' + checkboxId + '"]');
                        reorderedCells.push(cell);
                    });

                    cells.forEach(function (cell) {
                        row.removeChild(cell);
                    });

                    reorderedCells.forEach(function (cell) {
                        row.appendChild(cell);
                    });
                });
    
                $.ajax({
                    type: 'POST',
                    url: storeOrderUrl,
                    data: { orders: checkboxIds },
                    dataType: 'json'
                });
            }
            
        });

        function updateColumnVisibility() {
            $('.package-checkbox').each(function() {
                var packageId = $(this).val();
                var isChecked = $(this).prop('checked');

                var column = $('.package-column[data-id="' + packageId + '"]');
                var columnCompareRow = $('.compare-row-column[data-id="' + packageId + '"]');
            
                if (isChecked) {
                    column.show();
                    columnCompareRow.show()
                } else {
                    column.hide();
                    columnCompareRow.hide()
                }
            });
            $('.tr-add-btn td').attr('colspan', $('.package-checkbox:checked').length + 1);
        }
        $('.package-checkbox').change(function(e) {
            updateColumnVisibility();
        });

        updateColumnVisibility();
        
    }

    var initCompareTable = function()
    {
        $.rowCompare = {
            add: function()
            {
                var lastRowIndex = $('#pricing_table_form tbody').find('tr.compare-row').length;
                var newRow = $('.compare-row_blank').clone().removeClass('compare-row_blank').attr('name', function() {
                    return $(this).attr('name').replace('[0]', '[' + lastRowIndex + ']');
                }).show();
                newRow.attr('name').replace('[0]', '[' + lastRowIndex + ']')
                newRow.find('[name^="sc[0]"]').each(function() {
                    var newName = $(this).attr('name').replace('[0]', '[' + lastRowIndex + ']');
                    $(this).attr('name', newName)
                });
                $('#pricing_table_form tbody').find('tr:last').before(newRow);

                this.init();
                return false;
            },
            remove: function(obj)
            {
                if(confirm(adminTranslate.__("remove_row")))
                {
                    $(obj).closest('tr').remove();
                }
                return false;
            },
            init: function()
            {
                $('.compare-row_blank').hide();
                $('.compare_type_value').hide();
                $('.compare_type').each(function(){
                    var item = $(this).parent('td');
                    switch ($(this).val())
                    {
                        case 'text':
                            item.find('.type_text').show();
                            break;
                        case 'boolean':
                            item.find('.type_boolean').hide();
                            if(item.find('.boolean_value').val() == 1)
                            {
                                item.find('.type_yes').show();
                            }
                            else 
                            {
                                item.find('.type_no').show();
                            }
                            break;
                        default :
                            item.find('.type_text').show();
                    }
                })
            },
            switchType:function(item)
            {
                item = item.parents('td');
                if(item.find('.type_text').is(':visible'))
                {
                    item.find('.compare_type').val('boolean');
                }
                else 
                {
                    item.find('.compare_type').val('text');
                }
                this.init();
                return false;
            },
            switchYesNo: function(item, val)
            {
                item.parent().find('.type_boolean').show();
                item.hide();
                item.closest('td').find('.boolean_value').val(val);
                return false;
            }
        }
        $.rowCompare.init();
    }

    var initCreatePricingTable = function(url){
        $('#language').change(function () {
            window.location.href = url + '/' + $(this).val();
        });
    }

    var initPackagePlanListing = function(storeOrderUrl){
        new Sortable(document.getElementById('plan_list'), {
            animation: 150,
            filter: '.active_action',
            onUpdate: function () {
                $.ajax({
                    type: 'POST',
                    url: storeOrderUrl,
                    data: { orders: this.toArray() },
                    dataType: 'json'
                });
            }
        });
    }

    var toggleFlexForm = function() {
        var gatewayVal = $('#gateway_recurring_select').val();
        if (gatewayVal && gatewayVal != 0) {
            $('#flex_form_wrapper').show();
        } else {
            $('#flex_form_wrapper').hide();
            $('input[name="flex_form_id"]').val('');
        }
    };

    toggleFlexForm();

    $('#gateway_recurring_select').change(function(){
        toggleFlexForm();
    });

    return {
        initCreatePackage: initCreatePackage,
        initCreatePlan: initCreatePlan,
        initComparePackages: initComparePackages,
        initCompareTable: initCompareTable,
        initCreatePricingTable: initCreatePricingTable,
        initPackagePlanListing: initPackagePlanListing
    };
}();