<?php

/**
 * Unit test for the LocalRecord class.
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

class LocalRecordTest extends \PHPUnit_FrameWork_TestCase {

  public function testAddCopyRecord () {
    $r = new LocalRecord();
    $r->addCopyRecord(new CopyRecord());
  }

  public function testClone () {
    $r = new LocalRecord();
    $c = new CopyRecord(array(new Field('200@', 11)));
    $r->addCopyRecord($c);
    $clone = clone($r);
    $this->assertNotSame($clone, $r);
    $this->assertNotSame($c, $clone->getCopyRecordByItemNumber(11));
  }

  public function testRemoveCopyRecord () {
    $r = new LocalRecord();
    $r->addCopyRecord(new CopyRecord(array(new Field('200@', 11))));
    $this->assertEquals(1, count($r->getCopyRecords()));
    $r->removeCopyRecord($r->getCopyRecordByItemNumber(11));
  }

  public function testSort () {
    $r = new LocalRecord();
    $a = new CopyRecord(array(new Field('200@', 11)));
    $b = new CopyRecord(array(new Field('200@', 99)));
    $r->addCopyRecord($b);
    $r->addCopyRecord($a);
    $this->assertSame($b, reset($r->getCopyRecords()));
    $r->sort();
    $this->assertSame($a, reset($r->getCopyRecords()));
  }

  public function testGetILN () {
    $r = new LocalRecord();
    $this->assertNull($r->getILN());
    $r->append(new Field('101@', 0, array(new Subfield('a', '50'))));
    $this->assertEquals(50, $r->getILN());
  }

  public function testSelectPropagatesDown () {
    $r = new LocalRecord();
    $c = new CopyRecord(array(new Field('200@', 11)));
    $r->addCopyRecord($c);
    $this->assertEquals(1, count($r->select(Field::match('200@/11'))));
  }

  public function testDeletePropagatesDown () {
    $r = new LocalRecord();
    $c = new CopyRecord(array(new Field('200@', 11)));
    $r->addCopyRecord($c);
    $this->assertFalse($c->isEmpty());
    $r->delete(Field::match('200@/11'));
    $this->assertTrue($c->isEmpty());
  }

  public function testIsEmpty () {
    $r = new LocalRecord();
    $this->assertTrue($r->isEmpty());
    $r->addCopyRecord(new CopyRecord());
    $this->assertTrue($r->isEmpty());
    $r->addCopyRecord(new CopyRecord(array(new Field('200@', 11))));
    $this->assertFalse($r->isEmpty());
  }

  public function testGetMaximumOccurrenceOf () {
    $r = new LocalRecord();
    $this->assertNull($r->getMaximumOccurrenceOf('144Z'));
    $r->append(new Field('144Z', 0));
    $this->assertEquals(0, $r->getMaximumOccurrenceOf('144Z'));
    $r->append(new Field('144Z', 10));
    $this->assertEquals(10, $r->getMaximumOccurrenceOf('144Z'));
  }

  ///

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testAddCopyRecordThrowsExceptionOnItemNumberCollision () {
    $r = new LocalRecord();
    $r->addCopyRecord(new CopyRecord(array(new Field('200@', 11))));
    $r->addCopyRecord(new CopyRecord(array(new Field('200@', 11))));
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testAddCopyRecordThrowsExceptionOnDuplicateCopyRecord () {
    $r = new LocalRecord();
    $c = new CopyRecord();
    $r->addCopyRecord($c);
    $r->addCopyRecord($c);
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testRemoveCopyRecordThrowsExceptionOnCopyRecordNotContainedInRecord () {
    $r = new LocalRecord();
    $c = new CopyRecord();
    $r->removeCopyRecord($c);
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testAppendThrowsExceptionOnInvalidLevel () {
    $r = new LocalRecord();
    $r->append(new Field('003@', 0));
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testGetMaximumOccurrenceOfThrowsExceptionOnInvalidFieldTag () {
    $r = new LocalRecord();
    $r->getMaximumOccurrenceOf('@@@@');
  }
}