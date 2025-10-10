<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_user']['list']['global_operations']['sync_associate_groups'] = [
    'class'      => 'sync_associate_groups header_sync_all',
    'attributes' => 'onclick="Backend.getScrollOffset();"',
];
