<?php

return [
    'roles' => [
        'label' => 'role|roles',
        'labels' => [
            'super_admin' => 'Super admin',
            'admin' => 'Admin',
        ],
    ],
    'users' => [
        'label' => 'user|users',
        'filters' => [],
        'attributes' => [
            'role' => 'Role',
            'name' => 'Name',
            'email' => 'Email',
        ],
        'columns' => [
            'profile_photo_url' => 'Photo',
            'has_two_factor_authentication' => '2FA',
        ],
    ],
];
