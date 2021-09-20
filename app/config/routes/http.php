<?php

return [
    '[/]' => [
        'controller' => 'Resistor\Http\Controller\IndexController',
        'action'     => 'index'
    ],
    '*' => [
        'controller' => 'Resistor\Http\Controller\IndexController',
        'action'     => 'error'
    ]
];