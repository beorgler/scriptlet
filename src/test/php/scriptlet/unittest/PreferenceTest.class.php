<?php namespace scriptlet\unittest;

use scriptlet\Preference;

/**
 * TestCase
 *
 * @see   http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
 * @see   xp://scriptlet.Preference
 */
class PreferenceTest extends \unittest\TestCase {

  #[@test]
  public function create_with_single_preference() {
    $this->assertEquals(
      (new Preference(['text/xml'])),
      (new Preference('text/xml'))
    );
  }

  #[@test]
  public function create_with_multiple_preferences() {
    $this->assertEquals(
      (new Preference(['text/xml', 'text/plain'])),
      (new Preference('text/xml,text/plain'))
    );
  }

  #[@test]
  public function create_with_multiple_preferences_and_qvalues() {
    $this->assertEquals(
      (new Preference(['text/xml;q=1.0', 'text/plain;q=0.9'])),
      (new Preference('text/xml;q=1.0,text/plain;q=0.9'))
    );
  }

  #[@test]
  public function create_with_multiple_preferences_and_qvalues_reordered() {
    $this->assertEquals(
      (new Preference(['text/xml;q=1.0', 'text/plain;q=0.9'])),
      (new Preference('text/plain;q=0.9,text/xml;q=1.0'))
    );
  }

  #[@test]
  public function single_preference() {
    $this->assertEquals(
      ['text/xml'], 
      (new Preference('text/xml'))->all()
    );
  }

  #[@test]
  public function preferences_separated_by_comma() {
    $this->assertEquals(
      ['text/xml', 'text/plain'], 
      (new Preference('text/xml,text/plain'))->all()
    );
  }

  #[@test]
  public function preferences_separated_by_comma_and_space() {
    $this->assertEquals(
      ['text/xml', 'text/plain'], 
      (new Preference('text/xml, text/plain'))->all()
    );
  }

  #[@test]
  public function preferences_with_qvalues() {
    $this->assertEquals(
      ['text/xml', 'text/plain'], 
      (new Preference('text/xml;q=1.0, text/plain;q=0.9'))->all()
    );
  }

  #[@test]
  public function preferences_with_qvalues_and_spaces() {
    $this->assertEquals(
      ['text/xml', 'text/plain'], 
      (new Preference('text/xml; q=1.0, text/plain; q=0.9'))->all()
    );
  }

  #[@test]
  public function preferences_reordered() {
    $this->assertEquals(
      ['text/plain', 'text/xml'], 
      (new Preference('text/xml;q=0.9, text/plain;q=1.0'))->all()
    );
  }

  #[@test]
  public function rfc2616_more_specific_ranges_override() {
    $this->assertEquals(
      ['text/html;level=1', 'text/html', 'text/*', '*/*'], 
      (new Preference('text/*, text/html, text/html;level=1, */*'))->all()
    );
  }

  #[@test]
  public function preference_exactly_matching_supported() {
    $this->assertEquals(
      'text/xml', 
      (new Preference('text/xml'))->match(['text/xml'])
    );
  }

  #[@test]
  public function preference_matching_one_of_supported() {
    $this->assertEquals(
      'text/plain', 
      (new Preference('text/plain'))->match(['text/xml', 'text/html', 'text/plain'])
    );
  }

  #[@test]
  public function best_preference_matching_one_of_supported() {
    $this->assertEquals(
      'text/html', 
      (new Preference('text/plain;q=0.9, text/html'))->match(['text/xml', 'text/html', 'text/plain'])
    );
  }

  #[@test]
  public function first_preference_matching_one_of_supported() {
    $this->assertEquals(
      'text/plain', 
      (new Preference('text/plain, text/html'))->match(['text/xml', 'text/html', 'text/plain'])
    );
  }

  #[@test]
  public function text_any_matching_one_of_supported() {
    $this->assertEquals(
      'text/html', 
      (new Preference('text/*'))->match(['application/xml', 'text/html', 'text/plain'])
    );
  }

