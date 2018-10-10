<?php
/**
 * Resistor Color Code Label Maker
 *
 * @link       https://github.com/nicksagona/resistor-color-codes
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2018 NOLA Interactive. (http://www.nolainteractive.com)
 */

/**
 * @namespace
 */
namespace Resistor\Model;

/**
 * Resistor color codes model
 *
 * @category   Resistor
 * @package    Resistor
 * @link       https://github.com/nicksagona/resistor-color-codes
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2018 NOLA Interactive. (http://www.nolainteractive.com)
 * @version    0.0.1-alpha
 */
class ColorCodes extends \Pop\Model\AbstractModel
{

    /**
     * Resistor values
     * @var array
     */
    protected $values = [];

    /**
     * Get resistor values
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Process resistor values
     *
     * @param  string $textValues
     * @param  string $fileValues
     * @return void
     */
    public function parseValues($textValues = null, $fileValues = null)
    {
        $values = [];
        if (null !== $textValues) {
            $values = array_merge($values, explode(PHP_EOL, $textValues));
        }
        if (null !== $fileValues) {
            $values = array_merge($values, explode(PHP_EOL, $fileValues));
        }

        foreach ($values as $key => $value) {

            $value     = strtolower(trim($value));
            $tolerance = null;
            if (strrpos($value, ',') !== false) {
                $tolerance = trim(substr($value, (strrpos($value, ',') + 1)));
                if (strpos($tolerance, '%') === false) {
                    $tolerance .= '%';
                }
                $value = substr($value, 0, strrpos($value, ','));
            }

            if (substr($value, -1) == 'k') {
                $value = trim(substr($value, 0, -1));
                $value = $value * 1000;
            } else if (substr($value, -1) == 'm') {
                $value = trim(substr($value, 0, -1));
                $value = $value * 1000000;
            } else if (substr($value, -1) == 'g') {
                $value = trim(substr($value, 0, -1));
                $value = $value * 1000000000;
            }

            if (!empty($value) && !in_array($value, $this->values)) {
                $first      = null;
                $second     = null;
                $third      = null;
                $multiplier = null;

                if ($value >= 1000000000) {
                    $short  = (float)($value / 1000000000);
                    $places = str_split(str_replace('.', '', $short));
                    $short .= 'G';
                    $multiplier = 1000000000;
                } else if (($value < 1000000000) && ($value >= 1000000)) {
                    $short  = (float)($value / 1000000);
                    $places = str_split(str_replace('.', '', $short));
                    $short .= 'M';
                    if (($value < 1000000000) && ($value >= 100000000)) {
                        $multiplier = 100000000;
                    } else if (($value < 100000000) && ($value >= 10000000)) {
                        $multiplier = 10000000;
                    } else {
                        $multiplier = 1000000;
                    }
                } else if (($value < 1000000) && ($value >= 1000)) {
                    $short  = (float)($value / 1000);
                    $places = str_split(str_replace('.', '', $short));
                    $short .= 'K';
                    if (($value < 1000000) && ($value >= 100000)) {
                        $multiplier = 100000;
                    } else if (($value < 100000) && ($value >= 10000)) {
                        $multiplier = 10000;
                    } else {
                        $multiplier = 1000;
                    }
                } else {
                    $short  = $value;
                    $places = str_split(str_replace('.', '', $short));
                }

                if (isset($places[0])) {
                    $first = $places[0];
                }
                if (isset($places[1])) {
                    $second = $places[1];
                }
                if (isset($places[2])) {
                    if (($places[2] == 0) && (null === $multiplier)) {
                        $multiplier = 10;
                    } else {
                        $third = $places[2];
                    }
                }

                $this->values[] = [
                    'first'      => $first,
                    'second'     => $second,
                    'third'      => $third,
                    'multiplier' => $multiplier,
                    'short'      => $short,
                    'long'       => $value,
                    'tolerance'  => $tolerance
                ];
            }
        }
    }

}