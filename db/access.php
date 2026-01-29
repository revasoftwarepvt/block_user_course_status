<?php
$capabilities = [
    'block/course_status:addinstance' => [
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => [
            'student' => CAP_ALLOW
        ],
    ],
];
