<?php

/**
 * Pica+ AuthorityRecord.
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
 * @copyright Copyright (c) 2012-2019 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 */

namespace HAB\Pica\Record;

use InvalidArgumentException;

class AuthorityRecord extends Record
{

    /**
     * Append a field to the record.
     *
     * @see Record::append()
     *
     * @throws InvalidArgumentException Field level other than 0
     * @throws InvalidArgumentException Field already in record
     *
     * @param  Field $field Field to append
     * @return void
     */
    public function append (Field $field)
    {
        if ($field->getLevel() !== 0) {
            throw new InvalidArgumentException("Invalid field level {$field->getLevel()}");
        }
        return parent::append($field);
    }

    /**
     * Return the Pica production number (record identifier).
     *
     * @return string|null Pica production number or NULL if none exists
     */
    public function getPPN ()
    {
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
    public function setPPN ($ppn)
    {
        $ppnField = $this->getFirstMatchingField('003@/00');
        if ($ppnField) {
            $ppnSubfield = $ppnField->getNthSubfield('0', 0);
            if ($ppnSubfield) {
                $ppnSubfield->setValue($ppn);
            } else {
                $ppnField->addSubfield(new Subfield('0', $ppn));
            }
        } else {
            $this->append(new Field('003@', 0, array(new Subfield('0', $ppn))));
        }
    }

    /**
     * Return TRUE if the record is valid.
     *
     * A valid authority record MUST have exactly one valid PPN field
     * (003@/00$0) and exactly one type field (002@/0$0) with a type indicator
     * `T'.
     *
     * @see AuthorityRecord::checkPPN();
     * @see AuthorityRecord::checkType();
     *
     * @return boolean TRUE if the record is valid
     */
    public function isValid ()
    {
        return parent::isValid() && $this->checkPPN() && $this->checkType();
    }

    /**
     * Return true if the record has exactly one PPN field (003@/00) with a
     * subfield $0.
     *
     * @return boolean True if the record has a valid PPN field
     */
    protected function checkPPN ()
    {
        $ppnField = $this->getFields('003@/00');
        if (count($ppnField) === 1) {
            $ppnSubfield = reset($ppnField)->getNthSubfield('0', 0);
            if ($ppnSubfield) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return true if the record has exactly one type field (002@/00) with a
     * subfield $0 whose first character is `T'.
     *
     * @return boolean True if the record has a valid type field
     */
    protected function checkType ()
    {
        $typeField = $this->getFields('002@/00');
        if (count($typeField) === 1) {
            $typeSubfield = reset($typeField)->getNthSubfield('0', 0);
            if ($typeSubfield) {
                $typeCode = $typeSubfield->getValue();
                if ($typeCode === 'T') {
                    return true;
                }
            }
        }
    }
}
