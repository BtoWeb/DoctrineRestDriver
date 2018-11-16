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
 * Doctrine result for SELECT ... without WHERE id = <id>
 *
 * @author    Tobias Hauck <tobias@circle.ai>
 * @copyright 2015 TeeAge-Beatz UG
 */
class SelectAllResult {

    /**
     * Returns a valid Doctrine result for SELECT ... without WHERE id = <id>
     *
     * @param  array $tokens
     * @param  array $content
     * @return string
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     * @throws \Exception
     */
    public static function create(array $tokens, array $content) {
        $content = self::orderBy($tokens, $content);
        $content = self::groupBy($tokens, $content);

        if ( ! isset($content[0]) ) {
            return SelectSingleResult::create($tokens, $content);
        }

        return array_map(function($entry) use ($tokens) {
            $row = SelectSingleResult::create($tokens, $entry);
            return array_pop($row);
        }, $content);
    }

    /**
     * Orders the content with the given order by criteria
     *
     * @param  array $tokens
     * @param  array $content
     * @return array
     */
    public static function orderBy(array $tokens, array $content) {
        if (empty($tokens['ORDER'])) return $content;

        $sortingRules = array_map(function($token) use ($content) {
            return [
                end($token['no_quotes']['parts']),
                $token['direction']
            ];
        }, $tokens['ORDER']);

        $reducedSortingRules = array_reduce($sortingRules, 'array_merge', []);
        $sortArgs            = array_map(function($value) use ($content) {
            if ($value === 'ASC')  return SORT_ASC;
            if ($value === 'DESC') return SORT_DESC;

            $contents = [];
            foreach ($content as $c) array_push($contents, $c[$value]);
            return $contents;
        }, $reducedSortingRules);

        $sortArgs[] = &$content;
        call_user_func_array('array_multisort', $sortArgs);

        return end($sortArgs);
    }

    public static function groupBy(array $tokens, array $content)
    {

        if (!isset($tokens['SELECT'])) {
            return $content;
        }
        $hasAgregateFunctions = false;

        foreach ($tokens['SELECT'] as $column) {
            if ($column['expr_type'] == 'aggregate_function') {
                $hasAgregateFunctions = true;
            }
        }

        // Aucune fonction d'agregation ? On retourne le résultat direct
        if (!$hasAgregateFunctions) {
            return $content;
        }

        $ret = [];

        foreach ($tokens['SELECT'] as $column) {
            switch ($column['expr_type']) {
                case 'reserved':
                    break;

                case 'aggregate_function':
                    $ret[$column['alias']['name']] = null;

                    switch ($column['base_expr']) {
                        case 'count':
                            $ret[$column['alias']['name']] = array_reduce(
                                $content,
                                function ($count, $item) {
                                    return ++$count;
                                },
                                0
                            );
                            break;

                        default:
                            // TODO : Correct me
                            throw new \Exception("aggregate_function unknown : " . json_encode($column));
                    }

                    break;

                default:
                    // TODO : Correct me
                    throw new \Exception("column type unkown : " . json_encode($column));
            }
        }

        return $ret;
    }
}