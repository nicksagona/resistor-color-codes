<?php

namespace Resistor\Console\Controller;

use Pop\Application;
use Pop\Console\Console;
use Pop\Controller\AbstractController;
use Resistor\Exception;

/**
 * Console controller class
 *
 * @category   Nova\Auth
 * @package    Nova\Auth
 * @link       https://github.com/punctualabstract/titlenova-auth
 * @author     Nick Sagona, III <nsagona@punctualabstract.com>
 * @copyright  Copyright (c) 2018-2020 Punctual Abstract. (http://www.punctualabstract.com)
 * @version    0.0.8
 */
class ConsoleController extends AbstractController
{

    /**
     * Application object
     * @var Application
     */
    protected $application = null;

    /**
     * Console object
     * @var Console
     */
    protected $console = null;

    /**
     * Constructor for the controller
     *
     * @param  Application $application
     * @param  Console     $console
     */
    public function __construct(Application $application, Console $console)
    {
        $this->application = $application;
        $this->console     = $console;
        $moduleName        = null;

        foreach ($application->modules() as $module) {
            if ($module->hasName()) {
                $moduleName = $module->getName();
                break;
            }
        }

        $this->console->setHelpColors(Console::BOLD_CYAN, Console::BOLD_GREEN, Console::BOLD_MAGENTA);
        $this->console->addCommandsFromRoutes($application->router()->getRouteMatch(), './' . $moduleName);
    }

    /**
     * Print title to screen
     *
     * @param  string $title
     * @return void
     */
    public function printTitle($title)
    {
        $this->console->append($title);
        $this->console->append(str_repeat('-', strlen($title)));
        $this->console->append();
    }

    /**
     * Version command
     *
     * @return void
     */
    public function version()
    {
        $this->console->write('Version: ' . $this->console->colorize(\Resistor\Module::VERSION, Console::BOLD_GREEN));
    }

    /**
     * Help command
     *
     * @return void
     */
    public function help()
    {
        $this->console->help();
    }

}