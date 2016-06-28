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

        $this->logger->notice('Done');
    }

    /**
     * Create the plugin files at $targetdir.
     *
     * @param string $targetdir The target directory for the files.
     */
    public function write_files($targetdir) {

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
            $this->prepare_file_skeleton('db/uninstall.php', 'php_internal_file', 'uninstall');
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
    }

    /**
     * Registers a new file skeleton
     *
     * @param string $filename
     * @param string $skeltype
     * @param string $template
     */
    protected function prepare_file_skeleton($filename, $skeltype, $template) {

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

        // Skeletons have access to the whole recipe.
        $data = $this->recipe;

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
