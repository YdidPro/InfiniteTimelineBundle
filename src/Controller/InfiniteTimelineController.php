<?php 

namespace YDID\InfiniteTimelineBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InfiniteTimelineController extends AbstractController
{
    #[Route('/ydid/infinite-timeline', name:'infinite_timeline')]
    public function __invoke()
    {
        return $this->render('@YdidInfiniteTimeline/infinite_timeline.html.twig');
    }
}