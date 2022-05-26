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
 * File containing tests for generating the language file.
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
 * Lang file test class.
 *
 * @package     tool_pluginskel
 * @copyright   2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginskel_lang_testcase extends advanced_testcase {

    /** @var string[] The test recipe. */
    protected static $recipe = array(
        'component' => 'local_langtest',
        'release'   => '0.1.0',
        'version'   => '2016062300',
        'name'      => 'Lang test',
        'requires'  => '2015051100',
        'copyright' => '2016 Alexandru Elisei <alexandru.elisei@gmail.com>',
        'lang_strings'   => array(
            array('id' => 'somestring', 'text' => 'Test string')
        )
    );

    /**
     * Test creating the lang file.
     */
    public function test_lang() {
        $logger = new Logger('langtest');
        $logger->pushHandler(new NullHandler());
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $manager->load_recipe($recipe);
        $manager->make();

        $files = $manager->get_files_content();
        $this->assertArrayHasKey('lang/en/'.$recipe['component'].'.php', $files);
        $langfile = $files['lang/en/'.$recipe['component'].'.php'];

        $this->assertStringContainsString('Plugin strings are defined here.', $langfile);
        $this->assertMatchesRegularExpression('/\* @category\s+string/', $langfile);
        $this->assertStringContainsString("\$string['pluginname'] = '".$recipe['name'], $langfile);

        $id = $recipe['lang_strings'][0]['id'];
        $text = $recipe['lang_strings'][0]['text'];
        $this->assertStringContainsString("\$string['$id'] = '$text'", $langfile);
    }
}
