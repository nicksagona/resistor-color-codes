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

use Pop\Pdf\Document;
use Pop\Pdf\Document\Page;
use Pop\Image;

/**
 * Resistor label model
 *
 * @category   Resistor
 * @package    Resistor
 * @link       https://github.com/nicksagona/resistor-color-codes
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2018 NOLA Interactive. (http://www.nolainteractive.com)
 * @version    1.0.0
 */
class Label extends \Pop\Model\AbstractModel
{

    /**
     * Resistor values
     * @var array
     */
    protected $values = [];

    /**
     * Color values
     * @var array
     */
    protected $colors = [
        '0'  => [0, 0, 0],       // 000000 Black
        '1'  => [173, 115, 57],  // ad7339 Brown
        '2'  => [222, 5, 5],     // de0505 Red
        '3'  => [244, 132, 50],  // f48432 Orange
        '4'  => [244, 224, 2],   // f4e002 Yellow
        '5'  => [35, 159, 15],   // 239f0f Green
        '6'  => [37, 93, 195],   // 255dc3 Blue
        '7'  => [169, 0, 167],   // a900a7 Violet
        '8'  => [156, 156, 156], // 9c9c9c Gray
        '9'  => [255, 255, 255], // ffffff White
        '10' => [227, 178, 9],   // e3b209 Gold
        '11' => [206, 231, 231]  // cee7e7 Silver
    ];

    /**
     * Multiplier values
     * @var array
     */
    protected $multipliers = [
        '1'          => '0',
        '10'         => '1',
        '100'        => '2',
        '1000'       => '3',
        '10000'      => '4',
        '100000'     => '5',
        '1000000'    => '6',
        '10000000'   => '7',
        '100000000'  => '8',
        '1000000000' => '9',
        '0.1'        => '10',
        '0.01'       => '11'
    ];

    /**
     * Tolerance values
     * @var array
     */
    protected $tolerance = [
        '1%'    => '1',
        '2%'    => '2',
        '0.5%'  => '5',
        '0.25%' => '6',
        '0.1%'  => '7',
        '5%'    => '10',
        '10%'   => '11'
    ];

    /**
     * Text color values
     * @var array
     */
    protected $textColors = [
        '0'  => [255, 255, 255],  // ffffff White
        '1'  => [0, 0, 0],        // 000000 Black
        '2'  => [255, 255, 255],  // ffffff White
        '3'  => [0, 0, 0],        // 000000 Black
        '4'  => [0, 0, 0],        // 000000 Black
        '5'  => [255, 255, 255],  // ffffff White
        '6'  => [255, 255, 255],  // ffffff White
        '7'  => [255, 255, 255],  // ffffff White
        '8'  => [0, 0, 0],        // 000000 Black
        '9'  => [0, 0, 0],        // 000000 Black
        '10' => [0, 0, 0],        // 000000 Black
        '11' => [0, 0, 0]         // 000000 Black
    ];

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
     * Get color values
     *
     * @return array
     */
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * Get text color values
     *
     * @return array
     */
    public function getTextColors()
    {
        return $this->textColors;
    }

    /**
     * Get multipliers
     *
     * @return array
     */
    public function getMultipliers()
    {
        return $this->multipliers;
    }

    /**
     * Get tolerance
     *
     * @return array
     */
    public function getTolerance()
    {
        return $this->tolerance;
    }

    /**
     * Process resistor values
     *
     * @param  string  $textValues
     * @param  string  $fileValues
     * @param  boolean $forceThirdDigit
     * @param  string  $suffix
     * @return void
     */
    public function parseValues($textValues = null, $fileValues = null, $forceThirdDigit = false, $suffix = null)
    {
        $values = [];
        if (null !== $textValues) {
            $values = array_merge($values, explode(PHP_EOL, $textValues));
        }
        if (null !== $fileValues) {
            $values = array_merge($values, explode(PHP_EOL, $fileValues));
        }

        foreach ($values as $value) {
            $value = trim($value);
            if (!empty($value)) {
                $resistorValue = new Value();
                if (is_numeric($value) && ($value >= 1000)) {
                    $resistorValue->loadByValue($value, null, $forceThirdDigit, $suffix);
                } else {
                    $resistorValue->setShorthand($value, $forceThirdDigit, $suffix);
                }
                $this->values[] = $resistorValue;
            }
        }
    }

