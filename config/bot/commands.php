<?php

return [

    'ticket' => [
        'keywords' => [
            'ticket',
            'issue',
            'problem',
            'help'
        ],

        'steps' => [
            'start' => [
                'response' => 'Sure, please describe your issue.',
                'next_step' => 'waiting_description'
            ],

            'waiting_description' => [
                'response' => 'Thanks. Do you want to attach any file?',
                'next_step' => 'waiting_attachment'
            ],

            'waiting_attachment' => [
                'response' => 'Your ticket has been created.',
                'next_step' => null
            ]
        ]
    ],

    'invoice' => [
        'keywords' => [
            'invoice',
            'bill',
            'payment'
        ],

        'steps' => [
            'start' => [
                'response' => 'Please select invoice number.',
                'next_step' => 'waiting_selection'
            ]
        ]
    ]

];
