<?php namespace scriptlet\unittest\workflow\mock;

use scriptlet\xml\workflow\WorkflowScriptletRequest;
use scriptlet\xml\workflow\AbstractState;

/**
 * Mock request object
 *
 * @see       xp://scriptlet.unittest.workflow.HandlerTest
 * @purpose   Mock object
 */
class MockRequest extends WorkflowScriptletRequest {

  /**
   * Setup method.
   *
   * @param   string package
   * @param   string stateName
   * @param   string functionality
   * @param   [:var] params default array()
   */
  public function __construct($package, $stateName, $functionality, $params= []) {
    static $i= 0;

    parent::__construct($package);

    // Generate unique classname and put it into the environment
    // That way, the classloader will already know this class in 
    // WorkflowScriptletRequest::initialize() and be able to load
    // and instantiate it.
    $stateName= 'Mock__'.($i++).$stateName;
    $this->state= \lang\ClassLoader::getDefault()->defineClass(
      $package.'.mock.state.'.$stateName.'State', 
      'scriptlet.xml.workflow.AbstractState',
      [],
      $functionality
    )->newInstance();
    $_SERVER['STATE']= $stateName;

    // Set some defaults
    $_SERVER['PRODUCT']= 'xp';
    $_SERVER['LANGUAGE']= 'en_US';
  }
}
