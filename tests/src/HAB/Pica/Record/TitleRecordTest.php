<?php

/**
 * Unit test for the TitleRecord class.
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

class TitleRecordTest extends PHPUnit_FrameWork_TestCase
{

    public function testAppend ()
    {
        $r = new TitleRecord();
        $r->append(new Field('003@', 0));
        $this->assertEquals(1, count($r->getFields()));
    }

    public function testAddLocalRecord ()
    {
        $r = new TitleRecord();
        $l = new LocalRecord();
        $this->assertEquals(0, count($r->getLocalRecords()));
        $r->addLocalRecord($l);
        $this->assertEquals(1, count($r->getLocalRecords()));
        return $r;
    }

    /**
     * @depends testAddLocalRecord
     */
    public function testRemoveLocalRecord (TitleRecord $r)
    {
        $l = $r->getLocalRecords();
        $l = end($l);
        $r->removeLocalRecord($l);
        $this->assertEquals(0, count($r->getLocalRecords()));
    }

    public function testSetFields ()
    {
        $r = new TitleRecord();
        $r->append(new Field('003@', 0));
        $this->assertEquals(1, count($r->getFields()));
    }

    public function testSetFieldsCreatesNewLocalRecord ()
    {
        $r = new TitleRecord();
        $fields = array();
        $fields []= new Field('003@', 0);
        $r->setFields($fields);
        $this->assertEquals(0, count($r->getLocalRecords()));
        $fields []= new Field('101@', 0, array(new Subfield('a', 1)));
        $r->setFields($fields);
        $this->assertEquals(1, count($r->getLocalRecords()));
        $fields [] = new Field('200@', 0);
        $r->setFields($fields);
        $this->assertEquals(1, count($r->getLocalRecords()));
        $fields []= new Field('101@', 0, array(new Subfield('a', 2)));
        $r->setFields($fields);
        $this->assertEquals(2, count($r->getLocalRecords()));
    }

    public function testGetLocalRecordByILN ()
    {
        $r = new TitleRecord();
        $r->addLocalRecord(new LocalRecord(array(new Field('101@', 0, array(new Subfield('a', 11))))));
        $r->addLocalRecord(new LocalRecord(array(new Field('101@', 0, array(new Subfield('a', 99))))));
        $l = $r->getLocalRecordByILN(11);
        $this->assertInstanceOf('HAB\\Pica\\Record\\LocalRecord', $l);
        $this->assertEquals(11, $l->getILN());
        $l = $r->getLocalRecordByILN(33);
        $this->assertNull($l);
    }

    public function testGetPPN ()
    {
        $r = new TitleRecord();
        $this->assertNull($r->getPPN());
        $r->append(new Field('003@', 0, array(new Subfield('0', 'something'))));
        $this->assertEquals('something', $r->getPPN());
    }

    public function testSetPPN ()
    {
        $r = new TitleRecord();
        $r->setPPN('something');
        $this->assertEquals(1, count($r->getFields('003@/00')));
        $r->setPPN('something else');
        $this->assertEquals('something else', $r->getPPN());
    }

    public function testContainsLocalRecord ()
    {
        $r = new TitleRecord();
        $l = new LocalRecord();
        $this->assertFalse($r->containsLocalRecord($l));
        $r->addLocalRecord($l);
        $this->assertTrue($r->containsLocalRecord($l));
    }
}
