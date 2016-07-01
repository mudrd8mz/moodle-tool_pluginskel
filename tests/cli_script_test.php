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
 * File containing tests for the 'cli_script' feature.
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
 * Cli_script test class.
 *
 * @package     tool_pluginskel
 * @copyright   2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginskel_cli_script_testcase extends advanced_testcase {

    /** @var string[] The test recipe. */
    protected static $recipe = array(
        'component' => 'cliscripttest',
        'name'      => 'Cli_script test',
        'copyright' => '2016 Alexandru Elisei <alexandru.elisei@gmail.com>',
        'cli_script'=> array('first', 'second')
    );

    /**
     * Tests creating the cli/cliscripttest.php file.
     */
    public function test_cli_script() {
        $logger = new Logger('cliscripttest');
        $logger->pushHandler(new NullHandler());
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $manager->load_recipe($recipe);
        $manager->make();

        $files = $manager->get_files_content();
        $filename = 'cli/'.$recipe['cli_script'][0].'.php';
        $this->assertArrayHasKey($filename, $files);

        $clifile = $files[$filename];

        $description = 'CLI script for '.$recipe['component'].'.';
        $this->assertContains($description, $clifile);

        $cliscript = "define('CLI_SCRIPT', true)";
        $this->assertContains($cliscript, $clifile);

        $configphp = "require(__DIR__.'/../../../config.php')";
        $this->assertContains($configphp, $clifile);

        $clilib = "require_once(\$CFG->libdir.'/clilib.php')";
        $this->assertContains($clilib, $clifile);

        $filename = 'cli/'.$recipe['cli_script'][1].'.php';
        $this->assertArrayHasKey($filename, $files);
    }
}
