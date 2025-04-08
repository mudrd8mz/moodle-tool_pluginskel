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
 * File containing tests for the 'mobile_addons' feature.
 *
 * @package     tool_pluginskel
 * @copyright   2016 Alexandru Elisei <alexandru.elisei@gmail.com>, David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_pluginskel;

use Monolog\Logger;
use Monolog\Handler\NullHandler;
use tool_pluginskel\local\util\manager;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/setuplib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/pluginskel/vendor/autoload.php');

/**
 * Mobile_addons test class.
 *
 * @coversNothing
 * @package     tool_pluginskel
 * @copyright   2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class mobile_addons_test extends \advanced_testcase {

    /** @var string[] The test recipe. */
    protected static $recipe = [
        'component' => 'local_mobileaddonstest',
        'name'      => 'Mobile_addons test',
        'copyright' => '2016 Alexandru Elisei <alexandru.elisei@gmail.com>',
        'mobile_addons' => [
            [
                'name' => 'my_addon',
                'dependencies' => [
                    ['name' => 'dependency1'],
                    ['name' => 'dependency2'],
                ],
            ],
            [
                'name' => 'another_addon',
            ],
        ],
    ];

    /**
     * Tests creating the db/mobile.php file.
     */
    public function test_db_mobile_php(): void {
        $logger = new Logger('mobileaddonstest');
        $logger->pushHandler(new NullHandler());
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $manager->load_recipe($recipe);
        $manager->make();

        $files = $manager->get_files_content();
        $this->assertArrayHasKey('db/mobile.php', $files);
        $dbmobilefile = $files['db/mobile.php'];

        // Verify the boilerplate.
        $description = 'Mobile addons are declared here.';
        $this->assertStringContainsString($description, $dbmobilefile);

        $moodleinternal = "defined('MOODLE_INTERNAL') || die()";
        $this->assertStringContainsString($moodleinternal, $dbmobilefile);

        $addon = "'".$recipe['mobile_addons'][0]['name']."' => [";
        $this->assertStringContainsString($addon, $dbmobilefile);

        $dependencieslist = $recipe['mobile_addons'][0]['dependencies'];
        foreach ($dependencieslist as $dependency) {
            $this->assertStringContainsString("'".$dependency['name']."'", $dbmobilefile);
        }

        $addon = "'".$recipe['mobile_addons'][1]['name']."' => [";
        $this->assertStringContainsString($addon, $dbmobilefile);
    }
}
