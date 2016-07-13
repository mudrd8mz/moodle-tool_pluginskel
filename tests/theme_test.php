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
 * File containing tests for the theme plugin type.
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
 * Theme test class.
 *
 * @package     tool_pluginskel
 * @copyright   2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginskel_theme_testcase extends advanced_testcase {

    /** @var string[] The test recipe. */
    protected static $recipe = array(
        'component' => 'theme_test',
        'name'      => 'Theme test',
        'copyright' => '2016 Alexandru Elisei <alexandru.elisei@gmail.com>',
        'features'  => array(
            'layouts' => true,
        ),
        'parents' => array('base'),
        'stylesheets' => array('stylesheet'),
        'doctype' => 'html5',
        'layouts' => array('layout'),
        'strings' => array(
            array('id' => 'choosereadme', 'text' => 'Theme test')
        )
    );

    /**
     * Test creating the config.php file.
     */
    public function test_theme_config_php() {
        $logger = new Logger('themetest');
        $logger->pushHandler(new NullHandler());
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $manager->load_recipe($recipe);
        $manager->make();

        $files = $manager->get_files_content();
        $this->assertArrayHasKey('config.php', $files);
        $configfile = $files['config.php'];

        // Verify the boilerplate.
        $description = 'The configuration for '.$recipe['component'].' is defined here.';
        $this->assertContains($description, $configfile);

        $doctype = "\$THEME->doctype = '".$recipe['doctype']."'";
        $this->assertContains($doctype, $configfile);

        $parents = '/\$THEME->parents = array\(\s+\''.$recipe['parents'][0].'\',\s+\)/';
        $this->assertRegExp($parents, $configfile);

        $stylesheets = '/\$THEME->sheets = array\(\s*\''.$recipe['stylesheets'][0].'\',\s*\);/';
        $this->assertRegExp($stylesheets, $configfile);

        $layouts = '$THEME->layouts = array(';
        $this->assertContains($layouts, $configfile);
    }

    /**
     * Test creating the feature files.
     */
    public function test_theme_feature_files() {
        $logger = new Logger('themetest');
        $logger->pushHandler(new NullHandler());
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $manager->load_recipe($recipe);
        $manager->make();

        $files = $manager->get_files_content();

        $layoutfile = 'layout/'.$recipe['layouts'][0].'.php';
        $this->assertArrayHasKey($layoutfile, $files);

        $stylesheetfile = 'styles/'.$recipe['stylesheets'][0].'.css';
        $this->assertArrayHasKey($stylesheetfile, $files);
    }
}
