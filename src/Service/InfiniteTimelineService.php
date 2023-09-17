<?php

namespace YDID\InfiniteTimelineBundle\Service;

class InfiniteTimelineService
{
    const NB_ELEM_BY_ITEMS = 60;
    const MONTHS = [
        'Jan' => [
            'short' => 'Janv',
            'full' => 'Janvier'
        ],
        'Feb' => [
            'short' => 'Fév',
            'full' => 'Février'
        ],
        'Mar' => [
            'short' => 'Mars',
            'full' => 'Mars'
        ],
        'Apr' => [
            'short' => 'Avr',
            'full' => 'Avril'
        ],
        'May' => [
            'short' => 'Mai',
            'full' => 'Mai'
        ],
        'Jun' => [
            'short' => 'Juin',
            'full' => 'Juin'
        ],
        'Jul' => [
            'short' => 'Juil',
            'full' => 'Juiller'
        ],
        'Aug' => [
            'short' => 'Août',
            'full' => 'Août'
        ],
        'Sep' => [
            'short' => 'Sep',
            'full' => 'Septembre'
        ],
        'Oct' => [
            'short' => 'Oct',
            'full' => 'Octobre'
        ],
        'Nov' => [
            'short' => 'Nov',
            'full' => 'Novembre'
        ],
        'Dec' => [
            'short' => 'Déc',
            'full' => 'Décembre'
        ],
    ];

    private $currentDate = null;
    private $step = 10;
    private $divide = 10;
    private $focus;

    public function __construct($step = 10, \DateTime $year = null)
    {
        if (is_null($year)) {
            $year = new \DateTime();
        }
        $this->step = $step;
        $this->currentDate = $year;
    }

    private function roundYearToNearestUnit($currentYear, $roundingUnit)
    {
        $quotient = round($currentYear / $roundingUnit);
        return $quotient * $roundingUnit;
    }

    public function generateTimeLine()
    {
        if ($this->step == 0.5) {
            //Traiter par jours
            $res = $this->generateTimelineByDays();
        } else if ($this->step == 1) {
            //Traiter les mois
            $res = $this->generateTimelineByMonths();
        } else {
            $res = $this->generateTimelineByYears();
        }

        $res['file'] = $this->getFileName();

        return $res;
    }

    private function getFileName()
    {
        switch ($this->step) {
            case 0.5:
                return 'days';
                break;
            case 1:
                return 'months';
            default:
                return 'years';
                break;
        }

        return 'years';
    }

    private function generateTimelineByDays()
    {
        $currentDate = $this->currentDate;

        $nextDays = $this->getNextDays(self::NB_ELEM_BY_ITEMS / 2, $currentDate);
        $previousDays = $this->getPreviousDays(self::NB_ELEM_BY_ITEMS / 2, $currentDate);
        $currentTimeline = array_merge($previousDays, $nextDays);

        $startDate = clone $currentDate;
        $endDate = clone $currentDate;
        $previousTimeline = $this->getPreviousDays(self::NB_ELEM_BY_ITEMS, $startDate->modify('- ' . ((self::NB_ELEM_BY_ITEMS / 2) + 1) . ' days'));
        $nextTimeline = $this->getNextDays(self::NB_ELEM_BY_ITEMS, $endDate->modify('+' . ((self::NB_ELEM_BY_ITEMS / 2)) . ' days'));

        return [
            'currentTimeline' => $currentTimeline,
            'previousTimeline' => $previousTimeline,
            'nextTimeline' => $nextTimeline,
            'currentDate' => $this->getStartAndEndTimelineDays($currentTimeline),
            'previousDate' => $this->getStartAndEndTimelineDays($previousTimeline),
            'nextDate' => $this->getStartAndEndTimelineDays($nextTimeline),
            'focus' => $this->currentDate->format('Y-m-d'),
        ];
    }

    private function generateTimelineByMonths()
    {
        $currentDate = $this->currentDate;

        $nextMonths = $this->getNextMonths(self::NB_ELEM_BY_ITEMS / 2, clone $currentDate);
        $previousMonths = $this->getPreviousMonths(self::NB_ELEM_BY_ITEMS / 2, clone $currentDate);
        $currentTimeline = array_merge($previousMonths, $nextMonths);

        $startDate = new \DateTime($currentTimeline[0]['datetime']);
        $startDate->modify('-1 month');
        $previousTimeline = $this->getPreviousMonths(self::NB_ELEM_BY_ITEMS, $startDate);
        $nextTimeline = $this->getNextMonths(self::NB_ELEM_BY_ITEMS, new \DateTime($currentTimeline[count($currentTimeline) - 1]['datetime']));

        return [
            'currentTimeline' => $currentTimeline,
            'previousTimeline' => $previousTimeline,
            'nextTimeline' => $nextTimeline,
            'currentDate' => $this->getStartAndEndTimelineDays($currentTimeline),
            'previousDate' => $this->getStartAndEndTimelineDays($previousTimeline),
            'nextDate' => $this->getStartAndEndTimelineDays($nextTimeline),
            'focus' => $this->currentDate->format('Y-m'),
        ];
    }


