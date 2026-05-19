<?php


namespace Packages\ShaunSocial\Core\Services;

class Theme
{
    public function getCss($settings, $dark = false)
    {
        $css = '';
        foreach ($settings as $data) {
            foreach ($data as $key => $value) { 
                if (! $value) {
                    continue;
                }
                $method = 'get_css_'.$key;
                if (method_exists($this, $method)) {
                    $tmp = $this->{$method}($value);
                    if (! is_array($tmp)) {
                        $tmp = [$tmp];
                    }
                    foreach ($tmp as $string) {
                        $string = explode(',', $string);
                        if (! $dark) {
                            $string = array_map(function($value) { return 'html:not(.dark) '.$value; }, $string);
                        } else {
                            $string = array_map(function($value) { return 'html.dark '.$value; }, $string);
                        }
                        $css.= implode(', ', $string);
                    }
                }
            }
        }
        return $css; 
    }

    public function build($theme)
    {
        $path = $theme->getPathCss();
        $css = $this->getCss($theme->getSettings());
        $css.= $this->getCss($theme->getSettingsDark(), true);
        file_put_contents($path, $css);

        clearAllCache();
    }

    public function getAdminSettings($settings)
    {   
        $results = [];
        foreach ($settings as $group => $data) {
            $method = 'get_admin_title_group_'.$group;
            if (method_exists($this, $method)) {
                $guide_image = 'get_admin_guide_image_group_'.$group;
                $result = [
                    'title' => $this->$method(),
                    'childs' => []
                ];
                if(method_exists($this, $guide_image)){
                    $result['guide_image'] = $this->$guide_image();
                }
                
                foreach ($data as $key => $value) {
                    $method = 'get_admin_title_item_'.$key;
                    if (method_exists($this, $method)) {
                        $result['childs'][] = [
                            'title' => $this->$method(),
                            'value' => $value,
                            'name' => $group.'['.$key.']'
                        ];
                    }
                }

                $results[] = $result;
            }
        }

        return $results;
    }

    public function getThemeSettingGuideImage($group)
    {
        return asset('admin/images/theme-settings/'.$group.'.png');
    }

    // Layout
    public function get_admin_title_group_main()
    {
        return __('Layout');
    }
    public function get_admin_guide_image_group_main()
    {
        return $this->getThemeSettingGuideImage('layout');
    }

