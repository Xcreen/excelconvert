<?php
namespace ExcelConvert\Views\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigBaseURLExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('baseurl', array($this, 'baseurl')),
        );
    }

    public function baseurl(): string
    {
        return '//' . $_SERVER['SERVER_NAME'];
    }
}
