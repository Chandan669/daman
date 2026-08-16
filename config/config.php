<?php
declare(strict_types=1);
return [
    'app_name' => 'Ghost News & Magazine',
    'app_version' => '1.0.0',
    'base_url' => '',
    'timezone' => 'Asia/Kolkata',
    'envato' => [
        'item_id' => (int) getenv('GHOST_PRODUCT_ITEM_ID') ?: 0,
        'license_server' => getenv('GHOST_LICENSE_SERVER') ?: '',
    ],
];
