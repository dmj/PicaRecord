<?php

/**
 * Unit test for the Field class.
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
 * @copyright Copyright (c) 2012, 2013 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 */

namespace HAB\Pica\Record;

use PHPUnit_FrameWork_TestCase;

class FieldTest extends PHPUnit_FrameWork_TestCase
{

    public function testValidFieldOccurrenceCastNull () {
        $this->assertTrue(Field::isValidFieldOccurrence(null));
    }

    public function testValidFieldOccurrenceCastString () {
        $this->assertTrue(Field::isValidFieldOccurrence('10'));
    }

    public function testInvalidFieldOccurrenceCastString () {
        $this->assertFalse(Field::isValidFieldOccurrence("10\n"));
    }

    public function testInvalidFieldOccurrenceLowerBound () {
        $this->assertFalse(Field::isValidFieldOccurrence(-1));
    }

    public function testInvalidFieldOccurrenceUpperBound () {
        $this->assertFalse(Field::isValidFieldOccurrence(100));
    }

    public function testInvalidFieldTagTrailingNewline () {
        $this->assertFalse(Field::isValidFieldTag("003@\n"));
    }

    public function testMatch () {
        $this->assertTrue(call_user_func(Field::match('003./..'), new Field('003@', 0)));
        $this->assertTrue(call_user_func(Field::match('003./..'), new Field('003Z', 0)));
        $this->assertTrue(call_user_func(Field::match('003./..'), new Field('003Z', 99)));
    }

    public function testFactory () {
        $f = Field::factory(array('tag' => '003@', 'occurrence' => 10, 'subfields' => array()));
        $this->assertInstanceOf('HAB\Pica\Record\Field', $f);
        $this->assertEquals('003@/10', $f->getShorthand());
    }

    ///

    public function testIsEmpty ()
    {
        $f = new Field('003@', 0);
        $this->assertTrue($f->isEmpty());
        $s = new Subfield('a', 'valid');
        $f->addSubfield($s);
        $this->assertFalse($f->isEmpty());
        $f->removeSubfield($s);
    }

    public function testGetTag ()
    {
        $f = new Field('003@', 0);
        $this->assertEquals('003@', $f->getTag());
    }

    public function testGetOccurrence ()
    {
        $f = new Field('003@', 0);
        $this->assertEquals(0, $f->getOccurrence());
    }

    public function testGetLevel ()
    {
        $f = new Field('003@', 0);
        $this->assertEquals(0, $f->getLevel());
    }

    public function testGetShorthand ()
    {
        $f = new Field('003@', 0);
        $this->assertEquals('003@/00', $f->getShorthand());
    }

    ///

    public function testSetSubfields ()
    {
        $f = new Field('003@', 0);
        $f->setSubfields(array(new Subfield('a', 'first a'),
                               new Subfield('d', 'first d'),
                               new Subfield('a', 'second a')));
        $this->assertFalse($f->isEmpty());
        return $f;
    }

    public function testGetNthSubfield ()
    {
        $f = new Field('003@', 0, array(new Subfield('a', 'first a'),
                                        new Subfield('b', 'first b'),
                                        new Subfield('a', 'second a')));
        $s = $f->getNthSubfield('a', 0);
        $this->assertInstanceOf('HAB\Pica\Record\Subfield', $s);
        $this->assertEquals('first a', $s->getValue());
        $s = $f->getNthSubfield('a', 1);
        $this->assertInstanceOf('HAB\Pica\Record\Subfield', $s);
        $this->assertEquals('second a', $s->getValue());
        $s = $f->getNthSubfield('a', 2);
        $this->assertNull($s);
    }

    /**
     * @depends testSetSubfields
     */
    public function testGetSubfields (Field $f)
    {
        $this->assertEquals(3, count($f->getSubfields()));
        return $f;
    }

    /**
     * @depends testGetSubfields
     */
    public function testGetSubfieldsWithCode (Field $f)
    {
        $this->assertEquals(5, count($f->getSubfields('x', 'x', 'x', 'x', 'x')));
        $s = $f->getSubfields('d');
        $this->assertEquals('first d', reset($s));
        $s = $f->getSubfields('a');
        $this->assertEquals('first a', reset($s));
        $s = $f->getSubfields('a', 'd', 'a');
        $this->assertEquals('second a', end($s));;
        return $f;
    }

    ///

    public function testClone ()
    {
        $f = new Field('003@', 0);
        $s = new Subfield('a', 'valid');
        $f->addSubfield($s);
        $c = clone($f);
        $this->assertNotSame($c, $f);
        $this->assertNotSame($s, $c->getNthSubfield('a', 0));
    }

    ///

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFactoryThrowsExceptionOnMissingTagIndex ()
    {
        Field::factory(array('occurrence' => 10, 'subfields' => array()));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFactoryThrowsExceptionOnMissingOccurrenceIndex ()
    {
        Field::factory(array('tag' => '003@', 'subfields' => array()));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFactoryThrowsExceptionOnMissingSubfieldIndex ()
    {
        Field::factory(array('tag' => '003@', 'occurrence' => 10));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testContructorThrowsExceptionOnInvalidTag ()
    {
        new Field('invalid', 0);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorThrowsExceptionOnInvalidOccurrence ()
    {
        new Field('003@', 1000);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddSubfieldThrowsExceptionOnDuplicateSubfield ()
    {
        $f = new Field('003@', 0);
        $s = new Subfield('a', 'valid');
        $f->addSubfield($s);
        $f->addSubfield($s);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRemoveSubfieldThrowsExceptionOnNonExistentField ()
    {
        $f = new Field('003@', 0);
        $s = new Subfield('a', 'valid');
        $f->removeSubfield($s);
    }
}