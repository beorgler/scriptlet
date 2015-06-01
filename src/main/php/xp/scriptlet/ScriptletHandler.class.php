<?php namespace xp\scriptlet;

use peer\URL;
use peer\Socket;
use scriptlet\ScriptletException;

/**
 * Scriptlet handler
 */
class ScriptletHandler extends AbstractUrlHandler {
  protected $scriptlet, $env;

  /**
   * Constructor
   *
   * @param   string name
   * @param   string[] args
   * @param   [:string] env
   */
  public function __construct($name, $args, $env= []) {
    $class= \lang\XPClass::forName($name);
    if ($class->hasConstructor()) {
      $this->scriptlet= $class->getConstructor()->newInstance((array)$args);
    } else {
      $this->scriptlet= $class->newInstance();
    }
    $this->scriptlet->init();
    $this->env= $env;
  }

  /**
   * Handle a single request
   *
   * @param   string method request method
   * @param   string query query string
   * @param   [:string] headers request headers
   * @param   string data post data
   * @param   peer.Socket socket
   */
  public function handleRequest($method, $query, array $headers, $data, Socket $socket) {
    $url= new URL('http://localhost'.$query);
    $request= $this->scriptlet->request();
    $response= $this->scriptlet->response();

    // Fill request
    $request->method= $method;
    $request->env= $this->env;
    $request->env['SERVER_PROTOCOL']= 'HTTP/1.1';
    $request->env['REQUEST_URI']= $query;
    $request->env['QUERY_STRING']= substr($query, strpos($query, '?')+ 1);
    $request->env['HTTP_HOST']= $url->getHost();
    if ('https' === $url->getScheme()) {
      $request->env['HTTPS']= 'on';
    }
    if (isset($headers['Authorization'])) {
      if (0 === strncmp('Basic', $headers['Authorization'], 5)) {
        $credentials= explode(':', base64_decode(substr($headers['Authorization'], 6)));
        $request->env['PHP_AUTH_USER']= $credentials[0];
        $request->env['PHP_AUTH_PW']= $credentials[1];
      }
    }
    $request->setHeaders($headers);
    $request->setParams($url->getParams());

    // Rewire request and response I/O
    $request->readData= function() use($data) {
      return new \io\streams\MemoryInputStream($data);
    };
    $response->sendHeaders= function($version, $statusCode, $headers) use($socket) {
      $this->sendHeader($socket, $statusCode, '', $headers);
    };
    $response->sendContent= function($content) use($socket) {
      $socket->write($content);
    };

    try {
      $this->scriptlet->service($request, $response);
    } catch (ScriptletException $e) {
      $e->printStackTrace();
      $this->sendErrorMessage($socket, $e->getStatus(), $e->getClassName(), $e->getMessage());
      return;
    }

    $response->sendContent();
  }

  /**
   * Returns a string representation of this object
   *
   * @return  string
   */
  public function toString() {
    return $this->getClassName().'<'.$this->scriptlet->getClassName().'>';
  }
}
