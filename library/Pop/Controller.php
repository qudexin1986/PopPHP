<?php
/**
 * Pop PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://www.popphp.org/LICENSE.TXT
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@popphp.org so we can send you a copy immediately.
 *
 * @category   Pop
 * @package    Pop_Controller
 * @author     Nick Sagona, III <nick@moc10media.com>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * Pop_Controller
 *
 * @category   Pop
 * @package    Pop_Controller
 * @author     Nick Sagona, III <nick@moc10media.com>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    0.9 beta
 */

class Pop_Controller
{

    /**
     * Request
     * @var Pop_Http_Request
     */
    protected $_request = null;

    /**
     * Response
     * @var Pop_Http_Response
     */
    protected $_response = null;

    /**
     * Data model object
     * @var Pop_Model
     */
    protected $_model = null;

    /**
     * View object
     * @var Pop_View
     */
    protected $_view = null;

    /**
     * View path
     * @var string
     */
    protected $_viewPath = null;

    /**
     * Constructor
     *
     * Instantiate the controller object
     *
     * @param Pop_Http_Request  $request
     * @param Pop_Http_Response $response
     * @param string              $viewPath
     * @return void
     */
    public function __construct(Pop_Http_Request $request = null, Pop_Http_Response $response = null, $viewPath = null)
    {
        $this->_request = (!is_null($request)) ? $request : new Pop_Http_Request();
        $this->_response = (!is_null($response)) ? $response : new Pop_Http_Response(200, array('Content-Type' => 'text/html'));

        if (!is_null($viewPath)) {
            $this->_viewPath = $viewPath;
        }
    }

    /**
     * Set the request object
     *
     * @param  Pop_Http_Request
     * @return Pop_Controller
     */
    public function setRequest(Pop_Http_Request $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Set the response object
     *
     * @param  Pop_Http_Response
     * @return Pop_Controller
     */
    public function setResponse(Pop_Http_Response $response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Set the response object
     *
     * @param  string $viewPath
     * @return Pop_Controller
     */
    public function setViewPath($viewPath)
    {
        $this->_viewPath = $viewPath;
        return $this;
    }

    /**
     * Get the request object
     *
     * @return Pop_Http_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Get the repsonse object
     *
     * @return Pop_Http_Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Get the view path
     *
     * @return string
     */
    public function getViewPath()
    {
        return $this->_viewPath;
    }

    /**
     * Finalize the request and send the response.
     *
     * @param  int    $code
     * @param  array  $headers
     * @return void
     */
    public function dispatch($code = 200, array $headers = null)
    {
        $this->_response->setCode($code);

        if (!is_null($headers)) {
            foreach ($headers as $name => $value) {
                $this->_response->setHeader($name, $value);
            }
        }

        $this->_response->setBody($this->_view->render(true));
        $this->_response->send();
    }

}
