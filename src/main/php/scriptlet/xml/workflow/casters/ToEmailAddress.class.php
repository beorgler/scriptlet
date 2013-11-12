<?php namespace scriptlet\xml\workflow\casters;

use peer\mail\InternetAddress;


/**
 * Casts given values to peer.mail.InternetAddress objects
 *
 * @purpose  Caster
 */
class ToEmailAddress extends ParamCaster {

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
      try {
        $addr= InternetAddress::fromString($v);
      } catch (\lang\FormatException $e) {
        return $e->getMessage();
      }
      
      $return[$k]= $addr;
    }

    return $return;
  }
}
