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

class RecordTest extends \PHPUnit_FrameWork_TestCase {

  public function testFactoryCreatesAuthorityRecord () {
    $record = Record::factory(array('fields' => array(
                                      array('tag' => '002@',
                                            'occurrence' => 0,
                                            'subfields' => array(
                                              array('code' => '0',
                                                    'value' => 'T'))))));
    $this->assertInstanceOf('HAB\Pica\Record\AuthorityRecord', $record);
  }

  public function testGetFirstMatchingField () {
    $record = new AuthorityRecord(array(new Field('001@', 0),
                                        new Field('001@', 1)));
    $this->assertNull($record->getFirstMatchingField('002@/00'));
    $this->assertInstanceOf('HAB\Pica\Record\Field', $record->getFirstMatchingField('001@'));
  }

  ///

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testFactoryThrowsExceptionOnMissingFieldsIndex () {
    Record::factory(array());
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testFactoryThrowsExceptionOnMissingTypeField () {
    $record = Record::factory(array('fields' => array()));
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testFactoryThrowsExceptionOnMissingTypeSubfield () {
    $record = Record::factory(array('fields' => array(
                                      array('tag' => '002@',
                                            'occurrence' => 0,
                                            'subfields' => array()))));
  }
}