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


/**
 * Maps the response content of a GET query to a valid
 * Doctrine result for SELECT ...
 *
 * @author    Tobias Hauck <tobias@circle.ai>
 * @copyright 2015 TeeAge-Beatz UG
 */
class SelectResult {

    /**
     * Returns a valid Doctrine result for SELECT ...
     *
     * @param  array $tokens
     * @param  array $content
     * @return mixed
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     * @throws \Circle\DoctrineRestDriver\Validation\Exceptions\InvalidTypeException
     * @throws \Exception
     */
    public static function create(array $tokens, $content) {
        if (!is_array($content)) return [];

        $hasAgregateFunctions = false;
        foreach ($tokens['SELECT'] as $column) {
            if ($column['expr_type'] == 'aggregate_function') {
                $hasAgregateFunctions = true;
            }
        }

        if ( $hasAgregateFunctions ) {
            return SelectAggregatedResult::create($tokens, $content);
        } elseif ( empty($content[0]) ) {
            return  SelectSingleResult::create($tokens, $content);
        } else {
            return SelectAllResult::create($tokens, $content);
        }
    }
}