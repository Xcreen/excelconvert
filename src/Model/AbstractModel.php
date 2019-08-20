<?php
namespace ExcelConvert\Model;

use Psr\Container\ContainerInterface;

class AbstractModel
{
    protected $container;
    protected $pdo;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->pdo = $this->container->get('pdoservice');
    }
}
