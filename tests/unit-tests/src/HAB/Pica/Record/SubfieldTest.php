<?php

/**
 * Unit test for the Subfield class.
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

use PHPUnit\Framework\TestCase;

class SubfieldTest extends TestCase
{

    public function testValidSubfieldCodeZero ()
    {
        $this->assertTrue(Subfield::isValidSubfieldCode('0'));
    }

    public function testInvalidSubfieldCodeTrailingNewline ()
    {
        $this->assertFalse(Subfield::isValidSubfieldCode("a\n"));
    }

    public function testFactory ()
    {
        $s = Subfield::factory(array('code' => 'a', 'value' => 'valid'));
        $this->assertInstanceOf('HAB\Pica\Record\Subfield', $s);
        $this->assertEquals('a', $s->getCode());
        $this->assertEquals('valid', $s->getValue());
    }

    ///

    public function testGetValue ()
    {
        $s = new Subfield('a', 'valid');
        $this->assertEquals('valid', $s->getValue());
    }

    public function testGetCode ()
    {
        $s = new Subfield('a', 'valid');
        $this->assertEquals('a', $s->getCode());
    }

    public function testToString () {
        $s = new Subfield('a', 'valid');
        $this->assertEquals('valid', (string)$s);
    }

    ///

    public function testConstructorThrowsExceptionOnInvalidCode ()
    {
        $this->expectException('InvalidArgumentException');
        new Subfield(null, 'valid');
    }

    public function testFactoryThrowsExceptionOnMissingCodeIndex ()
    {
        $this->expectException('InvalidArgumentException');
        Subfield::factory(array('value' => 'valid'));
    }

    public function testFactoryThrowsExceptionOnMissingValueIndex ()
    {
        $this->expectException('InvalidArgumentException');
        Subfield::factory(array('code' => 'a'));
    }

}
