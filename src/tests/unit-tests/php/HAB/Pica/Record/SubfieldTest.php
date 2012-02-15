<?php

/**
 * Unit test for the Subfield class.
 *
 * This file is part of PicaRecord.
 *
 * PicaRecord is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PicaRecord is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PicaRecord.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   PicaRecord
 * @author    David Maus <maus@hab.de>
 * @copyright Copyright (c) 2012 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 */

namespace HAB\Pica\Record;

class SubfieldTest extends \PHPUnit_FrameWork_TestCase {

  public function testValidSubfieldCodeZero () {
    $this->assertTrue(Subfield::isValidSubfieldCode('0'));
  }

  public function testInvalidSubfieldCodeTrailingNewline () {
    $this->assertFalse(Subfield::isValidSubfieldCode("a\n"));
  }

  public function testValidSubfieldValueSingleSpace () {
    $this->assertTrue(Subfield::isValidSubfieldValue(' '));
  }

  public function testFactory () {
    $s = Subfield::factory(array('code' => 'a', 'value' => 'valid'));
    $this->assertInstanceOf('HAB\Pica\Record\Subfield', $s);
    $this->assertEquals('a', $s->getCode());
    $this->assertEquals('valid', $s->getValue());
  }

  ///

  public function testConstructor () {
    return new Subfield('a', 'valid');
  }

  /**
   * @depends testConstructor
   */
  public function testGetValue (Subfield $s) {
    $this->assertEquals('valid', $s->getValue());
  }

  /**
   * @depends testConstructor
   */
  public function testGetCode (Subfield $s) {
    $this->assertEquals('a', $s->getCode());
  }

  /**
   * @depends testConstructor
   */
  public function testToString (Subfield $s) {
    $this->assertEquals('valid', (string)$s);
  }

  ///

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testConstructorThrowsExceptionOnInvalidCode () {
    new Subfield(null, 'valid');
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testConstructorThrowsExceptionOnInvalidValue () {
    new Subfield('a', null);
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testFactoryThrowsExceptionOnMissingCodeIndex () {
    Subfield::factory(array('value' => 'valid'));
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testFactoryThrowsExceptionOnMissingValueIndex () {
    Subfield::factory(array('code' => 'a'));
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testSetValueThrowsExceptionOnInvalidValue () {
    $s = new Subfield('a', 'valid');
    $s->setValue(null);
  }

}