<?php
/**
 * Created by Aurélien RICHAUD (14/11/2018 14:47)
 */

namespace Circle\DoctrineRestDriver\Router;


interface EntityRouterInterface
{
    /**
     * @param array $tokens Les tokens de la requête
     *
     * @return string L'URL sur laquelle la requête sera faite
     */
    public function getUrl(array $tokens);
}