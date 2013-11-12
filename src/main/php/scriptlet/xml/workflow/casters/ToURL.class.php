<?php namespace scriptlet\xml\workflow\casters;

use peer\URL;


/**
 * Casts given values to peer.URL objects
 *
 * @see      xp://peer.URL
 * @purpose  Caster
 */
class ToURL extends ParamCaster {

  /**
   * Cast a given value
   *
   * @see     xp://scriptlet.xml.workflow.casters.ParamCaster
   * @param   array value
   * @return  array value
   */
  public function castValue($value) {
    $return= array();
    foreach ($value as $k => $v) {
      $return[$k]= new URL($v);
    }

    return $return;
  }
}
