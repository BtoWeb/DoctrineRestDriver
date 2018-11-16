<?php
/**
 * This file is part of DoctrineRestDriver.
 *
 * DoctrineRestDriver is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DoctrineRestDriver is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DoctrineRestDriver.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Circle\DoctrineRestDriver\Types;

use Circle\DoctrineRestDriver\Validation\Assertions;
use Circle\DoctrineRestDriver\Validation\Exceptions\InvalidTypeException;

/**
 * Value type
 *
 * @author    Tobias Hauck <tobias@circle.ai>
 * @copyright 2015 TeeAge-Beatz UG
 */
class Value
{

    /**
     * Infers the type of a given string
     *
     * @param  string $value
     * @return string
     * @throws InvalidTypeException
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public static function create($value)
    {
        Str::assert($value, 'value');

        if ($value === 'true') return true;
        if ($value === 'false') return false;
        if ($value === 'null') return null;

        $unquoted = preg_replace('#^\'(.*?)\'$#', '', preg_replace('/^\"|\\\'|\`/', '', $value));

        if ($unquoted != $value) {
            // TODO : A améliorer (json des champs au format JSON)
            if ( (
                    (substr($unquoted, 0, 1) == '{' && substr($unquoted, -1) == '}')
                    || (substr($unquoted, 0, 1) == '[' && substr($unquoted, -1) == ']')
                ) && json_decode($unquoted) ) {
                return json_decode($unquoted);
            }

            return $unquoted;
        } elseif ((string)intval($unquoted) === $unquoted) {
            return intval($unquoted);
        } else {
            return floatval($unquoted);
        }
    }
}
