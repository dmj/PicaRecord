<?php

/**
 * Pica+ subfield.
 *
 * A subfield is a cons of an alphanumeric character and a possibly empty
 * string value.
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
 * @copyright Copyright (c) 2012 - 2016 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 */

namespace HAB\Pica\Record;

use InvalidArgumentException;

class Subfield
{
    /**
     * Regular expression matching a valid subfield code.
     *
     * @var string
     */
    public static $validSubfieldCodePattern = '/^[a-z0-9#]$/Di';

    /**
     * Return true if argument is a valid subfield code.
     *
     * @param  mixed $arg Variable to check
     * @return boolean
     */
    public static function isValidSubfieldCode ($arg)
    {
        return (bool)preg_match(self::$validSubfieldCodePattern, $arg);
    }

    /**
     * Return a new subfield based on its array representation.
     *
     * The array representation of a subfield is an associative array with the
     * keys `code' and `value', holding the subfield code and value.
     *
     * @throws InvalidArgumentException Missing code or value index
     *
     * @param  array $subfield Array representation of a subfield
     * @return Subfield New subfield
     */
    public static function factory (array $subfield)
    {
        if (!array_key_exists('code', $subfield)) {
            throw new InvalidArgumentException("Missing 'code' index in subfield array");
        }
        if (!array_key_exists('value', $subfield)) {
            throw new InvalidArgumentException("Missing 'value' index in subfield array");
        }
        return new Subfield($subfield['code'], $subfield['value']);
    }

    ///

    /**
     * The subfield code.
     *
     * @var string
     */
    protected $_code;

    /**
     * The subfield value.
     *
     * @var string Value
     */
    protected $_value;

    /**
     * Constructor.
     *
     * @throws InvalidArgumentException Invalid subfield code
     *
     * @param  string $code Subfield code
     * @param  string $value Subfield value
     * @return void
     */
    public function __construct ($code, $value)
    {
        if (!self::isValidSubfieldCode($code)) {
            throw new InvalidArgumentException("Invalid subfield code: {$code}");
        }
        $this->_code = $code;
        $this->setValue($value);
    }

    /**
     * Set the subfield value.
     *
     * @param  string $value Subfield value
     * @return void
     */
    public function setValue ($value)
    {
        $this->_value = $value;
    }

    /**
     * Return the subfield value.
     *
     * @return string Subfield value
     */
    public function getValue ()
    {
        return $this->_value;
    }

    /**
     * Return the subfield code.
     *
     * @return string Subfield code
     */
    public function getCode ()
    {
        return $this->_code;
    }

    /**
     * Return printable representation of the subfield.
     *
     * The printable representation of a subfield is its value.
     *
     * @return string Subfield value
     */
    public function __toString ()
    {
        return $this->getValue();
    }
}
