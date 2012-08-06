<?php

/**
 * Unit test for the CopyRecord class.
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

class CopyRecordTest extends \PHPUnit_FrameWork_TestCase {

  public function testGetEPN () {
    $r = new CopyRecord();
    $this->assertNull($r->getEPN());
    $r->append(new Field('203@', 0, array(new Subfield('0', 'something'))));
    $this->assertEquals('something', $r->getEPN());
  }

  public function testSetEPN () {
    $r = new CopyRecord(array(new Field('203@', 0, array(new Subfield('0', 'something')))));
    $this->assertEquals('something', $r->getEPN());
    $r->setEPN('epn');
    $this->assertEquals('epn', $r->getEPN());
  }

  public function testLocalRecordReference () {
    $l = new LocalRecord();
    $c = new CopyRecord();
    $this->assertNull($c->getLocalRecord());
    $l->addCopyRecord($c);
    $this->assertSame($l, $c->getLocalRecord());
    $c->unsetLocalRecord();
    $this->assertNull($c->getLocalRecord());
    $this->assertFalse($l->containsCopyRecord($c));
  }

  ///

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testAppendThrowsExceptionOnInvalidFieldLevel () {
    $r = new CopyRecord();
    $r->append(new Field('003@', 0));
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testAppendThrowsExceptionOnNumberMismatch () {
    $r = new CopyRecord();
    $r->append(new Field('201@', 0));
    $r->append(new Field('202@', 1));
  }

}
