<?php namespace scriptlet\unittest\workflow;

use lang\IllegalArgumentException;
use scriptlet\xml\workflow\casters\ToDate;


/**
 * Test the ToDate caster
 *
 * @see       xp://scriptlet.unittest.workflow.AbstractCasterTest
 * @see       scriptlet.xml.workflow.casters.ToDate
 * @purpose   ToDate test
 */
class ToDateTest extends AbstractCasterTest {

  /**
   * Return the caster
   *
   * @return  scriptlet.xml.workflow.casters.ParamCaster
   */
  protected function caster() {
    return new ToDate();
  }

  /**
   * Test european date format (DD.MM.YYYY)
   *
   */
  #[@test]
  public function europeanDateFormat() {
    $this->assertEquals(new \util\Date('1977-12-14'), $this->castValue('14.12.1977'));
  }

  /**
   * Test european date format with short year
   *
   */
  #[@test, @expect(IllegalArgumentException::class)]
  public function europeanDateFormatShortYear() {
    $this->assertEquals(new \util\Date('2008-04-10'), $this->castValue('10.04.08'));
  }
  
  /**
   * Test european date format with short year but which composisiton
   * is so unambigous that the parser can extract year, month and
   * day values from it.
   *
   */
  #[@test]
  public function europeanDateFormatShortYearButUnambiguous() {
    $this->assertEquals(new \util\Date('1980-05-28'), $this->castValue('28.05.80'));
  }
  
  /**
   * Test US date format (YYYY-MM-DD)
   *
   */
  #[@test]
  public function usDateFormat() {
    $this->assertEquals(new \util\Date('1977-12-14'), $this->castValue('1977-12-14'));
  }

  /**
   * Test empty input
   *
   */
  #[@test, @expect(IllegalArgumentException::class)]
  public function emptyInput() {
    $this->castValue('');
  }

  /**
   * Test
   *
   */
  #[@test, @expect(IllegalArgumentException::class)]
  public function daysNotInMonth() {
    $this->castValue('31.11.2009');
  }

  /**
   * Test 31.02.2009 is invalid
   *
   */
  #[@test, @expect(IllegalArgumentException::class)]
  public function februaryDoesNotHave31st() {
    $this->castValue('31.02.2009');
  }
  
  /**
   * Test 30.02.2009 is invalid
   *
   */
  #[@test, @expect(IllegalArgumentException::class)]
  public function februaryDoesNotHave30th() {
    $this->castValue('30.02.2009');
  }
  
  /**
   * Test 29.02.2009 is invalid
   *
   */
  #[@test, @expect(IllegalArgumentException::class)]
  public function februaryDoesNotHave29th() {
    $this->castValue('29.02.2009');
  }

  /**
   * Test 29.02.2009 is valid in a leap year
   *
   */
  #[@test]
  public function february29thInLeapYear() {
    $this->assertEquals(new \util\Date('2008-02-29'), $this->castValue('29.02.2008'));
  }
  
  /**
   * Test with a day > 31
   *
   */
  #[@test, @expect(IllegalArgumentException::class)]
  public function dayLargerThan31() {
    $this->castValue('32.11.2009');
  }

  /**
   * Test with a month > 12
   *
   */
  #[@test, @expect(IllegalArgumentException::class)]
  public function monthLargerThan12() {
    $this->castValue('01.13.2009');
  }

  /**
   * Test
   *
   */
  #[@test, @expect(IllegalArgumentException::class)]
  public function brokenAmericanDateFormat() {
    $this->castValue('30/11/2009'); // Should be 11/30
  }
}
