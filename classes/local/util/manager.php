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
 * Provides \tool_pluginskel\local\util\manager class
 *
 * @package     tool_pluginskel
 * @subpackage  util
 * @copyright   2016 Alexandru Elisei <alexandru.elisei@gmail.com>, David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_pluginskel\local\util;

use moodle_exception;
use core_component;

/**
 * Main controller class for the plugin skeleton generation.
 *
 * @copyright 2016 David Mudrak <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /** @var Monolog\Logger */
    protected $logger = null;

    /** @var array */
    protected $recipe = null;

    /** @var Mustache_Engine */
    protected $mustache = null;

    /** @var array */
    protected $files = [];

    /**
     * Factory method returning manager instance.
     *
     * @param Monolog\Logger $logger
     * @param array $recipe
     * @return \tool_pluginskel\local\util\manager
     */
    public static function instance($logger) {

        $logger->debug('Initialising manager instance');

        $manager = new self();
        $manager->init_logger($logger);
        $manager->init_templating_engine();

        return $manager;
    }

    /**
     * Validate and initialize the plugin generation recipe.
     *
     * @param arrayu $recipe
     */
    public function load_recipe(array $recipe) {
        $this->init_recipe($recipe);
    }

    /**
     * Disable direct instantiation to force usage of a factory method.
     */
    protected function __construct() {
    }

    /**
     * Generate the plugin skeleton described by the recipe.
     */
    public function make() {

        $this->logger->info('Preparing file contents');
        $this->prepare_files_skeletons();

        foreach ($this->files as $filename => $file) {
            $this->logger->info('Rendering file skeleton:', ['file' => $filename]);
            $file->render($this->mustache);
        }
    }

    /**
     * Create the plugin files at $targetdir.
     *
     * @param string $targetdir The target directory for the files.
     */
    public function write_files($targetdir) {

        $this->logger->info('Writing skeleton files', ['targetdir' => $targetdir]);

        if (empty($this->files)) {
            throw new exception('There are no files to write');
        }

        $result = mkdir($targetdir, 0755, true);
        if ($result === false) {
            throw new exception('Error creating target directory: '.$targetdir);
        }

        foreach ($this->files as $filename => $file) {

            $filepath = $targetdir.'/'.$filename;
            $dirpath = dirname($filepath);

            if (!file_exists($dirpath)) {
                $result = mkdir($dirpath, 0755, true);
                if ($result === false) {
                    throw new exception('Error creating directory: '.$dirpath);
                }
            }

            $result = file_put_contents($filepath, $file->content);
            if ($result === false) {
                throw new exception('Error writing to file: '.$filepath);
            }
        }
    }


    /**
     * Return a list of the files and their contents.
     *
     * @return string[] The list of files.
     */
    public function get_files_content() {
        if (empty($this->files)) {
            $this->logger->notice('Requesting empty files');
            return array();
        }

        $files = array();
        foreach ($this->files as $filename => $file) {
            $files[$filename] = $file->content;
        }

        return $files;
    }

    /**
     * Populates the list of files skeletons instances
     */
    protected function prepare_files_skeletons() {

        $this->prepare_file_skeleton('version.php', 'version_php_file', 'version');
        $this->prepare_file_skeleton('lang/en/'.$this->recipe['component'].'.php', 'lang_file', 'lang');

        $plugintype = $this->recipe['component_type'];

        if ($plugintype === 'qtype') {
            $this->prepare_qtype_files();
        }

        if ($plugintype === 'mod') {
            $this->prepare_mod_files();
        }

        if ($plugintype === 'block') {
            $this->prepare_block_files();
        }

        if ($plugintype === 'theme') {
            $this->prepare_theme_files();
        }

        if ($plugintype === 'auth') {
            $this->prepare_auth_files();
        }

        if ($plugintype === 'atto') {
            $this->prepare_atto_files();
        }

        if ($plugintype === 'enrol') {
            $this->prepare_enrol_files();
        }

        if ($this->has_common_feature('readme')) {
            $this->prepare_file_skeleton('README.md', 'txt_file', 'readme');
        }

        if ($this->has_common_feature('license')) {
            $this->prepare_file_skeleton('LICENSE.md', 'txt_file', 'license');
        }

        if ($this->has_common_feature('capabilities')) {
            $this->prepare_capabilities();
        }

        if ($this->has_common_feature('settings')) {
            $this->prepare_file_skeleton('settings.php', 'php_internal_file', 'settings');
        }

        if ($this->has_common_feature('install')) {
            $this->prepare_file_skeleton('db/install.php', 'php_internal_file', 'db_install');
        }

        if ($this->has_common_feature('uninstall')) {
            $this->prepare_file_skeleton('db/uninstall.php', 'php_internal_file', 'db_uninstall');
        }

        if ($this->has_common_feature('upgrade')) {
            $this->prepare_file_skeleton('db/upgrade.php', 'php_internal_file', 'db_upgrade');
            if ($this->has_common_feature('upgradelib')) {
                $this->prepare_file_skeleton('db/upgradelib.php', 'php_internal_file', 'db_upgradelib');
            }
        }

        if ($this->has_common_feature('message_providers')) {
            $this->prepare_file_skeleton('db/messages.php', 'php_internal_file', 'db_messages');
        }

        if ($this->has_common_feature('mobile_addons')) {
            $this->prepare_file_skeleton('db/mobile.php', 'mobile_php_file', 'db_mobile');
        }

        if ($this->has_common_feature('observers')) {
            $this->prepare_file_skeleton('db/events.php', 'php_internal_file', 'db_events');
            $this->prepare_observers();
        }

        if ($this->has_common_feature('events')) {
            $this->prepare_events();
        }

        if ($this->has_common_feature('cli_scripts')) {
            $this->prepare_cli_files();
        }

        if ($this->has_common_feature('phpunit_tests')) {
            $this->prepare_phpunit_tests();
        }
    }

    /**
     * Prepares the capabilities.
     */
    protected function prepare_capabilities() {

        $this->prepare_file_skeleton('db/access.php', 'php_internal_file', 'db_access');

        $stringids = array();
        foreach ($this->recipe['capabilities'] as $capability) {
            $stringids[] = $this->recipe['component_name'].':'.$capability['name'];
        }

        $this->verify_strings_exist($stringids);
    }

    /**
     * Prepares the PHPUnit test files.
     */
    protected function prepare_phpunit_tests() {

        foreach ($this->recipe['phpunit_tests'] as $class) {

            $classname = $class['classname'];

            if (strpos($classname, $this->recipe['component']) !== false) {
                $classname = substr($classname, strlen($this->recipe['component']) + 1);
            }

            if (strpos($classname, '_testcase') !== false) {
                $classname = substr($classname, 0, strlen($classname) - strlen('_testcase'));
            }

            $filename = 'tests/'.$classname.'_test.php';
            $this->prepare_file_skeleton($filename, 'phpunit_test_file', 'phpunit');

            $this->files[$filename]->set_classname($classname);
        }
    }

    /**
     * Prepares the files for an authentication plugin.
     */
    protected function prepare_auth_files() {

        $this->prepare_file_skeleton('auth.php', 'auth_php_file', 'auth/auth');

        $stringids = array(
            'auth_description'
        );
        $this->verify_strings_exist($stringids);

        if ($this->has_component_feature('config_ui')) {
            $this->files['auth.php']->set_attribute('has_config_form');
            $this->files['auth.php']->set_attribute('has_process_config');
        }

        $recipefeatures = array(
            'can_change_password',
            'can_edit_profile',
            'prevent_local_passwords',
            'is_synchronised_with_external',
            'can_reset_password',
            'can_signup',
            'can_confirm',
            'can_be_manually_set',
            'is_internal'
        );

        foreach ($recipefeatures as $feature) {
            if ($this->has_component_feature($feature)) {
                $this->files['auth.php']->set_attribute('has_'.$feature);
            }
        }
    }

    /*
     * Prepares the files for an atto plugin.
     */
    protected function prepare_atto_files() {

        $buttonjsfile = 'yui/src/button/js/button.js';
        $this->prepare_file_skeleton($buttonjsfile, 'base', 'atto/button_js');

        $buttonjsonfile = 'yui/src/button/meta/button.json';
        $this->prepare_file_skeleton($buttonjsonfile, 'base', 'atto/button_json');

        $buildjsonfile = 'yui/src/button/build.json';
        $this->prepare_file_skeleton($buildjsonfile, 'base', 'atto/build');

        if ($this->has_component_feature('strings_for_js')) {

            $this->prepare_file_skeleton('lib.php', 'lib_php_file', 'atto/lib');
            $this->files['lib.php']->set_attribute('has_strings_for_js');

            $jsstrings = array();
            foreach ($this->recipe['atto_features']['strings_for_js'] as $string) {
                $jsstrings[] = $string['id'];
            }
            $this->verify_strings_exist($jsstrings);
        }

        if ($this->has_component_feature('params_for_js')) {

            if (empty($this->files['lib.php'])) {
                $this->prepare_file_skeleton('lib.php', 'lib_php_file', 'atto/lib');
            }

            $this->files['lib.php']->set_attribute('has_params_for_js');
            $this->files[$buttonjsfile]->set_attribute('has_params_for_js');
        }
    }

    /**
     * Prepares the files for an enrolment plugin.
     */
    protected function prepare_enrol_files() {

        $this->prepare_file_skeleton('lib.php', 'lib_php_file', 'enrol/lib');

        $enrolfeatures = array(
            'allow_enrol',
            'allow_unenrol',
            'allow_manage',
            'allow_unenrol_user'
        );

        foreach ($enrolfeatures as $enrolfeature) {
            if ($this->has_component_feature($enrolfeature)) {
                $this->files['lib.php']->set_attribute('has_'.$enrolfeature);
            }
        }

        if ($this->has_component_feature('allow_enrol')) {
            $this->verify_capability_exists('enrol');
        }

        if ($this->has_component_feature('allow_unenrol') || $this->has_component_feature('allow_unenrol_user')) {
            $this->verify_capability_exists('unenrol');
        }

        if ($this->has_component_feature('allow_manage')) {
            $this->verify_capability_exists('manage');
        }
    }

    /**
     * Checks if the capability has been defined by the user.
     *
     * If the capability hasn't been defined a warning is logged.
     *
     * @param string $capabilitnyname
     */
    protected function verify_capability_exists($capabilityname) {

        $hascapability = false;

        if ($this->has_common_feature('capabilities')) {
            foreach ($this->recipe['capabilities'] as $capability) {
                if ($capability['name'] == $capabilityname) {
                    $hascapability = true;
                    break;
                }
            }
        }

        if (!$hascapability) {
            $this->logger->warning("Missing capability: '$capabilityname'");
        }
    }

    /**
     * Prepares the files for a block plugin.
     */
    protected function prepare_block_files() {

        if (!$this->has_common_feature('capabilities')) {
            // 'block/<blockname>:addinstance' is required.
            // 'block/<blockname>:myaddinstance' is also required if applicable format 'my' is set to true.
            $this->logger->warning('Capabilities not defined');
        }

        $blockrecipe = $this->recipe;

        // Convert boolean to string.
        if ($this->has_component_feature('applicable_formats')) {
            foreach ($blockrecipe['block_features']['applicable_formats'] as $key => $value) {
                if (is_bool($value['allowed'])) {
                    if ($value['allowed'] === true) {
                        $blockrecipe['block_features']['applicable_formats'][$key]['allowed'] = 'true';
                    } else {
                        $blockrecipe['block_features']['applicable_formats'][$key]['allowed'] = 'false';
                    }
                }
            }
        }

        $this->prepare_file_skeleton($this->recipe['component'].'.php', 'base', 'block/block', $blockrecipe);

        if ($this->has_component_feature('edit_form')) {
            $this->prepare_file_skeleton('edit_form.php', 'base', 'block/edit_form');
        }

        if ($this->has_component_feature('instance_allow_multiple')) {
            $this->files[$this->recipe['component'].'.php']->set_attribute('has_instance_allow_multiple');
        }

        if ($this->has_common_feature('settings')) {
            $this->files[$this->recipe['component'].'.php']->set_attribute('has_config');
        }

        if ($this->has_component_feature('backup_moodle2')) {
            $this->prepare_block_backup_moodle2();
        }
    }

    /**
     * Prepares the backup files for a block plugin.
     */
    protected function prepare_block_backup_moodle2() {

        $componentname = $this->recipe['component_name'];
        $hassettingslib = $this->has_component_feature('settingslib');
        $hasbackupstepslib = $this->has_component_feature('backup_stepslib');
        $hasrestorestepslib = $this->has_component_feature('restore_stepslib');

        $backuptaskfile = 'backup/moodle2/backup_'.$componentname.'_block_task.class.php';
        $this->prepare_file_skeleton($backuptaskfile, 'php_internal_file', 'block/backup/moodle2/backup_block_task_class');

        if ($hassettingslib) {
            $settingslibfile = 'backup/moodle2/backup_'.$componentname.'_settingslib.php';
            $this->prepare_file_skeleton($settingslibfile, 'php_internal_file', 'block/backup/moodle2/backup_settingslib');
            $this->files[$backuptaskfile]->set_attribute('has_settingslib');
        }

        if ($hasbackupstepslib) {
            $stepslibfile = 'backup/moodle2/backup_'.$componentname.'_stepslib.php';
            $this->prepare_file_skeleton($stepslibfile, 'php_internal_file', 'block/backup/moodle2/backup_stepslib');
            $this->files[$backuptaskfile]->set_attribute('has_stepslib');
        }

        if ($this->has_component_feature('restore_task')) {
            $restoretaskfile = 'backup/moodle2/restore_'.$componentname.'_block_task.class.php';
            $this->prepare_file_skeleton($restoretaskfile, 'php_internal_file', 'block/backup/moodle2/restore_block_task_class');

            if ($hasrestorestepslib) {
                $stepslibfile = 'backup/moodle2/restore_'.$componentname.'_stepslib.php';
                $this->prepare_file_skeleton($stepslibfile, 'php_internal_file', 'block/backup/moodle2/restore_stepslib');
                $this->files[$restoretaskfile]->set_attribute('has_stepslib');
            }
        }
    }

    /**
     * Prepares the files for a theme.
     */
    protected function prepare_theme_files() {

        $stringids = array('choosereadme');
        $this->verify_strings_exist($stringids);

        $this->prepare_file_skeleton('config.php', 'base', 'theme/config');

        // HTML5 is the default Moodle doctype.
        $ishtml5 = true;

        if (!empty($this->recipe['theme_features']['doctype'])) {
            $this->files['config.php']->set_attribute('has_doctype');
            if ($this->recipe['theme_features']['doctype'] != 'html5') {
                $ishtml5 = false;
            }
        }

        if ($this->has_component_feature('parents')) {
            $this->files['config.php']->set_attribute('has_parents');
        }

        if ($this->has_component_feature('stylesheets')) {
            $this->files['config.php']->set_attribute('has_stylesheets');

            foreach ($this->recipe['theme_features']['stylesheets'] as $stylesheet) {
                $this->prepare_file_skeleton('styles/'.$stylesheet['name'].'.css', 'base', 'theme/stylesheet');
            }
        }

        if ($this->has_component_feature('all_layouts')) {
            $this->files['config.php']->set_attribute('has_all_layouts');
        }

        if ($this->has_component_feature('custom_layouts')) {
            foreach ($this->recipe['theme_features']['custom_layouts'] as $layout) {
                $layoutfile = 'layout/'.$layout['name'].'.php';
                $this->prepare_file_skeleton($layoutfile, 'base', 'theme/layout');

                if ($ishtml5) {
                    $this->files[$layoutfile]->set_attribute('is_html5');
                }
            }
        }
    }

    /**
     * Prepares the files for a question types plugin.
     */
    protected function prepare_qtype_files() {

        $stringids = array(
            'pluginnamesummary',
            'pluginnameadding',
            'pluginnameediting',
            'pluginname_help'
        );

        $this->verify_strings_exist($stringids);

        $this->prepare_file_skeleton('question.php', 'php_internal_file', 'qtype/question');
        $this->prepare_file_skeleton('questiontype.php', 'php_internal_file', 'qtype/questiontype');
        $this->prepare_file_skeleton('classes/output/renderer.php', 'php_internal_file', 'qtype/renderer');

        $editform = 'edit_'.$this->recipe['component_name'].'_form.php';
        $this->prepare_file_skeleton($editform, 'php_internal_file', 'qtype/edit_form');
    }

     /**
      * Verifies that the string ids are present in the recipe.
      *
      * @param string[] $stringids Sequence of string ids.
      */
    protected function verify_strings_exist($stringids) {
        foreach ($stringids as $stringid) {
            $found = false;
            if ($this->has_common_feature('lang_strings')) {
                foreach ($this->recipe['lang_strings'] as $string) {
                    if ($string['id'] === $stringid) {
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                $this->logger->warning("String id '$stringid' not set");
            }
        }
    }

    /**
     * Prepares the files for an activity module plugin.
     */
    protected function prepare_mod_files() {

        $componentname = $this->recipe['component_name'];

        $stringids = array(
            $componentname.'name',
            $componentname.'name_help',
            $componentname.'settings',
            $componentname.'fieldset',
            'missingidandcmid',
            'modulename',
            'modulename_help',
            'modulenameplural',
            'nonewmodules',
            'pluginadministration',
            'view'
        );

        $this->verify_strings_exist($stringids);

        $this->prepare_file_skeleton('index.php', 'php_web_file', 'mod/index');
        $this->prepare_file_skeleton('view.php', 'view_php_file', 'mod/view');
        $this->prepare_file_skeleton('mod_form.php', 'php_internal_file', 'mod/mod_form');
        $this->prepare_file_skeleton('lib.php', 'lib_php_file', 'mod/lib');

        if ($this->has_component_feature('gradebook')) {

            $gradebookfunctions = array(
                'scale_used',
                'scale_used_anywhere',
                'grade_item_update',
                'grade_item_delete',
                'update_grades'
            );

            foreach ($gradebookfunctions as $gradebookfunction) {
                $this->files['lib.php']->set_attribute('has_'.$gradebookfunction);
            }

            $this->files['lib.php']->add_supported_feature('FEATURE_GRADE_HAS_GRADE');
            $this->prepare_file_skeleton('grade.php', 'php_web_file', 'mod/grade');

            $this->files['mod_form.php']->set_attribute('has_gradebook');
        }

        if ($this->has_component_feature('file_area')) {

            $fileareafunctions = array(
                'get_file_areas',
                'get_file_info',
                'pluginfile'
            );

            foreach ($fileareafunctions as $fileareafunction) {
                $this->files['lib.php']->set_attribute('has_'.$fileareafunction);
            }

        }

        $this->files['lib.php']->add_supported_feature('FEATURE_MOD_INTRO');

        if ($this->has_component_feature('backup_moodle2')) {
            $this->prepare_mod_backup_moodle2();
            $this->files['lib.php']->add_supported_feature('FEATURE_BACKUP_MOODLE2');
        } else {
            $this->logger->warning('Backup_moodle2 feature not defined');
        }

        if ($this->has_component_feature('navigation')) {
            $this->files['lib.php']->set_attribute('has_navigation');
        }
    }

    /*
     * Prepares the skeleton files for the 'backup_moodle2' feature for an activity module.
     */
    protected function prepare_mod_backup_moodle2() {

        $componentname = $this->recipe['component_name'];
        $hassettingslib = $this->has_component_feature('settingslib');

        $this->prepare_file_skeleton('backup/moodle2/backup_'.$componentname.'_activity_task.class.php', 'backup_activity_task_file',
                                     'mod/backup/moodle2/backup_activity_task_class');
        if ($hassettingslib) {
            $this->files['backup/moodle2/backup_'.$componentname.'_activity_task.class.php']->set_attribute('has_settingslib');
        }

        $this->prepare_file_skeleton('backup/moodle2/backup_'.$componentname.'_stepslib.php', 'php_internal_file',
                                     'mod/backup/moodle2/backup_stepslib');

        if ($hassettingslib) {
            $this->prepare_file_skeleton('backup/moodle2/backup_'.$componentname.'_settingslib.php', 'php_internal_file',
                                         'mod/backup/moodle2/backup_settingslib');
        }

        $this->prepare_file_skeleton('backup/moodle2/restore_'.$componentname.'_activity_task.class.php', 'php_internal_file',
                                     'mod/backup/moodle2/restore_activity_task_class');

        $this->prepare_file_skeleton('backup/moodle2/restore_'.$componentname.'_stepslib.php', 'php_internal_file',
                                     'mod/backup/moodle2/restore_stepslib');
    }

    /**
     * Prepares the observer class files.
     */
    protected function prepare_observers() {

        foreach ($this->recipe['observers'] as $observer) {
            if (empty($observer['eventname'])) {
                throw new exception('Missing eventname from observers');
            }

            if (empty($observer['callback'])) {
                throw new exception('Missing callback from observers');
            }

            $observerrecipe = $this->recipe;
            $observerrecipe['observer'] = $observer;

            $isclass = strpos($observer['callback'], '::');

            // Adding observer class.
            if ($isclass !== false) {

                $isinsidenamespace = strpos($observer['callback'], '\\');
                if ($isinsidenamespace !== false) {
                    $observernamespace = explode('\\', $observer['callback']);
                    $namecallback = end($observernamespace);

                    list($observername, $callback) = explode('::', $namecallback);

                    $namespace = substr($observer['callback'], 0, strrpos($observer['callback'], '\\'));
                    $namespace = trim($namespace, '\\');
                } else {
                    list($observername, $callback) = explode('::', $observer['callback']);
                }

                if (strpos($observername, $this->recipe['component']) !== false) {
                    $observername = substr($observername, strlen($this->recipe['component'].'_'));
                }

                $observerfile = 'classes/'.$observername.'.php';

                if (empty($this->files[$observerfile])) {
                    $this->prepare_file_skeleton($observerfile, 'observer_file', 'classes_observer', $observerrecipe);
                    $this->files[$observerfile]->set_observer_name($observername);
                }

                $this->files[$observerfile]->add_event_callback($callback, $observer['eventname']);

                if ($isinsidenamespace !== false) {
                    $this->files[$observerfile]->set_file_namespace($namespace);
                }
            } else {

                // Functions specific to the plugin are defined in the locallib.php file.
                if (empty($this->files['locallib.php'])) {
                    $this->prepare_file_skeleton('locallib.php', 'locallib_php_file', 'locallib');
                }

                $this->files['locallib.php']->add_event_callback($observer['callback'], $observer['eventname']);
            }
        }
    }

    /*
     * Prepare the event class files.
     */
    protected function prepare_events() {

        foreach ($this->recipe['events'] as $event) {
            if (empty($event['eventname'])) {
                throw new exception('Missing event name');
            }

            $eventrecipe = $this->recipe;
            $eventrecipe['event'] = $event;

            if (empty($eventrecipe['event']['extends'])) {
                $eventrecipe['event']['extends'] = '\core\event\base';
            }

            $eventfile = 'classes/event/'.$eventrecipe['event']['eventname'].'.php';
            $this->prepare_file_skeleton($eventfile, 'php_internal_file', 'classes_event_event', $eventrecipe);
        }
    }

    /*
     * Prepare the file skeletons for the cli_scripts feature.
     */
    protected function prepare_cli_files() {

        foreach ($this->recipe['cli_scripts'] as $script) {
            $this->prepare_file_skeleton('cli/'.$script['filename'].'.php', 'php_cli_file', 'cli');
        }
    }

    /**
     * Registers a new file skeleton
     *
     * @param string $filename
     * @param string $skeltype
     * @param string $template
     * @param string[] $recipe Recipe to be used in generating the file instead of the global recipe.
     */
    protected function prepare_file_skeleton($filename, $skeltype, $template, $recipe = null) {

        if (strpos($template, 'file/') !== 0) {
            $template = 'file/'.$template;
        }

        $this->logger->debug('Preparing file skeleton:', ['filename' => $filename, 'skeltype' => $skeltype, 'template' => $template]);

        if (isset($this->files[$filename])) {
            throw new exception('The file has already been initialised: '.$filename);
        }

        $skelclass = '\\tool_pluginskel\\local\\skel\\'.$skeltype;

        $skel = new $skelclass();
        $skel->set_template($template);

        if (is_null($recipe)) {
            // Skeleton will have access to the whole recipe.
            $data = $this->recipe;
        } else {
            $data = $recipe;
        }

        // Populate some additional properties
        $data['self']['filename'] = $filename;
        $data['self']['relpath'] = $data['component_root'].'/'.$data['component_name'].'/'.$filename;
        $data['self']['pathtoconfig'] = "__DIR__.'/".str_repeat('../', substr_count($data['self']['relpath'], '/') - 1)."config.php'";

        $skel->set_data($data);

        $this->files[$filename] = $skel;
    }

    /**
     * Does the generated plugin have the given component feature?
     *
     * @param string $feature
     * @return bool
     */
    protected function has_component_feature($feature) {

        $componentfeatures = $this->recipe['component_type'].'_features';

        if ($feature === 'settingslib') {
            $hasbackup = $this->has_component_feature('backup_moodle2');
            return $hasbackup && !empty($this->recipe[$componentfeatures]['backup_moodle2']['settingslib']);
        }

        if ($feature === 'restore_task') {
            $hasbackup = $this->has_component_feature('backup_moodle2');
            return $hasbackup && !empty($this->recipe[$componentfeatures]['backup_moodle2']['restore_task']);
        }

        if ($feature === 'backup_stepslib') {
            $hasbackup = $this->has_component_feature('backup_moodle2');
            return $hasbackup && !empty($this->recipe[$componentfeatures]['backup_moodle2']['backup_stepslib']);
        }

        if ($feature === 'restore_stepslib') {
            $hasbackup = $this->has_component_feature('backup_moodle2');
            return $hasbackup && !empty($this->recipe[$componentfeatures]['backup_moodle2']['restore_stepslib']);
        }

        $attofeatures = array(
            'can_change_password',
            'can_edit_profile',
            'prevent_local_passwords',
            'is_synchronised_with_external',
            'can_reset_password',
            'can_signup',
            'can_confirm',
            'can_be_manually_set',
            'is_internal'
        );

        foreach ($attofeatures as $attofeature) {
            if ($attofeature === $feature) {
                return isset($this->recipe[$componentfeatures][$feature]);
            }
        }

        $enrolfeatures = array(
            'allow_enrol',
            'allow_unenrol',
            'allow_manage',
            'allow_unenrol_user'
        );

        foreach ($enrolfeatures as $enrolfeature) {
            if ($enrolfeature === $feature) {
                return isset($this->recipe[$componentfeatures][$feature]);
            }
        }

        return !empty($this->recipe[$componentfeatures][$feature]);
    }

    /**
     * Does the generated plugin have the given common feature?
     *
     * @param string $feature
     * @return bool
     */
    protected function has_common_feature($feature) {

        if ($feature === 'capabilities') {
            return !empty($this->recipe['capabilities']);
        }

        if ($feature === 'message_providers') {
            return !empty($this->recipe['message_providers']);
        }

        if ($feature === 'observers') {
            return !empty($this->recipe['observers']);
        }

        if ($feature === 'events') {
            return !empty($this->recipe['events']);
        }

        if ($feature === 'mobile_addons') {
            return !empty($this->recipe['mobile_addons']);
        }

        if ($feature === 'cli_scripts') {
            return !empty($this->recipe['cli_scripts']);
        }

        if ($feature === 'phpunit_tests') {
            return !empty($this->recipe['phpunit_tests']);
        }

        if ($feature === 'lang_strings') {
            return !empty($this->recipe['lang_strings']);
        }

        if ($feature === 'upgrade') {
            if (isset($this->recipe['features']['upgrade'])) {
                return (bool) $this->recipe['features']['upgrade'];
            } else {
                return !empty($this->recipe['upgrade']);
            }
        }

        if ($feature === 'upgradelib') {
            $hasupgrade = $this->has_common_feature('upgrade');
            return $hasupgrade && !empty($this->recipe['upgrade']['upgradelib']);
        }

        return !empty($this->recipe['features'][$feature]);
    }

    /**
     * Prepareskeleton of the language strings file
     */
    protected function prepare_lang_file() {

        $this->init_file('lang/en/'.$this->recipe['component'].'.php', 'lang', [
            'strings' => [
                'id' => 'pluginname',
                'text' => $this->recipe['name'],
            ]
        ]);
    }

    /**
     * Sets the logger to be used by this instance.
     *
     * @param Monolog\Logger $logger
     */
    protected function init_logger($logger) {
        $this->logger = $logger;
    }

    /**
     * Validate and set a recipe for the plugin generation.
     *
     * @param array $recipe
     */
    protected function init_recipe($recipe) {
        global $CFG;

        if ($this->recipe !== null) {
            throw new exception('The recipe has already been set for this manager instance');
        }

        if (empty($recipe['component'])) {
            throw new exception('The recipe does not provide the valid component of the plugin');
        }

        $this->recipe = $recipe;
        $this->logger->debug('Recipe loaded:', ['component' => $this->recipe['component']]);

        // Validate the component and set component_type, component_name and component_root.

        list($type, $name) = core_component::normalize_component($this->recipe['component']);

        if ($type === 'core') {
            throw new exception('Core subsystems components not supported');
        }

        if (!empty($this->recipe['component_type']) and $this->recipe['component_type'] !== $type) {
            throw new exception('Component type mismatch');
        }

        if (!empty($this->recipe['component_name']) and $this->recipe['component_name'] !== $name) {
            throw new exception('Component name mismatch');
        }

        $plugintypes = core_component::get_plugin_types();

        if (empty($plugintypes[$type])) {
            throw new exception('Unknown plugin type: '.$type);
        }

        $root = substr($plugintypes[$type], strlen($CFG->dirroot));

        if (!empty($this->recipe['component_root']) and $this->recipe['component_root'] !== $root) {
            throw new exception('Component type root location mismatch');
        }

        $this->recipe['component_type'] = $type;
        $this->recipe['component_name'] = $name;
        $this->recipe['component_root'] = $root;
    }

    /**
     * Validate and set the target location of the generated plugin.
     *
     * @param string $moodleroot
     */
    protected function init_target_location($moodleroot) {
        global $CFG;

        if ($this->rootdir !== null) {
            throw new exception('The target directory has already been set for this manager instance');
        }

        if (empty($moodleroot)) {
            $moodleroot = $CFG->dirroot;
        }

        if (!file_exists($moodleroot)) {
            throw new exception('Target Moodle root directory does not exist: '.$moodleroot);
        }

        if (empty($this->recipe['component_root'])) {
            throw new exception('The component type root location not detected');
        }

        $rootdir = $moodleroot.'/'.$this->recipe['component_root'].'/'.$this->recipe['component_name'];
        $rootdir = str_replace('//', '/', $rootdir);

        if (file_exists($rootdir)) {
            throw new exception('Target plugin directory already exists: '.$rootdir);
        }

        // TODO: Check the location is writable.

        $this->logger->info('Target directory: '.$rootdir);
        $this->rootdir = $rootdir;
    }

    /**
     * Prepare the mustache engine instance
     */
    protected function init_templating_engine() {
        $this->mustache = new mustache(['logger' => $this->logger]);
    }
}
