<?php

/**
 * Unit test for the AuthorityRecord class.
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

class AuthorityRecordTest extends \PHPUnit_FrameWork_TestCase {

  ///

  public function testConstructor () {
    return new AuthorityRecord();
  }

  /**
   * @depends testConstructor
   */
  public function testIsEmpty (AuthorityRecord $r) {
    $this->assertTrue($r->isEmpty());
  }

  /**
   * @depends testConstructor
   */
  public function testAppend (AuthorityRecord $r) {
    $r->append(new Field('000@', 0, array(new Subfield('0', 'valid'))));
    $this->assertFalse($r->isEmpty());
  }

  /**
   * @depends testConstructor
   */
  public function testGetPPN (AuthorityRecord $r) {
    $this->assertNull($r->getPPN());
    $r->append(new Field('003@', 0, array(new Subfield('0', 'valid'))));
    $this->assertEquals('valid', $r->getPPN());
  }

  /**
   * @depends testConstructor
   */
  public function testDelete (AuthorityRecord $r) {
    $this->assertFalse($r->isEmpty());
    $r->delete(Field::match('..../..'));
    $this->assertTrue($r->isEmpty());
  }

  ///

  public function testSetPPN () {
    $r = new AuthorityRecord();
    $this->assertNull($r->getPPN());
    $r->setPPN('something');
    $this->assertEquals('something', $r->getPPN());
    $r->setPPN('else');
    $this->assertEquals('else', $r->getPPN());
    $this->assertEquals(1, count($r->getFields('003@/00')));
  }

  public function testClone () {
    $r = new AuthorityRecord();
    $f = new Field('003@', 0);
    $r->append($f);
    $c = clone($r);
    $this->assertNotSame($r, $c);
    $this->assertNotSame($f, reset($c->getFields()));
  }

  public function testIsInvalidEmptyField () {
    $r = new AuthorityRecord(array(new Field('003@', 0)));
    $this->assertFalse($r->isValid());
  }

  public function testIsInvalidMissingPPN () {
    $r = new AuthorityRecord(array(new Field('002@', 0, array(new Subfield('0', 'T')))));
    $this->assertFalse($r->isValid());
  }

  public function testIsInvalidMissingType () {
    $r = new AuthorityRecord(array(new Field('003@', 0, array(new Subfield('0', 'something')))));
    $this->assertFalse($r->isValid());
  }

  public function testIsInvalidWrongType () {
    $r = new AuthorityRecord(array(new Field('002@', 0, array(new Subfield('0', 'A')))));
    $this->assertFalse($r->isValid());
  }

  public function testIsValid () {
    $r = new AuthorityRecord(array(new Field('002@', 0, array(new Subfield('0', 'T'))),
                                   new Field('003@', 0, array(new Subfield('0', 'valid')))));
    $this->assertTrue($r->isValid());
  }

  public function testSort () {
    $r = new AuthorityRecord(array(new Field('003@', 99, array(new Subfield('0', 'valid'))),
                                   new Field('003@', 0, array(new Subfield('0', 'valid')))));
    $r->sort();
    $this->assertEquals('003@/00', reset($r->getFields('003@'))->getShorthand());
    $this->assertEquals('003@/99', end($r->getFields('003@'))->getShorthand());
  }

  ///

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testAppendThrowsExceptionOnDuplicateField () {
    $r = new AuthorityRecord();
    $f = new Field('003@', 0);
    $r->append($f);
    $r->append($f);
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testAppendThrowsExceptionOnInvalidLevel () {
    $r = new AuthorityRecord();
    $r->append(new Field('101@', 0));
  }

}