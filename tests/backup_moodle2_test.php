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
 * File containing tests for the 'backup_moodle2' feature.
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
 * Backup_moodle2 test class.
 *
 * @package     tool_pluginskel
 * @copyright   2016 Alexandru Elisei alexandru.elisei@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginskel_backup_moodle2_testcase extends advanced_testcase {

    /** @var string[] The test recipe. */
    protected static $recipe = array(
        'component' => 'backupmoodle2test',
        'name'      => 'Backup_moodle2 test',
        'copyright' => '2016 Alexandru Elisei <alexandru.elisei@gmail.com>',
        'features'  => array(
            'all' => false
        ),
        'backup_moodle2' => array(
            'backup_elements' => array('node'),
            'restore_elements' => array(
                array('name' => 'node', 'path' => '/path/to/file')
            )
        )
    );

    /** @var string The plugin files path relative the Moodle root. */
    static protected $relpath;

    /** @var string The plugin name, without the frankenstyle prefix. */
    static protected $pluginname;

    /**
     * Sets the $relpath and the $pluginname.
     */
    public static function setUpBeforeClass() {
        global $CFG;

        list($type, $pluginname) = \core_component::normalize_component(self::$recipe['component']);

        $plugintypes = \core_component::get_plugin_types();
        $root = substr($plugintypes[$type], strlen($CFG->dirroot));

        self::$pluginname = $pluginname;
        self::$relpath = $root.'/'.$pluginname;
    }

    /**
     * Tests creating the backup/moodle2/backup_activity_task.class.php file.
     */
    public function test_backup_activity_task_class() {
        $logger = new Logger('backupmoodle2test');
        $logger->pushHandler(new NullHandler());
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $manager->load_recipe($recipe);
        $manager->make();

        $pluginname = self::$pluginname;

        $files = $manager->get_files_content();

        $filename = 'backup/moodle2/backup_'.$pluginname.'_activity_task.class.php';
        $this->assertArrayHasKey($filename, $files);
        $backupfile = $files[$filename];

        // Verify the boilerplate.
        $description = 'The task that provides all the steps to perform a complete backup is defined here.';
        $this->assertContains($description, $backupfile);

        $this->assertRegExp('/\* @subpackage\s+backup-moodle2/', $backupfile);

        $moodleinternal = "defined('MOODLE_INTERNAL') || die();";
        $this->assertContains($moodleinternal, $backupfile);

        $settingslibpath = self::$relpath.'/backup/moodle2/backup_'.$pluginname.'_settingslib.php';
        $this->assertNotContains('require_once($CFG->dirroot.'.'\'/'.$settingslibpath.'\')', $backupfile);

        $stepslibpath = self::$relpath.'/backup/moodle2/backup_'.$pluginname.'_stepslib.php';
        $this->assertContains('require_once($CFG->dirroot.'.'\'/'.$stepslibpath.'\')', $backupfile);

        $classdefinition = 'class backup_'.$pluginname.'_activity_task extends backup_activity_task';
        $this->assertContains($classdefinition, $backupfile);

        $stepdefinition = "\$this->add_step(new backup_".$pluginname."_activity_structure_step('".$pluginname."_structure', '".$pluginname.".xml')";
        $this->assertContains($stepdefinition, $backupfile);
    }

    /**
     * Tests creating the backup/moodle2/backup_settingslib.php file.
     */
    public function test_backup_settingslib() {
        $logger = new Logger('backupmoodle2test');
        $logger->pushHandler(new NullHandler());
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $recipe['backup_moodle2']['settingslib'] = true;
        $manager->load_recipe($recipe);
        $manager->make();

        $pluginname = self::$pluginname;

        $files = $manager->get_files_content();
        $filename = 'backup/moodle2/backup_'.$pluginname.'_settingslib.php';
        $this->assertArrayHasKey($filename, $files);
        $settingslibfile = $files[$filename];

        // Verify the boilerplate.
        $description = 'Plugin custom settings are defined here.';
        $this->assertContains($description, $settingslibfile);
        $this->assertRegExp('/\* @subpackage\s+backup-moodle2/', $settingslibfile);

        $moodleinternal = "defined('MOODLE_INTERNAL') || die();";
        $this->assertContains($moodleinternal, $settingslibfile);

        $filename = 'backup/moodle2/backup_'.$pluginname.'_activity_task.class.php';
        $activitytaskfile = $files[$filename];

        $settingslibpath = self::$relpath.'/backup/moodle2/backup_'.$pluginname.'_settingslib.php';
        $this->assertContains('require_once($CFG->dirroot.'.'\'/'.$settingslibpath.'\')', $activitytaskfile);
    }

    /**
     * Tests creating the backup/moodle2/backup_stepslib.php file.
     */
    public function test_backup_stepslib() {
        $logger = new Logger('backupmoodle2test');
        $logger->pushHandler(new NullHandler());
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $manager->load_recipe($recipe);
        $manager->make();

        $pluginname = self::$pluginname;

        $files = $manager->get_files_content();
        $filename = 'backup/moodle2/backup_'.$pluginname.'_stepslib.php';
        $this->assertArrayHasKey($filename, $files);
        $stepslibfile = $files[$filename];

        // Verify the boilerplate.
        $description = 'Backup steps for '.$recipe['component'].' are defined here.';
        $this->assertContains($description, $stepslibfile);

        $this->assertRegExp('/\* @subpackage\s+backup-moodle2/', $stepslibfile);

        $moodleinternal = "defined('MOODLE_INTERNAL') || die();";
        $this->assertContains($moodleinternal, $stepslibfile);

        $classdefinition = 'class backup_'.$pluginname.'_activity_structure_step extends backup_activity_structure_step';
        $this->assertContains($classdefinition, $stepslibfile);

        $element = $recipe['backup_moodle2']['backup_elements'][0];
        $nestedelement = '$'.$element.' = new backup_nested_element(\''.$element.'\', $attributes, $final_elements)';
        $this->assertContains($nestedelement, $stepslibfile);
    }

    /**
     * Tests creating the backup/moodle2/restore_activity_task.class.php file.
     */
    public function test_restore_activity_task() {
        $logger = new Logger('backupmoodle2test');
        $logger->pushHandler(new NullHandler());
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $manager->load_recipe($recipe);
        $manager->make();

        $pluginname = self::$pluginname;

        $files = $manager->get_files_content();
        $filename = 'backup/moodle2/restore_'.$pluginname.'_activity_task.class.php';
        $this->assertArrayHasKey($filename, $files);
        $restorefile = $files[$filename];

        // Verify the boilerplate.
        $description = 'The task that provides a complete restore of '.$recipe['component'].' is defined here.';
        $this->assertContains($description, $restorefile);

        $this->assertRegExp('/\* @subpackage\s+backup-moodle2/', $restorefile);

        $moodleinternal = "defined('MOODLE_INTERNAL') || die();";
        $this->assertContains($moodleinternal, $restorefile);

        $stepslibpath = self::$relpath.'/backup/moodle2/restore_'.$pluginname.'_stepslib.php';
        $this->assertContains('require_once($CFG->dirroot.'.'\'/'.$stepslibpath.'\')', $restorefile);

        $classdefinition = 'class restore_'.$pluginname.'_activity_task extends restore_activity_task';
        $this->assertContains($classdefinition, $restorefile);

        $stepdefinition = "\$this->add_step(new restore_".$pluginname."_activity_structure_step('".$pluginname."_structure', '".$pluginname.".xml')";
        $this->assertContains($stepdefinition, $restorefile);
    }

    /**
     * Tests creating the backup/moodle2/restore_stepslib.php file.
     */
    public function test_restore_stepslib() {
        $logger = new Logger('backupmoodle2test');
        $logger->pushHandler(new NullHandler());
        $manager = manager::instance($logger);

        $recipe = self::$recipe;
        $manager->load_recipe($recipe);
        $manager->make();

        $pluginname = self::$pluginname;

        $files = $manager->get_files_content();
        $filename = 'backup/moodle2/restore_'.$pluginname.'_stepslib.php';
        $this->assertArrayHasKey($filename, $files);
        $stepslibfile = $files[$filename];

        // Verify the boilerplate.
        $description = 'All the steps to restore '.$recipe['component'].' are defined here.';
        $this->assertContains($description, $stepslibfile);

        $this->assertRegExp('/\* @subpackage\s+backup-moodle2/', $stepslibfile);

        //TODO: restore_stepslib.php is not moodle internal and does not include the config file.

        $classdefinition = 'class restore_'.$pluginname.'_activity_structure_step extends restore_activity_structure_step';
        $this->assertContains($classdefinition, $stepslibfile);

        $element = $recipe['backup_moodle2']['restore_elements'][0]['name'];
        $path = $recipe['backup_moodle2']['restore_elements'][0]['path'];
        $elementpath = "\$paths[] = new restore_path_element('".$element."', '".$path."')";
        $this->assertContains($elementpath, $stepslibfile);

        $processfunction = 'protected function process_'.$element.'($data)';
        $this->assertContains($processfunction, $stepslibfile);
    }
}
