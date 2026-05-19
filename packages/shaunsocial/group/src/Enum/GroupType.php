<?php

namespace Packages\ShaunSocial\Group\Enum;

use Packages\ShaunSocial\Core\Traits\EnumToArray;

enum GroupType: int {
    use EnumToArray;
    
    case PUBLIC = 0;
    case PRIVATE = 1;
    case HIDDEN = 2;
}