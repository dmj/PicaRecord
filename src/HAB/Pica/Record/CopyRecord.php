<?php

/**
 * Pica+ CopyRecord.
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

class CopyRecord extends Record
{

    /**
     * The item number.
     *
     * @var integer
     */
    protected $_itemNumber;

    /**
     * Append a field to the copy record.
     *
     * You can only append field of level 2 to a copy record.
     *
     * @see Record::append()
     *
     * @throws InvalidArgumentException Field level other than 2
     * @throws InvalidArgumentException Item number mismatch
     * @throws InvalidArgumentException Field already in record
     *
     * @param  Field $field Field to append
     * @return void
     */
    public function append (Field $field)
    {
        if ($field->getLevel() !== 2) {
            throw new InvalidArgumentException("Invalid field level: {$field->getLevel()}");
        }
        if ($this->getItemNumber() === null) {
            $this->setItemNumber($field->getOccurrence());
        }
        If ($field->getOccurrence() != $this->getItemNumber()) {
            throw new InvalidArgumentException("Item number mismatch: {$this->getItemNumber()}, {$field->getOccurrence()}");
        }
        return parent::append($field);
    }

    /**
     * Return the item number.
     *
     * @return integer|null Item number
     */
    public function getItemNumber ()
    {
        return $this->_itemNumber;
    }

    /**
     * Set the item number.
     *
     * @param  integer $itemNumber Item number
     * @return void
     */
    protected function setItemNumber ($itemNumber)
    {
        $this->_itemNumber = (int)$itemNumber;
    }

    /**
     * Return the exemplar production number (EPN).
     *
     * @return string Exemplar production number
     */
    public function getEPN ()
    {
        $epnField = $this->getFirstMatchingField('203@');
        if ($epnField) {
            $epnSubfield = $epnField->getNthSubfield('0', 0);
            if ($epnSubfield) {
                return $epnSubfield->getValue();
            }
        }
        return null;
    }

    /**
     * Set the exemplar production number (EPN).
     *
     * Create a field 203@ if necessary.
     *
     * @param  string $epn Exemplar production number
     * @return void
     */
    public function setEPN ($epn)
    {
        $epnField = $this->getFirstMatchingField('203@');
        if ($epnField) {
            $epnSubfield = $epnField->getNthSubfield('0', 0);
            if ($epnSubfield) {
                $epnSubfield->setValue($epn);
            } else {
                $epnField->append(new Subfield('0', $epn));
            }
        } else {
            $this->append(new Field('203@', $this->getItemNumber(), array(new Subfield('0', $epn))));
        }
    }

    /**
     * Set the containing local record.
     *
     * @param  LocalRecord $record Local record
     * @return void
     */
    public function setLocalRecord (LocalRecord $record)
    {
        $this->unsetLocalRecord();
        if (!$record->containsCopyRecord($this)) {
            $record->addCopyRecord($this);
        }
        $this->_parent = $record;
    }

    /**
     * Unset the containing local record.
     *
     * @return void
     */
    public function unsetLocalRecord ()
    {
        if ($this->_parent) {
            if ($this->_parent->containsCopyRecord($this)) {
                $this->_parent->removeCopyRecord($this);
            }
            $this->_parent = null;
        }
    }

    /**
     * Return the containing local record.
     *
     * @return LocalRecord|null
     */
    public function getLocalRecord ()
    {
        return $this->_parent;
    }

}
