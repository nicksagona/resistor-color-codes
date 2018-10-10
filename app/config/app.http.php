<?php
/**
 * TitleNova HTTP Configuration File
 */
return [
    'routes' => [
        '[/]' => [
            'controller' => 'Resistor\Controller\IndexController',
            'action'     => 'index'
        ],
        '*' => [
            'controller' => 'Resistor\Controller\IndexController',
            'action'     => 'error'
        ]
    ]
];