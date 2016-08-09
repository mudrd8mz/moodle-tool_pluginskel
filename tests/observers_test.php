<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * File containing tests for the 'observers' feature.
 *
 * @package     tool_pluginskel
 * @copyright   2016 Alexandru Elisei <alexandru.elisei@gmail.com>, David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use Monolog\Logger;
use Monolog\Handler\NullHandler;
use tool_pluginskel\local\util\manager;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/setuplib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/pluginskel/vendor/autoload.php');

/**
 * Observers test class.
 *
 * @package     tool_pluginskel
 * @copyright   2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginskel_observers_testcase extends advanced_testcase {

    /** @var string[] The test recipe. */
    protected static $recipe = array(
        'component' => 'local_observerstest',
        'name'      => 'Observers test',
        'copyright' => '2016 Alexandru Elisei <alexandru.elisei@gmail.com>',
        'observers' => array(
            array(
                'eventname' => '\core\event\something_happened',
                'callback' => '\local_observerstest\event_observer::something_happened',
                'includefile' => '/path/to/file/relative/to/moodle/dir/root',
                'priority' => 200
            ),
            array(
                'eventname' => '\core\event\something_else_happened',
                'callback' => 'local_observerstest_another_event_observer::something_else_happened'
            ),
            array(
                'eventname' => '\core\event\another_eventname',
                'callback' => 'locallib_function'
            )
        )
    );

    /**
     * Tests creating the db/events.php file.
     */
    public function test_db_events_php() {
        $logger = new Logger('observerstest');
        $logger->pushHandler(new NullHandler);
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $manager->load_recipe($recipe);
        $manager->make();

        $files = $manager->get_files_content();
        $this->assertArrayHasKey('db/events.php', $files);
        $eventsfile = $files['db/events.php'];

        $description = 'Plugin event observers are registered here.';
        $this->assertContains($description, $eventsfile);

        $moodleinternal = "defined('MOODLE_INTERNAL') || die()";
        $this->assertContains($moodleinternal, $eventsfile);

        $this->assertContains('$observers = array(', $eventsfile);

        $eventname = $recipe['observers'][0]['eventname'];
        $this->assertContains("'eventname' => '".$eventname."'", $eventsfile);

        $callback = $recipe['observers'][0]['callback'];
        $this->assertContains("'callback' => '".$callback."'", $eventsfile);

        $includefile = $recipe['observers'][0]['includefile'];
        $this->assertContains("'includefile' => '".$includefile."'", $eventsfile);

        $priority = $recipe['observers'][0]['priority'];
        $this->assertContains("'priority' => ".$priority, $eventsfile);

        $eventname = $recipe['observers'][1]['eventname'];
        $this->assertContains("'eventname' => '".$eventname."'", $eventsfile);

        $callback = $recipe['observers'][1]['callback'];
        $this->assertContains("'callback' => '".$callback."'", $eventsfile);

        $eventname = $recipe['observers'][2]['eventname'];
        $this->assertContains("'eventname' => '".$eventname."'", $eventsfile);

        $callback = $recipe['observers'][2]['callback'];
        $this->assertContains("'callback' => '".$callback."'", $eventsfile);
    }

    /**
     * Tests creating the callback functions.
     */
    public function test_event_callback() {
        $logger = new Logger('observerstest');
        $logger->pushHandler(new NullHandler);
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $manager->load_recipe($recipe);
        $manager->make();

        $files = $manager->get_files_content();
        $this->assertArrayHasKey('classes/event_observer.php', $files);
        $this->assertArrayHasKey('classes/another_event_observer.php', $files);
        $this->assertArrayHasKey('locallib.php', $files);

        $observerfile = $files['classes/event_observer.php'];

        $description = 'Event observer class.';
        $this->assertContains($description, $observerfile);

        $namespace = 'namespace '.$recipe['component'].';';
        $this->assertContains($namespace, $observerfile);

        $moodleinternal = "defined('MOODLE_INTERNAL') || die()";
        $this->assertContains($moodleinternal, $observerfile);

        $this->assertContains('class event_observer', $observerfile);

        $paramevent = '* @param '.$recipe['observers'][0]['eventname'].' $event';
        $this->assertContains($paramevent, $observerfile);

        $function = 'public static function something_happened($event)';
        $this->assertContains($function, $observerfile);

        $secondobserverfile = $files['classes/another_event_observer.php'];
        $this->assertNotContains($namespace, $secondobserverfile);

        $locallibfile = $files['locallib.php'];

        $functiondescription = 'Handle the '.$recipe['observers'][2]['eventname'].' event.';
        $this->assertContains($functiondescription, $locallibfile);

        $functionname = $recipe['observers'][2]['callback'];
        $function = 'function '.$functionname.'($event)';
        $this->assertContains($function, $locallibfile);
    }
}
