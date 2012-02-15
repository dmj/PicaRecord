<?php

/**
 * The Helper class file.
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
 * Abstract class to anchor helper functions.
 *
 * @package   PicaRecord
 * @author    David Maus <maus@hab.de>
 * @copyright Copyright (c) 2012 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 */
abstract class Helper {

  /**
   * Return the complement of a function.
   *
   * The complement of a function is a function that takes the same arguments
   * as the original function but returns a boolean with the opposite truth
   * value.
   *
   * @param  callback $function Original function
   * @return callback Complement function
   */
  public static function complement ($function) {
    return function () use ($function) { return !call_user_func_array($function, func_get_args()); };
  }

  /**
   * Return an array of the results of calling a method for each element of a
   * sequence.
   *
   * @param  array $sequence Sequence of objects
   * @param  string $method Name of the method
   * @param  array $arguments Optional array of method arguments
   * @return array Result of calling method on each element of sequence
   */
  public static function mapMethod (array $sequence, $method, array $arguments = array()) {
    if (empty($arguments)) {
      $f = function ($element) use ($method) {
        return $element->$method();
      };
    } else {
      $f = function ($element) use ($method, $arguments) {
        return call_user_func_array(array($element, $method), $arguments);
      };
    }
    return array_map($f, $sequence);
  }

  /**
   * Return an array with clones of each element in sequence.
   *
   * @param  array $sequence Sequence of objects
   * @return array Sequence of clones
   */
  public static function mapClone (array $sequence) {
    return array_map(function ($element) { return clone($element); }, $sequence);
  }

  /**
   * Return TRUE if at leat one element of sequence matches predicate.
   *
   * @todo   Make FALSE and TRUE self-evaluating, maybe
   *
   * @param  array $sequence Sequence
   * @param  callback $predicate Predicate
   * @return boolean TRUE if at least one element matches predicate
   */
  public static function any (array $sequence, $predicate) {
    foreach ($sequence as $element) {
      if (call_user_func($predicate, $element)) {
        return true;
      }
    }
    return false;
  }

  /**
   * Return TRUE if every element of sequence fullfills predicate.
   *
   * @todo   Make FALSE and TRUE self-evaluating, maybe
   *
   * @param  array $sequence Sequence
   * @param  callback $predice Predicate
   * @return boolean TRUE if every element fullfills predicate
   */
  public static function every (array $sequence, $predicate) {
    foreach ($sequence as $element) {
      if (!call_user_func($predicate, $element)) {
        return false;
      }
    }
    return true;
  }

  /**
   * Flatten sequence.
   *
   * @param  array $sequence Sequence
   * @return array Flattend sequence
   */
  public static function flatten (array $sequence) {
    $flat = array();
    array_walk_recursive($sequence, function ($element) use (&$flat) { $flat []= $element; });
    return $flat;
  }
}