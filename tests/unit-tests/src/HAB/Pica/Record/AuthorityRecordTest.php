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
 * @copyright Copyright (c) 2012-2019 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 */

namespace HAB\Pica\Record;

use PHPUnit\Framework\TestCase;

class AuthorityRecordTest extends TestCase
{

    ///

    public function testIsEmpty ()
    {
        $r = new AuthorityRecord();
        $this->assertTrue($r->isEmpty());
    }

    public function testAppend ()
    {
        $r = new AuthorityRecord();
        $r->append(new Field('000@', 0, array(new Subfield('0', 'valid'))));
        $this->assertFalse($r->isEmpty());
    }

    public function testGetPPN ()
    {
        $r = new AuthorityRecord();
        $this->assertNull($r->getPPN());
        $r->append(new Field('003@', 0, array(new Subfield('0', 'valid'))));
        $this->assertEquals('valid', $r->getPPN());
    }

    public function testDelete ()
    {
        $r = new AuthorityRecord();
        $r->append(new Field('003@', 0, array(new Subfield('0', 'valid'))));
        $this->assertFalse($r->isEmpty());
        $r->delete(Field::match('..../..'));
        $this->assertTrue($r->isEmpty());
    }

    ///

    public function testSetPPN ()
    {
        $r = new AuthorityRecord();
        $this->assertNull($r->getPPN());
        $r->setPPN('something');
        $this->assertEquals('something', $r->getPPN());
        $r->setPPN('else');
        $this->assertEquals('else', $r->getPPN());
        $this->assertEquals(1, count($r->getFields('003@/00')));
    }

    public function testClone ()
    {
        $r = new AuthorityRecord();
        $f = new Field('003@', 0);
        $r->append($f);
        $c = clone($r);
        $this->assertNotSame($r, $c);
        $fields = $c->getFields();
        $this->assertNotSame($f, reset($fields));
    }

    public function testIsInvalidEmptyField ()
    {
        $r = new AuthorityRecord(array(new Field('003@', 0)));
        $this->assertFalse($r->isValid());
    }

    public function testIsInvalidMissingPPN ()
    {
        $r = new AuthorityRecord(array(new Field('002@', 0, array(new Subfield('0', 'T')))));
        $this->assertFalse($r->isValid());
    }

    public function testIsInvalidMissingType ()
    {
        $r = new AuthorityRecord(array(new Field('003@', 0, array(new Subfield('0', 'something')))));
        $this->assertFalse($r->isValid());
    }

    public function testIsInvalidWrongType ()
    {
        $r = new AuthorityRecord(array(new Field('002@', 0, array(new Subfield('0', 'A')))));
        $this->assertFalse($r->isValid());
    }

    public function testIsValid ()
    {
        $r = new AuthorityRecord(array(new Field('002@', 0, array(new Subfield('0', 'T'))),
                                       new Field('003@', 0, array(new Subfield('0', 'valid')))));
        $this->assertTrue($r->isValid());
    }

    public function testSort ()
    {
        $r = new AuthorityRecord(array(new Field('003@', 99, array(new Subfield('0', 'valid'))),
                                       new Field('003@', 0, array(new Subfield('0', 'valid')))));
        $r->sort();
        $fields = $r->getFields('003@');
        $this->assertEquals('003@/00', reset($fields)->getShorthand());
        $this->assertEquals('003@/99', end($fields)->getShorthand());
    }

    ///

    public function testAppendThrowsExceptionOnDuplicateField ()
    {
        $this->expectException('InvalidArgumentException');
        $r = new AuthorityRecord();
        $f = new Field('003@', 0);
        $r->append($f);
        $r->append($f);
    }

    public function testAppendThrowsExceptionOnInvalidLevel ()
    {
        $this->expectException('InvalidArgumentException');
        $r = new AuthorityRecord();
        $r->append(new Field('101@', 0));
    }

}
