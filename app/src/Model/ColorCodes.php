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
                $value = (int)($value * 1000);
            } else if (substr($value, -1) == 'm') {
                $value = trim(substr($value, 0, -1));
                $value = (int)($value * 1000000);
            } else if (substr($value, -1) == 'g') {
                $value = trim(substr($value, 0, -1));
                $value = (int)($value * 1000000000);
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

                    $placesNum  = implode('', $places);
                    $multiplier = 1;
                    while ($placesNum != $value) {
                        $placesNum  *= 10;
                        $multiplier *= 10;
                    }
                } else if (($value < 1000000000) && ($value >= 1000000)) {
                    $short  = (float)($value / 1000000);
                    $places = str_split(str_replace('.', '', $short));
                    $short .= 'M';

                    $placesNum  = (int)implode('', $places);
                    $multiplier = 1;

                    while ($placesNum != $value) {
                        $placesNum  *= 10;
                        $multiplier *= 10;
                    }
                } else if (($value < 1000000) && ($value >= 1000)) {
                    $short  = (float)($value / 1000);
                    $places = str_split(str_replace('.', '', $short));
                    $short .= 'K';

                    $placesNum  = (int)implode('', $places);
                    $multiplier = 1;
                    while ($placesNum != $value) {
                        $placesNum  *= 10;
                        $multiplier *= 10;
                    }
                } else {
                    $short  = $value;
                    $places = str_split(str_replace('.', '', $short));

                    $placesNum  = (int)implode('', $places);
                    $multiplier = 1;
                    while ($placesNum != $value) {
                        if ($placesNum < $value) {
                            $placesNum  *= 10;
                            $multiplier *= 10;
                        } else {
                            $placesNum  /= 10;
                            $multiplier /= 10;
                        }
                    }
                }

                if (isset($places[0])) {
                    $first = $places[0];
                }
                if (isset($places[1])) {
                    $second = $places[1];
                } else {
                    $second = 0;
                }
                if (isset($places[2])) {
                    $third = $places[2];
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

    /**
     * Generate labels
     *
     * @param  string $format
     * @return mixed
     */
    public function generateLabels($format)
    {
        return (strtolower($format) == 'pdf') ? $this->generatePdf() : $this->generateJpg($format);
    }

    /**
     * Generate PDF labels
     *
     * @return mixed
     */
    public function generatePdf()
    {
        $doc  = new Document();
        $doc->addFont(new Document\Font('Arial'));
        $doc->addFont(new Document\Font('Arial,Bold'));

        for ($i = 0; $i < count($this->values); $i++) {
            if (($i % 15) == 0) {
                $page = new Page(Page::LETTER);
                $x    = 36;
                $y    = 684;
                $doc->addPage($page);
            }

            $rowY = floor($i / 3);

            if ($rowY > 4) {
                $rowY -= (($doc->getNumberOfPages() - 1) * 5);
            }

            $curX = ($x + (($i % 3) * 198));
            $curY = ($y - ($rowY * 144));

            $rectangle = new Page\Path(Page\Path::STROKE);
            $rectangle->setStrokeColor(new Page\Color\Rgb(0, 0, 0))->setStroke(1);
            $rectangle->drawRectangle($curX, $curY, 144, 72);

            $page->addPath($rectangle);
            $page->addText(
                (new Page\Text($i + 1, 12))->setFillColor(new Page\Color\Rgb(0, 0, 0)), 'Arial', $curX + 5, $curY - 15
            );

            $page->addText(
                (new Page\Text($this->values[$i]['short'], 14))->setFillColor(new Page\Color\Rgb(0, 0, 0)), 'Arial,Bold', $curX + 5, $curY + 32
            );

            $firstRect = new Page\Path(Page\Path::FILL_STROKE);
            $firstRect->setStrokeColor(new Page\Color\Rgb(0, 0, 0))->setStroke(1)
                ->setFillColor(new Page\Color\Rgb(
                    $this->colors[$this->values[$i]['first']][0],
                    $this->colors[$this->values[$i]['first']][1],
                    $this->colors[$this->values[$i]['first']][2]
                ));

            $firstRect->drawRectangle($curX + 48, $curY + 12, 12, 48);

            $page->addPath($firstRect);
            $page->addText(
                (new Page\Text($this->values[$i]['first'], 9))->setFillColor(new Page\Color\Rgb(
                    $this->textColors[$this->values[$i]['first']][0],
                    $this->textColors[$this->values[$i]['first']][1],
                    $this->textColors[$this->values[$i]['first']][2]
                )), 'Arial,Bold', $curX + 51, $curY + 15
            );

            $secondRect = new Page\Path(Page\Path::FILL_STROKE);
            $secondRect->setStrokeColor(new Page\Color\Rgb(0, 0, 0))->setStroke(1)
                ->setFillColor(new Page\Color\Rgb(
                    $this->colors[$this->values[$i]['second']][0],
                    $this->colors[$this->values[$i]['second']][1],
                    $this->colors[$this->values[$i]['second']][2]
            ));

            $secondRect->drawRectangle($curX + 64, $curY + 12, 12, 48);

            $page->addPath($secondRect);
            $page->addText(
                (new Page\Text($this->values[$i]['second'], 9))->setFillColor(new Page\Color\Rgb(
                    $this->textColors[$this->values[$i]['second']][0],
                    $this->textColors[$this->values[$i]['second']][1],
                    $this->textColors[$this->values[$i]['second']][2]
                )), 'Arial,Bold', $curX + 67, $curY + 15
            );


            if (null !== $this->values[$i]['third']) {
                $thirdRect = new Page\Path(Page\Path::FILL_STROKE);
                $thirdRect->setStrokeColor(new Page\Color\Rgb(0, 0, 0))->setStroke(1)
                    ->setFillColor(new Page\Color\Rgb(
                        $this->colors[$this->values[$i]['third']][0],
                        $this->colors[$this->values[$i]['third']][1],
                        $this->colors[$this->values[$i]['third']][2]
                    ));

                $thirdRect->drawRectangle($curX + 80, $curY + 12, 12, 48);

                $page->addPath($thirdRect);
                $page->addText(
                    (new Page\Text($this->values[$i]['third'], 9))->setFillColor(new Page\Color\Rgb(
                        $this->textColors[$this->values[$i]['third']][0],
                        $this->textColors[$this->values[$i]['third']][1],
                        $this->textColors[$this->values[$i]['third']][2]
                    )), 'Arial,Bold', $curX + 83, $curY + 15
                );
            }

            $multiplierRect = new Page\Path(Page\Path::FILL_STROKE);
            $multiplierRect->setStrokeColor(new Page\Color\Rgb(0, 0, 0))->setStroke(1)
                ->setFillColor(new Page\Color\Rgb(
                    $this->colors[$this->multipliers[(string)$this->values[$i]['multiplier']]][0],
                    $this->colors[$this->multipliers[(string)$this->values[$i]['multiplier']]][1],
                    $this->colors[$this->multipliers[(string)$this->values[$i]['multiplier']]][2]
                ));

            $multiplierText = new Page\Text($this->values[$i]['multiplier'], 7);
            $multiplierText->setRotation(90)->setFillColor(new Page\Color\Rgb(
                $this->textColors[$this->multipliers[(string)$this->values[$i]['multiplier']]][0],
                $this->textColors[$this->multipliers[(string)$this->values[$i]['multiplier']]][1],
                $this->textColors[$this->multipliers[(string)$this->values[$i]['multiplier']]][2]
            ));

            if (null !== $this->values[$i]['third']) {
                $multiplierRect->drawRectangle($curX + 96, $curY + 12, 12, 48);
                $page->addPath($multiplierRect);
                $page->addText($multiplierText, 'Arial,Bold', $curX + 104, $curY + 15);
            } else {
                $multiplierRect->drawRectangle($curX + 80, $curY + 12, 12, 48);
                $page->addPath($multiplierRect);
                $page->addText($multiplierText, 'Arial,Bold', $curX + 88, $curY + 15);
            }

            if (null !== $this->values[$i]['tolerance']) {
                $toleranceRect = new Page\Path(Page\Path::FILL_STROKE);
                $toleranceRect->setStrokeColor(new Page\Color\Rgb(0, 0, 0))->setStroke(1)
                    ->setFillColor(new Page\Color\Rgb(
                        $this->colors[$this->tolerance[$this->values[$i]['tolerance']]][0],
                        $this->colors[$this->tolerance[$this->values[$i]['tolerance']]][1],
                        $this->colors[$this->tolerance[$this->values[$i]['tolerance']]][2]
                    ));

                $toleranceRect->drawRectangle($curX + 120, $curY + 12, 12, 48);

                $page->addPath($toleranceRect);
                $toleranceText = new Page\Text($this->values[$i]['tolerance'], 7);
                $toleranceText->setRotation(90)->setFillColor(new Page\Color\Rgb(
                    $this->textColors[$this->tolerance[$this->values[$i]['tolerance']]][0],
                    $this->textColors[$this->tolerance[$this->values[$i]['tolerance']]][1],
                    $this->textColors[$this->tolerance[$this->values[$i]['tolerance']]][2]
                ));

                $page->addText($toleranceText, 'Arial,Bold', $curX + 128, $curY + 15);
            }
        }

        return $doc;
    }

    /**
     * Generate JPG labels
     *
     * @param  string $format
     * @return array
     */
    public function generateJpg($format)
    {
        $images = [];

        for ($i = 0; $i < count($this->values); $i++) {
            if (($i % 15) == 0) {
                $image    = Image\Imagick::create(2550, 3300);
                $x        = 150;
                $y        = 150;
                $images[] = $image;
            }

            $rowY = floor($i / 3);

            if ($rowY > 4) {
                $rowY -= ((count($images) - 1) * 5);
            }

            $curX = ($x + (($i % 3) * 825));
            $curY = ($y + ($rowY * 600));

            $image->draw->setFillColor(new Image\Color\Rgb(255, 255, 255))
                ->setStrokeColor(new Image\Color\Rgb(0, 0, 0))
                ->setStrokeWidth(1)
                ->rectangle($curX, $curY, 600, 300);

            $image->type->setFillColor(new Image\Color\Rgb(0, 0, 0))
                ->size(36)
                ->font(__DIR__ . '/../../resources/assets/fonts/arial.ttf')
                ->xy($curX + 5, $curY + 336)
                ->text($i + 1);
        }

        if (strpos($format, '72') !== false) {
            foreach ($images as $image) {
                $image->resizeToWidth(612);
            }
        }

        return $images;
    }

}