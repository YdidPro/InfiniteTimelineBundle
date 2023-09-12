<?php 

namespace YDID\InfiniteTimelineBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
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
}