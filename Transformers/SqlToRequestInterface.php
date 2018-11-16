<?php
/**
 * Created by Aurélien RICHAUD (13/11/2018 17:30)
 */

namespace Circle\DoctrineRestDriver\Transformers;

use Circle\DoctrineRestDriver\Types\Request;


interface SqlToRequestInterface
{
    /**
     * @param string $query
     * @param array $tokens
     * @return Request
     */
    public function transform(string $query, array $tokens);
}