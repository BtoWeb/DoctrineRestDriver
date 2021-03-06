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

use function Clue\StreamFilter\fun;

/**
 * Maps the response content of a GET query to a valid
 * Doctrine result for SELECT ... WHERE id = <id>
 *
 * @author    Tobias Hauck <tobias@circle.ai>
 * @copyright 2015 TeeAge-Beatz UG
 */
class SelectSingleResult {

    /**
     * Returns a valid Doctrine result for SELECT ... WHERE id = <id>
     *
     * @param  array $tokens
     * @param  array $content
     * @return array
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     * @throws \Circle\DoctrineRestDriver\Validation\Exceptions\InvalidTypeException
     */
    public static function create(array $tokens, $content)
    {
        HashMap::assert($tokens, 'tokens');
        $tableAlias = Table::alias($tokens);

        // Traitement des jointures
        if (isset($tokens['FROM'])) {
            $contents = [];

            foreach ($tokens['FROM'] as $table) {
                $alias = $table['alias']['name'] ?? '';
                $name = $table['table'].'s' ?? '';

                // Détecte une jointure
                if (isset($content[$name]) && is_array($content[$name])) {

                    foreach ($content[$name] as $values) {
                        $newLine = $content;

                        foreach ($values as $key => $value) {
                            $newLine[$alias.'.'.$key] = $value;
                        }

                        unset($newLine[$name]);
                        $contents[] = $newLine;
                    }

                    unset($content[$name]);

                }
            }

            if ( count($contents) ) {
                $ret = [];
                foreach($contents as $c) {
                    $ret = array_merge($ret, self::create($tokens, $c));
                }

                return $ret;
            }
        }

        $usableTokens = array_filter(
            $tokens['SELECT'],
            function ($token) {
                return ($token['expr_type'] ?? 'reserved') !== 'reserved';
            }
        );

        $attributeValueMap = array_map(
            function ($token) use ($content, $tableAlias) {
                $key = empty($token['alias']['name']) ? $token['base_expr'] : $token['alias']['name'];

                if ($token['expr_type'] === 'aggregate_function') {
                    $value = $content[$key];
                } else {
                    $value = $content[$token['base_expr']] ?? $content[str_replace($tableAlias.'.', '', $token['base_expr'])] ?? null;
                }

                return [$key => $value];
            },
            $usableTokens
        );

        // Récupère toutes les entités
        $ret = [array_reduce($attributeValueMap, 'array_merge', [])];

        return $ret;
    }
}