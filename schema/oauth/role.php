<?php //-->
return [
    'singular'  => 'Role',
    'plural'    => 'Roles',
    'primary'   => 'role_id',
    'active'    => 'role_active',
    'created'   => 'role_created',
    'updated'   => 'role_updated',
    'relations' => [
        'auth' => [
            'primary' => 'auth_id',
            'many' => true
        ]
    ],
    'fields'    => [
        'role_name' => [
            'sql' => [
                'type'      => 'varchar',
                'length'    => 255,
                'required'  => true,
                'index'         => true,
                'searchable'    => true,
                'sortable'      => true,
                'filterable'    => true
            ],
            'elastic' => [
                'type' => 'string',
                'fields' => [
                    'keyword' => [
                        'type' => 'keyword'
                    ]
                ]
            ],
            'form' => [
                'label' => 'Role Name',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Administrator',
                ]
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Role Name is required'
                ]
            ],
            'list' => [
                'label' => 'Name'
            ],
            'test' => [
                'pass' => 'Apple',
                'fail' => ''
            ]
        ],
        'role_permissions' => [
            'sql' => [
                'type'      => 'json',
                'required'  => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Permissions',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Permissions',
                ]
            ]
        ],
        'role_type' => [
            'sql' => [
                'type'      => 'varchar',
                'length'    => 255,
                'required'  => false,
                'index'     => false
            ]
        ],
        'role_flag' => [
            'sql' => [
                'type' => 'int',
                'length' => 1,
                'default' => '0',
                'index' => true,
                'attribute' => 'unsigned'
            ],
            'elastic' => [
                'type' => 'integer'
            ]
        ]
    ],
    'fixtures' => [
        [
            'auth_id' => 1,
            'role_name' => 'Super Admin',
            'role_permissions' => json_encode([
                'admin:position:view',
                'admin:position:create',
                'admin:position:update',
                'admin:position:remove',
                'admin:utm:view',
                'admin:utm:create',
                'admin:utm:update',
                'admin:utm:remove',
                'admin:transaction:view',
                'admin:transaction:create',
                'admin:transaction:update',
                'admin:transaction:remove',
                'admin:transaction:export',
                'admin:service:view',
                'admin:service:create',
                'admin:service:update',
                'admin:service:remove',
                'admin:term:view',
                'admin:term:create',
                'admin:term:update',
                'admin:term:remove',
                'admin:post:view',
                'admin:post:create',
                'admin:post:update',
                'admin:post:remove',
                'admin:post:copy',
                'admin:profile:view',
                'admin:profile:create',
                'admin:profile:update',
                'admin:profile:remove',
                'admin:profile:send-claim-email',
                'admin:profile:export',
                'admin:profile:export-csv-format',
                'admin:profile:upload-csv',
                'admin:auth:view',
                'admin:auth:create',
                'admin:auth:update',
                'admin:auth:remove',
                'admin:article:view',
                'admin:article:create',
                'admin:article:update',
                'admin:article:remove',
                'admin:research:view',
                'admin:research:create',
                'admin:research:update',
                'admin:research:remove',
                'admin:feature:view',
                'admin:feature:create',
                'admin:feature:update',
                'admin:feature:remove'
            ]),
            'role_type' => 'admin',
            'role_created' => date('Y-m-d h:i:s'),
            'role_updated' => date('Y-m-d h:i:s')
        ]
    ]
];
