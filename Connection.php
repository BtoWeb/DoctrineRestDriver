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

namespace Circle\DoctrineRestDriver;

use Circle\DoctrineRestDriver\Router\EntityRouterInterface;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection as AbstractConnection;

/**
 * Doctrine connection for the rest driver
 *
 * @author    Tobias Hauck <tobias@circle.ai>
 * @copyright 2015 TeeAge-Beatz UG
 */
class Connection extends AbstractConnection
{

    /**
     * @var Statement
     */
    private $statement;

    /**
     * @var EntityRouterInterface
     */
    private $router;

    /**
     * @var ClassMetadataFactory
     */
    private $metadataFactory;

    /**
     * Connection constructor
     *
     * @param array $params
     * @param Driver $driver
     * @param EntityRouterInterface $router
     * @param Configuration|null $config
     * @param EventManager|null $eventManager
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(array $params, Driver $driver, EntityRouterInterface $router = null, Configuration $config = null, EventManager $eventManager = null)
    {
        $this->router = $router;
        parent::__construct($params, $driver, $config, $eventManager);
    }

    /**
     * prepares the statement execution
     *
     * @param  string $statement
     * @return Statement
     * @throws \Exception
     */
    public function prepare($statement)
    {
        $this->connect();

        $this->statement = new Statement($statement, $this->getParams(), $this->router, $this->metadataFactory);
        $this->statement->setFetchMode($this->defaultFetchMode);

        return $this->statement;
    }

    /**
     * returns the last inserted id
     *
     * @param  string|null $seqName
     * @return int
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function lastInsertId($seqName = null)
    {
        return $this->statement->getId();
    }

    /**
     * Executes a query, returns a statement
     *
     * @return Statement
     * @throws \Exception
     */
    public function query()
    {
        $statement = $this->prepare(func_get_args()[0]);
        $statement->execute();

        return $statement;
    }

    /**
     * @param EntityRouterInterface $router
     * @return Connection
     */
    public function setEntityRouter(EntityRouterInterface $router): Connection
    {
        $this->router = $router;

        return $this;
    }

    /**
     * @param ClassMetadataFactory $metadataFactory
     * @return Connection
     */
    public function setMetadataFactory(ClassMetadataFactory $metadataFactory): Connection
    {
        $this->metadataFactory = $metadataFactory;

        return $this;
    }
}