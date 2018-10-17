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
 * Resistor value model
 *
 * @category   Resistor
 * @package    Resistor
 * @link       https://github.com/nicksagona/resistor-color-codes
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2018 NOLA Interactive. (http://www.nolainteractive.com)
 * @version    0.2
 */
class Value
{

    /**
     * Resistor value
     * @var int
     */
    protected $value = null;

    /**
     * Resistor short-hand value
     * @var string
     */
    protected $shortHand = null;

    /**
     * Value places
     * @var array
     */
    protected $places = [];

    /**
     * First digit
     * @var int
     */
    protected $firstDigit = null;

    /**
     * Second digit
     * @var int
     */
    protected $secondDigit = null;

    /**
     * Third digit
     * @var int
     */
    protected $thirdDigit = null;

    /**
     * Multiplier
     * @var int
     */
    protected $multiplier = 1;

    /**
     * Tolerance
     * @var string
     */
    protected $tolerance = null;

    /**
     * Power
     * @var string
     */
    protected $power = null;

    /**
     * Composition
     * @var string
     */
    protected $comp = null;

    /**
     * Allowed composition values
     * @var array
     */
    protected $allowedComps = ['CF', 'MF', 'CC', 'MO', 'CE'];

    /**
     * Constructor
     *
     * Instantiate a model object
     *
     * @param string  $shortHand
     * @param boolean $forceThirdDigit
     * @param string  $suffix
     */
    public function __construct($shortHand = null, $forceThirdDigit = false, $suffix = null)
    {
        if (null !== $shortHand) {
            $this->setShorthand($shortHand, $forceThirdDigit, $suffix);
        }
    }

    /**
     * Load by value
     *
     * @param  int     $value
     * @param  string  $tolerance
     * @param  string  $power
     * @param  boolean $forceThirdDigit
     * @return self
     */
    public function loadByValue($value, $tolerance = null, $power = null, $forceThirdDigit = false)
    {
        $this->setValue($value);

        $places    = [];
        $valueNums = str_split(str_replace(',', '', $value));

        if (isset($valueNums[0])) {
            $places[] = $valueNums[0];
        }

        $places[] = (isset($valueNums[1])) ? $valueNums[1] : 0;

        if (!isset($valueNums[2]) && ($forceThirdDigit)) {
            $places[] = 0;
        } else if (isset($valueNums[2])) {
            if (($valueNums[2] != 0) || (($valueNums[2] == 0) && ($forceThirdDigit))) {
                $places[] = $valueNums[2];
            }
        }

        $this->setPlaces($places);

        if ($this->value >= 1000000000) {
            $shortHand = (float)($this->value / 1000000000) . 'G';
        } else if (($this->value < 1000000000) && ($this->value >= 1000000)) {
            $shortHand = (float)($this->value / 1000000) . 'M';
        } else if (($this->value < 1000000) && ($this->value >= 1000)) {
            $shortHand = (float)($this->value / 1000) . 'K';
        } else {
            $shortHand = (float)$this->value;
        }

        if (null !== $tolerance) {
            $this->setTolerance($tolerance);
            $shortHand .= ',' . $this->getTolerance();
        }

        if (null !== $power) {
            $this->setPower($power);
        }

        $this->shortHand = $shortHand;

        return $this;
    }

    /**
     * Load by digits
     *
     * @param  int    $first
     * @param  int    $second
     * @param  int    $third
     * @param  mixed  $multiplier
     * @param  string $tolerance
     * @param  string $power
     * @return self
     */
    public function loadByDigits($first, $second, $third = null, $multiplier = null, $tolerance = null, $power = null)
    {
        $places = [$first, $second];

        $this->setFirstDigit($first);
        $this->setSecondDigit($second);

        if (null !== $third) {
            $places[] = $third;
            $this->setThirdDigit($third);
        }
        if (null !== $multiplier) {
            $this->setMultiplier($multiplier);
        }
        if (null !== $tolerance) {
            $this->setTolerance($tolerance);
        }
        if (null !== $power) {
            $this->setPower($power);
        }

        if (null !== $multiplier) {
            $this->setValue((implode('', $places) * $multiplier));

            if ($this->value >= 1000000000) {
                $shortHand = (float)($this->value / 1000000000) . 'G';
            } else if (($this->value < 1000000000) && ($this->value >= 1000000)) {
                $shortHand = (float)($this->value / 1000000) . 'M';
            } else if (($this->value < 1000000) && ($this->value >= 1000)) {
                $shortHand = (float)($this->value / 1000) . 'K';
            } else {
                $shortHand = (float)$this->value;
            }

            if ($this->hasTolerance()) {
                $shortHand .= ',' . $this->getTolerance();
            }

            $this->shortHand = $shortHand;
        }

        $this->setPlaces($places);

        return $this;
    }

    /**
     * Set value
     *
     * @param  int $value
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Set short-hand value
     *
     * @param  string  $shortHand
     * @param  boolean $forceThirdDigit
     * @param  string  $suffix
     * @return self
     */
    public function setShorthand($shortHand, $forceThirdDigit = false, $suffix = null)
    {
        $shortHand = strtolower(trim($shortHand));
        $tolerance = null;
        $power     = null;
        $comp      = null;

        if (strpos($shortHand, ',') !== false) {
            $shortHandAry = explode(',', $shortHand);
            $shortHand = trim($shortHandAry[0]);
            unset($shortHandAry[0]);
            foreach ($shortHandAry as $shortHandValue) {
                $shortHandValue = trim($shortHandValue);
                if (stripos($shortHandValue, '%') !== false) {
                    $tolerance = $shortHandValue;
                }
                if (stripos($shortHandValue, 'w') !== false) {
                    $power = $shortHandValue;
                }
                if (in_array(strtoupper($shortHandValue), $this->allowedComps)) {
                    $comp = strtoupper($shortHandValue);
                }
            }
        }

        if (substr($shortHand, -1) == 'k') {
            $value     = trim(substr($shortHand, 0, -1));
            $places    = str_split(str_replace('.', '', $value));
            $shortHand = $value . 'K';
            $value     = (int)($value * 1000);
        } else if (substr($shortHand, -1) == 'm') {
            $value     = trim(substr($shortHand, 0, -1));
            $places    = str_split(str_replace('.', '', $value));
            $shortHand = $value . 'M';
            $value     = (int)($value * 1000000);
        } else if (substr($shortHand, -1) == 'g') {
            $value     = trim(substr($shortHand, 0, -1));
            $places    = str_split(str_replace('.', '', $value));
            $shortHand = $value . 'G';
            $value     = (int)($value * 1000000000);
        } else {
            $places    = str_split(str_replace('.', '', $shortHand));
            $value     = (float)trim($shortHand);
        }

        if (count($places) == 1) {
            $places[] = 0;
        } else if (count($places) > 3) {
            $places = array_slice($places, 0, 3);
        }

        if (($forceThirdDigit) && (count($places) == 2)) {
            $places[] = 0;
        }
        if (!($forceThirdDigit) && isset($places[2]) && ($places[2] == 0)) {
            unset($places[2]);
        }

        if (null !== $suffix) {
            if (($suffix == 'r-1000') && ($value < 1000)) {
                $shortHand .= ' R';
            } else if ($suffix == 'r-all') {
                $shortHand .= ' R';
            }
        }

        $this->shortHand = $shortHand;

        $this->setValue($value);
        $this->setPlaces($places);

        if (null !== $tolerance) {
            $this->setTolerance($tolerance);
        }
        if (null !== $power) {
            $this->setPower($power);
        }
        if (null !== $comp) {
            $this->setComp($comp);
        }

        return $this;
    }

    /**
     * Set places
     *
     * @param  array $places
     * @throws Exception
     * @return self
     */
    public function setPlaces(array $places)
    {
        if (count($places) < 2) {
            throw new Exception('Error: The number of places is not correct.');
        }
        $this->places = $places;

        for ($i = 0; $i < count($this->places); $i++) {
            switch ($i) {
                case 0:
                    $this->setFirstDigit($this->places[$i]);
                    break;
                case 1:
                    $this->setSecondDigit($this->places[$i]);
                    break;
                case 2:
                    $this->setThirdDigit($this->places[$i]);
                    break;
            }
        }

        if (null !== $this->value) {
            $placesNum  = (int)implode('', $this->places);
            $multiplier = 1;

            while ($placesNum != $this->value) {
                if ($placesNum < $this->value) {
                    $placesNum  *= 10;
                    $multiplier *= 10;
                } else {
                    $placesNum  /= 10;
                    $multiplier /= 10;
                }
            }

            $this->setMultiplier($multiplier);
        }

        return $this;
    }

    /**
     * Set first digit
     *
     * @param  int $first
     * @return self
     */
    public function setFirstDigit($first)
    {
        $this->firstDigit = (int)$first;
        return $this;
    }

    /**
     * Set second digit
     *
     * @param  int $second
     * @return self
     */
    public function setSecondDigit($second)
    {
        $this->secondDigit = (int)$second;
        return $this;
    }

    /**
     * Set third digit
     *
     * @param  int $third
     * @return self
     */
    public function setThirdDigit($third)
    {
        $this->thirdDigit = (int)$third;
        return $this;
    }

    /**
     * Set multiplier
     *
     * @param  mixed $multiplier
     * @return self
     */
    public function setMultiplier($multiplier)
    {
        $this->multiplier = $multiplier;
        return $this;
    }

    /**
     * Set tolerance
     *
     * @param  string $tolerance
     * @throws Exception
     * @return self
     */
    public function setTolerance($tolerance)
    {
        if (strpos($tolerance, '%') === false) {
            $tolerance .= '%';
        }

        if (!in_array($tolerance, ['1%', '2%', '0.5%', '0.25%', '0.1%', '5%', '10%'])) {
            throw new Exception('Error: Tolerance passed not allowed.');
        }

        $this->tolerance = $tolerance;
        return $this;
    }

    /**
     * Set power
     *
     * @param  string $power
     * @return self
     */
    public function setPower($power)
    {
        if (stripos($power, 'w') === false) {
            $power .= 'W';
        }

        $this->power = strtoupper($power);
        return $this;
    }

    /**
     * Set comp
     *
     * @param  string $comp
     * @return self
     */
    public function setComp($comp)
    {
        $comp = strtoupper($comp);
        if (in_array($comp, $this->allowedComps)) {
            $this->comp = $comp;
        }

        return $this;
    }

    /**
     * Get value
     *
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get short-hand value
     *
     * @return string
     */
    public function getShorthand()
    {
        return $this->shortHand;
    }

    /**
     * Get places
     *
     * @return array
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * Get first digit
     *
     * @return int
     */
    public function getFirstDigit()
    {
        return $this->firstDigit;
    }

    /**
     * Get second digit
     *
     * @return int
     */
    public function getSecondDigit()
    {
        return $this->secondDigit;
    }

    /**
     * Get third digit
     *
     * @return int
     */
    public function getThirdDigit()
    {
        return $this->thirdDigit;
    }

    /**
     * Get multiplier
     *
     * @return mixed
     */
    public function getMultiplier()
    {
        return $this->multiplier;
    }

    /**
     * Get tolerance
     *
     * @return string
     */
    public function getTolerance()
    {
        return $this->tolerance;
    }

    /**
     * Get power
     *
     * @return string
     */
    public function getPower()
    {
        return $this->power;
    }

    /**
     * Get comp
     *
     * @return string
     */
    public function getComp()
    {
        return $this->comp;
    }

    /**
     * Has value
     *
     * @return boolean
     */
    public function hasValue()
    {
        return (null !== $this->value);
    }

    /**
     * Has short-hand value
     *
     * @return string
     */
    public function hasShorthand()
    {
        return (null !== $this->shortHand);
    }

    /**
     * Has places
     *
     * @return boolean
     */
    public function hasPlaces()
    {
        return (!empty($this->places));
    }

    /**
     * Has first digit
     *
     * @return boolean
     */
    public function hasFirstDigit()
    {
        return (null !== $this->firstDigit);
    }

    /**
     * Has second digit
     *
     * @return boolean
     */
    public function hasSecondDigit()
    {
        return (null !== $this->secondDigit);
    }

    /**
     * Has third digit
     *
     * @return boolean
     */
    public function hasThirdDigit()
    {
        return (null !== $this->thirdDigit);
    }

    /**
     * Has multiplier
     *
     * @return int
     */
    public function hasMultiplier()
    {
        return (null !== $this->multiplier);
    }

    /**
     * Has tolerance
     *
     * @return boolean
     */
    public function hasTolerance()
    {
        return (null !== $this->tolerance);
    }

    /**
     * Has power
     *
     * @return boolean
     */
    public function hasPower()
    {
        return (null !== $this->power);
    }

    /**
     * Has comp
     *
     * @return boolean
     */
    public function hasComp()
    {
        return (null !== $this->comp);
    }

}