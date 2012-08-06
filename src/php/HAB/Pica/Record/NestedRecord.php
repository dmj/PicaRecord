<?php

/**
 * The NestedRecord class file.
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
 * Abstract base class of nested records.
 *
 * A nested record is a record that contains zero or more other records. It is
 * the base class of {@link TitleRecord title} and {@link LocalRecord local}
 * records and implements internal accessors for the contained records and the
 * propagation of field getters to the contained records.
 *
 * @package   PicaRecord
 * @author    David Maus <maus@hab.de>
 * @copyright Copyright (c) 2012 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 */
abstract class NestedRecord extends Record {

  /**
   * Contained records.
   *
   * @var array
   */
  protected $_records = array();

  /**
   * Delete fields matching predicate.
   *
   * The delete() is propagated down to all contained records.
   *
   * @see \HAB\Pica\Record\Record::delete()
   *
   * @param  callback $where Predicate
   * @return void
   */
  public function delete ($where) {
    parent::delete($where);
    Helper::mapMethod($this->_records, 'delete', array($where));
  }

  /**
   * Sort fields and contained records.
   *
   * The sort() is propagated down to all contained records. In addition the
   * nested records are sorted themselves using the implementing class'
   * compareNestedRecords() function.
   *
   * @see \HAB\Pica\Record\Record::sort()
   * @see \HAB\Pica\Record\NestedRecord::compareNestedRecords()
   *
   * @return void
   */
  public function sort () {
    parent::sort();
    Helper::mapMethod($this->_records, 'sort');
    usort($this->_records, array($this, 'compareRecords'));
  }

  /**
   * Return TRUE if the record is empty.
   *
   * A nested record is empty iff it contains no fields and no non-empty
   * contained record.
   *
   * @return boolean TRUE if the record is empty
   */
  public function isEmpty () {
    return parent::isEmpty() && Helper::every($this->_records, function (Record $record) { return $record->isEmpty(); });
  }

  /**
   * Return TRUE if the record is valid.
   *
   * A nested record is valid iff it and all contained records are valid.
   *
   * @see \HAB\Pica\Record\Record::isValid()
   *
   * @return boolean True if the record is valid
   */
  public function isValid () {
    return parent::isValid() && !Helper::every($this->_records, function (Record $record) { return $record->isValid(); });
  }

  /**
   * Return fields of the record.
   *
   * @see \HAB\Pica\Record\Record::getFields()
   *
   * @param  string $selector Body of regular expression
   * @return array Fields
   */
  public function getFields ($selector = null) {
    if ($selector === null) {
      return array_merge($this->_fields, Helper::flatten(Helper::mapMethod($this->_records, 'getFields')));
    } else {
      return $this->select(Field::match($selector));
    }
  }

  /**
   * Compare two contained records and return a comparism value suitable for
   * usort().
   *
   * @see http://www.php.net/manual/en/function.usort.php
   *
   * @param  \HAB\Pica\Record\Record $a First record
   * @param  \HAB\Pica\Record\Record $b Second record
   * @return integer Comparism value
   */
  abstract protected function compareRecords (\HAB\Pica\Record\Record $a, \HAB\Pica\Record\Record $b);

  /**
   * Add a record as a contained record.
   *
   * @throws \InvalidArgumentException Record already contains the record
   * @param  \HAB\Pica\Record\Record $record Record to add
   * @return void
   */
  protected function addRecord (\HAB\Pica\Record\Record $record) {
    if ($this->containsRecord($record)) {
      throw new \InvalidArgumentException("{$this} already contains {$record}");
    }
    $this->_records []= $record;
  }

  /**
   * Remove a contained record.
   *
   * @throws \InvalidArgumentException Record does not contain the record
   * @param  \HAB\Pica\Record\Record $record Record to remove
   * @return void
   */
  protected function removeRecord (\HAB\Pica\Record\Record $record) {
    $index = array_search($record, $this->_records, true);
    if ($index === false) {
      throw new \InvalidArgumentException("{$this} does not contain {$record}");
    }
    unset($this->_records[$index]);
  }

  /**
   * Return true if this record contains the requested record.
   *
   * @param  \HAB\Pica\Record\Record Record to check
   * @return boolean
   */
  protected function containsRecord (\HAB\Pica\Record\Record $record) {
    return in_array($record, $this->_records, true);
  }

 /**
   * Finalize the clone() operation.
   *
   * Clone all contained records.
   *
   * @return void
   */
  public function __clone () {
    $this->_records = Helper::mapClone($this->_records);
  }
}