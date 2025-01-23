<?php

return [
    'global' => [
        'buttons' => [
            'import' => 'Import',
            'export' => 'Export',
        ],
        'labels' => [
            'row' => 'Row',
            'never' => 'Never',
        ],
        'attributes' => [
            'id' => 'ID',
            'uuid' => 'UUID',
            'name' => 'Name',
            'title' => 'Title',
            'slug' => 'slug',
            'created_at' => 'Created Date',
            'updated_at' => 'Last Modified Date',
            'updated_at_short' => 'Last Modified',
        ],
        'columns' => [
            'users_count' => '# Users',
        ],
        'alerts' => [
            'one_or_more_rows_has_issues' => 'One or more rows has issues.',
        ],
    ],
    'navigation_groups' => [
        'settings' => 'Settings',
        'user_management' => 'User management',
    ],
    'roles' => [
        'titles' => [
            'permissions' => 'Permissions',
        ],
        'labels' => [
            'role_can' => ':role can :permission',
            'role_cannot' => ':role cannot :permission',
        ],
        'columns' => [
            'roles_count' => '# Roles',
        ],
    ],
    'health_check_results' => [
        'model' => 'Health Check Results',
    ],
    'activities' => [
        'model' => 'activity|activities',
        'attributes' => [
            'type' => 'Type',
            'description' => 'Description',
            'subject' => 'Subject',
            'causer' => 'Causer',
            'event' => 'Event',
            'Old' => 'Old',
            'New' => 'New',
        ],
    ],
    'language_lines' => [
        'model' => 'translation|translations',
        'attributes' => [
            'group' => 'Group',
            'key' => 'Code',
            'text' => 'Text',
            'excel' => 'Spreadsheet',
        ],
        'labels' => [
            'translations' => 'Translations',
            'original_translation' => 'Original translation',
        ],
        'buttons' => [
            'scan' => 'Scan',
        ],
        'alerts' => [
            'translations_imported' => 'Translations imported',
            'translations_scanned' => 'Translations scanned',
        ],
        'helpers' => [
            'export_first' => 'Start out by importing translations first.',
            'delete_translation' => 'Deleting this translation will reset it to its original value.',
        ],
    ],
    'widgets' => [
        'latest_users' => [
            'title' => 'Latest users',
        ],
    ],
];
