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
use Pop\Http\Server\Request;
use Pop\Http\Server\Response;
use Pop\View\View;

/**
 * Resistor module class
 *
 * @category   Resistor
 * @package    Resistor
 * @link       https://github.com/nicksagona/resistor-color-codes
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2018 NOLA Interactive. (http://www.nolainteractive.com)
 * @version    0.6
 */
class Module extends \Pop\Module\Module
{

    /**
     * Module version
     * @var string
     */
    const VERSION = '0.6';

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

        if ($this->application->router()->isCli()) {
            $this->registerCli();
        } else {
            $this->registerHttp();
        }

        return $this;
    }



    /**
     * Register HTTP
     *
     * @return void
     */
    public function registerHttp()
    {
        if (null !== $this->application->router()) {
            $this->application->router()->addControllerParams(
                '*', [
                    'application' => $this->application,
                    'request'     => new Request(),
                    'response'    => new Response()
                ]
            );
        }
    }

    /**
     * Register CLI
     *
     * @return void
     */
    public function registerCli()
    {
        if (null !== $this->application->router()) {
            $this->application->router()->addControllerParams(
                '*', [
                    'application' => $this->application,
                    'console'     => new \Pop\Console\Console(120, '    ')
                ]
            );
        }

        $this->application->on('app.route.pre', function() {
            echo PHP_EOL . '    Resistor Color Codes' . PHP_EOL;
            echo '    ====================' . PHP_EOL . PHP_EOL;
        }, 2);

        $this->application->on('app.dispatch.post', function() {
            echo PHP_EOL;
        }, 1);
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

        $response->addHeader('Content-Type', 'text/html');
        $response->setBody($view->render());
        $response->send(500);
    }

    /**
     * CLI error handler method
     *
     * @param  \Exception $exception
     * @return void
     */
    public function cliError(\Exception $exception)
    {
        $message = strip_tags($exception->getMessage());

        if (stripos(PHP_OS, 'win') === false) {
            $string  = "    \x1b[1;37m\x1b[41m    " . str_repeat(' ', strlen($message)) . "    \x1b[0m" . PHP_EOL;
            $string .= "    \x1b[1;37m\x1b[41m    " . $message . "    \x1b[0m" . PHP_EOL;
            $string .= "    \x1b[1;37m\x1b[41m    " . str_repeat(' ', strlen($message)) . "    \x1b[0m" . PHP_EOL . PHP_EOL;
            $string .= "    Try \x1b[1;33m./resistor help\x1b[0m for help" . PHP_EOL . PHP_EOL;
        } else {
            $string = $message . PHP_EOL . PHP_EOL;
            $string .= '    Try \'./resistor help\' for help' . PHP_EOL . PHP_EOL;
        }

        echo $string;
        echo PHP_EOL;

        exit(127);
    }

}