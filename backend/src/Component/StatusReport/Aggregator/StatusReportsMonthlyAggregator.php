<?php

namespace Component\StatusReport\Aggregator;

use AppBundle\Entity\StatusReport;

class StatusReportsMonthlyAggregator extends AbstractStatusReportsAggregator
{
    /**
     * @param StatusReport[] $statusReports
     *
     * @return StatusReport[]
     */
    public function aggregate(array $statusReports): array
    {
        $statusReports = $this->sortByCreatedAt($statusReports);

        $results = [];
        foreach ($statusReports as $statusReport) {
            $createdAt = $statusReport->getCreatedAt();
            if (!$createdAt) {
                continue;
            }

            $month = $statusReport->getCreatedAt()->format('Y-m');
            $results[$month] = $statusReport;
        }

        return array_values($results);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_MONTHLY;
    }
}
