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
 * File containing tests for the 'readme' feature.
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
 * Readme test class.
 *
 * @coversNothing
 * @package     tool_pluginskel
 * @copyright   2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class readme_test extends \advanced_testcase {

    /** @var string[] The test recipe. */
    protected static $recipe = [
        'component' => 'local_readmetest',
        'name'      => 'Readme test',
        'copyright' => '2016 Alexandru Elisei <alexandru.elisei@gmail.com>',
        'features'  => [
            'readme' => true,
        ],
    ];

    /**
     * Test creating the README.md file.
     */
    public function test_readme(): void {
        $logger = new Logger('demo');
        $logger->pushHandler(new NullHandler);
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $manager->load_recipe($recipe);
        $manager->make();

        $files = $manager->get_files_content();
        $this->assertArrayHasKey('README.md', $files);
        $this->assertStringContainsString($recipe['name'], $files['README.md']);
        $this->assertStringContainsString($recipe['copyright'], $files['README.md']);
    }
}
