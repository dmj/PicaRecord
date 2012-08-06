<?php

/**
 * The TitleRecord class file.
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
 * A Pica+ title record.
 *
 * @package   PicaRecord
 * @author    David Maus <maus@hab.de>
 * @copyright Copyright (c) 2012 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 */
class TitleRecord extends NestedRecord {

  /**
   * Append a field to the title record.
   *
   * @see \HAB\Pica\Record\Record::append()
   *
   * You can only directly add fields with a level of 0.
   *
   * @throws \InvalidArgumentException Field level invalid
   * @throws \InvalidArgumentException Field already in record
   * @param  \HAB\Pica\Record\Field $field Field to append
   * @return void
   */
  public function append (\HAB\Pica\Record\Field $field) {
    if ($field->getLevel() !== 0) {
      throw new \InvalidArgumentException("Invalid field level: {$field->getLevel()}");
    }
    parent::append($field);
  }

  /**
   * Set the record's fields.
   *
   * @todo   Relocate to \HAB\Pica\Record\Record::factory(), maybe
   *
   * @param  array $fields Field
   * @return void
   */
  public function setFields (array $fields) {
    $this->_fields = array();
    $this->_records = array();
    $prevLevel = null;
    foreach ($fields as $field) {
      $level = $field->getLevel();
      if ($level === 0) {
        $this->append($field);
      } else {
        if ($level === 1 && $prevLevel !== 1) {
          $localRecord = new LocalRecord(array($field));
          $this->addLocalRecord($localRecord);
        } else {
          $records = $this->getLocalRecords();
          $localRecord = end($records);
          if ($level === 1) {
            $localRecord->append($field);
          } else {
            $copyRecord = $localRecord->getCopyRecordByItemNumber($field->getOccurrence());
            if ($copyRecord) {
              $copyRecord->append($field);
            } else {
              $localRecord->addCopyRecord(new CopyRecord(array($field)));
            }
          }
        }
      }
      $prevLevel = $level;
    }
  }

  /**
   * Add a local record.
   *
   * @throws \InvalidArgumentException Record already contains the local record
   * @param  \HAB\Pica\Record\LocalRecord $record Local record
   * @return void
   */
  public function addLocalRecord (\HAB\Pica\Record\LocalRecord $record) {
    $this->addRecord($record);
    $record->setTitleRecord($this);
  }

  /**
   * Remove a local record.
   *
   * @throws \HAB\Pica\Record\Exception Record does not contain the local record
   * @param  \HAB\Pica\Record\LocalRecord $record Local record to remove
   * @return void
   */
  public function removeLocalRecord (\HAB\Pica\Record\LocalRecord $record) {
    $this->removeRecord($record);
    $record->unsetTitleRecord();
  }

  /**
   * Return array of all local records.
   *
   * @return array Local records
   */
  public function getLocalRecords () {
    return $this->_records;
  }

  /**
   * Return a local record identified by its ILN.
   *
   * @param  integer $iln Intenal library number
   * @return \HAB\Pica\Record\LocalRecord|null The local record or NULL if none exists
   */
  public function getLocalRecordByILN ($iln) {
    foreach ($this->getLocalRecords() as $localRecord) {
      if ($localRecord->getILN() == $iln) {
        return $localRecord;
      }
    }
    return null;
  }

  /**
   * Return the Pica production number (record identifier).
   *
   * @return string|null Pica production number or NULL if none exists
   */
  public function getPPN () {
    $ppnField = $this->getFirstMatchingField('003@/00');
    if ($ppnField) {
      $ppnSubfield = $ppnField->getNthSubfield('0', 0);
      if ($ppnSubfield) {
        return $ppnSubfield->getValue();
      }
    }
    return null;
  }

  /**
   * Set the Pica production number.
   *
   * Create a field 003@/00 if necessary.
   *
   * @param  string $ppn Pica production number
   * @return void
   */
  public function setPPN ($ppn) {
    $ppnField = $this->getFirstMatchingField('003@/00');
    if ($ppnField) {
      $ppnSubfield = $ppnField->getNthSubfield('0', 0);
      if ($ppnSubfield) {
        $ppnSubfield->setValue($ppn);
      } else {
        $ppnField->append(new Subfield('0', $ppn));
      }
    } else {
      $this->append(new Field('003@', 0, array(new Subfield('0', $ppn))));
    }
  }

  /**
   * Return true if title record contains the local record.
   *
   * @param  \HAB\Pica\Record\LocalRecord $record Local record
   * @return boolean
   */
  public function containsLocalRecord (\HAB\Pica\Record\LocalRecord $record) {
      return $this->containsRecord($record);
  }

  /**
   * Compare two local records.
   *
   * @see \HAB\Pica\Record\NestedRecord::compareRecords()
   *
   * Local records are compared by their ILN.
   *
   * @param  \HAB\Pica\Record\Record $a First record
   * @param  \HAB\Pica\Record\Record $b Second record
   * @return Comparism value
   */
  protected function compareRecords (\HAB\Pica\Record\Record $a, \HAB\Pica\Record\Record $b) {
    return $a->getILN() - $b->getILN();
  }

}