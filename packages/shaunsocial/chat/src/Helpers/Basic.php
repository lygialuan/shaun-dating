<?php


if ( ! function_exists('getCodeFromTwoUser')) {
    function getCodeFromTwoUser($viewerId, $userId)
    {
        $code = '';
        if ($viewerId > $userId) {
            $code = $userId.'_'.$viewerId;
        } else {
            $code = $viewerId.'_'.$userId;
        }

        return $code;
    }
}