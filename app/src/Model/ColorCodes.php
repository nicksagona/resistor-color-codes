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
            $page->addText(new Page\Text($i + 1, 12), 'Arial', $curX + 5, $curY - 15);
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