<?php
/**
 * Created by Aurélien RICHAUD (09/11/2018 09:32)
 */

namespace Circle\DoctrineRestDriver\Decorator;

use Circle\DoctrineRestDriver\Router\EntityRouterInterface;
use Circle\DoctrineRestDriver\Router\RoutingTable;
use Circle\DoctrineRestDriver\Router\DefaultEntityRouter;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;


class RestEntityManager extends EntityManagerDecorator
{
    /**
     * returns all namespaces of managed entities
     *
     * @return array
     */
    protected function getEntityNamespaces() {
        return array_reduce($this->getMetadataFactory()->getAllMetadata(), function($carry, \Doctrine\ORM\Mapping\ClassMetadata $item) {
            $carry[$item->table['name']] = $item->getName();
            return $carry;
        }, []);
    }

    /**
     * RestEntityManager constructor.
     * @param EntityManagerInterface $wrapped
     * @throws \Circle\DoctrineRestDriver\Validation\Exceptions\InvalidTypeException
     */
    public function __construct(EntityManagerInterface $wrapped)
    {
        parent::__construct($wrapped);

        $driver = $this->getConnection()->getDriver();
        if ( $driver instanceof \Circle\DoctrineRestDriver\Driver) {
            $driver->setMetadataFactory($this->getMetadataFactory());
        }

        $this->setEntityRouter( new DefaultEntityRouter(new RoutingTable($this->getEntityNamespaces()), $this->getConnection()->getParams() ) );
    }

    public function setEntityRouter(EntityRouterInterface $router) {
        $driver = $this->getConnection()->getDriver();
        if ( $driver instanceof \Circle\DoctrineRestDriver\Driver) {
            $driver->setEntityRouter($router);
        }
    }
}