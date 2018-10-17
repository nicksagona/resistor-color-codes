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
namespace Resistor;

use Pop\Application;
use Pop\Http\Request;
use Pop\Http\Response;
use Pop\View\View;

/**
 * Resistor module class
 *
 * @category   Resistor
 * @package    Resistor
 * @link       https://github.com/nicksagona/resistor-color-codes
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2018 NOLA Interactive. (http://www.nolainteractive.com)
 * @version    0.2
 */
class Module extends \Pop\Module\Module
{

    /**
     * Module version
     * @var string
     */
    const VERSION = '0.2';

    /**
     * Module name
     * @var string
     */
    const NAME = 'resistor-cc';

    /**
     * Module name
     * @var string
     */
    protected $name = self::NAME;

    /**
     * Module version
     * @var string
     */
    protected $version = self::VERSION;

    /**
     * Register module
     *
     * @param  Application $application
     * @return Module
     */
    public function register(Application $application)
    {
        parent::register($application);

        if (null !== $this->application->router()) {
            $this->application->router()->addControllerParams(
                '*', [
                    'application' => $this->application,
                    'request'     => new Request(),
                    'response'    => new Response()
                ]
            );
        }

        return $this;
    }

    /**
     * HTTP error handler method
     *
     * @param  \Exception $exception
     * @return void
     */
    public function httpError(\Exception $exception)
    {
        $response = new Response();
        $view     = new View(__DIR__ . '/../view/exception.phtml', ['message' => $exception->getMessage()]);

        $response->setHeader('Content-Type', 'text/html');
        $response->setBody($view->render());
        $response->send(500);
    }

}