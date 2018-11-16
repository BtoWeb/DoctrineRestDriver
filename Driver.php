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

use Circle\DoctrineRestDriver\Router\DefaultEntityRouter;
use Circle\DoctrineRestDriver\Router\EntityRouterInterface;
use Circle\DoctrineRestDriver\Router\RoutingTable;
use Circle\DoctrineRestDriver\Router\RoutingTableInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Connection as AbstractConnection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\MySqlSchemaManager;

/**
 * Rest driver class
 *
 * @author    Tobias Hauck <tobias@circle.ai>
 * @copyright 2015 TeeAge-Beatz UG
 */
class Driver implements DriverInterface {

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var RoutingTableInterface
     */
    private $routings;

    /**
     * @var ClassMetadataFactory
     */
    private $metadataFactory;

    /**
     * @var EntityRouterInterface
     */
    protected $router;

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     * @throws \Doctrine\DBAL\DBALException
     * @throws Validation\Exceptions\InvalidTypeException
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array()) {
        if (!empty($this->connection)) return $this->connection;

        $this->connection = new Connection($params, $this, $this->router);
        $this->connection->setMetadataFactory( $this->metadataFactory );

        if ( $this->router ) {
            $this->connection->setEntityRouter($this->router);
        } else {
            // Cas par défaut pour retro-compatibilité
            $metaData         = new MetaData();
            $routingTable     = new RoutingTable($metaData->getEntityNamespaces());
            $this->connection->setEntityRouter( new DefaultEntityRouter($routingTable, $driverOptions) );
        }

        return $this->connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform() {
        return new MySqlPlatform();
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(AbstractConnection $conn) {
        return new MySqlSchemaManager($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return 'circle_rest';
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabase(AbstractConnection $conn) {
        return 'rest_database';
    }

    public function setEntityRouter(EntityRouterInterface $router) {
        $this->router = $router;
    }

    /**
     * @param ClassMetadataFactory $metadataFactory
     * @return Driver
     */
    public function setMetadataFactory(ClassMetadataFactory $metadataFactory): Driver
    {
        $this->metadataFactory = $metadataFactory;
        return $this;
    }
}