    // Body Background Color
    public function get_admin_title_item_body_background_color()
    {
        return __('Body Background Color');
    }
    public function get_css_body_background_color($value)
    {
        return '.bg-body{
            background-color: '.$value.';
        }';
    }

    // Body Border Color
    public function get_admin_title_item_border_divider_color()
    {
        return __('Divider Border Color');
    }
    public function get_css_border_divider_color($value)
    {
        return '.border-divider{
            border-color: '.$value.';
        }';
    }

    // Main Text Color
    public function get_admin_title_item_main_text_color()
    {
        return __('Main Text Color');
    }
    public function get_css_main_text_color($value)
    {
        return '.text-main-color{
            color: '.$value.';
        }';
    }

    // Sub Text Color
    public function get_admin_title_item_sub_text_color()
    {
        return __('Sub Text Color');
    }
    public function get_css_sub_text_color($value)
    {
        return '.text-sub-color{
            color: '.$value.';
        }';
    }

    // Link Color
    public function get_admin_title_item_link_text_color()
    {
        return __('Link Color');
    }
    public function get_css_link_text_color($value)
    {
        return 'a, button.text-primary-color, a.text-primary-color{
            color: '.$value.';
        }';
    }

    // Link Hover Color
    public function get_admin_title_item_link_hover_color()
    {
        return __('Link Hover Color');
    }
    public function get_css_link_hover_color($value)
    {
        return 'a:hover, button.text-primary-color:hover{
            color: '.$value.';
        }';
    }

    // Body Background Loading Color
    public function get_admin_title_item_body_background_loading_color()
    {
        return __('Body Background Loading Color');
    }
    public function get_css_body_background_loading_color($value)
    {
        return '.bg-main-bg{
            background-color: '.$value.';
        }';
    }

    // Widget
    public function get_admin_title_group_widget()
    {
        return __('Widget');
    }
    public function get_admin_guide_image_group_widget()
    {
        return $this->getThemeSettingGuideImage('widget');
    }

    // Widget Box Background Color
    public function get_admin_title_item_widget_background_color()
    {
        return __('Widget Box Background Color');
    }
    public function get_css_widget_background_color($value)
    {
        return '.widget-box{
            background-color: '.$value.';
        }';
    }

    // Widget Box Border Color
    public function get_admin_title_item_widget_border_color()
    {
        return __('Widget Box Border Color');
    }
    public function get_css_widget_border_color($value)
    {
        return '.widget-box{
            border-color: '.$value.';
        }';
    }

    // Widget Box Text Color
    public function get_admin_title_item_widget_text_color()
    {
        return __('Widget Box Text Color');
    }
    public function get_css_widget_text_color($value)
    {
        return '.widget-box{
            color: '.$value.';
        }';
    }

    // Widget Box Title Color
    public function get_admin_title_item_widget_title_color()
    {
        return __('Widget Box Title Color');
    }
    public function get_css_widget_title_color($value)
    {
        return '.widget-box-header .widget-box-header-title{
            color: '.$value.';
        }';
    }

    // Widget Box View All Color
    public function get_admin_title_item_widget_view_all_text_color()
    {
        return __('Widget Box View All Color');
    }
    public function get_css_widget_view_all_text_color($value)
    {
        return '.widget-box-header a, .widget-box-header a:hover{
            color: '.$value.';
        }';
    }

    // Feed Items
    public function get_admin_title_group_feed()
    {
        return __('Feed Items');
    }
    public function get_admin_guide_image_group_feed()
    {
        return $this->getThemeSettingGuideImage('feed-items');
    }

    // Feed Background Color
    public function get_admin_title_item_feed_background_color()
    {
        return __('Feed Background Color');
    }
    public function get_css_feed_background_color($value)
    {
        return '.feed-item, 
        .comment-form-wrapper, 
        .p-dialog.post-detail-modal, 
        .post-header-back{
            background-color: '.$value.';
        }';
    }

    // Feed Background Hover Color
    public function get_admin_title_item_feed_background_hover_color()
    {
        return __('Feed Background Hover Color');
    }
    public function get_css_feed_background_hover_color($value)
    {
        return '.feed-item.feed-item-hover:hover{
            background-color: '.$value.';
        }';
    }

    // Feed Border Color
    public function get_admin_title_item_feed_border_color()
    {
        return __('Feed Border Color');
    }
    public function get_css_feed_border_color($value)
    {
        return '.feed-item{
            border-color: '.$value.';
        }';
    }

    // Feed Border Hover Color
    public function get_admin_title_item_feed_border_hover_color()
    {
        return __('Feed Border Hover Color');
    }
    public function get_css_feed_border_hover_color($value)
    {
        return '.feed-item.feed-item-hover:hover{
            border-color: '.$value.';
        }';
    }

    // Feed Dropdown Icon Color
    public function get_admin_title_item_feed_dropdown_icon_color()
    {
        return __('Feed Dropdown Icon Color');
    }
    public function get_css_feed_dropdown_icon_color($value)
    {
        return '.feed-item-dropdown{
            color: '.$value.';
        }';
    }

    // Feed Action Border Color
    public function get_admin_title_item_feed_action_border_color()
    {
        return __('Feed Action Border Color');
    }
    public function get_css_feed_action_border_color($value)
    {
        return '.feed-main-action{
            border-color: '.$value.';
        }';
    }

    // Feed Action Item Color
    public function get_admin_title_item_feed_action_item_color()
    {
        return __('Feed Action Item Color');
    }
    public function get_css_feed_action_item_color($value)
    {
        return '.feed-main-action .feed-main-action-item{
            color: '.$value.';
        }';
    }

    // Feed Like Active Color
    public function get_admin_title_item_feed_like_active_color()
    {
        return __('Feed Like Active Color');
    }
    public function get_css_feed_like_active_color($value)
    {
        return '.feed-main-action .feed-main-action-item.feed-main-action-like.is-liked{
            color: '.$value.';
        }';
    }

    // Feed Bookmark Active Color
    public function get_admin_title_item_feed_bookmark_active_color()
    {
        return __('Feed Bookmark Active Color');
    }
    public function get_css_feed_bookmark_active_color($value)
    {
        return '.feed-main-action .feed-main-action-item.feed-main-action-bookmark.is-bookmarked{
            color: '.$value.';
        }';
    }

    // Feed Like Text Color
    public function get_admin_title_item_feed_like_text_color()
    {
        return __('Feed Like Text Color');
    }
    public function get_css_feed_like_text_color($value)
    {
        return [
            '.feed-item-like{
                color: '.$value.';
            }',
            '.feed-item-like-text{
                color: '.$value.';
            }',
        ];
    }

    // Feed Header Info Title Text Color
    public function get_admin_title_item_feed_header_info_title_text_color()
    {
        return __('Feed Header Info Title Text Color');
    }
    public function get_css_feed_header_info_title_text_color($value)
    {
        return [
            '.feed-item-header-info-title{
                color: '.$value.';
            }',
            '.feed-share-content .activity_feed_header a{
                color: '.$value.';
            }'
        ];
    }

    // Feed Header Info Date Text Color
    public function get_admin_title_item_feed_header_info_date_text_color()
    {
        return __('Feed Header Info Date Text Color');
    }
    public function get_css_feed_header_info_date_text_color($value)
    {
        return '.feed-item-header-info-date{
            color: '.$value.';
        }';
    }

    // Feed Content Text Color
    public function get_admin_title_item_feed_content_text_color()
    {
        return __('Feed Content Text Color');
    }
    public function get_css_feed_content_text_color($value)
    {
        return '.feed-item-content{
            color: '.$value.';
        }';
    }

    // Feed Background Poll Color
    public function get_admin_title_item_feed_poll_background_color()
    {
        return __('Feed Background Poll Color');
    }
    public function get_css_feed_poll_background_color($value)
    {
        return '.feed-polls-list .feed-polls-list-item{
            background-color: '.$value.';
        }';
    }

    // Feed Text Poll Color
    public function get_admin_title_item_feed_poll_text_color()
    {
        return __('Feed Text Poll Color');
    }
    public function get_css_feed_poll_text_color($value)
    {
        return '.feed-polls-list .feed-polls-list-item{
            color: '.$value.';
        }';
    }

    // Feed Background Selected Poll Color
    public function get_admin_title_item_feed_selected_poll_background_color()
    {
        return __('Feed Background Selected Poll Color');
    }
    public function get_css_feed_selected_poll_background_color($value)
    {
        return '.feed-polls-list .feed-polls-list-item-selected{
            background-color: '.$value.';
        }';
    }

    // Feed Text Selected Poll Color
    public function get_admin_title_item_feed_selected_poll_text_color()
    {
        return __('Feed Text Selected Poll Color');
    }
    public function get_css_feed_selected_poll_text_color($value)
    {
        return '.feed-polls-list .feed-polls-list-item.is_voted{
            color: '.$value.';
        }';
    }

    // Feed Sub Text Poll Color
    public function get_admin_title_item_feed_poll_sub_text_color()
    {
        return __('Feed Sub Text Poll Color');
    }
    public function get_css_feed_poll_sub_text_color($value)
    {
        return '.feed-polls .feed-polls-sub-text{
            color: '.$value.';
        }';
    }

    // Feed Content Share Background Color
    public function get_admin_title_item_feed_content_share_background_color()
    {
        return __('Feed Content Share Background Color');
    }
    public function get_css_feed_content_share_background_color($value)
    {
        return '.feed-share-content{
            background-color: '.$value.';
        }';
    }

    // Feed Slider Dot Background Color
    public function get_admin_title_item_feed_slider_dot_background_color()
    {
        return __('Feed Slider Dot Background Color');
    }
    public function get_css_feed_slider_dot_background_color($value)
    {
        return '.feed-item .activity_content_photos_list .vueperslides__bullets .vueperslides__bullet .default{
            background-color: '.$value.';
            border-color: '.$value.';
        }';
    }

    // Feed Slider Dot Active Background Color
    public function get_admin_title_item_feed_slider_dot_active_background_color()
    {
        return __('Feed Slider Dot Active Background Color');
    }
    public function get_css_feed_slider_dot_active_background_color($value)
    {
        return '.feed-item .activity_content_photos_list .vueperslides__bullets .vueperslides__bullet--active .default{
            background-color: '.$value.';
            border-color: '.$value.';
        }';
    }

    // Feed Comment Form Border Color
    public function get_admin_title_item_feed_comment_form_border_color()
    {
        return __('Feed Comment Form Border Color');
    }

    public function get_css_feed_comment_form_border_color($value)
    {
        return '.comment-form-wrapper{
            border-color: '.$value.';
        }';
    }

    // Feed Comment Form Background Color
    public function get_admin_title_item_feed_comment_form_background_color()
    {
        return __('Feed Comment Form Background Color');
    }

    public function get_css_feed_comment_form_background_color($value)
    {
        return '.comment-form{
            background-color: '.$value.';
        }';
    }

    // Feed Comment Form Text Color
    public function get_admin_title_item_feed_comment_form_text_color()
    {
        return __('Feed Comment Form Text Color');
    }
    public function get_css_feed_comment_form_text_color($value)
    {
        return '.feed-comment-info-comment-holder textarea{
            color: '.$value.';
        }';
    }

    // Feed Comment Form Icon Color
    public function get_admin_title_item_feed_comment_form_icon_color()
    {
        return __('Feed Comment Form Icon Color');
    }
    public function get_css_feed_comment_form_icon_color($value)
    {
        return '.comment-form .comment-form-action .comment-form-action-item .base-icon{
            color: '.$value.';
        }';
    }

    // Feed Comment Form Placeholder Color
    public function get_admin_title_item_feed_comment_form_placeholder_color()
    {
        return __('Feed Comment Form Placeholder Color');
    }
    public function get_css_feed_comment_form_placeholder_color($value)
    {
        return '.feed-comment-info-comment-holder textarea::placeholder{
            color: '.$value.';
        }';
    }

    // Feed Comment Form Button Color
    public function get_admin_title_item_feed_comment_form_button_color()
    {
        return __('Feed Comment Form Button Color');
    }
    public function get_css_feed_comment_form_button_color($value)
    {
        return ['button.feed-comment-info-comment-holder-btn{
            color: '.$value.';
        }',
        'button.feed-comment-info-comment-holder-btn:hover{
            color: '.$value.';
        }'];
    }

    // Feed Notifications Button Background Color
    public function get_admin_title_item_feed_notifications_button_bg_color()
    {
        return __('Feed Notifications Button Background Color');
    }
    public function get_css_feed_notifications_button_bg_color($value)
    {
        return '.post-notifications{
            background-color: '.$value.';
        }';
    }

    // Feed Notifications Button Text Color
    public function get_admin_title_item_feed_notifications_button_text_color()
    {
        return __('Feed Notifications Button Text Color');
    }
    public function get_css_feed_notifications_button_text_color($value)
    {
        return '.post-notifications{
            color: '.$value.';
        }';
    }

    // Comment Items
    public function get_admin_title_group_comment()
    {
        return __('Comment Items');
    }
    public function get_admin_guide_image_group_comment()
    {
        return $this->getThemeSettingGuideImage('comment-items');
    }

    // Comment Username Color
    public function get_admin_title_item_comment_username_color()
    {
        return __('Comment Username Color');
    }
    public function get_css_comment_username_color($value)
    {
        return '.comment-item-username{
            color: '.$value.';
        }';
    }
    
    // Comment Content Color
    public function get_admin_title_item_comment_content_color()
    {
        return __('Comment Content Color');
    }
    public function get_css_comment_content_color($value)
    {
        return '.comment-item-content{
            color: '.$value.';
        }';
    }

    // Comment Icon Color
    public function get_admin_title_item_comment_icon_color()
    {
        return __('Comment Icon Color');
    }
    public function get_css_comment_icon_color($value)
    {
        return '.comment-item-like{
            color: '.$value.';
        }';
    }

    // Comment Icon Active Color
    public function get_admin_title_item_comment_icon_active_color()
    {
        return __('Comment Icon Active Color');
    }
    public function get_css_comment_icon_active_color($value)
    {
        return '.comment-item-like.is-liked{
            color: '.$value.';
        }';
    }

    // Comment Date Color
    public function get_admin_title_item_comment_date_color()
    {
        return __('Comment Date Color');
    }
    public function get_css_comment_date_color($value)
    {
        return '.comment-item-date, .comment-item-date a{
            color: '.$value.';
        }';
    }

    // Comment View All Color
    public function get_admin_title_item_comment_view_all_color()
    {
        return __('Comment View All Color');
    }
    public function get_css_comment_view_all_color($value)
    {
        return '.comment-item-view_all{
            color: '.$value.';
        }';
    }

    // Reply UserName Color
    public function get_admin_title_item_reply_username_color()
    {
        return __('Reply Username Color');
    }
    public function get_css_reply_username_color($value)
    {
        return '.reply-item-username{
            color: '.$value.';
        }';
    }

    // Reply Content Color
    public function get_admin_title_item_reply_content_color()
    {
        return __('Reply Content Color');
    }
    public function get_css_reply_content_color($value)
    {
        return '.reply-item-content{
            color: '.$value.';
        }';
    }

    // Reply Date Color
    public function get_admin_title_item_reply_date_color()
    {
        return __('Reply Date Color');
    }
    public function get_css_reply_date_color($value)
    {
        return '.reply-item-date, .reply-item-date a{
            color: '.$value.';
        }';
    }

    // Reply Icon Color
    public function get_admin_title_item_reply_icon_color()
    {
        return __('Reply Icon Color');
    }
    public function get_css_reply_icon_color($value)
    {
        return '.reply-item-like{
            color: '.$value.';
        }';
    }

    // Reply Icon Active Color
    public function get_admin_title_item_reply_icon_active_color()
    {
        return __('Reply Icon Active Color');
    }
    public function get_css_reply_icon_active_color($value)
    {
        return '.reply-item-like.is-liked{
            color: '.$value.';
        }';
    }

    // Reply Status Background Color
    public function get_admin_title_item_reply_status_background_color()
    {
        return __('Reply Status Background Color');
    }
    public function get_css_reply_status_background_color($value)
    {
        return '.reply-status-item{
            background-color: '.$value.';
        }';
    }

    // Reply Status Text Color
    public function get_admin_title_item_reply_status_text_color()
    {
        return __('Reply Status Text Color');
    }
    public function get_css_reply_status_text_color($value)
    {
        return '.reply-status-item{
            color: '.$value.';
        }';
    }

    // Reply Status Icon Color
    public function get_admin_title_item_reply_status_icon_color()
    {
        return __('Reply Status Icon Color');
    }
    public function get_css_reply_status_icon_color($value)
    {
        return '.reply-status-item-icon{
            color: '.$value.';
        }';
    }

    // Sidebar
    public function get_admin_title_group_sidebar()
    {
        return __('Sidebar');
    }
    public function get_admin_guide_image_group_sidebar()
    {
        return $this->getThemeSettingGuideImage('sidebar');
    }

    // Sidebar Background Color
    public function get_admin_title_item_sidebar_background_color()
    {
        return __('Sidebar Background Color');
    }
    public function get_css_sidebar_background_color($value)
    {
        return '.sidebar-user-menu, .bg-sidebar{
            background-color: '.$value.';
        }';
    }

    // Sidebar Info Box Background Color
    public function get_admin_title_item_sidebar_box_background_color()
    {
        return __('Sidebar Info Box Background Color');
    }
    public function get_css_sidebar_box_background_color($value)
    {
        return '.sidebar-user-menu-box{
            background-color: '.$value.';
        }';
    }

    // Sidebar Info Box Border Color
    public function get_admin_title_item_sidebar_box_border_color()
    {
        return __('Sidebar Info Box Border Color');
    }
    public function get_css_sidebar_box_border_color($value)
    {
        return '.sidebar-user-menu-box{
            border-color: '.$value.';
        }';
    }

    // Sidebar Name Color
    public function get_admin_title_item_sidebar_name_text_color()
    {
        return __('Sidebar Name Color');
    }
    public function get_css_sidebar_name_text_color($value)
    {
        return '.sidebar-user-menu-name{
            color: '.$value.';
        }';
    }

    // Sidebar Sub Text Color
    public function get_admin_title_item_sidebar_sub_text_color()
    {
        return __('Sidebar Sub Text Color');
    }
    public function get_css_sidebar_sub_text_color($value)
    {
        return '.sidebar-user-menu-sub-text, a.sidebar-user-menu-sub-text:hover{
            color: '.$value.';
        }';
    }

    // Sidebar Link Text Color
    public function get_admin_title_item_sidebar_link_text_color()
    {
        return __('Sidebar Link Text Color');
    }
    public function get_css_sidebar_link_text_color($value)
    {
        return '.sidebar-user-menu-link{
            color: '.$value.';
        }';
    }

    // Sidebar Icon Color
    public function get_admin_title_item_sidebar_icon_color()
    {
        return __('Sidebar Icon Color');
    }
    public function get_css_sidebar_icon_color($value)
    {
        return '.sidebar-user-menu-icon{
            color: '.$value.';
        }';
    }

    // Sidebar Menu Title Color
    public function get_admin_title_item_sidebar_menu_title_color()
    {
        return __('Sidebar Menu Title Color');
    }
    public function get_css_sidebar_menu_title_color($value)
    {
        return '.sidebar-user-menu .sidebar-user-menu-title{
            color: '.$value.';
        }';
    }

    // Sidebar Menu Item Background Color
    public function get_admin_title_item_sidebar_menu_item_background_color()
    {
        return __('Sidebar Menu Item Background Color');
    }
    public function get_css_sidebar_menu_item_background_color($value)
    {
        return 'ul.sidebar-user-menu-list li .sidebar-user-menu-list-item-wrap,
        ul.sidebar-user-menu-child-list li .sidebar-user-menu-list-item-wrap{
            background-color: '.$value.';
        }';
    }

    // Sidebar Menu Item Text Color
    public function get_admin_title_item_sidebar_menu_item_text_color()
    {
        return __('Sidebar Menu Item Text Color');
    }
    public function get_css_sidebar_menu_item_text_color($value)
    {
        return 'ul.sidebar-user-menu-list li .sidebar-user-menu-list-item-wrap,
        ul.sidebar-user-menu-child-list li .sidebar-user-menu-list-item-wrap{
            color: '.$value.';
        }';
    }

    // Sidebar Menu Item Icon Color
    public function get_admin_title_item_sidebar_menu_item_icon_color()
    {
        return __('Sidebar Menu Item Icon Color');
    }
    public function get_css_sidebar_menu_item_icon_color($value)
    {
        return ['ul.sidebar-user-menu-list li .sidebar-user-menu-list-item-wrap .sidebar-main-menu-item-icon,
        ul.sidebar-user-menu-child-list li .sidebar-user-menu-list-item-wrap .sidebar-main-menu-item-icon{
            background-color: '.$value.';
        }',
        'ul.sidebar-user-menu-list li .sidebar-user-menu-list-item-wrap .sidebar-main-menu-item-icon-more,
        ul.sidebar-user-menu-child-list li .sidebar-user-menu-list-item-wrap .sidebar-main-menu-item-icon-more{
            color: '.$value.';
        }'];

    }

    // Sidebar Menu Item Background Hover Color
    public function get_admin_title_item_sidebar_menu_item_background_hover_color()
    {
        return __('Sidebar Menu Item Background Hover Color');
    }
    public function get_css_sidebar_menu_item_background_hover_color($value)
    {
        return 'ul.sidebar-user-menu-list li .sidebar-user-menu-list-item-wrap:hover,
        ul.sidebar-user-menu-child-list li .sidebar-user-menu-list-item-wrap:hover{
            background-color: '.$value.';
        }';
    }

    // Sidebar Menu Item Text Hover Color
    public function get_admin_title_item_sidebar_menu_item_text_hover_color()
    {
        return __('Sidebar Menu Item Text Hover Color');
    }
    public function get_css_sidebar_menu_item_text_hover_color($value)
    {
        return 'ul.sidebar-user-menu-list li .sidebar-user-menu-list-item-wrap:hover, 
        ul.sidebar-user-menu-list li .sidebar-user-menu-list-item-wrap:hover .sidebar-main-menu-item-icon-more,
        ul.sidebar-user-menu-child-list li .sidebar-user-menu-list-item-wrap:hover, 
        ul.sidebar-user-menu-child-list li .sidebar-user-menu-list-item-wrap:hover .sidebar-main-menu-item-icon-more{
            color: '.$value.';
        }';
    }

    // Sidebar Menu Item Icon Hover Color
    public function get_admin_title_item_sidebar_menu_item_icon_hover_color()
    {
        return __('Sidebar Menu Item Icon Hover Color');
    }
    public function get_css_sidebar_menu_item_icon_hover_color($value)
    {
        return ['.sidebar-user-menu ul.sidebar-user-menu-list li .sidebar-user-menu-list-item-wrap:hover .sidebar-main-menu-item-icon,
        .sidebar-user-menu ul.sidebar-user-menu-child-list li .sidebar-user-menu-list-item-wrap:hover .sidebar-main-menu-item-icon {
            background-color: '.$value.';
        }',
        '.sidebar-user-menu ul.sidebar-user-menu-list li .sidebar-user-menu-list-item-wrap:hover .sidebar-main-menu-item-icon-more,
        .sidebar-user-menu ul.sidebar-user-menu-child-list li .sidebar-user-menu-list-item-wrap:hover .sidebar-main-menu-item-icon-more{
            color: '.$value.';
        }'];
    }

    // Sidebar Menu Item Background Active Color
    public function get_admin_title_item_sidebar_menu_item_background_active_color()
    {
        return __('Sidebar Menu Item Background Active Color');
    }
    public function get_css_sidebar_menu_item_background_active_color($value)
    {
        return '.sidebar-user-menu ul.sidebar-user-menu-list li .sidebar-user-menu-list-item-wrap.router-link-exact-active,
        .sidebar-user-menu ul.sidebar-user-menu-child-list li .sidebar-user-menu-list-item-wrap.router-link-exact-active {
            background-color: '.$value.';
        }';
    }

    // Sidebar Menu Item Text Active Color
    public function get_admin_title_item_sidebar_menu_item_text_active_color()
    {
        return __('Sidebar Menu Item Text Active Color');
    }
    public function get_css_sidebar_menu_item_text_active_color($value)
    {
        return '.sidebar-user-menu ul.sidebar-user-menu-list li .sidebar-user-menu-list-item-wrap.router-link-exact-active,
        .sidebar-user-menu ul.sidebar-user-menu-child-list li .sidebar-user-menu-list-item-wrap.router-link-exact-active{
            color: '.$value.';
        }';
    }

    // Sidebar Menu Item Icon Active Color
    public function get_admin_title_item_sidebar_menu_item_icon_active_color()
    {
        return __('Sidebar Menu Item Icon Active Color');
    }
    public function get_css_sidebar_menu_item_icon_active_color($value)
    {
        return ['.sidebar-user-menu ul.sidebar-user-menu-list li .sidebar-user-menu-list-item-wrap.router-link-exact-active .sidebar-main-menu-item-icon,
        .sidebar-user-menu ul.sidebar-user-menu-child-list li .sidebar-user-menu-list-item-wrap.router-link-exact-active .sidebar-main-menu-item-icon {
            background-color: '.$value.';
        }',
        '.sidebar-user-menu ul.sidebar-user-menu-list li .sidebar-user-menu-list-item-wrap.router-link-exact-active .sidebar-main-menu-item-icon-more,
        .sidebar-user-menu ul.sidebar-user-menu-child-list li .sidebar-user-menu-list-item-wrap.router-link-exact-active .sidebar-main-menu-item-icon-more {
            color: '.$value.';
        }'];
    }

    // Main Content
    public function get_admin_title_group_main_content()
    {
        return __('Main Content');
    }
    public function get_admin_guide_image_group_main_content()
    {
        return $this->getThemeSettingGuideImage('main-content');
    }

    // Main Content Background Color
    public function get_admin_title_item_main_content_background_color()
    {
        return __('Main Content Background Color');
    }

    public function get_css_main_content_background_color($value)
    {
        return '.main-content-section {
            background-color: '.$value.';
        }';
    }

    // Main Content Border Color
    public function get_admin_title_item_main_content_border_color()
    {
        return __('Main Content Border Color');
    }

    public function get_css_main_content_border_color($value)
    {
        return '.main-content-section {
            border-color: '.$value.';
        }';
    }

    // Main Content Menu Background Color
    public function get_admin_title_item_main_content_menu_background_color()
    {
        return __('Main Content Menu Background Color');
    }
    public function get_css_main_content_menu_background_color($value)
    {
        return '.main-content-menu{
            background-color: '.$value.';
        }';
    }

    // Main Content Border Color
    public function get_admin_title_item_main_content_menu_border_color()
    {
        return __('Main Content Menu Border Color');
    }
    public function get_css_main_content_menu_border_color($value)
    {
        return ['.main-content-menu {
            border-color: '.$value.';
        }',
        '.chat-rooms-list{
            border-color: '.$value.';
        }'];
    }

    // Main Content Menu Item Text Color
    public function get_admin_title_item_main_content_menu_item_text_color()
    {
        return __('Main Content Menu Item Text Color');
    }
    public function get_css_main_content_menu_item_text_color($value)
    {
        return '.main-content-menu-item, .main-content-menu-item:hover{
            color: '.$value.';
        }';
    }

    // Main Content Menu Border Color
    public function get_admin_title_item_main_content_menu_item_border_color()
    {
        return __('Main Content Menu Item Border Color');
    }
    public function get_css_main_content_menu_item_border_color($value)
    {
        return '.main-content-menu-item .main-content-menu-item-wrap{
            border-color: '.$value.';
        }';
    }

    // Main Content Menu Text Active Color
    public function get_admin_title_item_main_content_menu_item_text_active_color()
    {
        return __('Main Content Menu Item Text Active Color');
    }
    public function get_css_main_content_menu_item_text_active_color($value)
    {
        return ['.main-content-menu-item.router-link-exact-active{
            color: '.$value.';
        }',
        '.main-content-menu-item.active{
            color: '.$value.';
        }'];
    }

    // Main Content Menu Border Active Color
    public function get_admin_title_item_main_content_menu_item_border_active_color()
    {
        return __('Main Content Menu Item Border Active Color');
    }
    public function get_css_main_content_menu_item_border_active_color($value)
    {
        return ['.main-content-menu-item.router-link-exact-active .main-content-menu-item-wrap{
            border-color: '.$value.';
        }',
        '.main-content-menu-item.active .main-content-menu-item-wrap{
            border-color: '.$value.';
        }'];
    }

    // Profile
    public function get_admin_title_group_profile()
    {
        return __('Profile');
    }
    public function get_admin_guide_image_group_profile()
    {
        return $this->getThemeSettingGuideImage('profile');
    }

    // Profile Header Background Color
    public function get_admin_title_item_profile_header_background_color()
    {
        return __('Profile Header Background Color');
    }
    public function get_css_profile_header_background_color($value)
    {
        return '.header-profile{
            background-color: '.$value.';
        }';
    }

    // Profile Header Name Text Color
    public function get_admin_title_item_profile_header_name_text_color()
    {
        return __('Profile Header Name Text Color');
    }
    public function get_css_profile_header_name_text_color($value)
    {
        return '.header-profile-name{
            color: '.$value.';
        }';
    }

    // Profile Header Username Text Color
    public function get_admin_title_item_profile_header_user_name_text_color()
    {
        return __('Profile Header Username Text Color');
    }
    public function get_css_profile_header_user_name_text_color($value)
    {
        return '.header-profile-username{
            color: '.$value.';
        }';
    }

    // Profile Header Count Text Color
    public function get_admin_title_item_profile_header_count_text_color()
    {
        return __('Profile Header Count Text Color');
    }
    public function get_css_profile_header_count_text_color($value)
    {
        return '.header-profile-count{
            color: '.$value.';
        }';
    }

    // Post
    public function get_admin_title_group_status()
    {
        return __('Post Status');
    }
    public function get_admin_guide_image_group_status()
    {
        return $this->getThemeSettingGuideImage('post-status');
    }
    
    // Post Status Background Color
    public function get_admin_title_item_status_post_modal_background_color()
    {
        return __('Post Status Background Color');
    }
    public function get_css_status_post_modal_background_color($value)
    {
        return [
            '.post-status-box{
                background-color: '.$value.';
            }',
            '.status-box-main{
                background-color: '.$value.';
            }'
        ];
    }

    // Post Status Border Color
    public function get_admin_title_item_status_post_modal_border_color()
    {
        return __('Post Status Border Color');
    }
    public function get_css_status_post_modal_border_color($value)
    {
        return [
            '.post-status-box{
                border-color: '.$value.';
            }',
            '.status-box-main{
                border-color: '.$value.';
            }'
        ];
    }

    // Post Status Close Icon Color
    public function get_admin_title_item_status_post_modal_close_icon_color()
    {
        return __('Post Status Close Icon Color');
    }
    public function get_css_status_post_modal_close_icon_color($value)
    {
        return '.post-status-modal.p-dialog .p-dialog-content .post-status-close-icon{
            color: '.$value.';
        }';
    }

    // Post Status User Text Color
    public function get_admin_title_item_status_post_modal_user_text_color()
    {
        return __('Post Status User Text Color');
    }
    public function get_css_status_post_modal_user_text_color($value)
    {
        return '.post-status-modal.p-dialog .p-dialog-content .status-box-main .status-box-header{
            color: '.$value.';
        }';
    }

    // Post Status Textarea Color
    public function get_admin_title_item_status_post_modal_textarea_color()
    {
        return __('Post Status Textarea Color');
    }
    public function get_css_status_post_modal_textarea_color($value)
    {
        return '.post-status-modal.p-dialog .p-dialog-content .status-box-main .status-box-message-input textarea{
            color: '.$value.';
        }';
    }

    // Post Status Textarea Placeholder Color
    public function get_admin_title_item_status_post_modal_textarea_placeholder_color()
    {
        return __('Post Status Textarea Placeholder Color');
    }
    public function get_css_status_post_modal_textarea_placeholder_color($value)
    {
        return [
            '.post-status-modal.p-dialog .p-dialog-content .status-box-main .status-box-message-input textarea::placeholder{
                color: '.$value.';
            }',
            '.post-status-box .post-status-box-placeholder{
                color: '.$value.';
            }'
        ];
    }

    // Post Status Add Icon Background Color
    public function get_admin_title_item_status_post_modal_background_add_icon_color()
    {
        return __('Post Status Add Icon Background Color');
    }
    public function get_css_status_post_modal_background_add_icon_color($value)
    {
        return '.add-images-icon{
            background-color: '.$value.';
        }';
    }

    // Post Status Add Icon Border Color
    public function get_admin_title_item_status_post_modal_border_add_icon_color()
    {
        return __('Post Status Add Icon Border Color');
    }
    public function get_css_status_post_modal_border_add_icon_color($value)
    {
        return '.add-images-icon{
            border-color: '.$value.';
        }';
    }

    // Post Status Add Icon Color
    public function get_admin_title_item_status_post_modal_add_icon_color()
    {
        return __('Post Status Add Icon Color');
    }
    public function get_css_status_post_modal_add_icon_color($value)
    {
        return '.add-images-icon{
            color: '.$value.';
        }';
    }

    // Post Status Privacy Button Background Color
    public function get_admin_title_item_status_post_privacy_button_background_color()
    {
        return __('Post Status Privacy Button Background Color');
    }
    public function get_css_status_post_privacy_button_background_color($value)
    {
        return '.privacy-button{
            background-color: '.$value.';
        }';
    }

    // Post Status Privacy Button Text Color
    public function get_admin_title_item_status_post_privacy_button_text_color()
    {
        return __('Post Status Privacy Button Text Color');
    }
    public function get_css_status_post_privacy_button_text_color($value)
    {
        return '.privacy-button{
            color: '.$value.';
        }';
    }

    // Post Status Footer Border Color
    public function get_admin_title_item_status_post_modal_action_border_color()
    {
        return __('Post Status Footer Border Color');
    }
    public function get_css_status_post_modal_action_border_color($value)
    {
        return [
            '.post-status-modal.p-dialog .p-dialog-content .status-box-action{
                border-color: '.$value.';
            }',
            '.post-status-box .status-box-action{
                border-color: '.$value.';
            }'
        ];
    }

    // Post Status Footer Background Color
    public function get_admin_title_item_status_post_modal_action_background_color()
    {
        return __('Post Status Footer Background Color');
    }
    public function get_css_status_post_modal_action_background_color($value)
    {
        return '.post-status-modal.p-dialog .p-dialog-content .status-box-action{
            background-color: '.$value.';
        }';
    }

    // Post Status Footer Icon Color
    public function get_admin_title_item_status_post_modal_action_icon_color()
    {
        return __('Post Status Footer Icon Color');
    }
    public function get_css_status_post_modal_action_icon_color($value)
    {
        return [
            '.post-status-modal.p-dialog .p-dialog-content .status-box-action .status-box-action-list-item{
                color: '.$value.';
            }',
            '.post-status-modal.p-dialog .p-dialog-content .status-box-action .status-box-action-list-item .emoji-picker{
                color: '.$value.';
            }'
        ];
    }

    // Post Status Footer Button Background Color
    public function get_admin_title_item_status_post_modal_action_button_background_color()
    {
        return __('Post Status Footer Button Background Color');
    }
    public function get_css_status_post_modal_action_button_background_color($value)
    {
        return '.post-status-box .status-box-action .status-box-action-button{
            background-color: '.$value.';
        }';
    }

    // Post Status Footer Button Text Color
    public function get_admin_title_item_status_post_modal_action_button_text_color()
    {
        return __('Post Status Footer Button Text Color');
    }
    public function get_css_status_post_modal_action_button_text_color($value)
    {
        return '.post-status-box .status-box-action .status-box-action-button{
            color: '.$value.';
        }';
    }

    // Post Status Footer Button Icon Color
    public function get_admin_title_item_status_post_modal_action_button_icon_color()
    {
        return __('Post Status Footer Button Icon Color');
    }
    public function get_css_status_post_modal_action_button_icon_color($value)
    {
        return '.post-status-box .status-box-action .status-box-action-button .status-box-action-button-icon{
            color: '.$value.';
        }';
    }

    // Footer
    public function get_admin_title_group_footer()
    {
        return __('Footer');
    }
    public function get_admin_guide_image_group_footer()
    {
        return $this->getThemeSettingGuideImage('footer');
    }

    // Footer Text Color
    public function get_admin_title_item_footer_text_color()
    {
        return __('Footer Text Color');
    }
    public function get_css_footer_text_color($value)
    {
        return '.main-footer-menu{
            color: '.$value.';
        }';
    }

    // Footer Border Color
    public function get_admin_title_item_footer_border_color()
    {
        return __('Footer Border Color');
    }
    public function get_css_footer_border_color($value)
    {
        return '.main-footer-menu{
            border-color: '.$value.';
        }';
    }

    // Footer Link Color
    public function get_admin_title_item_footer_link_color()
    {
        return __('Footer Link Color');
    }
    public function get_css_footer_link_color($value)
    {
        return '.main-footer-menu a, .main-footer-menu button{
            color: '.$value.';
        }';
    }

    // Footer Link Hover Color
    public function get_admin_title_item_footer_link_hover_color()
    {
        return __('Footer Link Hover Color');
    }
    public function get_css_footer_link_hover_color($value)
    {
        return '.main-footer-menu a:hover, .main-footer-menu button:hover{
            color: '.$value.';
        }';
    }

    // Footer Menu Text Color
    public function get_admin_title_item_footer_menu_item_text_color()
    {
        return __('Footer Menu Text Color');
    }
    public function get_css_footer_menu_item_text_color($value)
    {
        return '.main-footer-menu-list li a{
            color: '.$value.';
        }';
    }
    
    // Footer Menu Hover Color
    public function get_admin_title_item_footer_menu_item_hover_color()
    {
        return __('Footer Menu Hover Color');
    }
    public function get_css_footer_menu_item_hover_color($value)
    {
        return '.main-footer-menu-list li a:hover{
            color: '.$value.';
        }';
    }

    // Modal
    public function get_admin_title_group_modal()
    {
        return __('Modal');
    }
    public function get_admin_guide_image_group_modal()
    {
        return $this->getThemeSettingGuideImage('modal');
    }

    // Modal Background Color
    public function get_admin_title_item_modal_background_color()
    {
        return __('Modal Background Color');
    }
    public function get_css_modal_background_color($value)
    {
        return '.p-dialog{
            background-color: '.$value.';
        }';
    }

    // Modal Text Color
    public function get_admin_title_item_modal_text_color()
    {
        return __('Modal Text Color');
    }
    public function get_css_modal_text_color($value)
    {
        return '.p-dialog{
            color: '.$value.';
        }';
    }

    // Modal Icon Color
    public function get_admin_title_item_modal_icon_color()
    {
        return __('Modal Icon Color');
    }
    public function get_css_modal_icon_color($value)
    {
        return '.p-dialog .p-dialog-header .p-button-text.p-button-secondary{
            color: '.$value.';
        }';
    }

    // Modal Background Icon Color
    public function get_admin_title_item_modal_icon_background_color()
    {
        return __('Modal Background Icon Color');
    }
    public function get_css_modal_icon_background_color($value)
    {
        return '.p-dialog .p-dialog-header .p-button-text.p-button-secondary:not(:disabled):hover{
            background: '.$value.';
        }';
    }

    // Emoji Picker
    public function get_admin_title_group_emoji()
    {
        return __('Emoji Picker');
    }
    public function get_admin_guide_image_group_emoji()
    {
        return $this->getThemeSettingGuideImage('emoji');
    }

    // Emoji Icon Color
    public function get_admin_title_item_emoji_icon_color()
    {
        return __('Emoji Icon Color');
    }
    public function get_css_emoji_icon_color($value)
    {
        return '.emoji-picker{
            color: '.$value.';
        }';
    }

    // Emoji Box Background Color
    public function get_admin_title_item_emoji_box_background_color()
    {
        return __('Emoji Box Background Color');
    }
    public function get_css_emoji_box_background_color($value)
    {
        return '.emoji-picker-box {
            background-color: '.$value.';
        }';
    }

    // Emoji Box Text Color
    public function get_admin_title_item_emoji_box_text_color()
    {
        return __('Emoji Box Text Color');
    }
    public function get_css_emoji_box_text_color($value)
    {
        return '.emoji-picker-box-title {
            color: '.$value.';
        }';
    }

    // Form
    public function get_admin_title_group_form()
    {
        return __('Form');
    }
    public function get_admin_guide_image_group_form()
    {
        return $this->getThemeSettingGuideImage('form');
    }

    // Button Primary Background Color
    public function get_admin_title_item_button_primary_background_color()
    {
        return __('Button Primary Background Color');
    }
    public function get_css_button_primary_background_color($value)
    {
        return '.btn-primary, 
        .p-dialog .p-dialog-footer button.p-button.p-confirmdialog-accept-button{
            background-color: '.$value.';
        }';
    }

    // Button Primary Border Color
    public function get_admin_title_item_button_primary_border_color()
    {
        return __('Button Primary Border Color');
    }
    public function get_css_button_primary_border_color($value)
    {
        return '.btn-primary, 
        .p-dialog .p-dialog-footer button.p-button.p-confirmdialog-accept-button{
            border-color: '.$value.';
        }';
    }

    // Button Primary Text Color
    public function get_admin_title_item_button_primary_text_color()
    {
        return __('Button Primary Text Color');
    }

    public function get_css_button_primary_text_color($value)
    {
        return '.btn-primary,
        .btn-primary:hover,
        .p-dialog .p-dialog-footer button.p-button.p-confirmdialog-accept-button,
        .p-dialog .p-dialog-footer button.p-button.p-confirmdialog-accept-button:hover{
            color: '.$value.';
        }';
    }

    // Button Secondary Background Color
    public function get_admin_title_item_button_secondary_background_color()
    {
        return __('Button Secondary Background Color');
    }
    public function get_css_button_secondary_background_color($value)
    {
        return '.btn-secondary{
            background-color: '.$value.';
        }';
    }

    // Button Secondary Border Color
    public function get_admin_title_item_button_secondary_border_color()
    {
        return __('Button Secondary Border Color');
    }
    public function get_css_button_secondary_border_color($value)
    {
        return '.btn-secondary{
            border-color: '.$value.';
        }';
    }

    // Button Secondary Text Color
    public function get_admin_title_item_button_secondary_text_color()
    {
        return __('Button Secondary Text Color');
    }

    public function get_css_button_secondary_text_color($value)
    {
        return '.btn-secondary{
            color: '.$value.';
        }';
    }

    public function get_admin_title_item_button_outlined_background_color()
    {
        return __('Button Outlined Background Color');
    }

    public function get_css_button_outlined_background_color($value)
    {
        return '.btn-outlined, 
        .p-dialog .p-dialog-footer button.p-button.p-confirmdialog-reject-button{
            background-color: '.$value.';
        }';
    }

    public function get_admin_title_item_button_outlined_border_color()
    {
        return __('Button Outlined Border Color');
    }

    public function get_css_button_outlined_border_color($value)
    {
        return '.btn-outlined, 
        .p-dialog .p-dialog-footer button.p-button.p-confirmdialog-reject-button{
            border-color: '.$value.';
        }';
    }

    public function get_admin_title_item_button_outlined_text_color()
    {
        return __('Button Outlined Text Color');
    }

    public function get_css_button_outlined_text_color($value)
    {
        return '.btn-outlined,
        .btn-outlined:hover,
        .p-dialog .p-dialog-footer button.p-button.p-confirmdialog-reject-button,
        .p-dialog .p-dialog-footer button.p-button.p-confirmdialog-reject-button:hover{
            color: '.$value.';
        }';
    }

    // Button Secondary Outlined Background Color
    public function get_admin_title_item_button_secondary_outlined_background_color()
    {
        return __('Button Secondary Outlined Background Color');
    }
    public function get_css_button_secondary_outlined_background_color($value)
    {
        return '.btn-secondary-outlined{
            background-color: '.$value.';
        }';
    }

    // Button Secondary Outlined Border Color
    public function get_admin_title_item_button_secondary_outlined_border_color()
    {
        return __('Button Secondary Outlined Border Color');
    }
    public function get_css_button_secondary_outlined_border_color($value)
    {
        return '.btn-secondary-outlined{
            border-color: '.$value.';
        }';
    }

    // Button Secondary Outlined Text Color
    public function get_admin_title_item_button_secondary_outlined_text_color()
    {
        return __('Button Secondary Outlined Text Color');
    }

    public function get_css_button_secondary_outlined_text_color($value)
    {
        return '.btn-secondary-outlined, .btn-secondary-outlined:hover{
            color: '.$value.';
        }';
    }

    // Button Transparent
    public function get_admin_title_item_button_transparent_background_color()
    {
        return __('Button Transparent Background Color');
    }
    public function get_css_button_transparent_background_color($value)
    {
        return '.btn-transparent{
            background-color: '.$value.';
        }';
    }
    public function get_admin_title_item_button_transparent_border_color()
    {
        return __('Button Transparent Border Color');
    }
    public function get_css_button_transparent_border_color($value)
    {
        return '.btn-transparent{
            border-color: '.$value.';
        }';
    }
    public function get_admin_title_item_button_transparent_text_color()
    {
        return __('Button Transparent Text Color');
    }
    public function get_css_button_transparent_text_color($value)
    {
        return '.btn-transparent, .btn-danger:hover{
            color: '.$value.';
        }';
    }

    // Button Danger
    public function get_admin_title_item_button_danger_background_color()
    {
        return __('Button Danger Background Color');
    }
    public function get_css_button_danger_background_color($value)
    {
        return '.btn-danger{
            background-color: '.$value.';
        }';
    }
    public function get_admin_title_item_button_danger_border_color()
    {
        return __('Button Danger Border Color');
    }
    public function get_css_button_danger_border_color($value)
    {
        return '.btn-danger{
            border-color: '.$value.';
        }';
    }
    public function get_admin_title_item_button_danger_text_color()
    {
        return __('Button Danger Text Color');
    }
    public function get_css_button_danger_text_color($value)
    {
        return '.btn-danger, .btn-danger:hover{
            color: '.$value.';
        }';
    }

    // Button Danger Outlined
    public function get_admin_title_item_button_danger_outlined_background_color()
    {
        return __('Button Danger Outlined Background Color');
    }
    public function get_css_button_danger_outlined_background_color($value)
    {
        return '.btn-danger-outlined{
            background-color: '.$value.';
        }';
    }
    public function get_admin_title_item_button_danger_outlined_border_color()
    {
        return __('Button Danger Outlined Border Color');
    }
    public function get_css_button_danger_outlined_border_color($value)
    {
        return '.btn-danger-outlined{
            border-color: '.$value.';
        }';
    }
    public function get_admin_title_item_button_danger_outlined_text_color()
    {
        return __('Button Danger Outlined Text Color');
    }
    public function get_css_button_danger_outlined_text_color($value)
    {
        return '.btn-danger-outlined, .btn-danger-outlined:hover{
            color: '.$value.';
        }';
    }

    // Button Success
    public function get_admin_title_item_button_success_background_color()
    {
        return __('Button Success Background Color');
    }
    public function get_css_button_success_background_color($value)
    {
        return '.btn-success{
            background-color: '.$value.';
        }';
    }
    public function get_admin_title_item_button_success_border_color()
    {
        return __('Button Success Outlined Border Color');
    }
    public function get_css_button_success_border_color($value)
    {
        return '.btn-success{
            border-color: '.$value.';
        }';
    }
    public function get_admin_title_item_button_success_text_color()
    {
        return __('Button Success Text Color');
    }
    public function get_css_button_success_text_color($value)
    {
        return '.btn-success, .btn-success:hover{
            color: '.$value.';
        }';
    }

    // Button Warning
    public function get_admin_title_item_button_warning_background_color()
    {
        return __('Button Warning Background Color');
    }
    public function get_css_button_warning_background_color($value)
    {
        return '.btn-warning{
            background-color: '.$value.';
        }';
    }
    public function get_admin_title_item_button_warning_border_color()
    {
        return __('Button Warning Border Color');
    }
    public function get_css_button_warning_border_color($value)
    {
        return '.btn-warning{
            border-color: '.$value.';
        }';
    }
    public function get_admin_title_item_button_warning_text_color()
    {
        return __('Button Warning Text Color');
    }
    public function get_css_button_warning_text_color($value)
    {
        return '.btn-warning, .btn-warning:hover{
            color: '.$value.';
        }';
    }

    // Button Info
    public function get_admin_title_item_button_info_background_color()
    {
        return __('Button Info Background Color');
    }
    public function get_css_button_info_background_color($value)
    {
        return '.btn-info{
            background-color: '.$value.';
        }';
    }
    public function get_admin_title_item_button_info_border_color()
    {
        return __('Button Info Border Color');
    }
    public function get_css_button_info_border_color($value)
    {
        return '.btn-info{
            border-color: '.$value.';
        }';
    }
    public function get_admin_title_item_button_info_text_color()
    {
        return __('Button Info Text Color');
    }
    public function get_css_button_info_text_color($value)
    {
        return '.btn-info, .btn-info:hover{
            color: '.$value.';
        }';
    }

    // Input
    public function get_admin_title_item_input_background_color()
    {
        return __('Input Background Color');
    }

    public function get_css_input_background_color($value)
    {
        return '.p-inputtext, .bg-input-background-color, .base-input-tel .vue-tel-input, .p-textarea, .p-datepicker-panel, .p-datepicker-header, .p-autocomplete-input-multiple{
            background-color: '.$value.';
        }';
    }

    public function get_admin_title_item_input_border_color()
    {
        return __('Input Border Color');
    }

    public function get_css_input_border_color($value)
    {
        return '.p-inputtext, 
        .border-input-border-color, 
        .base-input-tel .vue-tel-input, 
        .p-textarea, 
        .p-datepicker-panel, 
        .p-autocomplete-input-multiple{
            border-color: '.$value.';
        }';
    }

    public function get_admin_title_item_input_text_color()
    {
        return __('Input Text Color');
    }

    public function get_css_input_text_color($value)
    {
        return '.p-inputtext, 
        .text-input-text-color, 
        .base-input-tel .vue-tel-input .vti__input, .p-textarea, 
        .p-datepicker-panel, .p-datepicker-header, .p-datepicker-select-month, .p-datepicker-weekday, .p-datepicker-select-year, .p-datepicker-day,
        .p-autocomplete-input-multiple{
            color: '.$value.';
        }';
    }

    public function get_admin_title_item_input_icon_color()
    {
        return __('Input Icon Color');
    }

    public function get_css_input_icon_color($value)
    {
        return '.p-inputtext-icon, 
        .text-input-icon-color, 
        .base-input-tel .vue-tel-input .vti__dropdown-arrow, 
        .p-select-dropdown, 
        .p-datepicker-header .p-button-text.p-button-secondary,
        .p-multiselect-dropdown,
        .p-treeselect-dropdown{
            color: '.$value.';
        }';
    }

    public function get_admin_title_item_input_placeholder_color()
    {
        return __('Input Placeholder Color');
    }

    public function get_css_input_placeholder_color($value)
    {
        return '.p-inputtext::placeholder, .base-input-tel .vue-tel-input .vti__input::placeholder, .p-textarea::placeholder{
            color: '.$value.';
        }';
    }

    public function get_admin_title_item_select_background_color()
    {
        return __('Select Background Color');
    }

    public function get_css_select_background_color($value)
    {
        return '.p-select, 
        .p-select-overlay, 
        .p-multiselect, 
        .p-multiselect-overlay,
        .p-treeselect,
        .p-tree{
            background-color: '.$value.';
        }';
    }

    public function get_admin_title_item_select_border_color()
    {
        return __('Select Border Color');
    }

    public function get_css_select_border_color($value)
    {
        return '.p-select,
        .p-select:not(.p-disabled):hover,
        .p-select-overlay,
        .p-multiselect,
        .p-multiselect:not(.p-disabled):hover,
        .p-multiselect-overlay,
        .p-treeselect,
        .p-treeselect:not(.p-disabled):hover{
            border-color: '.$value.';
        }';
    }

    public function get_admin_title_item_select_text_color()
    {
        return __('Select Text Color');
    }

    public function get_css_select_text_color($value)
    {
        return '.p-select-label, 
        .p-select-overlay, 
        .p-select-option, 
        .p-multiselect-label, 
        .p-multiselect-option, 
        .p-treeselect-label{
            color: '.$value.';
        }';
    }

    public function get_admin_title_item_select_text_color_hover()
    {
        return __('Select Text Hover Color');
    }

    public function get_css_select_text_color_hover($value)
    {
        return '.p-select-option:not(.p-select-option-selected):not(.p-disabled).p-focus,
        .p-datepicker-day:not(.p-datepicker-day-selected):not(.p-disabled):hover,
        .p-multiselect-option:not(.p-multiselect-option-selected):not(.p-disabled).p-focus,
        .p-tree-node-content.p-tree-node-selectable:not(.p-tree-node-selected):hover{
            color: '.$value.';
        }';
    }

    public function get_admin_title_item_select_background_color_hover()
    {
        return __('Select Text Hover Background Color');
    }

    public function get_css_select_background_color_hover($value)
    {
        return '.p-select-option:not(.p-select-option-selected):not(.p-disabled).p-focus,
        .p-datepicker-day:not(.p-datepicker-day-selected):not(.p-disabled):hover,
        .p-multiselect-option:not(.p-multiselect-option-selected):not(.p-disabled).p-focus,
        .p-tree-node-content.p-tree-node-selectable:not(.p-tree-node-selected):hover{
            background-color: '.$value.';
        }';
    }

    public function get_admin_title_item_select_text_color_selected()
    {
        return __('Select Text Selected Color');
    }

    public function get_css_select_text_color_selected($value)
    {
        return '.p-select-option.p-select-option-selected,
        .p-datepicker-day-selected,
        .p-tree-node-content.p-tree-node-selected{
            color: '.$value.';
        }';
    }

    public function get_admin_title_item_select_background_color_selected()
    {
        return __('Select Text Selected Background Color');
    }

    public function get_css_select_background_color_selected($value)
    {
        return '.p-select-option.p-select-option-selected,
        .p-datepicker-day-selected,
        .p-tree-node-content.p-tree-node-selected{
            background-color: '.$value.';
        }';
    }

    

    // Switch Color
    public function get_admin_title_item_switch_color()
    {
        return __('Switch Color');
    }
    public function get_css_switch_color($value)
    {
        return '.p-toggleswitch-slider, .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:hover) .p-toggleswitch-slider{
            background: '.$value.';
            border-color: '.$value.';
        }';
    }

    // Switch Active Color
    public function get_admin_title_item_switch_active_color()
    {
        return __('Switch Active Color');
    }
    public function get_css_switch_active_color($value)
    {
        return '.p-toggleswitch.p-toggleswitch-checked .p-toggleswitch-slider, .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:hover).p-toggleswitch-checked .p-toggleswitch-slider{
            background: '.$value.';
            border-color: '.$value.';
        }';
    }

    // Radio Button Color
    public function get_admin_title_item_radio_button_color()
    {
        return __('Radio Button Color');
    }
    public function get_css_radio_button_color($value)
    {
        return '.p-radiobutton-box, .p-radiobutton:not(.p-disabled):has(.p-radiobutton-input:hover) .p-radiobutton-box{
            border-color: '.$value.';
        }';
    }

    // Radio Button Active Color
    public function get_admin_title_item_radio_button_active_color()
    {
        return __('Radio Button Active Color');
    }
    public function get_css_radio_button_active_color($value)
    {
        return '.p-radiobutton-checked .p-radiobutton-box, .p-radiobutton-checked:not(.p-disabled):has(.p-radiobutton-input:hover) .p-radiobutton-box{
            background: '.$value.';
            border-color: '.$value.';
        }';
    }

    // Checkbox Button Color
    public function get_admin_title_item_checkbox_button_color()
    {
        return __('Checkbox Button Color');
    }
    public function get_css_checkbox_button_color($value)
    {
        return '.p-checkbox-box, .p-checkbox:not(.p-disabled):has(.p-checkbox-input:hover) .p-checkbox-box{
            border-color: '.$value.';
        }';
    }

    // Checkbox Button Active Color
    public function get_admin_title_item_checkbox_button_active_color()
    {
        return __('Checkbox Button Active Color');
    }
    public function get_css_checkbox_button_active_color($value)
    {
        return '.p-checkbox-checked .p-checkbox-box, .p-checkbox-checked:not(.p-disabled):has(.p-checkbox-input:hover) .p-checkbox-box{
            background: '.$value.';
            border-color: '.$value.';
        }';
    }

    // Selected Box Background Color
    public function get_admin_title_item_selected_background_color()
    {
        return __('Selected Box Background Color');
    }
    public function get_css_selected_background_color($value)
    {
        return '.p-chip{
            background-color: '.$value.';
        }';
    }

    // Selected Box Text Color
    public function get_admin_title_item_selected_text_color()
    {
        return __('Selected Box Text Color');
    }
    public function get_css_selected_text_color($value)
    {
        return '.p-chip{
            color: '.$value.';
        }';
    }

    // Selected Box Icon Color
    public function get_admin_title_item_selected_icon_color()
    {
        return __('Selected Box Icon Color');
    }
    public function get_css_selected_icon_color($value)
    {
        return '.p-chip-remove-icon{
            color: '.$value.';
        }';
    }

    // Header
    public function get_admin_title_group_header()
    {
        return __('Header');
    }
    public function get_admin_guide_image_group_header()
    {
        return $this->getThemeSettingGuideImage('header');
    }

    // Header Background Color
    public function get_admin_title_item_header_background_color()
    {
        return __('Header Background Color');
    }
    public function get_css_header_background_color($value)
    {
        return '.main-header{
            background-color: '.$value.';
        }';
    }

    // Header Border Color
    public function get_admin_title_item_header_border_scrolling_color()
    {
        return __('Header Border Color');
    }
    public function get_css_header_border_scrolling_color($value)
    {
        return 'body.documentScrolling .main-header{
            border-color: '.$value.';
        }';
    }

    // Header Icon Color
    public function get_admin_title_item_header_icon_color()
    {
        return __('Header Icon Color');
    }
    public function get_css_header_icon_color($value)
    {
        return '.header-icons-list .header-icons-list-item {
            color: '.$value.';
        }';
    }

    // Header Icon Badge Background Color
    public function get_admin_title_item_header_icon_badge_background_color()
    {
        return __('Header Icon Badge Background Color');
    }
    public function get_css_header_icon_badge_background_color($value)
    {
        return '.header-icons-badge{
            background-color: '.$value.';
        }';
    }

    // Header Icon Badge Text Color
    public function get_admin_title_item_header_icon_badge_text_color()
    {
        return __('Header Icon Badge Text Color');
    }
    public function get_css_header_icon_badge_text_color($value)
    {
        return '.header-icons-badge{
            color: '.$value.';
        }';
    }
    
    // Global Search
    public function get_admin_title_group_global_search()
    {
        return __('Global Search');
    }
    public function get_admin_guide_image_group_global_search()
    {
        return $this->getThemeSettingGuideImage('global_search');
    }

    // Global Search Icon Color
    public function get_admin_title_item_global_search_icon_color()
    {
        return __('Global Search Icon Color');
    }
    public function get_css_global_search_icon_color($value)
    {
        return '.global-search-header .global-search-header-input .p-inputtext-icon{
            color: '.$value.';
        }';
    }

    // Global Search Background Color
    public function get_admin_title_item_global_search_background_color()
    {
        return __('Global Search Background Color');
    }
    public function get_css_global_search_background_color($value)
    {
        return '.global-search-header .global-search-header-input input{
            background-color: '.$value.';
        }';
    }

    // Global Search Border Color
    public function get_admin_title_item_global_search_border_color()
    {
        return __('Global Search Border Color');
    }
    public function get_css_global_search_border_color($value)
    {
        return '.global-search-header .global-search-header-input input{
            border-color: '.$value.';
        }';
    }

    // Global Search Text Color
    public function get_admin_title_item_global_search_text_color()
    {
        return __('Global Search Text Color');
    }
    public function get_css_global_search_text_color($value)
    {
        return '.global-search-header .global-search-header-input input{
            color: '.$value.';
        }';
    }

    // Global Search Placeholder Color
    public function get_admin_title_item_global_search_placeholder_color()
    {
        return __('Global Search Placeholder Color');
    }
    public function get_css_global_search_placeholder_color($value)
    {
        return '.global-search-header .global-search-header-input input::placeholder{
            color: '.$value.';
        }';
    }

    // Global Search Suggestion Background Color
    public function get_admin_title_item_global_search_suggestion_background()
    {
        return __('Global Search Suggestion Background Color');
    }
    public function get_css_global_search_suggestion_background($value)
    {
        return '.global-search-suggestion-box{
            background-color: '.$value.';
        }';
    }

    // Global Search Suggestion Icon Color
    public function get_admin_title_item_global_search_suggestion_icon()
    {
        return __('Global Search Suggestion Icon Color');
    }
    public function get_css_global_search_suggestion_icon($value)
    {
        return '.global-search-suggestion-box .global-search-suggestion-box-icon{
            color: '.$value.';
        }';
    }

    // Global Search Suggestion Text Color
    public function get_admin_title_item_global_search_suggestion_text()
    {
        return __('Global Search Suggestion Text Color');
    }
    public function get_css_global_search_suggestion_text($value)
    {
        return '.global-search-suggestion-box .global-search-suggestion-box-text{
            color: '.$value.';
        }';
    }

    // Global Search Suggestion Title Color
    public function get_admin_title_item_global_search_result_title()
    {
        return __('Global Search Suggestion Title Color');
    }
    public function get_css_global_search_result_title($value)
    {
        return '.global-search-suggestion-box .global-search-suggestion-box-title{
            color: '.$value.';
        }';
    }

    // Global Search Suggestion Sub Text Color
    public function get_admin_title_item_global_search_result_sub()
    {
        return __('Global Search Suggestion Sub Text Color');
    }
    public function get_css_global_search_result_sub($value)
    {
        return '.global-search-suggestion-box .global-search-suggestion-box-sub{
            color: '.$value.';
        }';
    }

    // More
    public function get_admin_title_group_more()
    {
        return __('More');
    }
    public function get_admin_guide_image_group_more()
    {
        return $this->getThemeSettingGuideImage('more');
    }

    // Loading Icon Color
    public function get_admin_title_item_loading_icon_color()
    {
        return __('Loading Icon Color');
    }
    public function get_css_loading_icon_color($value)
    {
        return '.loading-icon{
            color: '.$value.';
        }';
    }

    // Table Header Background Color
    public function get_admin_title_item_table_header_background_color()
    {
        return __('Table Header Background Color');
    }
    public function get_css_table_header_background_color($value)
    {
        return '.bg-table-header-color{
            background-color: '.$value.';
        }';
    }

    // Table Header Text Color
    public function get_admin_title_item_table_header_text_color()
    {
        return __('Table Header Text Color');
    }
    public function get_css_table_header_text_color($value)
    {
        return '.bg-table-header-color{
            color: '.$value.';
        }';
    }

    // Table Body Background Color
    public function get_admin_title_item_table_body_background_color()
    {
        return __('Table Body Background Color');
    }
    public function get_css_table_body_background_color($value)
    {
        return 'table.s-table tbody tr td{
            background-color: '.$value.';
        }';
    }

     // Table Body Border Color
     public function get_admin_title_item_table_body_border_color()
     {
         return __('Table Body Border Color');
     }
     public function get_css_table_body_border_color($value)
     {
         return '.border-table-border-color{
             border-color: '.$value.';
         }';
     }

    // Table Body Text Color
    public function get_admin_title_item_table_body_text_color()
    {
        return __('Table Body Text Color');
    }
    public function get_css_table_body_text_color($value)
    {
        return 'table.s-table tbody tr td{
            color: '.$value.';
        }';
    }

    // Options Modal Background Color
    public function get_admin_title_item_options_modal_background_color()
    {
        return __('Options Modal Background Color');
    }
    public function get_css_options_modal_background_color($value)
    {
        return '.p-dialog.dropdown-menu-modal{
            background-color: '.$value.';
        }';
    }

    // Options Modal Border Color
    public function get_admin_title_item_options_modal_border_color()
    {
        return __('Options Modal Border Color');
    }
    public function get_css_options_modal_border_color($value)
    {
        return '.options-menu-modal-border{
            border-color: '.$value.';
        }';
    }

    // Options Modal Text Color
    public function get_admin_title_item_options_modal_text_color()
    {
        return __('Options Modal Text Color');
    }
    public function get_css_options_modal_text_color($value)
    {
        return '.options-menu-modal-text{
            color: '.$value.';
        }';
    }

    // Options Modal Sub Text Color
    public function get_admin_title_item_options_modal_sub_text_color()
    {
        return __('Options Modal Sub Text Color');
    }
    public function get_css_options_modal_sub_text_color($value)
    {
        return '.options-menu-modal-sub-text{
            color: '.$value.';
        }';
    }

    // Dropdown Options Background Color
    public function get_admin_title_item_dropdown_options_background_color()
    {
        return __('Dropdown Options Background Color');
    }
    public function get_css_dropdown_options_background_color($value)
    {
        return ['.dropdown-menu-box, .p-popover.dropdown-menu-box{
            background-color: '.$value.';
        }',
        '.p-popover.dropdown-menu-box:after{
            border-bottom-color: '.$value.';
        }'];
    }

     // Dropdown Options Border Color
    public function get_admin_title_item_dropdown_options_border_color()
    {
        return __('Dropdown Options Border Color');
    }
    public function get_css_dropdown_options_border_color($value)
    {
        return ['.dropdown-menu-box, .p-popover.dropdown-menu-box{
            border-color: '.$value.';
        }',
        '.p-popover.dropdown-menu-box:before{
            border-bottom-color: '.$value.';
        }'];
    }

    // Dropdown Options Text Color
    public function get_admin_title_item_dropdown_options_text_color()
    {
        return __('Dropdown Options Text Color');
    }
    public function get_css_dropdown_options_text_color($value)
    {
        return '.dropdown-menu-box li, .p-popover.dropdown-menu-box li{
            color: '.$value.';
        }';
    }

    // Dropdown Options Background Hover
    public function get_admin_title_item_dropdown_options_background_hover()
    {
        return __('Dropdown Options Background Hover');
    }
    public function get_css_dropdown_options_background_hover($value)
    {
        return '.dropdown-menu-box ul li:hover, .p-popover.dropdown-menu-box ul li:hover{
            background-color: '.$value.';
        }';
    }

    // Dropdown Options Text Color Hover
    public function get_admin_title_item_dropdown_options_text_color_hover()
    {
        return __('Dropdown Options Text Color Hover');
    }
    public function get_css_dropdown_options_text_color_hover($value)
    {
        return '.dropdown-menu-box ul li:hover, .p-popover.dropdown-menu-box ul li:hover{
            color: '.$value.';
        }';
    }

    // Dropdown Options Background Active
    public function get_admin_title_item_dropdown_options_background_active()
    {
        return __('Dropdown Options Background Active');
    }
    public function get_css_dropdown_options_background_active($value)
    {
        return '.dropdown-menu-box ul li.active, .p-popover.dropdown-menu-box ul li.active{
            background-color: '.$value.';
        }';
    }

    // Dropdown Options Text Color Active
    public function get_admin_title_item_dropdown_options_text_color_active()
    {
        return __('Dropdown Options Text Color Active');
    }
    public function get_css_dropdown_options_text_color_active($value)
    {
        return '.dropdown-menu-box ul li.active, .p-popover.dropdown-menu-box ul li.active{
            color: '.$value.';
        }';
    }

    // Tabs List Item Background Color
    public function get_admin_title_item_tab_list_item_background_color()
    {
        return __('Tabs List Item Background Color');
    }
    public function get_css_tab_list_item_background_color($value)
    {
        return '.tabs-list .tabs-list-item{
            background-color: '.$value.';
        }';
    }

    // Tabs List Item Text Color
    public function get_admin_title_item_tab_list_item_text_color()
    {
        return __('Tabs List Item Text Color');
    }
    public function get_css_tab_list_item_text_color($value)
    {
        return '.tabs-list .tabs-list-item{
            color: '.$value.';
        }';
    }

    // Tabs List Item Active Background Color
    public function get_admin_title_item_tab_list_item_active_background_color()
    {
        return __('Tabs List Item Active Background Color');
    }
    public function get_css_tab_list_item_active_background_color($value)
    {
        return '.tabs-list .tabs-list-item.active{
            background-color: '.$value.';
        }';
    }

    // Tabs List Item Active Text Color
    public function get_admin_title_item_tab_list_item_active_text_color()
    {
        return __('Tabs List Item Active Text Color');
    }
    public function get_css_tab_list_item_active_text_color($value)
    {
        return '.tabs-list .tabs-list-item.active{
            color: '.$value.';
        }';
    }

    // Tooltip Background Color
    public function get_admin_title_item_tooltip_background_color()
    {
        return __('Tooltip Background Color');
    }
    public function get_css_tooltip_background_color($value)
    {
        return ['.p-popover{
            background: '.$value.';
        }',
        '.p-popover:after{
            border-bottom-color: '.$value.';
        }'];
    }

    // Tooltip Border Color
    public function get_admin_title_item_tooltip_border_color()
    {
        return __('Tooltip Border Color');
    }
    public function get_css_tooltip_border_color($value)
    {
        return ['.p-popover{
            border-color: '.$value.';
        }',
        '.p-popover:before{
            border-bottom-color: '.$value.';
        }'];
    }

    // Tooltip Text Color
    public function get_admin_title_item_tooltip_text_color()
    {
        return __('Tooltip Text Color');
    }
    public function get_css_tooltip_text_color($value)
    {
        return '.p-popover{
            color: '.$value.';
        }';
    }

    // Progress Track Background Color
    public function get_admin_title_item_progress_track_background()
    {
        return __('Progress Track Background Color');
    }
    public function get_css_progress_track_background($value)
    {
        return '.p-progressbar{
            background: '.$value.';
        }';
    }

    // Progress Bar Background Color
    public function get_admin_title_item_progress_bar_background()
    {
        return __('Progress Bar Background Color');
    }
    public function get_css_progress_bar_background($value)
    {
        return '.p-progressbar-value{
            background: '.$value.';
        }';
    }

     // Progress Bar Text Color
     public function get_admin_title_item_progress_bar_text()
     {
         return __('Progress Bar Text Color');
     }
     public function get_css_progress_bar_text($value)
     {
         return '.p-progressbar-label{
             color: '.$value.';
         }';
     }

    // List Items
    public function get_admin_title_group_list_item()
    {
        return __('List Items');
    }
    public function get_admin_guide_image_group_list_item()
    {
        return $this->getThemeSettingGuideImage('list-items');
    }

    // List Items Title Color
    public function get_admin_title_item_list_items_title_text_color()
    {
        return __('List Items Title Color');
    }
    public function get_css_list_items_title_text_color($value)
    {
        return '.list_items_title_text_color{
            color: '.$value.';
        }';
    }

    // List Items Sub Text Color
    public function get_admin_title_item_list_items_sub_text_color()
    {
        return __('List Items Sub Text Color');
    }
    public function get_css_list_items_sub_text_color($value)
    {
        return '.list_items_sub_text_color{
            color: '.$value.';
        }';
    }

    // List Items Button Color
    public function get_admin_title_item_list_items_button()
    {
        return __('List Items Button Color');
    }
    public function get_css_list_items_button($value)
    {
        return 'button.list_items_button{
            color: '.$value.';
        }';
    }

    // List Items Button Hover Color
    public function get_admin_title_item_list_items_button_hover_color()
    {
        return __('List Items Button Hover Color');
    }
    public function get_css_list_items_button_hover_color($value)
    {
        return 'button.list_items_button:hover{
            color: '.$value.';
        }';
    }

    // List Box Items Background Color
    public function get_admin_title_item_list_box_items_background_color()
    {
        return __('List Box Items Background Color');
    }
    public function get_css_list_box_items_background_color($value)
    {
        return '.boxes-list .boxes-list-item{
            background-color: '.$value.';
        }';
    }

    // List Box Items Border Color
    public function get_admin_title_item_list_box_items_border_color()
    {
        return __('List Box Items Border Color');
    }
    public function get_css_list_box_items_border_color($value)
    {
        return '.boxes-list .boxes-list-item{
            border-color: '.$value.';
        }';
    }

    // List Box Items Text Color
    public function get_admin_title_item_list_box_items_text_color()
    {
        return __('List Box Items Text Color');
    }
    public function get_css_list_box_items_text_color($value)
    {
        return '.boxes-list .boxes-list-item{
            color: '.$value.';
        }';
    }

    // Grid Items
    public function get_admin_title_group_grid_item()
    {
        return __('Grid Items');
    }
    public function get_admin_guide_image_group_grid_item()
    {
        return $this->getThemeSettingGuideImage('grid-items');
    }

    // Grid Items Background Color
    public function get_admin_title_item_grid_items_background_color()
    {
        return __('Grid Item Background Color');
    }
    public function get_css_grid_items_background_color($value)
    {
        return '.grid-item{
            background-color: '.$value.';
        }';
    }

    // Grid Items Border Color
    public function get_admin_title_item_grid_items_border_color()
    {
        return __('Grid Item Border Color');
    }
    public function get_css_grid_items_border_color($value)
    {
        return '.grid-item{
            border-color: '.$value.';
        }';
    }


    // Grid Items Title Color
    public function get_admin_title_item_grid_items_title_color()
    {
        return __('Grid Item Title Color');
    }
    public function get_css_grid_items_title_color($value)
    {
        return '.grid-item .grid-item-title{
            color: '.$value.';
        }';
    }

    // Grid Items Sub Text Color
    public function get_admin_title_item_grid_items_sub_color()
    {
        return __('Grid Item Sub Text Color');
    }
    public function get_css_grid_items_sub_color($value)
    {
        return '.grid-item .grid-item-sub{
            color: '.$value.';
        }';
    }

    // Chat
    public function get_admin_title_group_chat()
    {
        return __('Chat');
    }
    public function get_admin_guide_image_group_chat()
    {
        return $this->getThemeSettingGuideImage('chat');
    }

    // Chat Title Color
    public function get_admin_title_item_chat_title_color()
    {
        return __('Chat Title Color');
    }
    public function get_css_chat_title_color($value)
    {
        return '.chat-rooms-list .page-title{
            color: '.$value.';
        }';
    }

    // Chat Username Color
    public function get_admin_title_item_chat_user_name_color()
    {
        return __('Chat Username Color');
    }
    public function get_css_chat_user_name_color($value)
    {
        return '.chat-header-wrap{
            color: '.$value.';
        }';
    }

    // Chat Options Icon Color
    public function get_admin_title_item_chat_options_icon_color()
    {
        return __('Chat Options Icon Color');
    }
    public function get_css_chat_options_icon_color($value)
    {
        return '.chat-header-wrap .chat-header-wrap-icon{
            color: '.$value.';
        }';
    }

    // Chat Date Time Color
    public function get_admin_title_item_chat_date_color()
    {
        return __('Chat Date Time Color');
    }
    public function get_css_chat_date_color($value)
    {
        return '.chat-date-time{
            color: '.$value.';
        }';
    }

    // Search Room Background Color
    public function get_admin_title_item_room_search_background()
    {
        return __('Search Room Background Color');
    }
    public function get_css_room_search_background($value)
    {
        return '.room_search_bar{
            background-color: '.$value.';
        }';
    }

    // Search Room Border Color
    public function get_admin_title_item_room_search_border()
    {
        return __('Search Room Border Color');
    }
    public function get_css_room_search_border($value)
    {
        return '.room_search_bar{
            border-color: '.$value.';
        }';
    }

    // Search Room Placeholder Color
    public function get_admin_title_item_room_search_placeholder()
    {
        return __('Search Room Placeholder Color');
    }
    public function get_css_room_search_placeholder($value)
    {
        return '.room_search_bar::placeholder{
            color: '.$value.';
        }';
    }

    // Search Room Text Color
    public function get_admin_title_item_room_search_text()
    {
        return __('Search Room Text Color');
    }
    public function get_css_room_search_text($value)
    {
        return '.room_search_bar{
            color: '.$value.';
        }';
    }

    // Room Background Color
    public function get_admin_title_item_room_item_background()
    {
        return __('Room Background Color');
    }
    public function get_css_room_item_background($value)
    {
        return '.room_items{
            background-color: '.$value.';
        }';
    }

    // Name Room Text Color
    public function get_admin_title_item_room_item_name()
    {
        return __('Name Room Text Color');
    }
    public function get_css_room_item_name($value)
    {
        return '.room_items_title_text{
            color: '.$value.';
        }';
    }

    // Date Room Text Color
    public function get_admin_title_item_room_item_date()
    {
        return __('Date Room Text Color');
    }
    public function get_css_room_item_date($value)
    {
        return '.room_items_date_text{
            color: '.$value.';
        }';
    }

     // Room Dropdown Icon Color
     public function get_admin_title_item_room_item_dropdown_icon()
     {
         return __('Room Dropdown Icon Color');
     }
     public function get_css_room_item_dropdown_icon($value)
     {
         return '.room_items_dropdown_icon{
             color: '.$value.';
         }';
     }

    // Room Background Color Hover
    public function get_admin_title_item_room_item_background_hover()
    {
        return __('Room Background Color Hover');
    }
    public function get_css_room_item_background_hover($value)
    {
        return '.room_items:hover{
            background-color: '.$value.';
        }';
    }

    // Name Room Text Color Hover
    public function get_admin_title_item_room_item_name_hover()
    {
        return __('Name Room Text Color Hover');
    }
    public function get_css_room_item_name_hover($value)
    {
        return '.room_items:hover .room_items_title_text{
            color: '.$value.';
        }';
    }

    // Date Room Text Color Hover
    public function get_admin_title_item_room_item_date_hover()
    {
        return __('Date Room Text Color Hover');
    }
    public function get_css_room_item_date_hover($value)
    {
        return '.room_items:hover .room_items_date_text{
            color: '.$value.';
        }';
    }

    // Room Background Color Active
    public function get_admin_title_item_room_item_background_active()
    {
        return __('Room Background Color Active');
    }
    public function get_css_room_item_background_active($value)
    {
        return '.room_items.room_items_active{
            background-color: '.$value.';
        }';
    }

    // Name Room Text Color Active
    public function get_admin_title_item_room_item_name_active()
    {
        return __('Name Room Text Color Active');
    }
    public function get_css_room_item_name_active($value)
    {
        return '.room_items.room_items_active .room_items_title_text{
            color: '.$value.';
        }';
    }

    // Date Room Text Color Active
    public function get_admin_title_item_room_item_date_active()
    {
        return __('Date Room Text Color Active');
    }
    public function get_css_room_item_date_active($value)
    {
        return '.room_items.room_items_active .room_items_date_text{
            color: '.$value.';
        }';
    }

    // Room Dot Color Active
    public function get_admin_title_item_room_item_dot_active()
    {
        return __('Room Dot Color Active');
    }
    public function get_css_room_item_dot_active($value)
    {
        return '.room_items_dot{
            background-color: '.$value.';
        }';
    }

    // Chat Messages Background Color
    public function get_admin_title_item_message_item_background()
    {
        return __('Chat Messages Background Color');
    }
    public function get_css_message_item_background($value)
    {
        return '.message-item{
            background-color: '.$value.';
        }';
    }

    // Chat Messages Border Color
    public function get_admin_title_item_message_item_border()
    {
        return __('Chat Messages Border Color');
    }
    public function get_css_message_item_border($value)
    {
        return '.message-item{
            border-color: '.$value.';
        }';
    }

    // Chat Messages Text Color
    public function get_admin_title_item_message_item_color()
    {
        return __('Chat Messages Text Color');
    }
    public function get_css_message_item_color($value)
    {
        return '.message-item{
            color: '.$value.';
        }';
    }

    // Chat Owner Messages Background Color
    public function get_admin_title_item_owner_message_item_background()
    {
        return __('Chat Owner Messages Background Color');
    }
    public function get_css_owner_message_item_background($value)
    {
        return '.owner-message-item{
            background-color: '.$value.';
        }';
    }

    // Chat Owner Messages Border Color
    public function get_admin_title_item_owner_message_item_border()
    {
        return __('Chat Owner Messages Border Color');
    }
    public function get_css_owner_message_item_border($value)
    {
        return '.owner-message-item{
            border-color: '.$value.';
        }';
    }

    // Chat Owner Messages Text Color
    public function get_admin_title_item_owner_message_item_color()
    {
        return __('Chat Owner Messages Text Color');
    }
    public function get_css_owner_message_item_color($value)
    {
        return '.owner-message-item{
            color: '.$value.';
        }';
    }

    // Waiting Request Background Color
    public function get_admin_title_item_waiting_request_background()
    {
        return __('Waiting Request Background Color');
    }
    public function get_css_waiting_request_background($value)
    {
        return '.waiting-request{
            background-color: '.$value.';
        }';
    }

    // Waiting Request Border Color
    public function get_admin_title_item_waiting_request_border()
    {
        return __('Waiting Request Border Color');
    }
    public function get_css_waiting_request_border($value)
    {
        return '.waiting-request{
            border-color: '.$value.';
        }';
    }

    // Waiting Request Text Color
    public function get_admin_title_item_waiting_request_text()
    {
        return __('Waiting Request Text Color');
    }
    public function get_css_waiting_request_text($value)
    {
        return '.waiting-request{
            color: '.$value.';
        }';
    }

    // Accept Button Background Color
    public function get_admin_title_item_accept_btn_background()
    {
        return __('Accept Button Background Color');
    }
    public function get_css_accept_btn_background($value)
    {
        return '.accept-request-btn{
            background-color: '.$value.';
        }';
    }

    // Accept Button Border Color
    public function get_admin_title_item_accept_btn_border()
    {
        return __('Accept Button Border Color');
    }
    public function get_css_accept_btn_border($value)
    {
        return '.accept-request-btn{
            border-color: '.$value.';
        }';
    }

    // Accept Button Text Color
    public function get_admin_title_item_accept_btn_text()
    {
        return __('Accept Button Text Color');
    }
    public function get_css_accept_btn_text($value)
    {
        return '.accept-request-btn{
            color: '.$value.';
        }';
    }

    // Block/Delete Button Background Color
    public function get_admin_title_item_delete_btn_background()
    {
        return __('Block/Delete Button Background Color');
    }
    public function get_css_delete_btn_background($value)
    {
        return '.decline-request-btn{
            background-color: '.$value.';
        }';
    }

    // Block/Delete Button Border Color
    public function get_admin_title_item_delete_btn_border()
    {
        return __('Block/Delete Button Border Color');
    }
    public function get_css_delete_btn_border($value)
    {
        return '.decline-request-btn{
            border-color: '.$value.';
        }';
    }

    // Block/Delete Button Text Color
    public function get_admin_title_item_delete_btn_text()
    {
        return __('Block/Delete Button Text Color');
    }
    public function get_css_delete_btn_text($value)
    {
        return '.decline-request-btn{
            color: '.$value.';
        }';
    }

    // Message Form Background Color
    public function get_admin_title_item_message_form_background_color()
    {
        return __('Message Form Background Color');
    }
    public function get_css_message_form_background_color($value)
    {
        return '.chat-form, .chat-form textarea {
            background-color: '.$value.';
        }';
    }

    // Message Form Placeholder Color
    public function get_admin_title_item_message_form_placeholder_color()
    {
        return __('Message Form Placeholder Color');
    }
    public function get_css_message_form_placeholder_color($value)
    {
        return '.chat-form textarea::placeholder {
            color: '.$value.';
        }';
    }

    // Message Form Text Color
    public function get_admin_title_item_message_form_text_color()
    {
        return __('Message Form Text Color');
    }
    public function get_css_message_form_text_color($value)
    {
        return '.chat-form textarea {
            color: '.$value.';
        }';
    }

    // Send Message Button
    public function get_admin_title_item_send_message_btn()
    {
        return __('Send Message Button');
    }
    public function get_css_send_message_btn($value)
    {
        return '.send-message-btn, .send-message-btn:hover {
            background-color: '.$value.';
            border-color: '.$value.';
        }';
    }

    // Scroll To Bottom Button Background Color
    public function get_admin_title_item_scroll_bottom_btn_background()
    {
        return __('Scroll To Bottom Button Background Color');
    }
    public function get_css_scroll_bottom_btn_background($value)
    {
        return 'button.scroll-bottom-btn {
            background-color: '.$value.';
        }';
    }

    // Scroll To Bottom Button Icon Color
    public function get_admin_title_item_scroll_bottom_btn_color()
    {
        return __('Scroll To Bottom Button Icon Color');
    }
    public function get_css_scroll_bottom_btn_color($value)
    {
        return 'button.scroll-bottom-btn {
            color: '.$value.';
        }';
    }

    // Chat Bubble Background Color
    public function get_admin_title_item_chat_bubble_background_color()
    {
        return __('Chat Bubble Background Color');
    }
    public function get_css_chat_bubble_background_color($value)
    {
        return '.chat-bubble{
            background-color: '.$value.';
        }';
    }

    // Chat Bubble Icon Color
    public function get_admin_title_item_chat_bubble_icon_color()
    {
        return __('Chat Bubble Icon Color');
    }
    public function get_css_chat_bubble_icon_color($value)
    {
        return '.chat-bubble{
            color: '.$value.';
        }';
    }

    // Notification
    public function get_admin_title_group_notification()
    {
        return __('Notification');
    }
    public function get_admin_guide_image_group_notification()
    {
        return $this->getThemeSettingGuideImage('notification');
    }

    // Notification Popup Background
    public function get_admin_title_item_notification_popup_color()
    {
        return __('Notification Popup Background');
    }
    public function get_css_notification_popup_color($value)
    {
        return '.dropdown-menu-box-notification{
            background-color: '.$value.';
        }';
    }

    // Notification Background Color
    public function get_admin_title_item_notification_background()
    {
        return __('Notification Background Color');
    }
    public function get_css_notification_background($value)
    {
        return '.notification-item{
            background-color: '.$value.';
        }';
    }

    // Notification Name Color
    public function get_admin_title_item_notification_name_color()
    {
        return __('Notification Name Color');
    }
    public function get_css_notification_name_color($value)
    {
        return '.notification-item .notification-item-name{
            color: '.$value.';
        }';
    }

    // Notification Message Color
    public function get_admin_title_item_notification_message_color()
    {
        return __('Notification Message Color');
    }
    public function get_css_notification_message_color($value)
    {
        return '.notification-item .notification-item-message{
            color: '.$value.';
        }';
    }

    // Notification Date Color
    public function get_admin_title_item_notification_date_color()
    {
        return __('Notification Date Color');
    }
    public function get_css_notification_date_color($value)
    {
        return '.notification-item .notification-item-date{
            color: '.$value.';
        }';
    }

    // Notification Active Background Color
    public function get_admin_title_item_notification_active_background()
    {
        return __('Notification Active Background Color');
    }
    public function get_css_notification_active_background($value)
    {
        return '.notification-item-active{
            background-color: '.$value.';
        }';
    }

    // Notification Active Name Color
    public function get_admin_title_item_notification_active_name_color()
    {
        return __('Notification Active Name Color');
    }
    public function get_css_notification_active_name_color($value)
    {
        return '.notification-item-active .notification-item-name{
            color: '.$value.';
        }';
    }

    // Notification Active Message Color
    public function get_admin_title_item_notification_active_message_color()
    {
        return __('Notification Active Message Color');
    }
    public function get_css_notification_active_message_color($value)
    {
        return '.notification-item-active .notification-item-message{
            color: '.$value.';
        }';
    }

    // Notification Active Date Color
    public function get_admin_title_item_notification_active_date_color()
    {
        return __('Notification Active Date Color');
    }
    public function get_css_notification_active_date_color($value)
    {
        return '.notification-item-active .notification-item-date{
            color: '.$value.';
        }';
    }

    // Notification Active Dot Color
    public function get_admin_title_item_notification_active_dot_color()
    {
        return __('Notification Active Dot Color');
    }
    public function get_css_notification_active_dot_color($value)
    {
        return '.notification-item-active .notification-item-dot{
            background-color: '.$value.';
        }';
    }

    // Notification View All Color
    public function get_admin_title_item_notification_view_all_color()
    {
        return __('Notification View All Color');
    }
    public function get_css_notification_view_all_color($value)
    {
        return '.notification-view-all, .notification-view-all:hover{
            color: '.$value.';
        }';
    }

    // Story
    public function get_admin_title_group_story()
    {
        return __('Story');
    }
    public function get_admin_guide_image_group_story()
    {
        return $this->getThemeSettingGuideImage('story');
    }

    // Create Story Background Color
    public function get_admin_title_item_create_box_background_color()
    {
        return __('Create Story Background Color');
    }
    public function get_css_create_box_background_color($value)
    {
        return '.create-story-box{
            background-color: '.$value.';
        }';
    }

    // Create Story Border Color
    public function get_admin_title_item_create_box_border_color()
    {
        return __('Create Story Border Color');
    }
    public function get_css_create_box_border_color($value)
    {
        return '.create-story-box{
            border-color: '.$value.';
        }';
    }

    // Create Story Button Color
    public function get_admin_title_item_create_btn_color()
    {
        return __('Create Story Button Color');
    }
    public function get_css_create_btn_color($value)
    {
        return '.create-story-btn{
            background-color: '.$value.';
        }';
    }

    // Create Story Text Color
    public function get_admin_title_item_create_box_text_color()
    {
        return __('Create Story Text Color');
    }
    public function get_css_create_box_text_color($value)
    {
        return '.create-story-box .create-story-box-bottom{
            color: '.$value.';
        }';
    }

    // Story Progress Bar Color
    public function get_admin_title_item_story_progress_bar_color()
    {
        return __('Story Progress Bar Color');
    }
    public function get_css_story_progress_bar_color($value)
    {
        return '.story-progress-bar{
            background-color: '.$value.';
        }';
    }

    // Story Remaining Bar Color
    public function get_admin_title_item_story_remaining_bar_color()
    {
        return __('Story Remaining Bar Color');
    }
    public function get_css_story_remaining_bar_color($value)
    {
        return '.story-remaining-bar{
            background-color: '.$value.';
        }';
    }

    // Story Name Color
    public function get_admin_title_item_story_name_color()
    {
        return __('Story Name Color');
    }
    public function get_css_story_name_color($value)
    {
        return [
            '.story-name{
                color: '.$value.';
            }',
            '.stories-list-scroll .stories-list-scroll-name{
                color: '.$value.';
            }'
        ];
    }
    
    // Story Date Color
    public function get_admin_title_item_story_date_color()
    {
        return __('Story Date Color');
    }
    public function get_css_story_date_color($value)
    {
        return '.story-date{
            color: '.$value.';
        }';
    }

    // Story Icon Color
    public function get_admin_title_item_story_icon_color()
    {
        return __('Story Icon Color');
    }
    public function get_css_story_icon_color($value)
    {
        return '.story-icon{
            color: '.$value.';
        }';
    }

    // Story Viewers Color
    public function get_admin_title_item_story_viewer_color()
    {
        return __('Story Viewers Color');
    }
    public function get_css_story_viewer_color($value)
    {
        return '.story-viewers{
            color: '.$value.';
            border-color: '.$value.';
        }';
    }

    // Mobile
    public function get_admin_title_group_mobile()
    {
        return __('Mobile');
    }
    public function get_admin_guide_image_group_mobile()
    {
        return $this->getThemeSettingGuideImage('mobile');
    }

    // Header Mobile Background Color
    public function get_admin_title_item_header_mobile_background_color()
    {
        return __('Header Mobile Background Color');
    }
    public function get_css_header_mobile_background_color($value)
    {
        return '.header-mobile{
            background-color: '.$value.';
        }';
    }

    // Header Mobile Icon Color
    public function get_admin_title_item_header_mobile_icon_color()
    {
        return __('Header Mobile Icon Color');
    }
    public function get_css_header_mobile_icon_color($value)
    {
        return '.header-mobile-icon{
            color: '.$value.';
        }';
    }

    // Footer Mobile Background Color
    public function get_admin_title_item_footer_mobile_background_color()
    {
        return __('Footer Mobile Background Color');
    }
    public function get_css_footer_mobile_background_color($value)
    {
        return '.footer-mobile{
            background-color: '.$value.';
        }';
    }

    // Footer Mobile Icon Color
    public function get_admin_title_item_footer_mobile_icon_color()
    {
        return __('Footer Mobile Icon Color');
    }
    public function get_css_footer_mobile_icon_color($value)
    {
        return '.footer-mobile .footer-mobile-item a, .footer-mobile .footer-mobile-item button{
            color: '.$value.';
        }';
    }

    // Footer Mobile Icon Active Color
    public function get_admin_title_item_footer_mobile_icon_active_color()
    {
        return __('Footer Mobile Icon Active Color');
    }
    public function get_css_footer_mobile_icon_active_color($value)
    {
        return '.footer-mobile .footer-mobile-item a.router-link-exact-active, .footer-mobile .footer-mobile-item button.router-link-exact-active{
            color: '.$value.';
        }';
    }

    // Footer Mobile Create Post Icon Color
    public function get_admin_title_item_footer_mobile_create_icon_color()
    {
        return __('Footer Mobile Create Post Icon Color');
    }
    public function get_css_footer_mobile_create_icon_color($value)
    {
        return '.create-post-btn-mobile{
            color: '.$value.';
        }';
    }

    // Footer Icon Badge Background Color
    public function get_admin_title_item_footer_icon_badge_background_color()
    {
        return __('Footer Icon Badge Background Color');
    }
    public function get_css_footer_icon_badge_background_color($value)
    {
        return '.footer-icons-badge{
            background-color: '.$value.';
        }';
    }

    // Footer Icon Badge Text Color
    public function get_admin_title_item_footer_icon_badge_text_color()
    {
        return __('Footer Icon Badge Text Color');
    }
    public function get_css_footer_icon_badge_text_color($value)
    {
        return '.footer-icons-badge{
            color: '.$value.';
        }';
    }

    // Cookies Warning
    public function get_admin_title_group_cookies_warning()
    {
        return __('Cookies Warning');
    }
    public function get_admin_guide_image_group_cookies_warning()
    {
        return $this->getThemeSettingGuideImage('cookies_warning');
    }

    // Cookies Warning Background Color
    public function get_admin_title_item_cookies_warning_background_color()
    {
        return __('Cookies Warning Background Color');
    }
    public function get_css_cookies_warning_background_color($value)
    {
        return '.cookies-warning{
            background-color: '.$value.';
        }';
    }

    // Cookies Warning Text Color
    public function get_admin_title_item_cookies_warning_text_color()
    {
        return __('Cookies Warning Text Color');
    }
    public function get_css_cookies_warning_text_color($value)
    {
        return '.cookies-warning{
            color: '.$value.';
        }';
    }

    // Mention Box
    public function get_admin_title_group_mention()
    {
        return __('Mention Box');
    }
    public function get_admin_guide_image_group_mention()
    {
        return $this->getThemeSettingGuideImage('mention');
    }

    // Mention Box Background Color
    public function get_admin_title_item_mention_background_color()
    {
        return __('Mention Box Background Color');
    }
    public function get_css_mention_background_color($value)
    {
        return '.dropdown-menu-box-mention{
            background-color: '.$value.';
        }';
    }

    // Mention Box Title Color
    public function get_admin_title_item_mention_title_color()
    {
        return __('Mention Box Title Color');
    }
    public function get_css_mention_title_color($value)
    {
        return '.dropdown-menu-box-mention .dropdown-menu-box-mention-title{
            color: '.$value.';
        }';
    }

    // Mention Box Sub Title Color
    public function get_admin_title_item_mention_sub_color()
    {
        return __('Mention Box Sub Title Color');
    }
    public function get_css_mention_sub_color($value)
    {
        return '.dropdown-menu-box-mention .dropdown-menu-box-mention-sub-title{
            color: '.$value.';
        }';
    }

    // Mention Box Background Active Color
    public function get_admin_title_item_mention_background_active()
    {
        return __('Mention Box Background Active Color');
    }
    public function get_css_mention_background_active($value)
    {
        return '.mention-selected{
            background-color: '.$value.';
        }';
    }

    // Fetched Link
    public function get_admin_title_group_fetch_link()
    {
        return __('Fetched Link');
    }
    public function get_admin_guide_image_group_fetch_link()
    {
        return $this->getThemeSettingGuideImage('fetch_link');
    }

    // Fetched Link Background Color
    public function get_admin_title_item_fetch_link_background_color()
    {
        return __('Fetched Link Background Color');
    }
    public function get_css_fetch_link_background_color($value)
    {
        return '.fetched-link{
            background-color: '.$value.';
        }';
    }

    // Fetched Link Text Color
    public function get_admin_title_item_fetch_link_text_color()
    {
        return __('Fetched Link Text Color');
    }
    public function get_css_fetch_link_text_color($value)
    {
        return '.fetched-link{
            color: '.$value.';
        }';
    }

    // Fetched Link Close Icon Color
    public function get_admin_title_item_fetch_link_close_icon_color()
    {
        return __('Fetched Link Close Icon Color');
    }
    public function get_css_fetch_link_close_icon_color($value)
    {
        return '.fetched-link-close{
            background-color: '.$value.';
        }';
    }

    // Video Player
    public function get_admin_title_group_video_player()
    {
        return __('Video Player');
    }
    public function get_admin_guide_image_group_video_player()
    {
        return $this->getThemeSettingGuideImage('video_player');
    }

    // Video Player Progress Color
    public function get_admin_title_item_video_player_progress_color()
    {
        return __('Video Player Progress Color');
    }
    public function get_css_video_player_progress_color($value)
    {
        return '.video-progress .p-slider .p-slider-range{
            background-color: '.$value.';
        }';
    }

    // Video Player Volume Color
    public function get_admin_title_item_video_player_volume()
    {
        return __('Video Player Volume Color');
    }
    public function get_css_video_player_volume($value)
    {
        return '.volume-controls .volume-controls-slider .p-slider .p-slider-range{
            background-color: '.$value.';
        }';
    }

    // Pages
    public function get_admin_title_group_pages()
    {
        return __('Pages');
    }
    public function get_admin_guide_image_group_pages()
    {
        return $this->getThemeSettingGuideImage('pages');
    }

    // Switch Page Background Color
    public function get_admin_title_item_pages_switch_color()
    {
        return __('Switch Page Background Color');
    }
    public function get_css_pages_switch_color($value)
    {
        return [
            '.switch-page-icon{
                background-color: '.$value.';
            }',
            '.switch-page-icon::after{
                background-color: '.$value.';
            }'
        ];
    }

    // Vibb
    public function get_admin_title_group_vibb()
    {
        return __('Vibb');
    }
    public function get_admin_guide_image_group_vibb()
    {
        return $this->getThemeSettingGuideImage('vibb');
    }

    // Vibb Menu Item Color
    public function get_admin_title_item_vibb_menu_color()
    {
        return __('Vibb Menu Item Color');
    }
    public function get_css_vibb_menu_color($value)
    {
        return '.main-content-menu.vibb-menu .main-content-menu-item{
            color: '.$value.';
        }';
    }

    // Vibb Menu Item Active Color
    public function get_admin_title_item_vibb_menu_active_color()
    {
        return __('Vibb Menu Item Active Color');
    }
    public function get_css_vibb_menu_active_color($value)
    {
        return '.main-content-menu.vibb-menu .main-content-menu-item.active{
            color: '.$value.';
        }';
    }

    // Vibb Menu Item Active Border Color
    public function get_admin_title_item_vibb_menu_active_border_color()
    {
        return __('Vibb Menu Item Active Border Color');
    }
    public function get_css_vibb_menu_active_border_color($value)
    {
        return '.main-content-menu.vibb-menu .main-content-menu-item.active .main-content-menu-item-wrap{
            border-color: '.$value.';
        }';
    }

    // Vibb Menu Icon Color
    public function get_admin_title_item_vibb_menu_icon_color()
    {
        return __('Vibb Menu Icon Color');
    }
    public function get_css_vibb_menu_icon_color($value)
    {
        return '.vibb-header .base-icon{
            color: '.$value.';
        }';
    }

    // Vibb Action Background Color
    public function get_admin_title_item_vibb_action_background_color()
    {
        return __('Vibb Action Background Color');
    }
    public function get_css_vibb_action_background_color($value)
    {
        return ['.vibb-main-action .vibb-main-action-item .vibb-main-action-item-icon{
            background-color: '.$value.';
        }',
        '.vibb-action-button {
            background-color: '.$value.';
        }'];
    }

    // Vibb Action Icon Color
    public function get_admin_title_item_vibb_action_icon_color()
    {
        return __('Vibb Action Icon Color');
    }
    public function get_css_vibb_action_icon_color($value)
    {
        return ['.vibb-main-action .vibb-main-action-item .vibb-main-action-item-icon{
            color: '.$value.';
        }',
        '.vibb-action-button{
            color: '.$value.';
        }'];
    }

    // Vibb Action Text Color
    public function get_admin_title_item_vibb_action_text_color()
    {
        return __('Vibb Action Text Color');
    }
    public function get_css_vibb_action_text_color($value)
    {
        return '.vibb-main-action .vibb-main-action-item .vibb-main-action-item-text{
            color: '.$value.';
        }';
    }

    // Vibb Icon Active Color
    public function get_admin_title_item_vibb_icon_active_color()
    {
        return __('Vibb Icon Like Active Color');
    }
    public function get_css_vibb_icon_active_color($value)
    {
        return '.vibb-main-action .vibb-main-action-item .vibb-main-action-like.is-liked{
            color: '.$value.';
        }';
    }

    // Vibb Icon Bookmark Active Color
    public function get_admin_title_item_vibb_icon_bookmark_active_color()
    {
        return __('Vibb Icon Bookmark Active Color');
    }
    public function get_css_vibb_icon_bookmark_active_color($value)
    {
        return '.vibb-main-action .vibb-main-action-item .vibb-main-action-bookmark.is-bookmarked{
            color: '.$value.';
        }';
    }

    // Tabs Menu
    public function get_admin_title_group_tabs_menu()
    {
        return __('Tabs Menu');
    }
    public function get_admin_guide_image_group_tabs_menu()
    {
        return $this->getThemeSettingGuideImage('tabs_menu');
    }

    // Tabs Menu Item Background Color
    public function get_admin_title_item_tabs_menu_item_background_color()
    {
        return __('Tabs Menu Item Background Color');
    }
    public function get_css_tabs_menu_item_background_color($value)
    {
        return '.main-content-menu-tab .main-content-menu-tab-item{
            background-color: '.$value.';
        }';
    }

    // Tabs Menu Item Icon Color
    public function get_admin_title_item_tabs_menu_item_icon_color()
    {
        return __('Tabs Menu Item Icon Color');
    }
    public function get_css_tabs_menu_item_icon_color($value)
    {
        return '.main-content-menu-tab .main-content-menu-tab-item .base-icon{
            color: '.$value.';
        }';
    }

    // Tabs Menu Item Text Color
    public function get_admin_title_item_tabs_menu_item_text_color()
    {
        return __('Tabs Menu Item Text Color');
    }
    public function get_css_tabs_menu_item_text_color($value)
    {
        return '.main-content-menu-tab .main-content-menu-tab-item{
            color: '.$value.';
        }';
    }

    // Tabs Menu Item Active Background Color
    public function get_admin_title_item_tabs_menu_item_active_background_color()
    {
        return __('Tabs Menu Item Active Background Color');
    }
    public function get_css_tabs_menu_item_active_background_color($value)
    {
        return '.main-content-menu-tab .main-content-menu-tab-item.active{
            background-color: '.$value.';
        }';
    }

    // Tabs Menu Item Active Icon Color
    public function get_admin_title_item_tabs_menu_item_active_icon_color()
    {
        return __('Tabs Menu Item Active Icon Color');
    }
    public function get_css_tabs_menu_item_active_icon_color($value)
    {
        return '.main-content-menu-tab .main-content-menu-tab-item.active .base-icon{
            color: '.$value.';
        }';
    }

    // Tabs Menu Item Active Text Color
    public function get_admin_title_item_tabs_menu_item_active_text_color()
    {
        return __('Tabs Menu Item Active Text Color');
    }
    public function get_css_tabs_menu_item_active_text_color($value)
    {
        return '.main-content-menu-tab .main-content-menu-tab-item.active{
            color: '.$value.';
        }';
    }

    // Invites
    public function get_admin_title_group_invites()
    {
        return __('Invites');
    }
    public function get_admin_guide_image_group_invites()
    {
        return $this->getThemeSettingGuideImage('invites');
    }

    // Invites Button Background Color
    public function get_admin_title_item_invites_button_background_color()
    {
        return __('Invites Button Background Color');
    }
    public function get_css_invites_button_background_color($value)
    {
        return '.invites-button{
            background-color: '.$value.';
        }';
    }

    // Invites Button Border Color
    public function get_admin_title_item_invites_button_border_color()
    {
        return __('Invites Button Border Color');
    }
    public function get_css_invites_button_border_color($value)
    {
        return '.invites-button{
            border-color: '.$value.';
        }';
    }

    // Invites Button Text Color
    public function get_admin_title_item_invites_button_text_color()
    {
        return __('Invites Button Text Color');
    }
    public function get_css_invites_button_text_color($value)
    {
        return '.invites-button{
            color: '.$value.';
        }';
    }

    // Invites Button Icon Color
    public function get_admin_title_item_invites_button_icon_color()
    {
        return __('Invites Button Icon Color');
    }
    public function get_css_invites_button_icon_color($value)
    {
        return '.invites-button .invites-button-icon{
            color: '.$value.';
        }';
    }

    // Badges
    public function get_admin_title_group_badges()
    {
        return __('Badges');
    }
    public function get_admin_guide_image_group_badges()
    {
        return $this->getThemeSettingGuideImage('badges');
    }

    // Ads Badge Background Color
    public function get_admin_title_item_ads_badge_background_color()
    {
        return __('Ads Badge Background Color');
    }
    public function get_css_ads_badge_background_color($value)
    {
        return '.feed-item-header-info-ads-badge{
            background-color: '.$value.';
        }';
    }

    // Ads Badge Text Color
    public function get_admin_title_item_ads_badge_text_color()
    {
        return __('Ads Badge Text Color');
    }
    public function get_css_ads_badge_text_color($value)
    {
        return '.feed-item-header-info-ads-badge{
            color: '.$value.';
        }';
    }

    // Label Badge Background Color
    public function get_admin_title_item_label_badge_background_color()
    {
        return __('Label Badge Background Color');
    }
    public function get_css_label_badge_background_color($value)
    {
        return '.feed-item-header-info-label-badge{
            background-color: '.$value.';
        }';
    }

    // Label Badge Text Color
    public function get_admin_title_item_label_badge_text_color()
    {
        return __('Label Badge Text Color');
    }
    public function get_css_label_badge_text_color($value)
    {
        return '.feed-item-header-info-label-badge{
            color: '.$value.';
        }';
    }

    // Pinned Badge Background Color
    public function get_admin_title_item_pinned_badge_background_color()
    {
        return __('Pinned Badge Background Color');
    }
    public function get_css_pinned_badge_background_color($value)
    {
        return '.feed-item-header-info-pinned-badge{
            background-color: '.$value.';
        }';
    }

    // Pinned Badge Text Color
    public function get_admin_title_item_pinned_badge_text_color()
    {
        return __('Pinned Badge Text Color');
    }
    public function get_css_pinned_badge_text_color($value)
    {
        return '.feed-item-header-info-pinned-badge{
            color: '.$value.';
        }';
    }

}
