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

namespace Circle\DoctrineRestDriver\Transformers;

use Circle\DoctrineRestDriver\Enums\HttpMethods;
use Circle\DoctrineRestDriver\Factory\RequestFactory;
use Circle\DoctrineRestDriver\Router\EntityRouterInterface;
use Circle\DoctrineRestDriver\Types\Request;
use Circle\DoctrineRestDriver\Types\SqlOperation;

/**
 * Transforms a given sql query to a corresponding request
 *
 * @author    Tobias Hauck <tobias@circle.ai>
 * @copyright 2015 TeeAge-Beatz UG
 */
class MysqlToRequest implements SqlToRequestInterface
{
    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var array
     */
    private $options;

    /**
     * @var EntityRouterInterface
     */
    private $router;

    /**
     * MysqlToRequest constructor
     *
     * @param array $options
     * @param EntityRouterInterface $router
     */
    public function __construct(array $options, EntityRouterInterface $router)
    {
        $this->options = $options;
        $this->requestFactory = new RequestFactory();
        $this->router = $router;
    }

    /**
     * Transforms the given query into a request object
     *
     * @param  string $query
     * @param array $tokens
     * @return Request
     *
     * @throws \Circle\DoctrineRestDriver\Exceptions\InvalidSqlOperationException
     * @throws \Circle\DoctrineRestDriver\Validation\Exceptions\InvalidTypeException
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function transform(string $query, array $tokens)
    {
        $usePatch = isset($this->options['driverOptions']['use_patch']) ? $this->options['driverOptions']['use_patch'] : false;
        $method = HttpMethods::ofSqlOperation(SqlOperation::create($tokens), $usePatch);

        $url = $this->router->getUrl( $tokens );

        return $this->requestFactory->createOne($method, $tokens, $this->options, $url);
    }
}