<?php


namespace Packages\ShaunSocial\Core\Enum;

enum UserDownloadStatus: string {
    case RUNNING = 'running';
    case ITEM_RUNNING = 'item_running';
    case EXPORTING = 'exporting';
    case ZIPPING = 'zipping';
    case DONE = 'done';
}