    private function generateTimelineByYears()
    {
        $currentYear = intval($this->currentDate->format('Y'));
        $roundedYear = $this->roundYearToNearestUnit($currentYear, $this->step / $this->divide);
        $currentYearDizaine = str_replace($currentYear, $roundedYear, $this->currentDate->format('Y'));
        if ($this->step > 10) {
            $currentYear = $currentYearDizaine;
        }

        $nextYears = $this->getNextYears(self::NB_ELEM_BY_ITEMS / 2, $currentYearDizaine);
        $previousYears = $this->getPreviousYears(((self::NB_ELEM_BY_ITEMS) / 2) - 1, $currentYearDizaine);

        $currentYearArr[] = [
            'year' => $currentYearDizaine,
            'dizaine' => ($currentYearDizaine % $this->step === 0) ? true : false,
        ];

        $currentTimeline = array_merge($previousYears, $currentYearArr, $nextYears);
        $previousTimeline = $this->getPreviousYears(self::NB_ELEM_BY_ITEMS, $currentTimeline[0]['year']);
        $nextTimeline = $this->getNextYears(self::NB_ELEM_BY_ITEMS, $currentTimeline[self::NB_ELEM_BY_ITEMS - 1]['year']);

        return [
            'currentTimeline' => $currentTimeline,
            'previousTimeline' => $previousTimeline,
            'nextTimeline' => $nextTimeline,
            'currentDate' => $this->getStartAndEndTimeline($currentTimeline),
            'previousDate' => $this->getStartAndEndTimeline($previousTimeline),
            'nextDate' => $this->getStartAndEndTimeline($nextTimeline),
            'focus' => $currentYear,
        ];
    }

    private function getStartAndEndTimeline($timeline)
    {
        return [
            'start' => $timeline[0]['year'],
            'end' => $timeline[self::NB_ELEM_BY_ITEMS - 1]['year'],
        ];
    }

    private function getStartAndEndTimelineDays($timeline)
    {
        return [
            'start' => $timeline[0]['datetime'],
            'end' => $timeline[count($timeline) - 1]['datetime'],
        ];
    }

    public function generatePreviousTimeline()
    {
        if ($this->step == 0.5) {
            //traiter les jours
            $previousTimeline = $this->getPreviousDays(self::NB_ELEM_BY_ITEMS, $this->currentDate);
            $previous = $this->getStartAndEndTimelineDays($previousTimeline);
        } else if ($this->step == 1) {
            //traiter les mois
            $previousTimeline = $this->getPreviousMonths(self::NB_ELEM_BY_ITEMS, $this->currentDate);
            $previous = $this->getStartAndEndTimelineDays($previousTimeline);
        } else {
            //traiter les années
            $currentYear = intval($this->currentDate->format('Y'));
            $previousTimeline = $this->getPreviousYears(self::NB_ELEM_BY_ITEMS, $currentYear);
            $previous = $this->getStartAndEndTimeline($previousTimeline);
        }

        return [
            'previousTimeline' => $previousTimeline,
            'previous' => $previous,
            'file' => $this->getFileName(),
        ];
    }

    public function generateNextTimeline()
    {
        if ($this->step == 0.5) {
            //traiter les jours
            $nextTimeline = $this->getNextDays(self::NB_ELEM_BY_ITEMS, $this->currentDate);
            $next = $this->getStartAndEndTimelineDays($nextTimeline);
        } else if ($this->step == 1) {
            //traiter les mois
            $nextTimeline = $this->getNextMonths(self::NB_ELEM_BY_ITEMS, $this->currentDate);
            $next = $this->getStartAndEndTimelineDays($nextTimeline);
        } else {
            $currentYear = intval($this->currentDate->format('Y'));
            $nextTimeline = $this->getNextYears(self::NB_ELEM_BY_ITEMS, $currentYear);
            $next = $this->getStartAndEndTimeline($nextTimeline);
        }

        return [
            'nextTimeline' => $nextTimeline,
            'next' => $next,
            'file' => $this->getFileName(),
        ];
    }

