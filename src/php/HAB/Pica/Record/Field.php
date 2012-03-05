<?php

/**
 * The Field class file.
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
 * The Pica+ field.
 *
 * @package   PicaRecord
 * @author    David Maus <maus@hab.de>
 * @copyright Copyright (c) 2012 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 */
class Field {

  /**
   * Regular expression matching a valid Pica+ field tag.
   *
   * @var string
   */
  const TAG_RE = '|^[012][0-9]{2}[A-Z@]$|D';

  /**
   * Return TRUE if argument is a valid field tag.
   *
   * @param  mixed $arg Variable to check
   * @return boolean TRUE if argument is a valid field tag
   */
  public static function isValidFieldTag ($arg) {
    return (bool)preg_match(self::TAG_RE, $arg);
  }

  /**
   * Return TRUE if argument is a valid field occurrence.
   *
   * Argument is casted to int iff it is either null or a numeric string.
   *
   * @param  mixed $arg Variable to check
   * @return boolean TRUE if argument is a valid field occurrence
   */
  public static function isValidFieldOccurrence ($arg) {
    if ($arg === null || ctype_digit($arg)) {
      $arg = (int)$arg;
    }
    return is_int($arg) && $arg >= 0 && $arg < 100;
  }

  /**
   * Return predicate that matches a field shorthand against a regular
   * expression.
   *
   * @param  string $reBody Body of regular expression
   * @return callback Predicate
   */
  public static function match ($reBody) {
    if (strpos($reBody, '#') !== false) {
      $reBody = str_replace('#', '\#', $reBody);
    }
    $regexp = "#{$reBody}#D";
    return function (Field $field) use ($regexp) {
      return (bool)preg_match($regexp, $field->getShorthand());
    };
  }

  /**
   * Return a new field based on its array representation.
   *
   * @throws \InvalidArgumentException Missing `tag', `occurrene', or `subfields' index
   * @param  array $field Array representation of a field
   * @return \HAB\Pica\Record\Field A shiny new field
   */
  public static function factory (array $field) {
    foreach (array('tag', 'occurrence', 'subfields') as $index) {
      if (!array_key_exists($index, $field)) {
        throw new \InvalidArgumentException("Missing '{$index}' index in field array");
      }
    }
    return new Field($field['tag'],
                     $field['occurrence'],
                     array_map(array('HAB\Pica\Record\Subfield', 'factory'), $field['subfields']));
  }

  ///

  /**
   * The field tag.
   *
   * @var string
   */
  protected $_tag;

  /**
   * The field level.
   *
   * @var integer
   */
  protected $_level;

  /**
   * The field occurrence.
   *
   * @var integer
   */
  protected $_occurrence;

  /**
   * The field shorthand.
   *
   * @var string
   */
  protected $_shorthand;

  /**
   * Constructor.
   *
   * @throws \InvalidArgumentException Invalid field tag or occurrence
   * @param  string $tag Field tag
   * @param  integer $occurrence Field occurrence
   * @param  array $subfields Initial set of subfields
   * @return void
   */
  public function __construct ($tag, $occurrence, array $subfields = array()) {
    if (!self::isValidFieldTag($tag)) {
      throw new \InvalidArgumentException("Invalid field tag: $tag");
    }
    if (!self::isValidFieldOccurrence($occurrence)) {
      throw new \InvalidArgumentException("Invalid field occurrence: $occurrence");
    }
    $this->_tag = $tag;
    $this->_occurrence = (int)$occurrence;
    $this->_shorthand = sprintf('%4s/%02d', $tag, $occurrence);
    $this->_level = (int)$tag[0];
    $this->setSubfields($subfields);
  }

  /**
   * Set the field subfields.
   *
   * Replaces the subfield list with subfields in argument.
   *
   * @param  array $subfields Subfields
   * @return void
   */
  public function setSubfields (array $subfields) {
    $this->_subfields = array();
    foreach ($subfields as $subfield) {
      $this->addSubfield($subfield);
    }
  }

