<?php
/**
 * Created by Aurélien RICHAUD (14/11/2018 15:07)
 */

namespace Circle\DoctrineRestDriver\Router;


use Circle\DoctrineRestDriver\Enums\HttpMethods;
use Circle\DoctrineRestDriver\Types\Annotation;
use Circle\DoctrineRestDriver\Types\Identifier;
use Circle\DoctrineRestDriver\Types\SqlOperation;
use Circle\DoctrineRestDriver\Types\Table;
use Circle\DoctrineRestDriver\Types\Url;

class DefaultEntityRouter implements EntityRouterInterface
{
    /**
     * @var RoutingTable
     */
    protected $routingTable;

    /**
     * @var array
     */
    protected $options;

    public function __construct(RoutingTable $routingTable, array $options)
    {
        $this->routingTable = $routingTable;
        $this->options = $options;
    }

    /**
     * @param array $tokens Les tokens de la requête
     *
     * @return string L'URL sur laquelle la requête sera faite
     * @throws \Circle\DoctrineRestDriver\Exceptions\InvalidSqlOperationException
     */
    public function getUrl(array $tokens)
    {
        $usePatch = isset($this->options['driverOptions']['use_patch']) ? $this->options['driverOptions']['use_patch'] : false;

        $method = HttpMethods::ofSqlOperation(SqlOperation::create($tokens), $usePatch);
        $id = Identifier::create($tokens);
        if ($id && count(explode(',', $id)) > 1) {
            $id = '';
        } else {
            $id = preg_replace('/\"|\\\'|\`$/', '', preg_replace('/^\"|\\\'|\`/', '', $id));
        }
        $annotation = Annotation::get($this->routingTable, Table::create($tokens), ($method != 'get' || $id ? $method : 'getall'));

        return Url::createFromTokens($tokens, $this->options['host'], $annotation);
    }
}