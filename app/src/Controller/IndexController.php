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
namespace Resistor\Controller;

use Pop\Application;
use Pop\Http\Request;
use Pop\Http\Response;
use Pop\Pdf\Pdf;
use Pop\View\View;
use Resistor\Model;

/**
 * Resistor index controller class
 *
 * @category   Resistor
 * @package    Resistor
 * @link       https://github.com/nicksagona/resistor-color-codes
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2018 NOLA Interactive. (http://www.nolainteractive.com)
 * @version    0.2
 */
class IndexController extends \Pop\Controller\AbstractController
{

    /**
     * Application object
     * @var Application
     */
    protected $application = null;
    /**
     * Request object
     * @var Request
     */
    protected $request = null;
    /**
     * Response object
     * @var Response
     */
    protected $response = null;

    /**
     * View path
     * @var string
     */
    protected $viewPath = __DIR__ . '/../../view';

    /**
     * View object
     * @var View
     */
    protected $view = null;

    /**
     * Constructor for the controller
     *
     * @param  Application $application
     * @param  Request     $request
     * @param  Response    $response
     */
    public function __construct(Application $application, Request $request, Response $response)
    {
        $this->application = $application;
        $this->request     = $request;
        $this->response    = $response;
    }

    /**
     * Index action method
     *
     * @return void
     */
    public function index()
    {
        $this->prepareView('index.phtml');

        if ($this->request->isPost()) {
            $format     = strtolower($this->request->getPost('format'));
            $textValues = $this->request->getPost('text_values');
            $forceThird = (null !== $this->request->getPost('allow_three_band')) ? (bool)$this->request->getPost('allow_three_band') : true;
            $suffix     = ((null !== $this->request->getPost('suffix')) && ($this->request->getPost('suffix') != 'none')) ? $this->request->getPost('suffix') : null;
            $fileValues = null;

            if ($this->request->hasFiles() && (null !== $this->request->getFiles('file_values')) &&
                !empty($this->request->getFiles('file_values')['tmp_name'])) {
                $fileValues = file_get_contents($this->request->getFiles('file_values')['tmp_name']);
            }

            if (empty($textValues) && empty($fileValues)) {
                $this->view->error = true;
                $this->send();
            } else {
                $label = new Model\Label();
                $label->parseValues($textValues, $fileValues, $forceThird, $suffix);

                clearstatcache();

                $doc = $label->generatePdf();

                if ($format == 'pdf') {
                    $pdf = new Pdf();
                    $pdf->outputToHttp($doc, 'resistor-labels.pdf', true);
                } else {
                    $res = (strpos($format, '72') !== false) ? 72 : 300;
                    $uid = uniqid();
                    $pdf = new Pdf();
                    $pdf->writeToFile($doc, __DIR__ . '/../../../data/tmp/resistor-labels-' . $uid . '.pdf');

                    $images = $label->generateJpg(
                        __DIR__ . '/../../../data/tmp/resistor-labels-' . $uid . '.pdf', $uid, $doc->getNumberOfPages(), $res
                    );

                    if (file_exists(__DIR__ . '/../../../data/tmp/resistor-labels-'. $uid . '.pdf')) {
                        unlink(__DIR__ . '/../../../data/tmp/resistor-labels-'. $uid . '.pdf');
                    }

                    if (count($images) == 1) {
                        $imageContents = file_get_contents($images[0]);

                        if (file_exists($images[0])) {
                            unlink($images[0]);
                        }

                        header('HTTP/1.1 200 OK');
                        header('Content-Disposition: attachment; filename="resistor-labels.jpg"');
                        header('Content-Type: image/jpeg');
                        echo $imageContents;
                    } else {
                        $uid = uniqid();
                        $zip = new \ZipArchive();
                        $zip->open(__DIR__ . '/../../../data/tmp/resistor-labels-'. $uid . '.zip', \ZipArchive::CREATE);

                        foreach ($images as $page => $image) {
                            $zip->addFile($image, 'resistor-labels-' . ($page + 1) . '.jpg');
                        }

                        $zip->close();

                        $zipContents = file_get_contents(__DIR__ . '/../../../data/tmp/resistor-labels-'. $uid . '.zip');

                        if (file_exists(__DIR__ . '/../../../data/tmp/resistor-labels-'. $uid . '.pdf')) {
                            unlink(__DIR__ . '/../../../data/tmp/resistor-labels-'. $uid . '.pdf');
                        }

                        if (file_exists(__DIR__ . '/../../../data/tmp/resistor-labels-'. $uid . '.zip')) {
                            unlink(__DIR__ . '/../../../data/tmp/resistor-labels-'. $uid . '.zip');
                        }

                        foreach ($images as $page => $image) {
                            if (file_exists($image)) {
                                unlink($image);
                            }
                        }

                        header('HTTP/1.1 200 OK');
                        header('Content-Disposition: attachment; filename="resistor-labels-'. $uid . '.zip"');
                        header('Content-Type: application/octet-stream');
                        echo $zipContents;
                    }
                }
            }
        } else {
            $this->send();
        }
    }

    /**
     * Error action method
     *
     * @return void
     */
    public function error()
    {
        $this->prepareView('error.phtml');
        $this->send(404);
    }

    /**
     * Get application object
     *
     * @return Application
     */
    public function application()
    {
        return $this->application;
    }

    /**
     * Get request object
     *
     * @return Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Get response object
     *
     * @return Response
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * Get view object
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }
    /**
     * Determine if the view object has been created
     *
     * @return boolean
     */
    public function hasView()
    {
        return (null !== $this->view);
    }
    /**
     * Send response
     *
     * @param  int    $code
     * @param  string $body
     * @param  array  $headers
     * @return void
     */
    public function send($code = 200, $body = null, array $headers = null)
    {
        if ((null === $body) && (null !== $this->view)) {
            $body = $this->view->render();
        }

        if (null === $this->response->getHeader('Content-Type')) {
            $this->response->setHeader('Content-Type', 'text/html');
        }

        $this->response->setBody($body . PHP_EOL . PHP_EOL);
        $this->response->send($code, $headers);
    }
    /**
     * Prepare view
     *
     * @param  string $template
     * @return void
     */
    protected function prepareView($template)
    {
        $this->view = new View($this->viewPath . '/' . $template);
    }

    /**
     * Redirect response
     *
     * @param  string $url
     * @param  string $code
     * @param  string $version
     * @return void
     */
    public function redirect($url, $code = '302', $version = '1.1')
    {
        Response::redirect($url, $code, $version);
        exit();
    }

}