    private function getNextYears($nbYears, $year)
    {
        $nextYears = [];
        for ($i = 1; $i <= $nbYears; $i++) {
            $nextYears[] = [
                'year' => $year + ($i * ($this->step / $this->divide)),
                'dizaine' => ((intval($year) + ($i * ($this->step / $this->divide))) % $this->step === 0) ? true : false,
            ];
        }
        return $nextYears;
    }


    private function getNextDays($nbDays, $currentDate)
    {
        $currentDate = $currentDate->format('Y-m-d');
        $startDate = date('Y-m-d', strtotime('+1 day', strtotime($currentDate)));
        $endDate = date('Y-m-d', strtotime('+' . $nbDays . ' days', strtotime($currentDate)));

        $dateArray = array();

        $currentMonth = "";
        $currentYear = "";
        for ($i = strtotime($startDate); $i <= strtotime($endDate); $i = strtotime('+1 day', $i)) {
            $day = date('d', $i);
            $month = date('M', $i);
            $monthNb = date('m', $i);
            $year = date('Y', $i);
            $yearNb = date('y', $i);

            if ($monthNb == '01' && $day == '01') {
                $currentYear = $year;
                $dateArray[] = [
                    'date' => $currentYear,
                    'month' => false,
                    'year' => true,
                    'datetime' => "$year",
                ];
            }

            if ($day == '01') {
                $currentMonth = self::MONTHS[$month]['short'];
                $dateArray[] = [
                    'date' => $currentMonth,
                    'month' => true,
                    'year' => false,
                    'datetime' => "$year-$monthNb",
                ];
            }

            $dateArray[] = [
                'date' => "$day/$monthNb/$yearNb",
                'month' => false,
                'year' => false,
                'datetime' => date('Y-m-d', $i),
            ];
        }

        return $dateArray;
    }

    private function getPreviousDays($nbDays, $currentDate)
    {
        $currentDate = $currentDate->format('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-' . $nbDays . ' days', strtotime($currentDate)));
        $endDate = $currentDate;

        $dateArray = array();

        $currentMonth = "";
        $currentYear = "";
        for ($i = strtotime($startDate); $i <= strtotime($endDate); $i = strtotime('+1 day', $i)) {
            $day = date('d', $i);
            $month = date('M', $i);
            $monthNb = date('m', $i);
            $year = date('Y', $i);
            $yearNb = date('y', $i);

            if ($monthNb == '01' && $day == '01') {
                $currentYear = $year;
                $dateArray[] = [
                    'date' => $currentYear,
                    'month' => false,
                    'year' => true,
                    'datetime' => "$year",
                ];
            }

            if ($day == '01') {
                $currentMonth = self::MONTHS[$month]['short'];
                $dateArray[] = [
                    'date' => $currentMonth,
                    'month' => true,
                    'year' => false,
                    'datetime' => "$year-$month",
                ];
            }

            $dateArray[] = [
                'date' => "$day/$monthNb/$yearNb",
                'month' => false,
                'year' => false,
                'datetime' => date('Y-m-d', $i),
            ];
        }

        return $dateArray;
    }

    private function getPreviousMonths($nbMonths, $currentDate)
    {
        $dateArray = [];
        $currentDate->modify('-' . $nbMonths . ' month');

        $dateArray = $this->getNextMonths($nbMonths, clone $currentDate);

        return $dateArray;
    }

    private function getNextMonths($nbMonths, $currentDate)
    {
        $dateArray = [];

        for ($i = 0; $i < $nbMonths; $i++) {
            $currentDate->modify('+1 month');
            $month = $currentDate->format('M');
            $monthNb = $currentDate->format('m');
            $year = $currentDate->format('Y');
            $yearNb = $currentDate->format('y');

            if ($monthNb == '01') {
                $dateArray[] = [
                    'date' => $year,
                    'year' => true,
                    'datetime' => "$year",
                ];
            }

            $dateArray[] = [
                'date' => "$monthNb/$yearNb",
                'year' => false,
                'datetime' => $currentDate->format('Y-m'),
            ];
        }

        return $dateArray;
    }

    private function getPreviousYears($nbYears, $year)
    {
        $previousYears = [];
        for ($i = $nbYears; $i >= 1; $i--) {
            $previousYears[] = [
                'year' =>  $year - ($i * ($this->step / $this->divide)),
                'dizaine' => ((intval($year) - ($i * ($this->step / $this->divide))) % $this->step === 0) ? true : false,
            ];
        }
        return $previousYears;
    }
}