  /**
   * Add a subfield to the end of the subfield list.
   *
   * @throws \InvalidArgumentException Subfield already present in subfield list
   * @param  \HAB\Pica\Record\Subfield $subfield Subfield to add
   * @return void
   */
  public function addSubfield (\HAB\Pica\Record\Subfield $subfield) {
    if (in_array($subfield, $this->getSubfields(), true)) {
      throw new \InvalidArgumentException("Cannot add subfield: Subfield already part of the subfield list");
    }
    $this->_subfields []= $subfield;
  }

  /**
   * Remove a subfield.
   *
   * @throws \InvalidArgumentException Subfield is not part of the subfield list
   * @param  \HAB\Pica\Record\Subfield $subfield Subfield to delete
   * @return void
   */
  public function removeSubfield (\HAB\Pica\Record\Subfield $subfield) {
    $index = array_search($subfield, $this->_subfields, true);
    if ($index === false) {
      throw new \InvalidArgumentException("Cannot remove subfield: Subfield not part of the subfield list");
    }
    unset($this->_subfields[$index]);
  }

  /**
   * Return the field's subfields.
   *
   * Returns all subfields when called with no arguments.
   *
   * Otherwise the returned array is constructed as follows:
   *
   * Each argument is interpreted as a subfield code. The nth element of the
   * returned array maps to the nth argument in the function call and contains
   * NULL if the field does not have a subfield with the selected code, or the
   * subfield if it exists. In order to retrieve multiple subfields with an
   * identical code you repeat the subfield code in the argument list.
   *
   * @return array Subfields
   */
  public function getSubfields () {
    if (func_num_args() === 0) {
      return $this->_subfields;
    } else {
      $selected = array();
      $codes = array();
      $subfields = $this->getSubfields();
      array_walk($subfields, function ($value, $index) use (&$codes) { $codes[$index] = $value->getCode(); });
      foreach (func_get_args() as $arg) {
        $index = array_search($arg, $codes, true);
        if ($index === false) {
          $selected []= null;
        } else {
          $selected []= $subfields[$index];
          unset($codes[$index]);
        }
      }
      return $selected;
    }
  }

  /**
   * Return the nth occurrence of a subfield with specified code.
   *
   * @param  string $code Subfield code
   * @param  integer $n Zero-based subfield index
   * @return \HAB\Pica\record\Subfield|null The requested subfield or NULL if
   *         none exists
   */
  public function getNthSubfield ($code, $n) {
    $count = 0;
    foreach ($this->getSubfields() as $subfield) {
      if ($subfield->getCode() == $code) {
        if ($count == $n) {
          return $subfield;
        }
        $count++;
      }
    }
    return null;
  }

  /**
   * Return the field tag.
   *
   * @return string Field tag
   */
  public function getTag () {
    return $this->_tag;
  }

  /**
   * Return the field occurrence.
   *
   * @return integer Field occurrence
   */
  public function getOccurrence () {
    return $this->_occurrence;
  }

  /**
   * Return the field level.
   *
   * @return integer Field level
   */
  public function getLevel () {
    return $this->_level;
  }

  /**
   * Return the field shorthand.
   *
   * @return string Field shorthand
   */
  public function getShorthand () {
    return $this->_shorthand;
  }

  /**
   * Return TRUE if the field is empty.
   *
   * A field is empty if it contains no subfields.
   *
   * @return boolean TRUE if the field is empty
   */
  public function isEmpty () {
    return empty($this->_subfields);
  }

  /**
   * Finalize the clone() operation.
   *
   * @return void
   */
  public function __clone () {
    $this->_subfields = Helper::mapClone($this->_subfields);
  }

  /**
   * Return a printable representation of the field.
   *
   * The printable representation of a field is its shorthand.
   *
   * @return string Printable representation of the field
   */
  public function __toString () {
    return $this->getShorthand();
  }
}