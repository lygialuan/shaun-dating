<?php


namespace Packages\ShaunSocial\Advertising\Enum;

enum AdvertisingReportStatus: string {
    case PROCESS = 'process';
    case DONE = 'done';
    case STOP = 'stop';
}