    /**
     * Generate PDF labels
     *
     * @return Document
     */
    public function generatePdf()
    {
        $doc  = new Document();
        $doc->addFont(new Document\Font('Arial'));
        $doc->addFont(new Document\Font('Arial,Bold'));
        $doc->setCompression(true);

        $metadata = new Document\Metadata();
        $metadata->setTitle('Resistor Color Code Label Maker');

        $doc->setMetadata($metadata);

        for ($i = 0; $i < count($this->values); $i++) {
            if (($i % 24) == 0) {
                $page = new Page(Page::LETTER);
                $x    = 36;
                $y    = 688;
                $doc->addPage($page);
            }

            $rowY = floor($i / 3);

            if ($rowY > 7) {
                $rowY -= (($doc->getNumberOfPages() - 1) * 8);
            }

            $curX = ($x + (($i % 3) * 193));
            $curY = ($y - ($rowY * 90));

            $rectangle = new Page\Path(Page\Path::STROKE);
            $rectangle->setStrokeColor(new Page\Color\Rgb(0, 0, 0))->setStroke(1);
            $rectangle->drawRectangle($curX, $curY, 126, 54);

            $page->addPath($rectangle);
            $page->addText(
                (new Page\Text($i + 1, 10))->setFillColor(new Page\Color\Rgb(0, 0, 0)), 'Arial', $curX + 2, $curY - 10
            );

            $page->addText(
                (new Page\Text($this->values[$i]->getShortHand(), 11))->setFillColor(new Page\Color\Rgb(0, 0, 0)), 'Arial,Bold', $curX + 5, $curY + 23
            );

            $firstRect = new Page\Path(Page\Path::FILL_STROKE);
            $firstRect->setStrokeColor(new Page\Color\Rgb(0, 0, 0))->setStroke(1)
                ->setFillColor(new Page\Color\Rgb(
                    $this->colors[$this->values[$i]->getFirstDigit()][0],
                    $this->colors[$this->values[$i]->getFirstDigit()][1],
                    $this->colors[$this->values[$i]->getFirstDigit()][2]
                ));

            $firstRect->drawRectangle($curX + 48, $curY + 7, 9, 40);

            $page->addPath($firstRect);
            $page->addText(
                (new Page\Text($this->values[$i]->getFirstDigit(), 9))->setFillColor(new Page\Color\Rgb(
                    $this->textColors[$this->values[$i]->getFirstDigit()][0],
                    $this->textColors[$this->values[$i]->getFirstDigit()][1],
                    $this->textColors[$this->values[$i]->getFirstDigit()][2]
                )), 'Arial,Bold', $curX + 50, $curY + 11
            );

            $secondRect = new Page\Path(Page\Path::FILL_STROKE);
            $secondRect->setStrokeColor(new Page\Color\Rgb(0, 0, 0))->setStroke(1)
                ->setFillColor(new Page\Color\Rgb(
                    $this->colors[$this->values[$i]->getSecondDigit()][0],
                    $this->colors[$this->values[$i]->getSecondDigit()][1],
                    $this->colors[$this->values[$i]->getSecondDigit()][2]
            ));

            $secondRect->drawRectangle($curX + 60, $curY + 7, 9, 40);

            $page->addPath($secondRect);
            $page->addText(
                (new Page\Text($this->values[$i]->getSecondDigit(), 9))->setFillColor(new Page\Color\Rgb(
                    $this->textColors[$this->values[$i]->getSecondDigit()][0],
                    $this->textColors[$this->values[$i]->getSecondDigit()][1],
                    $this->textColors[$this->values[$i]->getSecondDigit()][2]
                )), 'Arial,Bold', $curX + 62, $curY + 11
            );

            if ($this->values[$i]->hasThirdDigit()) {
                $thirdRect = new Page\Path(Page\Path::FILL_STROKE);
                $thirdRect->setStrokeColor(new Page\Color\Rgb(0, 0, 0))->setStroke(1)
                    ->setFillColor(new Page\Color\Rgb(
                        $this->colors[$this->values[$i]->getThirdDigit()][0],
                        $this->colors[$this->values[$i]->getThirdDigit()][1],
                        $this->colors[$this->values[$i]->getThirdDigit()][2]
                    ));

                $thirdRect->drawRectangle($curX + 72, $curY + 7, 9, 40);

                $page->addPath($thirdRect);
                $page->addText(
                    (new Page\Text($this->values[$i]->getThirdDigit(), 9))->setFillColor(new Page\Color\Rgb(
                        $this->textColors[$this->values[$i]->getThirdDigit()][0],
                        $this->textColors[$this->values[$i]->getThirdDigit()][1],
                        $this->textColors[$this->values[$i]->getThirdDigit()][2]
                    )), 'Arial,Bold', $curX + 74, $curY + 11
                );
            }

            $multiplierRect = new Page\Path(Page\Path::FILL_STROKE);
            $multiplierRect->setStrokeColor(new Page\Color\Rgb(0, 0, 0))->setStroke(1)
                ->setFillColor(new Page\Color\Rgb(
                    $this->colors[$this->multipliers[(string)$this->values[$i]->getMultiplier()]][0],
                    $this->colors[$this->multipliers[(string)$this->values[$i]->getMultiplier()]][1],
                    $this->colors[$this->multipliers[(string)$this->values[$i]->getMultiplier()]][2]
                ));

            $multiplierText = new Page\Text($this->values[$i]->getMultiplier(), 7);
            $multiplierText->setRotation(90)->setFillColor(new Page\Color\Rgb(
                $this->textColors[$this->multipliers[(string)$this->values[$i]->getMultiplier()]][0],
                $this->textColors[$this->multipliers[(string)$this->values[$i]->getMultiplier()]][1],
                $this->textColors[$this->multipliers[(string)$this->values[$i]->getMultiplier()]][2]
            ));

            if ($this->values[$i]->hasThirdDigit()) {
                $multiplierRect->drawRectangle($curX + 84, $curY + 7, 9, 40);
                $page->addPath($multiplierRect);
                $page->addText($multiplierText, 'Arial,Bold', $curX + 91, $curY + 10);
            } else {
                $multiplierRect->drawRectangle($curX + 72, $curY + 7, 9, 40);
                $page->addPath($multiplierRect);
                $page->addText($multiplierText, 'Arial,Bold', $curX + 79, $curY + 10);
            }

            if ($this->values[$i]->hasTolerance()) {
                $toleranceRect = new Page\Path(Page\Path::FILL_STROKE);
                $toleranceRect->setStrokeColor(new Page\Color\Rgb(0, 0, 0))->setStroke(1)
                    ->setFillColor(new Page\Color\Rgb(
                        $this->colors[$this->tolerance[$this->values[$i]->getTolerance()]][0],
                        $this->colors[$this->tolerance[$this->values[$i]->getTolerance()]][1],
                        $this->colors[$this->tolerance[$this->values[$i]->getTolerance()]][2]
                    ));

                $toleranceRect->drawRectangle($curX + 108, $curY + 7, 9, 40);

                $page->addPath($toleranceRect);
                $toleranceText = new Page\Text($this->values[$i]->getTolerance(), 7);
                $toleranceText->setRotation(90)->setFillColor(new Page\Color\Rgb(
                    $this->textColors[$this->tolerance[$this->values[$i]->getTolerance()]][0],
                    $this->textColors[$this->tolerance[$this->values[$i]->getTolerance()]][1],
                    $this->textColors[$this->tolerance[$this->values[$i]->getTolerance()]][2]
                ));

                $page->addText($toleranceText, 'Arial,Bold', $curX + 115, $curY + 11);
            }

            $powerAndCompString = [];
            if ($this->values[$i]->hasPower()) {
                $powerAndCompString[] = $this->values[$i]->getPower();
            }
            if ($this->values[$i]->hasComp()) {
                $powerAndCompString[] = $this->values[$i]->getComp();
            }

            if (!empty($powerAndCompString)) {
                $string = '\(' . implode(', ', $powerAndCompString) . '\)';
                if (strpos($string, '0.5') !== false) {
                    $string = str_replace('0.5', '   ', $string);
                    $page->addText(
                        (new Page\Text('1', 6))->setFillColor(new Page\Color\Rgb(0, 0, 0)), 'Arial', $curX + 10, $curY + 13
                    );
                    $page->addText(
                        (new Page\Text('2', 6))->setFillColor(new Page\Color\Rgb(0, 0, 0)), 'Arial', $curX + 10, $curY + 6
                    );
                    $page->addPath(
                        (new Page\Path(Page\Path::STROKE))->setStroke(0.5)->setStrokeColor(new Page\Color\Rgb(0, 0, 0))->drawLine($curX + 9, $curY + 12, $curX + 14, $curY + 12)
                    );
                }
                $page->addText(
                    (new Page\Text($string, 9))->setFillColor(new Page\Color\Rgb(0, 0, 0)), 'Arial', $curX + 5, $curY + 9
                );
            }
        }

        return $doc;
    }

    /**
     * Generate JPG labels
     *
     * @param  string $pdf
     * @param  string $uid
     * @param  int    $numOfPages
     * @param  int    $resolution
     * @return array
     */
    public function generateJpg($pdf, $uid, $numOfPages, $resolution = 300)
    {
        $images = [];

        for ($i = 0; $i < $numOfPages; $i++) {
            $filename = __DIR__ . '/../../../data/tmp/resistor-labels-' . $uid . '-' . ($i + 1) . '.jpg';
            $img = new Image\Adapter\Imagick();
            $img->setResolution(300, 300);
            $img->setCompression(100);
            $img->load($pdf . '[' . $i . ']');
            $img->convert('jpg');
            $img->writeToFile($filename, 100);

            chmod($filename, 0777);

            clearstatcache();

            if ($resolution == 72) {
                $img72 =  new Image\Adapter\Imagick();
                $img72->load($filename);
                $img72->resizeToWidth(612);
                $img72->writeToFile($filename, 100);
            }

            $images[] = $filename;
        }

        return $images;
    }

}