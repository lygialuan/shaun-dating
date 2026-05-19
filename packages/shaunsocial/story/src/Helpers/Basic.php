<?php 
if (! function_exists('getStoryShareUserMax')) {
    function getStoryShareUserMax()
    {       
        $count = setting('story.share_user_max');
        if ($count < 1 || $count > 20) {
            $count = 10;
        }

        return $count;
    }
}