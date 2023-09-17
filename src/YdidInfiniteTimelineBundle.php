<?php 

namespace YDID\InfiniteTimelineBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use YDID\InfiniteTimelineBundle\DependencyInjection\YdidInfiniteTimelineExtension;

class YdidInfiniteTimelineBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new YdidInfiniteTimelineExtension();
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addResource(new DirectoryResource(__DIR__.'\\..\\Resources\\public'));
    }
}