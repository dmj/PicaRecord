<?php

/**
 * The LocalRecord class file.
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

/**
 * The Pica+ local record.
 *
 * @package   PicaRecord
 * @author    David Maus <maus@hab.de>
 * @copyright Copyright (c) 2012 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 */
class LocalRecord extends NestedRecord {

  /**
   * Append a field to the local record.
   *
   * You can only append field with a level of 0 to a local record.
   *
   * @see \HAB\Pica\Record\Record::append()
   *
   * @throws \InvalidArgumentException Field level invalid
   * @throws \InvalidArgumentException Field already in record
   * @param  \HAB\Pica\Record\Field $field Field to add
   * @return void
   */
  public function append (Field $field) {
    if ($field->getLevel() !== 1) {
      throw new \InvalidArgumentException("Invalid field level: {$field->getLevel()}");
    }
    parent::append($field);
  }

  /**
   * Add a copy record.
   *
   * @throws \InvalidArgumentException Record already contains the copy record
   * @throws \InvalidArgumentException Record already contains a copy record with the same item number
   * @param  \HAB\Pica\Record\CopyRecord $record Copy record to add
   * @return void
   */
  public function addCopyRecord (\HAB\Pica\Record\CopyRecord $record) {
    if ($this->getCopyRecordByItemNumber($record->getItemNumber())) {
      throw new \InvalidArgumentException("Cannot add copy record: Copy record with item number {$record->getItemNumber()} already present");
    }
    $this->addRecord($record);
  }

  /**
   * Remove a copy record.
   *
   * @throws \HAB\Pica\Record\Exception Record does not contain the specified copy record
   * @param  \HAB\Pica\Record\CopyRecord $record Record to remove
   * @return void
   */
  public function removeCopyRecord (\HAB\Pica\Record\CopyRecord $record) {
    $this->removeRecord($record);
  }

  /**
   * Return copy record by item number.
   *
   * @param  integer $itemNumber Item number
   * @return \HAB\Pica\Record\CopyRecord|null The copy record or null if none exists
   */
  public function getCopyRecordByItemNumber ($itemNumber) {
    foreach ($this->_records as $record) {
      if ($record->getItemNumber() === $itemNumber) {
        return $record;
      }
    }
    return null;
  }

  /**
   * Return all copy records.
   *
   * @return array Copy records
   */
  public function getCopyRecords () {
    return $this->_records;
  }

  /**
   * Return the ILN (internal library number) of the local record.
   *
   * @return integer|null ILN of the local record or NULL if none set
   */
  public function getILN () {
    $ilnField = $this->getFirstMatchingField('101@/00');
    if ($ilnField) {
      $ilnSubfield = $ilnField->getNthSubfield('a', 0);
      if ($ilnSubfield) {
        return $ilnSubfield->getValue();
      }
    }
    return null;
  }

  /**
   * Return true if local record contains the copy record.
   *
   * @param  \HAB\Pica\Record\CopyRecord $record Copy record
   * @return boolean
   */
  public function containsCopyRecord (\HAB\Pica\Record\CopyRecord $record) {
    return $this->containsRecord($record);
  }

  /**
   * Compare two copy records.
   *
   * Copyrecords are compared by their item number.
   *
   * @see \HAB\Pica\Record\CopyRecord::getItemNumber()
   * @see \HAB\Pica\Record\NestedRecord::compareRecords()
   *
   * @param  \HAB\Pica\Record\Record $a First copy record
   * @param  \HAB\Pica\Record\Record $b Second copy record
   * @return integer Comparism value
   */
  protected function compareRecords (\HAB\Pica\Record\Record $a, \HAB\Pica\Record\Record $b) {
    return $a->getItemNumber() - $b->getItemNumber();
  }
}