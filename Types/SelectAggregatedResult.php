<?php
/**
 * Created by Aurélien RICHAUD (20/11/2018 09:23)
 */

namespace Circle\DoctrineRestDriver\Types;


class SelectAggregatedResult
{
    /**
     * Returns a valid Doctrine result for SELECT COUNT, MIN, MAX,... [GROUP BY]
     *
     * @param  array $tokens
     * @param  array $content
     * @return array
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     * @throws \Exception
     */
    public static function create(array $tokens, array $content) {
        return [self::groupBy($tokens, $content)];
    }

    /**
     * @param array $tokens
     * @param array $content
     * @return array
     * @throws \Exception
     */
    public static function groupBy(array $tokens, array $content)
    {
        if (!isset($tokens['SELECT'])) {
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