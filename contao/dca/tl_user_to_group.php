<?php

declare(strict_types=1);

use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_user_to_group'] = [
    'config' => [
        'sql' => [
            'keys' => ['id' => 'primary'],
        ],
    ],
    'fields' => [
        'id'       => [
            'sql' => [
                'type'          => Types::INTEGER,
                'notnull'       => true,
                'default'       => 0,
                'autoincrement' => true,
            ],
        ],
        'tstamp'   => [
            'sql' => [
                'type'    => Types::INTEGER,
                'notnull' => true,
                'default' => 0,
            ],
        ],
        'user_id'  => [
            'sql' => [
                'type'    => Types::INTEGER,
                'notnull' => true,
                'default' => 0,
            ],
        ],
        'group_id' => [
            'sql' => [
                'type'    => Types::INTEGER,
                'notnull' => true,
                'default' => 0,
            ],
        ],
    ],
];
