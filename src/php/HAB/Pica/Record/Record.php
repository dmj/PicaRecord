<?php

/**
 * The Record class file.
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
 * Abstract base class of all record structures.
 *
 * The abstract base class defines and partially implements the interface to
 * all record structures. This class is the direct parent of records that do
 * not contain other records, i.e. {@link AuthorityRecord authority} and
 * {@link CopyRecord copy} records.
 *
 * @package   PicaRecord
 * @author    David Maus <maus@hab.de>
 * @copyright Copyright (c) 2012 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 */
abstract class Record {

  /**
   * Return a new record based on its array representation.
   *
   * Returns either a {@link TitleRecord} or a {@link AuthorityRecord}
   * depending on the field 002@ which encodes the record type.
   *
   * @throws \InvalidArgumentException Missing type field
   * @throws \InvalidArgumentException Missing `fields' index
   * @param  array $record Array representation of a record
   * @return \HAB\Pica\Record\TitleRecord|\HAB\Pica\Record\AuthorityRecord New record instance
   */
  public static function factory (array $record) {
    if (!array_key_exists('fields', $record)) {
      throw new \InvalidArgumentException("Missing 'fields' index in record array");
    }
    $fields = array_map(array('HAB\Pica\Record\Field', 'factory'), $record['fields']);
    $type = null;
    $typePredicate = Field::match('002@/00');
    foreach ($fields as $field) {
      if ($typePredicate($field)) {
        $typeSubfield = reset($field->getSubfields('0'));
        if ($typeSubfield) {
          $type = $typeSubfield->getValue();
          break;
        }
      }
    }
    if ($type === null) {
      throw new \InvalidArgumentException("Missing type field (002@/00$0)");
    }
    if ($type[0] === 'T') {
      return new AuthorityRecord($fields);
    } else {
      return new TitleRecord($fields);
    }
  }

  ///

  /**
   * The record fields.
   *
   * @var array
   */
  protected $_fields = array();

  /**
   * Constructor.
   *
   * @param  array $field Initial set of fields
   * @return void
   */
  public function __construct (array $fields = array()) {
    $this->setFields($fields);
  }

  /**
   * Return array of fields matching predicate.
   *
   * @param  callback $where Predicate
   * @return array Matching fields
   */
  public function select ($where) {
    return array_filter($this->getFields(), $where);
  }

  /**
   * Delete fields matching predicate.
   *
   * @param  callback $where Predicate
   * @return void
   */
  public function delete ($where) {
    $complement = Helper::complement($where);
    $this->_fields = array_filter($this->_fields, $complement);
  }

  /**
   * Append a field to the record.
   *
   * @throws \InvalidArgumentException Field already in record
   * @param  \HAB\Pica\Record\Field $field Field to append
   * @return void
   */
  public function append (Field $field) {
    if (in_array($field, $this->_fields, true)) {
      throw new \InvalidArgumentException("{$this} already contains {$field}");
    }
    $this->_fields []= $field;
  }

  /**
   * Sort the fields of the record.
   *
   * Fields are sorted by their shorthand.
   *
   * @see \HAB\Pica\Record\Field::getShorthand()
   *
   * @return void
   */
  public function sort () {
    usort($this->_fields,
          function (Field $fieldA, Field $fieldB) {
            return strcmp($fieldA->getShorthand(), $fieldB->getShorthand());
          });
  }

  /**
   * Set the record fields.
   *
   * Removes the current set of fields and replaces it with the fields in
   * argument.
   *
   * @param  array $fields Fields
   * @return void
   */
  public function setFields (array $fields) {
    $this->_fields = array();
    foreach ($fields as $field) {
      $this->append($field);
    }
  }

  /**
   * Return TRUE if the record is empty.
   *
   * A record is empty if it contains no fields.
   *
   * @return boolean TRUE if record is empty
   */
  public function isEmpty () {
    return empty($this->_fields);
  }

  /**
   * Return TRUE if the record is valid.
   *
   * The base implementation checks that record is not empty and does not
   * contain an empty field.
   *
   * @return boolean TRUE if the record is valid
   */
  public function isValid () {
    return !$this->isEmpty() && !Helper::any($this->getFields(), function (Field $field) { return $field->isEmpty(); });
  }

  /**
   * Return fields of the record.
   *
   * Optional argument $selector is the body of a regular expression. If set,
   * this function returns only fields whose shorthand is matched by the
   * regular expression.
   *
   * @see \HAB\Pica\Record\Field::match()
   *
   * @param  string $selector Body of regular expression
   * @return array Fields
   */
  public function getFields ($selector = null) {
    if ($selector === null) {
      return $this->_fields;
    } else {
      return $this->select(Field::match($selector));
    }
  }

 /**
   * Finalize the clone() operation.
   *
   * @return void
   */
  public function __clone () {
    $this->_fields = Helper::mapClone($this->_fields);
  }

  /**
   * Return a printable representation of the record.
   *
   * The printable representation of a record is the object hash prefixed by
   * the class name.
   *
   * @return string Printable representation of the record
   */
  public function __toString () {
    return get_class($this) . ':' . spl_object_hash($this);
  }
}