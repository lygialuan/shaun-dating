var adminCore = function () {
  var init = function () {
    $('.dropdown').click(function () {
      if ($(this).hasClass('navbar-item')) {
        $(this).toggleClass('active');
      }
      else {
        $(this).parent().toggleClass('active');
        $(this).find('.mdi').toggleClass('mdi-plus').toggleClass('mdi-minus')
      }
    });

    $('.mobile-aside-button').click(function () {
      $('body').toggleClass('aside-mobile-expanded');
      $(this).find('.icon > .mdi').toggleClass('mdi-forwardburger').toggleClass('mdi-backburger');
    });

    $('.--jb-navbar-menu-toggle').click(function () {
      $(this).find('.icon > .mdi').toggleClass('mdi-dots-vertical').toggleClass('mdi-close');
      $('#' + $(this).data('target')).toggleClass('active');
    });

    if ($('.admin-message').length > 0) {
      window.scrollTo({ top: 0});
    }

    // Scroll to Element
    if (window.location.hash) {
      var target = $(window.location.hash);
      if (target.length) {
        $('html, body').animate({
          scrollTop: target.offset().top
        }, 200);
      }
    }

    initModal();

    initTriggerClickTranslate();
  }
  var initModal = function () {
    $('body').on('click', '.admin_modal_confirm_delete', function (e) {
      e.preventDefault();
      openModal('modal-delete');
      var url = $(this).data('url');
      var content = $(this).data('content');
      if (content) {
        $('#modal-delete .modal-card-body p').html(content);
      } else {
        $('#modal-delete .modal-card-body p').html(adminTranslate.__('confirm_delete_item'));
      }
      $('.modal-delete-confirm').unbind('click').click(function () {
        window.location.href = url;
      });
    });

    $('body').on('click', '.admin_modal_ajax', function (e) {
      e.preventDefault();
      $('#modal-ajax').html($('#modal-loading').html());
      openModal('modal-ajax');
      var url = $(this).data('url');
      $('#modal-ajax .modal-card').load(url, function( response, status, xhr ) {
        $('#modal-ajax .modal-card .modal-card-body .card-content')[0].scrollIntoView()
        initTriggerClickTranslate();
        $('#modal-ajax .modal-card-foot .btn-filled-blue').focus();
      });
    });

    $('body').on('click', '.modal-close', function (e) {
      e.preventDefault();
      $(this).parents('.modal').removeClass('active');
      $('body').removeClass('modal-open')
    });

  }

  var setMessageError = function (element, messages) {    
    messages.forEach(message => {
      element.append($('#message-error div').clone().html(message));
    });
    element[0].scrollIntoView()
  }

  var initAllowAccess = function (fieldName) {
    var elementAll = $('.allow_' + fieldName + '[value=\'all\']');
    var first = true;
    elementAll.off('change');
    elementAll.change(function () {
      var checked = $(this).prop('checked');
      $('.allow_' + fieldName).each(function(){
        if ($(this).val() != 'all') {
          if (!first) {
            $(this).prop('checked', false);
          }
          if (checked) {
            $(this).parents('div.form-check').hide();
          } else {
            $(this).parents('div.form-check').show();
          }
        }
      });
      first =  false;
    });
    elementAll.trigger('change');
  }

  var initLeftSidebarMenu = function(){
    $('#toggleSidebarMenu').click(function(){
      $('#leftSidebarMenu').toggleClass('sidebar-show');
      $('.modal-backdrop-sidebar').toggleClass('backdrop-show')
    })

    $('.modal-backdrop-sidebar').click(function(){
      $('#leftSidebarMenu').removeClass('sidebar-show');
      $('.modal-backdrop-sidebar').removeClass('backdrop-show')
    })

    $('#asideMenuList .aside-menu-list-child .aside-menu-list-child-item-list li a').click(function(){
      $('#leftSidebarMenu').removeClass('sidebar-show');
      $('.modal-backdrop-sidebar').removeClass('backdrop-show')
    })
  }

  var showHiddenPassword = function(){
    $('#showHiddenPassword').click(function(){
      if($('#formControlPassword').attr('type') == 'password'){
        $('#formControlPassword').prop('type', 'text');
        $(this).html('visibility')
      }else{
        $('#formControlPassword').prop('type', 'password');
        $(this).html('visibility_off')
      }
    });
  }

  var handleSidebarMenu = function (){
    $($($('ul.aside-menu-list-child').find('li.active')[0]).parents('ul')[0]).addClass('show');
    $('.aside-menu-list .aside-menu-list-child').each(function(e){
        if ($(this).children().length == 0) {
            $(this).parent().remove();
        }
    })
  }

  var initCheckAll = function() {
    $('.check_all').change(function(){
      $('.check_item').prop('checked',$(this).is(':checked'));      
    });
  }

  var showConfirmModal = function (title, content, callback) {    
    openModal('modal-confirm');
    
    $('#modal-confirm .modal-card-title').html(title);
    $('#modal-confirm .modal-card-body p').html(content);
    $('#modal-confirm .modal-action-confirm').unbind('click').click(function () {
      callback()
      $('#modal-confirm').removeClass('active');
    });
  }

  var showLanguageModal = function(){
    $('#showLanguageModal').click(function(){
      openModal('modal-language');
      $('#modal-language .modal-action-change').unbind('click').click(function () {
        callback()
      });
      $('body').addClass('modal-open')
    })
  }

  var initSearch = function(searchUrl){
    $('#global-search').keyup(window.adminDelay(function (e) {
      var searchVal = $(this).val();
        if (searchVal != '') {
          $.get(searchUrl + '/' + searchVal).then((response) => {
            suggest_results = '';
            if(response.length > 0){
              response.forEach(function(item) {
                suggest_results += '<a href="'+item.href+'" class="suggest-item"><div class="suggest-item-name">'+item.name+'</div><div class="suggest-item-desc">'+item.des+'</div></a>';
              });
            } else {
              suggest_results = '<div class="no-results-found">No result found</div>';
            }
            $('#display-suggestion').html(suggest_results).show()
          })
        }
    }, 500));

    $('#global-search').focusout(function () {
      if ($('#display-suggestion').is(":hover") == false) {
        $('#display-suggestion').html('').hide();
      }
    });

    $('#global-search').focus(function () {
      $('#global-search').trigger('keyup');
    });

    $('#display-suggestion').click(function () {
      $('#display-suggestion').html('').hide();
    });
  }

  var openModal = function(id){
    $('#' + id).addClass('active');
    $('body').addClass('modal-open')
  }

  var hideModal = function(id){
    $('#' + id).removeClass('active');
    $('body').removeClass('modal-open')
  }

  var viewport = function () {
    var e = window, a = 'inner';
    if (!('innerWidth' in window)) {
        a = 'client';
        e = document.documentElement || document.body;
    }
    return e[ a + 'Width' ];
  };

  var scrollHeaderAdmin = function(){
    $(window).scroll(function () {
      if (viewport() > 992) {
        var _top = $(window).scrollTop();
        if (_top > 30) {
            $('body').addClass('documentScrolling');
        } else {
            $('body').removeClass('documentScrolling');
        }
      }
    });
  }

  var initScrollToTop = function(){
    $("body").append('<button id="scrollToTop" class="scroll-to-top-btn" style="display: none;"><span class="material-symbols-outlined scroll-to-top-btn-icon"> arrow_upward </span></button>');

    $(window).scroll(function() {
      if($(this).scrollTop() > 100)
        $("#scrollToTop").fadeIn("slow");
      else
        $("#scrollToTop").fadeOut("slow");
    });

    $("#scrollToTop").click(function (){
      $("body, html").animate({ scrollTop:0 }, 500);
    });
  }

  var decodeHtml= function(text) {
    return $("<textarea/>").html(text).text();
  }

  var endocdeHtml= function(text) {
    return $("<textarea/>").text(text).html();
  }

  var initTriggerClickTranslate = function() {
    const readonlyElements = $('.form-group .form-control[readonly]').filter(function() {
      return $(this).siblings('.admin_modal_ajax').length > 0;
    });
    if (readonlyElements.length > 0) {
      readonlyElements.each(function() {
        const readonlyElement = $(this);
        const adminModalAjax = readonlyElement.siblings('.admin_modal_ajax');
        const clonedElement = adminModalAjax.clone().text('').addClass('form-control-wrap-modal-ajax');
        readonlyElement.wrap('<div class="form-control-wrap"></div>');
        readonlyElement.parent('.form-control-wrap').append(clonedElement);
      });
    }
  }

  return {
    init: init,
    initModal: initModal,
    setMessageError: setMessageError,
    initAllowAccess: initAllowAccess,
    initLeftSidebarMenu: initLeftSidebarMenu,
    showHiddenPassword: showHiddenPassword,
    handleSidebarMenu: handleSidebarMenu,
    initCheckAll: initCheckAll,
    showConfirmModal: showConfirmModal,
    showLanguageModal: showLanguageModal,
    initSearch: initSearch,
    openModal: openModal,
    hideModal: hideModal,
    scrollHeaderAdmin: scrollHeaderAdmin,
    initScrollToTop: initScrollToTop,
    decodeHtml: decodeHtml,
    endocdeHtml: endocdeHtml
  };
}();
$(document).ready(function () {
  adminCore.init();
  adminCore.initScrollToTop();
});

