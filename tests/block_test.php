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
 * File containing tests for generating a block plugin type.
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
 * Blocks test class.
 *
 * @package     tool_pluginskel
 * @copyright   2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginskel_block_testcase extends advanced_testcase {

    /** @var string[] The test recipe. */
    protected static $recipe = array(
        'component' => 'block_test',
        'name'      => 'Block test',
        'copyright' => '2016 Alexandru Elisei <alexandru.elisei@gmail.com>',
        'features'  => array(
            'settings' => true,
            'instance_allow_multiple' => true,
            'edit_form' => true,
            'settings' => true,
        ),
        'capabilities' => array(
            array(
                'name' => 'addinstance',
                'riskbitmask' => 'RISK_XSS | RISK_XSS',
                'captype' => 'write',
                'contextlevel' => 'CONTEXT_BLOCK',
                'archetypes' => array(
                    array(
                        'role' => 'student',
                        'permission' => 'CAP_ALLOW'
                    ),
                    array(
                        'role' => 'editingteacher',
                        'permission' => 'CAP_ALLOW'
                    )
                ),
                'clonepermissionsfrom' => 'moodle/site:manageblocks'
            )
        ),
        'applicable_formats' => array(
            array('page' => 'all', 'allowed' => false),
            array('page' => 'course-view', 'allowed' => true),
            array('page' => 'course-view-social', 'allowed' => false)
        )
    );

    /**
     * Tests creating the block_<blockname>.php file.
     */
    public function test_block_block_file() {
        $logger = new Logger('blocktest');
        $logger->pushHandler(new NullHandler());
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $manager->load_recipe($recipe);
        $manager->make();


        $files = $manager->get_files_content();

        $filename = $recipe['component'].'.php';
        $this->assertArrayHasKey($filename, $files);
        $blockfile = $files[$filename];

        list($type, $blockname) = \core_component::normalize_component($recipe['component']);
        $description = 'Block '.$blockname.' is defined here.';
        $this->assertContains($description, $blockfile);

        $moodleinternal = "defined('MOODLE_INTERNAL') || die()";
        // The block file is not internal.
        $this->assertNotContains($moodleinternal, $blockfile);

        // The block file should not include the config.php file.
        $this->assertNotRegExp('/require.+config\.php/', $blockfile);

        $classdefinition = 'class '.$recipe['component'].' extends block_base';
        $this->assertContains($classdefinition, $blockfile);

        $init = 'public function init()';
        $this->assertContains($init, $blockfile);

        $getcontent = 'public function get_content()';
        $this->assertContains($getcontent, $blockfile);

        $specialization = 'public function specialization()';
        $this->assertContains($specialization, $blockfile);

        $allowmultiple = 'function instance_allow_multiple()';
        $this->assertContains($allowmultiple, $blockfile);

        $hasconfig = 'function has_config()';
        $this->assertContains($hasconfig, $blockfile);

        $applicableformats = 'public function applicable_formats()';
        $this->assertContains($applicableformats, $blockfile);

        $allformat = "'all' => false,";
        $this->assertContains($allformat, $blockfile);

        $courseviewformat = "'course-view' => true,";
        $this->assertContains($courseviewformat, $blockfile);

        $courseviewsocialformat = "'course-view-social' => false,";
        $this->assertContains($courseviewsocialformat, $blockfile);
    }

    /**
     * Tests creating the edit_form.php file.
     */
    public function test_block_edit_form_feature() {
        $logger = new Logger('blocktest');
        $logger->pushHandler(new NullHandler());
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $manager->load_recipe($recipe);
        $manager->make();


        $files = $manager->get_files_content();

        $this->assertArrayHasKey('edit_form.php', $files);
        $editformfile = $files['edit_form.php'];

        list($type, $blockname) = \core_component::normalize_component($recipe['component']);
        $description = 'Form for editing '.$blockname.' block instances.';
        $this->assertContains($description, $editformfile);

        $moodleinternal = "defined('MOODLE_INTERNAL') || die()";
        // The edit_form file is not internal.
        $this->assertNotContains($moodleinternal, $editformfile);

        // The edit_form file should not include the config.php file.
        $this->assertNotRegExp('/require.+config\.php/', $editformfile);

        $classdefinition = 'class '.$recipe['component'].'_edit_form extends block_edit_form';
        $this->assertContains($classdefinition, $editformfile);
    }
}
