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
 * File containing tests for the 'supported_features' feature.
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
 * Supported_features test class.
 *
 * @package     tool_pluginskel
 * @copyright   2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginskel_supported_features_testcase extends advanced_testcase {

    /** @var string[] The test recipe. */
    protected static $recipe = array(
        'component' => 'supportedfeaturestest',
        'name'      => 'Supported_features test',
        'copyright' => '2016 Alexandru Elisei <alexandru.elisei@gmail.com>',
        'features'  => array(
            'all' => false,
        ),
        'supported_features' => array('FEATURE_BACKUP_MOODLE2', 'FEATURE_MOD_INTRO')
    );

    /**
     * Test creating lib.php with the supported features.
     */
    public function test_supported_features() {
        $logger = new Logger('supportedfeaturestest');
        $logger->pushHandler(new NullHandler());
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $manager->load_recipe($recipe);
        $manager->make();

        $files = $manager->get_files_content();
        $this->assertArrayHasKey('lib.php', $files);
        $libfile = $files['lib.php'];

        $description = 'Library of interface functions and constants.';
        $this->assertContains($description, $libfile);

        $moodleinternal = "defined('MOODLE_INTERNAL') || die()";
        $this->assertContains($moodleinternal, $libfile);

        $supports = 'function '.$recipe['component'].'_supports($feature)';
        $this->assertContains($supports, $libfile);

        $this->assertContains('case '.$recipe['supported_features'][0], $libfile);
        $this->assertContains('case '.$recipe['supported_features'][1], $libfile);
    }
}
