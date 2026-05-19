<?php


namespace Packages\ShaunSocial\Core\Enum;

enum SitemapStep: string {
    case GET_URL = 'get_url';
    case GENERATE_SUB_FILE = 'generate_sub_file';
    case GENERATE_MAIN_FILE = 'generate_main_file';
}