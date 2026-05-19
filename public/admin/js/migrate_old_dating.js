var adminMigrateOldDating = function () {
    var loading = false
    var initCreate = function () {
        loading = false
        $('#migrate_old_dating_submit').unbind('click');
        $('#migrate_old_dating_submit').click(function () {
          if (loading) {
            return
          }
            loading = true
            let btn = $(this);
            btn.prop('disabled', true);
            btn.find('.btn-text').hide();
            btn.find('.btn-loading').show();
          $.ajax({ 
              type: 'POST', 
              url: $('#migrate_old_dating_form').attr('action'), 
              data: $('#migrate_old_dating_form').serialize(), 
              dataType: 'json',
              success: function (res) {
                if (res.status) {
                    showConfirmUI();
                }
                else{
                  $('#migrate_old_dating_form #errors').html('');
                  adminCore.setMessageError($('#migrate_old_dating_form #errors'),res.messages);
                }
              }
          }).done(function( data ) {
            loading = false
            btn.prop('disabled', false);
            btn.find('.btn-text').show();
            btn.find('.btn-loading').hide();
          });
        })
    }

    function bindImportNow() {
        $('#import_now').off('click').on('click', function () {
            if (loading) return;
            loading = true;

            $.ajax({
                type: 'POST',
                url:  $('#import_now').data('url'),
                dataType: 'json',
                success: function (res) {
                    if (res.status) {
                        location.reload();
                    }
                },
                complete: function () {
                    loading = false;
                }
            });
        });
    }

    function bindRemoveConnection() {
        $('#remove_connection').off('click').on('click', function () {
            if (loading) return;
            loading = true;

            $.ajax({
                type: 'POST',
                url:  $('#remove_connection').data('url'),
                dataType: 'json',
                success: function (res) {
                    if (res.status) {
                        $('#migrate_old_dating_form')[0].reset();
                        $('#migrate_old_dating_form #errors').html('');
                        showFormUI();
                    }
                },
                complete: function () {
                    loading = false;
                }
            });
        });
    }

    function showConfirmUI() {
        $('#form_import_users').hide();
        $('#confirm_import_users').show();
        $('#text_not_yet_import').show();
        $('#text_imported').hide();
        $('#import_now').show();
    }

    function showFormUI() {
        $('#confirm_import_users').hide();
        $('#form_import_users').show();
    }

    return {
        initCreate: initCreate,
        bindImportNow: bindImportNow,
        bindRemoveConnection: bindRemoveConnection,
    };
}();
  