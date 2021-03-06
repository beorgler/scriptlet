<?php namespace xp\scriptlet;

use peer\URL;
use peer\Socket;
use scriptlet\ScriptletException;
use lang\XPClass;

/**
 * Scriptlet handler
 */
class ScriptletHandler extends AbstractUrlHandler {
  protected $scriptlet, $env;

  /**
   * Constructor
   *
   * @param   lang.XPClass $scriptlet
   * @param   string[] args
   * @param   [:string] env
   */
  public function __construct(XPClass $scriptlet, $args, $env= [], $filters= []) {
    if ($scriptlet->hasConstructor()) {
      $this->scriptlet= $scriptlet->getConstructor()->newInstance((array)$args);
    } else {
      $this->scriptlet= $scriptlet->newInstance();
    }

    foreach ($filters as $filter) {
      $this->scriptlet->filter($filter);
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
   * @return  int
   */
  public function handleRequest($method, $query, array $headers, $data, Socket $socket) {
    $url= new URL('http://'.(isset($headers['Host']) ? $headers['Host'] : 'localhost').$query);
    $port= $url->getPort(-1);
    $request= $this->scriptlet->request();
    $response= $this->scriptlet->response();

    // Fill request
    $request->method= $method;
    $request->env= $this->env;
    $request->env['SERVER_PROTOCOL']= 'HTTP/1.1';
    $request->env['REQUEST_URI']= $query;
    $request->env['QUERY_STRING']= substr($query, strpos($query, '?')+ 1);
    $request->env['HTTP_HOST']= $url->getHost().(-1 === $port ? '' : ':'.$port);
    if (isset($headers['Authorization'])) {
      if (0 === strncmp('Basic', $headers['Authorization'], 5)) {
        $credentials= explode(':', base64_decode(substr($headers['Authorization'], 6)));
        $request->env['PHP_AUTH_USER']= $credentials[0];
        $request->env['PHP_AUTH_PW']= $credentials[1];
      }
    }
    $request->setHeaders($headers);

    // Merge POST and GET parameters
    if (isset($headers['Content-Type']) && 'application/x-www-form-urlencoded' === $headers['Content-Type']) {
      parse_str($data, $params);
      $request->setParams(array_merge($url->getParams(), $params));
    } else {
      $request->setParams($url->getParams());
    }

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
      $this->sendErrorMessage($socket, $e->getStatus(), nameof($e), $e->getMessage());
      return $e->getStatus();
    }

    if (!$response->isCommitted()) {
      $response->flush();
    }
    $response->sendContent();
    return $response->statusCode;
  }

  /**
   * Returns a string representation of this object
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'<'.nameof($this->scriptlet).'>';
  }
}
