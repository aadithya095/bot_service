<?php

return [

    /*
    |--------------------------------------------------------------------------
    | TICKET FLOW (STATE MACHINE)
    |--------------------------------------------------------------------------
    */
    'ticket' => [
        'keywords' => [
            'ticket',
            'issue',
            'problem',
            'help'
        ],

        'steps' => [

            'start' => [
                'response'  => 'Sure, please describe your issue.',
                'next_step' => 'waiting_description'
            ],

            'waiting_description' => [
                'response'  => 'Thanks. Do you want to attach any file?',
                'next_step' => 'waiting_attachment'
            ],

            'waiting_attachment' => [
                'response'  => 'Please upload the file now or type skip.',
                'next_step' => 'waiting_file'
            ],

            'waiting_file' => [
                'response'  => 'Your ticket has been created successfully.',
                'next_step' => null
            ]

        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | INVOICE COMMAND (DYNAMIC ACTION)
    |--------------------------------------------------------------------------
    */
    'invoice' => [
        'keywords' => [
            'invoice',
            'invoices',
            'bill',
            'payment'
        ],

        'steps' => [
            'start' => [
                'action'    => 'show_invoices',
                'next_step' => null
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | MEETING COMMAND (DYNAMIC ACTION)
    |--------------------------------------------------------------------------
    */
    'meeting' => [
        'keywords' => [
            'meeting',
            'meetings',
            'today meeting',
            'today meetings'
        ],

        'steps' => [
            'start' => [
                'action'    => 'show_meetings',
                'next_step' => null
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | FORUM COMMAND (DYNAMIC ACTION)
    |--------------------------------------------------------------------------
    */
    'forum' => [
        'keywords' => [
            'forum',
            'forums',
            'discussion'
        ],

        'steps' => [
            'start' => [
                'action'    => 'show_forums',
                'next_step' => null
            ]
        ]
    ]

];