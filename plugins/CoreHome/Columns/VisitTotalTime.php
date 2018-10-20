<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Config;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\CoreHome\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class VisitTotalTime extends VisitDimension
{
    protected $columnName = 'visit_total_time';
    protected $columnType = 'INT(11) UNSIGNED NOT NULL';
    protected $segmentName = 'visitDuration';
    protected $nameSingular = 'General_ColumnVisitDuration';
    protected $type = self::TYPE_DURATION_S;

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $totalTime = Config::getInstance()->Tracker['default_time_one_page_visit'];
        print "new visit: $totalTime\n";
        $totalTime = $this->cleanupVisitTotalTime($totalTime);
        print "cleaned: $totalTime\n";

        return $totalTime;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        $firstActionTime = $visitor->getVisitorColumn('visit_first_action_time');
print "firstActionTime: $firstActionTime\n";
        $totalTime = 1 + $request->getCurrentTimestamp() - $firstActionTime;
        print "totalTime update :$totalTime\n";
        $totalTime = $this->cleanupVisitTotalTime($totalTime);
        print "clean total time: $totalTime\n";

        return $totalTime;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int
     */
    public function onConvertedVisit(Request $request, Visitor $visitor, $action)
    {
        if (!$visitor->isVisitorKnown()) {
            print "not known\n";
            return false;
        }

        $totalTime = $visitor->getVisitorColumn('visit_total_time');
print "total time: $totalTime\n";
        // If a pageview and goal conversion in the same second, with previously a goal conversion recorded
        // the request would not "update" the row since all values are the same as previous
        // therefore the request below throws exception, instead we make sure the UPDATE will affect the row
        $totalTime = $totalTime + $request->getParam('idgoal');
        print "total time 2: $totalTime\n";
        // +2 to offset idgoal=-1 and idgoal=0
        $totalTime = $totalTime + 2;
        print "total time 3: $totalTime\n";

        return $this->cleanupVisitTotalTime($totalTime);
    }

    public function getRequiredVisitFields()
    {
        return array('visit_first_action_time');
    }

    private function cleanupVisitTotalTime($t)
    {
        $t = (int)$t;

        if ($t < 0) {
            $t = 0;
        }

        return $t;
    }

}
