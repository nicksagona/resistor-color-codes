<?php

namespace Resistor\Test;

use PHPUnit\Framework\TestCase;

class ResistorTest extends TestCase
{

    public function testConstructor()
    {
        $resistor = new \Resistor\Module();
        $this->assertInstanceOf('Resistor\Module', $resistor);
    }

}