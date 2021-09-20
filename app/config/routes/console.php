<?php

return [
    'help' => [
        'controller' => 'Resistor\Console\Controller\ConsoleController',
        'action'     => 'help',
        'help'       => "Show the help screen"
    ],
    'version' => [
        'controller' => 'Resistor\Console\Controller\ConsoleController',
        'action'     => 'version',
        'help'       => "Show the version"
    ],
    '*' => [
        'controller' => 'Resistor\Console\Controller\ConsoleController',
        'action'     => 'error'
    ]
];