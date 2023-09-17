<?php 

namespace YDID\InfiniteTimelineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use YDID\InfiniteTimelineBundle\Service\InfiniteTimelineService;

#[Route('/ydid/infinite-timeline', name:'infinite_timeline_')]
class InfiniteTimelineController extends AbstractController
{
    #[Route('/', name:'index')]
    public function index()
    {
        return $this->render('@YdidInfiniteTimeline/infinite_timeline.html.twig', []);
    }

    #[Route('/generateTimeLine', name: 'generate_timeline')]
    public function generateTimeLine(Request $request)
    {
        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true);
        $year = null;
        if(!is_null($data['centeredYear'])) {
            $data['centeredYear'] = str_replace('/', '-', $data['centeredYear']);
            if($data['step'] > 1 && count(explode('-', $data['centeredYear'])) == 1) {
                $year = new \DateTime(str_replace(' ', '', $data['centeredYear']) . '-01-01');
            } else {
                $year = new \DateTime($data['centeredYear']);
            }
        }
        $timeLineService = new InfiniteTimelineService($data['step'], $year);
        $timelines = $timeLineService->generateTimeLine();

        $currentTimelineHtml = $this->render('@YdidInfiniteTimeline/' . $timelines['file'] . '.html.twig', ['timeline' => $timelines['currentTimeline']])->getContent();
        $previousTimelineHtml = $this->render('@YdidInfiniteTimeline/' . $timelines['file'] . '.html.twig', ['timeline' => $timelines['previousTimeline']])->getContent();
        $nextTimelineHtml = $this->render('@YdidInfiniteTimeline/' . $timelines['file'] . '.html.twig', ['timeline' => $timelines['nextTimeline']])->getContent();

        return new JsonResponse([
            'currentTimelineHtml' => $currentTimelineHtml,
            'previousTimelineHtml' => $previousTimelineHtml,
            'nextTimelineHtml' => $nextTimelineHtml,
            'current' => $timelines['currentDate'],
            'previous' => $timelines['previousDate'],
            'next' => $timelines['nextDate'],
            'focus' => $timelines['focus'],
        ]);
    }

    #[Route('/generatePreviousTimeline', name: 'generate_previous_timeline')]
    public function generatePreviousTimeline(Request $request)
    {
        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true);
        $year = $data['previous']['start'];
        if($data['previous']['start'] < 100) {
            if($data['previous']['start'] > 0) {
                $year = '00' . $data['previous']['start'];
            } else {
                $year = str_replace('-', '-00', $data['previous']['start']);
            }
        }
        $timeLineService = new InfiniteTimelineService($data['step'], new \DateTime($year . '-01-01'));
        $timelines = $timeLineService->generatePreviousTimeline();

        $previousTimelineHtml = $this->render('@YdidInfiniteTimeline/' . $timelines['file'] . '.html.twig', ['timeline' => $timelines['previousTimeline']])->getContent();
        return new JsonResponse([
            'previousTimelineHtml' => $previousTimelineHtml,
            'current' => $data['previous'],
            'previous' => $timelines['previous'],
            'next' => $data['current'],
        ]);
    }

    #[Route('/generateNextTimeline', name: 'generate_next_timeline')]
    public function generateNextTimeline(Request $request)
    {
        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true);
        $year = $data['next']['end'];
        if($data['next']['end'] < 100) {
            if($data['next']['end'] > 0) {
                $year = '00' . $data['next']['end'];
            } else {
                $year = str_replace('-', '-00', $data['next']['end']);
            }
        }
        $timeLineService = new InfiniteTimelineService($data['step'], new \DateTime($year . '-01-01'));
        $timelines = $timeLineService->generateNextTimeline();

        $nextTimelineHtml = $this->render('@YdidInfiniteTimeline/' . $timelines['file'] . '.html.twig', ['timeline' => $timelines['nextTimeline']])->getContent();

        return new JsonResponse([
            'nextTimelineHtml' => $nextTimelineHtml,
            'current' => $data['next'],
            'previous' => $data['current'],
            'next' => $timelines['next'],
        ]);
    }
}