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

        if ($plugintype === 'mod') {
            $this->prepare_mod_files();
        }

        if ($this->should_have('readme')) {
            $this->prepare_file_skeleton('README.md', 'txt_file', 'readme');
        }

        if ($this->should_have('license')) {
            $this->prepare_file_skeleton('LICENSE.md', 'txt_file', 'license');
        }

        if ($this->should_have('capabilities')) {
            $this->prepare_file_skeleton('db/access.php', 'php_internal_file', 'db_access');
        }

        if ($this->should_have('settings')) {
            $this->prepare_file_skeleton('settings.php', 'php_internal_file', 'settings');
        }

        if ($this->should_have('install')) {
            $this->prepare_file_skeleton('db/install.php', 'php_internal_file', 'db_install');
        }

        if ($this->should_have('uninstall')) {
            $this->prepare_file_skeleton('db/uninstall.php', 'php_internal_file', 'db_uninstall');
        }

        if ($this->should_have('upgrade')) {
            $this->prepare_file_skeleton('db/upgrade.php', 'php_internal_file', 'db_upgrade');
            if ($this->should_have('upgradelib')) {
                $this->prepare_file_skeleton('db/upgradelib.php', 'php_internal_file', 'db_upgradelib');
            }
        }

        if ($this->should_have('message_providers')) {
            $this->prepare_file_skeleton('db/messages.php', 'php_internal_file', 'db_messages');
        }

        if ($this->should_have('mobile_addons')) {
            $this->prepare_file_skeleton('db/mobile.php', 'mobile_php_file', 'db_mobile');
        }

        if ($this->should_have('observers')) {
            $this->prepare_file_skeleton('db/events.php', 'php_internal_file', 'db_events');
            $this->prepare_observers();
        }

        if ($this->should_have('events')) {
            $this->prepare_events();
        }

        if ($this->should_have('cli_scripts')) {
            $this->prepare_cli_files();
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

        foreach ($stringids as $stringid) {

            $found = false;
            if (!empty($this->recipe['strings'])) {
                foreach ($this->recipe['strings'] as $string) {
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

        $this->prepare_file_skeleton('index.php', 'php_web_file', 'mod/index');
        $this->prepare_file_skeleton('view.php', 'view_php_file', 'mod/view');
        $this->prepare_file_skeleton('mod_form.php', 'php_internal_file', 'mod/mod_form');
        $this->prepare_file_skeleton('lib.php', 'lib_php_file', 'mod/lib');

        if ($this->should_have('gradebook')) {
            $gradebookfunctions = array(
                'scale_used',
                'scale_used_anywhere',
                'grade_item_update',
                'grade_item_delete',
                'update_grades'
            );
            $this->files['lib.php']->add_functions($gradebookfunctions);

            $this->files['lib.php']->add_supported_feature('FEATURE_GRADE_HAS_GRADE');
            $this->prepare_file_skeleton('grade.php', 'php_web_file', 'mod/grade');

            $this->files['mod_form.php']->set_attribute('has_gradebook');
        }

        if ($this->should_have('file_area')) {
            $fileareafunctions = array(
                'get_file_areas',
                'get_file_info',
                'pluginfile'
            );
            $this->files['lib.php']->add_functions($fileareafunctions);
        }

        $this->files['lib.php']->add_supported_feature('FEATURE_MOD_INTRO');

        // 'mod/<modname>:addinstance' and 'mod/<modname>:view' capabilities are recommended.
        if (!$this->should_have('capabilities')) {
            $this->logger->warning('Capabilities not defined');
        }

        if (!$this->should_have('observers')) {
            $this->logger->warning('Observers not defined');
        }

        if (!$this->should_have('upgrade')) {
            $this->logger->warning('Upgrade feature not defined');
        }

        if ($this->should_have('backup_moodle2')) {
            $this->prepare_mod_backup_moodle2();
            $this->files['lib.php']->add_supported_feature('FEATURE_BACKUP_MOODLE2');
        } else {
            $this->logger->warning('Backup_moodle2 feature not defined');
        }

        if ($this->should_have('navigation')) {
            $this->files['lib.php']->set_attribute('has_navigation');
        }
    }

    /*
     * Prepares the skeleton files for the 'backup_moodle2' feature for an activity module.
     */
    public function prepare_mod_backup_moodle2() {

        $componentname = $this->recipe['component_name'];
        $hassettingslib = $this->should_have('settingslib');

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

                $this->files['locallib.php']->add_function($observer['callback']);
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

        if (!is_array($this->recipe['cli_scripts'])) {
            throw new exception('No cli_script file names specified');
        }

        foreach ($this->recipe['cli_scripts'] as $filename) {
            $this->prepare_file_skeleton('cli/'.$filename.'.php', 'php_cli_file', 'cli');
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
     * Should the generated plugin have the given feature?
     *
     * @param string $feature
     * @return bool
     */
    protected function should_have($feature) {

        if (isset($this->recipe['features'][$feature])) {
            return (bool) $this->recipe['features'][$feature];
        }

        if ($feature === 'capabilities') {
            return !empty($this->recipe['capabilities']);
        }

        if ($feature === 'upgradelib') {
            return !empty($this->recipe['upgrade']['upgradelib']);
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
        if ($feature === 'backup_moodle2') {
            return !empty($this->recipe['backup_moodle2']);
        }

        if ($feature === 'settingslib') {
            $shouldhavebackup = $this->should_have('backup_moodle2');
            $notempty = !empty($this->recipe['backup_moodle2']['settingslib']);

            return $shouldhavebackup && $notempty && ($this->recipe['backup_moodle2']['settingslib'] === true);
        }

        return false;
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
