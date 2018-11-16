<?php
/**
 * Created by Aurélien RICHAUD (14/11/2018 14:57)
 */

namespace Circle\DoctrineRestDriver\Router;


interface RoutingTableInterface
{
    /**
     * @param string $alias
     * @return Routing
     */
    public function get($alias);
}