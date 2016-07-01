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
 * File containing tests for the 'capabilities' feature.
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
 * Capabilities test class.
 *
 * @package     tool_pluginskel
 * @copyright   2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginskel_capabilities_testcase extends advanced_testcase {

    /** @var string[] The test recipe. */
    protected static $recipe = array(
        'component' => 'capabilitiestest',
        'name'      => 'Capabilities test',
        'copyright' => '2016 Alexandru Elisei <alexandru.elisei@gmail.com>',
        'capabilities' => array(
            array(
                'name' => 'view',
                'riskbitmask' => 'RISK_XSS',
                'captype' => 'view',
                'contextlevel' => 'CONTEXT_MODULE',
                'archetypes' => array(
                    array('role' => 'student', 'permission' => 'CAP_ALLOW'),
                    array('role' => 'editingteacher', 'permission' => 'CAP_ALLOW')
                ),
                'clonepermissionsfrom' => 'moodle/course:view'
            ),
        )
    );

    /**
     * Test creating the db/access.php file with one capability.
     */
    public function test_one_capability() {
        $logger = new Logger('capabilitiestest');
        $logger->pushHandler(new NullHandler());
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $manager->load_recipe($recipe);
        $manager->make();

        $files = $manager->get_files_content();
        $this->assertArrayHasKey('db/access.php', $files);
        $dbaccessfile = $files['db/access.php'];

        // Verify the boilerplate.
        $this->assertContains('Plugin capabilities are defined here.', $dbaccessfile);
        $this->assertRegExp('/\* @category\s+access/', $dbaccessfile);

        $moodleinternal = "defined('MOODLE_INTERNAL') || die()";
        $this->assertContains($moodleinternal, $dbaccessfile);

        // Verify if the capability has been generated correctly.
        $this->assertContains('mod/capabilitiestest:view', $dbaccessfile);
        $this->assertContains("'riskbitmask' => RISK_XSS", $dbaccessfile);
        $this->assertContains("'captype' => 'view'", $dbaccessfile);
        $this->assertContains("'contextlevel' => CONTEXT_MODULE", $dbaccessfile);
        $this->assertContains("'archetypes'", $dbaccessfile);
        $this->assertContains("'student' => CAP_ALLOW", $dbaccessfile);
        $this->assertContains("'editingteacher' => CAP_ALLOW", $dbaccessfile);
        $this->assertContains("'clonepermissionsfrom' => 'moodle/course:view'", $dbaccessfile);
    }

    public function test_capabilities() {
        $logger = new Logger('capabilitiestest');
        $logger->pushHandler(new NullHandler());
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $recipe['capabilities'][] = array('name' => 'edit', 'captype' => 'write');
        $manager->load_recipe($recipe);
        $manager->make();

        $files = $manager->get_files_content();
        $this->assertArrayHasKey('db/access.php', $files);
        $dbaccessfile = $files['db/access.php'];

        // Verify if all the capabilities have been generated.
        $this->assertContains('mod/capabilitiestest:view', $dbaccessfile);
        $this->assertContains('mod/capabilitiestest:edit', $dbaccessfile);
    }
}