  #[@test]
  public function text_any_matching_first_of_supported() {
    $this->assertEquals(
      'text/plain', 
      (new Preference('text/*'))->match(['text/plain', 'text/html'])
    );
  }

  #[@test]
  public function any_any_matches_first_of_supported() {
    $this->assertEquals(
      'application/xml', 
      (new Preference('*/*'))->match(['application/xml', 'text/html', 'text/plain'])
    );
  }

  #[@test]
  public function application_any_matches_first_of_supported() {
    $this->assertEquals(
      'application/xml', 
      (new Preference('*/*;q=0.1; application/*'))->match(['application/xml', 'text/html', 'text/plain'])
    );
  }

  #[@test]
  public function ie9_default_accept_match_html_vs_plaintext() {
    $this->assertEquals(
      'text/html', 
      (new Preference('text/html, application/xhtml+xml, */*'))->match(['text/plain', 'text/html'])
    );
  }

  #[@test]
  public function ff11_default_accept_match_html_vs_plaintext() {
    $this->assertEquals(
      'text/html', 
      (new Preference('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'))->match(['text/plain', 'text/html'])
    );
  }

  #[@test]
  public function chrome_21_default_accept_match_html_vs_plaintext() {
    $this->assertEquals(
      'text/html', 
      (new Preference('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'))->match(['text/plain', 'text/html'])
    );
  }

  /**
   * Test match()
   *
   * @see  http://en.wikipedia.org/wiki/Content_negotiation
   */
  #[@test]
  public function wikipedia_example_match_html_vs_plaintext() {
    $this->assertEquals(
      'text/html', 
      (new Preference('text/html; q=1.0, text/*; q=0.8, image/gif; q=0.6, image/jpeg; q=0.6, image/*; q=0.5, */*; q=0.1
'))->match(['text/plain', 'text/html'])
    );
  }

  #[@test]
  public function application_json_not_supported() {
    $this->assertNull(
      (new Preference('application/json'))->match(['text/html', 'text/plain'])
    );
  }

  #[@test]
  public function application_any_not_supported() {
    $this->assertNull(
      (new Preference('application/*'))->match(['text/html', 'text/plain'])
    );
  }

  #[@test]
  public function single_preference_string() {
    $this->assertEquals(
      'scriptlet.Preference<text/xml>',
      (new Preference('text/xml'))->toString()
    );
  }

  #[@test]
  public function preferences_string() {
    $this->assertEquals(
      'scriptlet.Preference<text/xml, text/html>',
      (new Preference('text/xml, text/html'))->toString()
    );
  }

  #[@test]
  public function preferences_with_qvalue_string() {
    $this->assertEquals(
      'scriptlet.Preference<text/xml, text/html;q=0.8>',
      (new Preference('text/xml, text/html;q=0.8'))->toString()
    );
  }

  #[@test]
  public function one_point_zero_qvalue_omitted_in_string() {
    $this->assertEquals(
      'scriptlet.Preference<text/xml, text/html;q=0.8>',
      (new Preference('text/xml;q=1.0, text/html;q=0.8'))->toString()
    );
  }

  #[@test]
  public function quality_of_xml() {
    $this->assertEquals(
      1.0,
      (new Preference('text/xml;q=1.0, text/html;q=0.8'))->qualityOf('text/xml')
    );
  }

  #[@test]
  public function quality_of_html() {
    $this->assertEquals(
      0.8,
      (new Preference('text/xml;q=1.0, text/html;q=0.8'))->qualityOf('text/html')
    );
  }

  #[@test]
  public function quality_of_plain() {
    $this->assertEquals(
      1.0,
      (new Preference('text/xml, text/plain'))->qualityOf('text/plain')
    );
  }

  #[@test]
  public function quality_of_plain_with_asterisk() {
    $this->assertEquals(
      0.99999,
      (new Preference('text/*'))->qualityOf('text/plain', 6)
    );
  }
}
