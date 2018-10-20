<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor\tests\System;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Plugins\VisitsSummary;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group SegmentEditor
 * @group System
 * @group UnprocessedSegmentsTest
 */
class UnprocessedSegmentsTest extends IntegrationTestCase
{
    /**
     * @var OneVisitorTwoVisits
     */
    public static $fixture;

    const TEST_SEGMENT = 'browserCode==ff';

    public function test_apiOutput_whenPreprocessedSegmentUsed_WithBrowserArchivingDisabled()
    {
        print "in test 0: ".print_r(\Piwik\Db::fetchAll("SELECT visit_total_time FROM " . Common::prefixTable('log_visit')));

        $idSegment = API::getInstance()->add('testsegment', self::TEST_SEGMENT, self::$fixture->idSite, $autoArchive = true);

        print "in test 1: ".print_r(\Piwik\Db::fetchAll("SELECT visit_total_time FROM " . Common::prefixTable('log_visit')));

        $storedSegment = API::getInstance()->get($idSegment);
        $this->assertNotEmpty($storedSegment);
/*
        VisitsSummary\API::getInstance()->get(self::$fixture->idSite, 'week',
            Date::factory(self::$fixture->dateTime)->toString(), self::TEST_SEGMENT); // archive

        Rules::setBrowserTriggerArchiving(false);

        $segments = Rules::getSegmentsToProcess([self::$fixture->idSite]);
        $this->assertContains(self::TEST_SEGMENT, $segments);
*/
        $this->runAnyApiTest('VisitsSummary.get', 'autoArchiveSegmentPreprocessed', [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => self::TEST_SEGMENT,
        ]);
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

    public function provideContainerConfig()
    {
        return [
            Config::class => \DI\decorate(function (Config $previous) {
                $previous->General['browser_archiving_disabled_enforce'] = 1;
                return $previous;
            }),
        ];
    }

    private function clearLogData()
    {
        Db::query('TRUNCATE ' . Common::prefixTable('log_visit'));
        Db::query('TRUNCATE ' . Common::prefixTable('log_link_visit_action'));
        Db::query('TRUNCATE ' . Common::prefixTable('log_conversion'));
    }
}

UnprocessedSegmentsTest::$fixture = new OneVisitorTwoVisits();
