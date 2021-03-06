<?php namespace scriptlet\xml\workflow\casters;



/**
 * Casts given values to a "trimmed string". A trimmed sring contains
 * no whitespace at the beginning or the end - this is useful when
 * handling situations where one would expect data to be copy & pasted
 * (and may therefore contain whitespace if not done accurately).
 *
 * @purpose  Caster
 */
class ToTrimmedString extends ParamCaster {

  /**
   * Cast a given value.
   *
   * @see     php://trim
   * @see     xp://scriptlet.xml.workflow.casters.ParamCaster
   * @param   array value
   * @return  array value
   */
  public function castValue($value) {
    $return= [];
    foreach ($value as $k => $v) {
      $return[$k]= trim($v);
    }

    return $return;
  }
}
