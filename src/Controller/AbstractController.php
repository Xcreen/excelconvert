<?php
namespace ExcelConvert\Controller;

use Psr\Container\ContainerInterface;

class AbstractController
{
    protected $container;
    protected $twig;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->twig = $this->container->get('twigservice');
    }
}
