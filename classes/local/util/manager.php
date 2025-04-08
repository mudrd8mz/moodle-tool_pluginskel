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
     * @param string $mustachedir Path to the base directory containing the mustache skeleton templates
     * @return \tool_pluginskel\local\util\manager
     */
    public static function instance($logger, $mustachedir = null) {

        $logger->debug('Initialising manager instance');

        $manager = new self();
        $manager->init_logger($logger);
        $manager->init_templating_engine($mustachedir);

        return $manager;
    }

    /**
     * Returns a list of (component => name) values.
     *
     * @return string[].
     */
    public static function get_plugintype_names() {

        $pluginman = \core_plugin_manager::instance();
        $plugintypes = $pluginman->get_plugin_types();

        // Replacing the directory with the plugin name.
        foreach ($plugintypes as $type => $dir) {
            $plugintypes[$type] = $pluginman->plugintype_name($type);
        }

        return $plugintypes;
    }

    /**
     * Returns a list of general variables needed by the plugin templates.
     *
     * @return string[].
     */
    public static function get_general_variables() {

        $copyright = [
            [
                'name' => 'copyright',
                'type' => 'text',
                'required' => true,
                'default' => get_config('tool_pluginskel', 'copyright'),
            ],
        ];
        $versionvars = \tool_pluginskel\local\skel\version_php_file::get_template_variables();
        $langvars = \tool_pluginskel\local\skel\lang_file::get_template_variables();

        $templatevars = array_merge($copyright, $versionvars, $langvars);

        return $templatevars;
    }

    /**
     * Returns a list of component specific variables needed by the plugin templates.
     *
     * @param string $component
     * @return string[]
     */
    public static function get_component_variables($component) {

        list($type, $name) = core_component::normalize_component($component);

        $componentvars = [];

        if ($type === 'atto') {
            $componentvars = [
                ['name' => 'strings_for_js', 'type' => 'numeric-array', 'values' => [
                    ['name' => 'id', 'type' => 'text'],
                    ['name' => 'text', 'type' => 'text']],
                ],
                ['name' => 'params_for_js', 'type' => 'numeric-array', 'values' => [
                    ['name' => 'name', 'type' => 'text'],
                    ['name' => 'value', 'type' => 'text'],
                    ['name' => 'default', 'type' => 'text'],
                ],
                ],
            ];
        }

        if ($type === 'tiny') {
            $componentvars = [
                [
                    'name' => 'buttons',
                    'type' => 'boolean',
                ],
                [
                    'name' => 'menuitems',
                    'type' => 'boolean',
                ],
                [
                    'name' => 'options',
                    'type' => 'numeric-array',
                    'values' => [
                        [
                            'name' => 'name',
                            'type' => 'text',
                        ],
                        [
                            'name' => 'type',
                            'type' => 'multiple-options',
                            'values' => [
                                'string' => 'string',
                                'number' => 'number',
                                'boolean' => 'boolean',
                                'array' => 'array',
                                'function' => 'function',
                                'object' => 'object',
                                'string[]' => 'string[]',
                                'object[]' => 'object[]',
                                'regexp' => 'regexp',
                            ],
                        ],
                    ],
                ],
            ];
        }

        if ($type === 'auth') {
            $componentvars = [
                ['name' => 'config_ui', 'type' => 'boolean', 'required' => true],
                ['name' => 'description', 'type' => 'text'],
                ['name' => 'can_change_password', 'type' => 'boolean'],
                ['name' => 'can_edit_profile', 'type' => 'boolean'],
                ['name' => 'is_internal', 'type' => 'boolean'],
                ['name' => 'prevent_local_passwords', 'type' => 'boolean'],
                ['name' => 'is_synchronised_with_external', 'type' => 'boolean'],
                ['name' => 'can_reset_password', 'type' => 'boolean'],
                ['name' => 'can_signup', 'type' => 'boolean'],
                ['name' => 'can_confirm', 'type' => 'boolean'],
                ['name' => 'can_be_manually_set', 'type' => 'boolean'],
            ];
        }

        if ($type === 'block') {
            $componentvars = [
                ['name' => 'edit_form', 'type' => 'boolean', 'required' => true],
                ['name' => 'instance_allow_multiple', 'type' => 'boolean', 'required' => true],
                ['name' => 'applicable_formats', 'type' => 'numeric-array', 'values' => [
                    ['name' => 'page', 'type' => 'text'],
                    ['name' => 'allowed', 'type' => 'boolean']],
                ],
                ['name' => 'backup_moodle2', 'type' => 'associative-array', 'values' => [
                    ['name' => 'restore_task', 'type' => 'boolean'],
                    ['name' => 'restore_stepslib', 'type' => 'boolean'],
                    ['name' => 'backup_stepslib', 'type' => 'boolean'],
                    ['name' => 'settingslib', 'type' => 'boolean'],
                    ['name' => 'backup_elements', 'type' => 'numeric-array', 'values' => [
                        ['name' => 'name', 'type' => 'text'],
                    ],
                    ],
                    ['name' => 'restore_elements', 'type' => 'numeric-array', 'values' => [
                        ['name' => 'name', 'type' => 'text'],
                        ['name' => 'path', 'type' => 'text'],
                    ],
                    ],
                ],
                ],
            ];
        }

        if ($type === 'mod') {
            $componentvars = [
                ['name' => 'gradebook', 'type' => 'boolean', 'required' => true],
                ['name' => 'file_area', 'type' => 'boolean', 'required' => true],
                ['name' => 'navigation', 'type' => 'boolean', 'required' => true],
                ['name' => 'backup_moodle2', 'type' => 'associative-array', 'values' => [
                    ['name' => 'settingslib', 'type' => 'boolean'],
                    ['name' => 'backup_elements', 'type' => 'numeric-array', 'values' => [
                        ['name' => 'name', 'type' => 'text'],
                    ],
                    ],
                    ['name' => 'restore_elements', 'type' => 'numeric-array', 'values' => [
                        ['name' => 'name', 'type' => 'text'],
                        ['name' => 'path', 'type' => 'text'],
                    ],
                    ],
                ],
                ],
            ];
        }

        if ($type === 'qtype') {
            $componentvars = [
                ['name' => 'base_class', 'type' => 'text', 'required' => true],
            ];
        }

        if ($type === 'enrol') {
            $componentvars = [
                ['name' => 'allow_enrol', 'type' => 'boolean'],
                ['name' => 'allow_unenrol', 'type' => 'boolean'],
                ['name' => 'allow_unenrol_user', 'type' => 'boolean'],
                ['name' => 'allow_manage', 'type' => 'boolean'],
            ];
        }

        if ($type === 'theme') {
            $componentvars = [
                ['name' => 'all_layouts', 'type' => 'boolean', 'required' => true],
                ['name' => 'doctype', 'type' => 'text'],
                ['name' => 'parents', 'type' => 'numeric-array', 'values' => [
                    ['name' => 'base_theme', 'type' => 'text'],
                ],
                ],
                ['name' => 'stylesheets', 'type' => 'numeric-array', 'values' => [
                    ['name' => 'name', 'type' => 'text'],
                ],
                ],
                ['name' => 'custom_layouts', 'type' => 'numeric-array', 'values' => [
                    ['name' => 'name', 'type' => 'text'],
                ],
                ],
            ];
        }

        return $componentvars;
    }

    /**
     * Returns a list of variables needed for the common features.
     *
     * @return string[].
     */
    public static function get_features_variables() {

        $featuresvars = [];

        $featuresvars[] = ['name' => 'install', 'type' => 'boolean'];
        $featuresvars[] = ['name' => 'uninstall', 'type' => 'boolean'];
        $featuresvars[] = ['name' => 'settings', 'type' => 'boolean'];
        $featuresvars[] = ['name' => 'readme', 'type' => 'boolean', 'default' => true];
        $featuresvars[] = ['name' => 'license', 'type' => 'boolean', 'default' => true];
        $featuresvars[] = ['name' => 'upgrade', 'type' => 'boolean'];
        $featuresvars[] = ['name' => 'upgradelib', 'type' => 'boolean'];

        $capabilities = [
            ['name' => 'capabilities', 'type' => 'numeric-array', 'values' => [
                ['name' => 'name', 'type' => 'text'],
                ['name' => 'title', 'type' => 'text'],
                ['name' => 'riskbitmask', 'type' => 'text'],
                ['name' => 'captype', 'type' => 'multiple-options',
                'values' => ['view' => 'view', 'write' => 'write']],
                ['name' => 'contextlevel', 'type' => 'text'],
                ['name' => 'archetypes', 'type' => 'numeric-array', 'values' => [
                    ['name' => 'role', 'type' => 'multiple-options', 'values' => get_role_archetypes()],
                    ['name' => 'permission', 'type' => 'multiple-options',
                    'values' => [
                        'CAP_ALLOW' => 'CAP_ALLOW',
                        'CAP_PREVENT' => 'CAP_PREVENT',
                        'CAP_PROHIBIT' => 'CAP_PROHIBIT',
                    ],
                    ],
                ],
                ],
                ['name' => 'clonepermissionsfrom', 'type' => 'text'],
            ],
            ],
        ];

        $messageproviders = [
            ['name' => 'message_providers', 'type' => 'numeric-array', 'values' => [
                ['name' => 'name', 'type' => 'text'],
                ['name' => 'title', 'type' => 'text'],
                ['name' => 'capability', 'type' => 'text']]],
        ];

        $cliscripts = [
            ['name' => 'cli_scripts', 'type' => 'numeric-array', 'values' => [
                ['name' => 'filename', 'type' => 'text'],
            ],
            ],
        ];

        $observers = [
            ['name' => 'observers', 'type' => 'numeric-array', 'values' => [
                ['name' => 'eventname', 'type' => 'text'],
                ['name' => 'callback', 'type' => 'text'],
                ['name' => 'includefile', 'type' => 'text'],
                ['name' => 'priority', 'type' => 'int'],
                ['name' => 'internal', 'type' => 'boolean'],
            ],
            ],
        ];

        $events = [
            ['name' => 'events', 'type' => 'numeric-array', 'values' => [
                ['name' => 'eventname', 'type' => 'text'],
                ['name' => 'extends', 'type' => 'text'],
            ],
            ],
        ];

        $mobileaddons = [
            ['name' => 'mobile_addons', 'type' => 'numeric-array', 'values' => [
                ['name' => 'name', 'type' => 'text'],
                ['name' => 'dependencies', 'type' => 'numeric-array', 'values' => [
                    ['name' => 'name', 'type' => 'text'],
                ],
                ],
            ],
            ],
        ];

        $phpunittests = [
            ['name' => 'phpunit_tests', 'type' => 'numeric-array', 'values' => [
                ['name' => 'classname', 'type' => 'text']],
            ],
        ];

        $featuresvars = array_merge(
            $featuresvars,
            $cliscripts,
            $messageproviders,
            $capabilities,
            $observers,
            $events,
            $mobileaddons,
            $phpunittests
        );

        return $featuresvars;
    }

    /**
     * Validate and initialize the plugin generation recipe.
     *
     * @param array $recipe
     */
    public function load_recipe(array $recipe) {
        $this->init_recipe($recipe);
    }

    /**
     * Adds a new lang string to be generated.
     *
     * @param string $id
     * @param string $text
     */
    public function add_lang_string($id, $text) {

        if (!$this->has_common_feature('lang_strings')) {
            $this->recipe['lang_strings'] = [];
        }

        $this->recipe['lang_strings'][] = ['id' => $id, 'text' => $text];
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
            return [];
        }

        $files = [];
        foreach ($this->files as $filename => $file) {
            $files[$filename] = $file->content;
        }

        return $files;
    }

    /**
     * Populates the list of files skeletons instances
     */
    protected function prepare_files_skeletons() {

        $plugintype = $this->recipe['component_type'];

        if ($plugintype === 'contenttype') {
            $this->prepare_contenttype_files();
        }

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

        if ($plugintype === 'tiny') {
            $this->prepare_tiny_files();
        }

        if ($plugintype === 'enrol') {
            $this->prepare_enrol_files();
        }

        if ($plugintype === 'format') {
            $this->prepare_format_files();
        }

        if ($this->has_common_feature('privacy')) {
            $this->prepare_privacy_files();
        }

        if ($this->has_common_feature('external')) {
            $this->prepare_external_files();
        }

        if ($this->has_common_feature('external') || $this->has_common_feature('services')) {
            $this->prepare_db_services();
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
            $this->prepare_file_skeleton('db/install.php', 'php_library_file', 'db_install');
        }

        if ($this->has_common_feature('uninstall')) {
            $this->prepare_file_skeleton('db/uninstall.php', 'php_library_file', 'db_uninstall');
        }

        if ($this->has_common_feature('upgrade')) {
            $this->prepare_file_skeleton('db/upgrade.php', 'php_internal_file', 'db_upgrade');
            if ($this->has_common_feature('upgradelib')) {
                $this->prepare_file_skeleton('db/upgradelib.php', 'php_library_file', 'db_upgradelib');
                $this->files['db/upgrade.php']->set_attribute('has_upgradelib');
            }
        }

        if ($this->has_common_feature('message_providers')) {
            $this->prepare_message_providers();
        }

        if ($this->has_common_feature('mobile_addons')) {
            $this->prepare_file_skeleton('db/mobile.php', 'mobile_php_file', 'db_mobile');
        }

        if ($this->has_common_feature('observers')) {
            $this->prepare_file_skeleton('db/events.php', 'db_events_php_file', 'db_events');
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

        if ($this->has_common_feature('templates')) {
            $this->prepare_templates();
        }

        $this->prepare_file_skeleton('version.php', 'version_php_file', 'version');

        if ($plugintype === 'mod') {
            $this->prepare_file_skeleton('lang/en/'.$this->recipe['component_name'].'.php', 'lang_file', 'lang');
        } else {
            $this->prepare_file_skeleton('lang/en/'.$this->recipe['component'].'.php', 'lang_file', 'lang');
        }
    }

    /**
     * Prepare the privacy implementation.
     */
    protected function prepare_privacy_files() {
        $this->prepare_file_skeleton('classes/privacy/provider.php', 'privacy_provider_file', 'classes_privacy_provider');
    }

    /**
     * Prepare the files implementing declared external functions.
     */
    protected function prepare_external_files() {

        foreach ($this->recipe['external'] as $externalfunction) {
            if (empty($externalfunction['name'])) {
                $this->logger->warning('External function name not set');
                continue;
            }

            $filename = 'classes/external/' . $externalfunction['name'] . '.php';
            $this->prepare_file_skeleton($filename, 'external_function_file', 'classes_external');
            $this->files[$filename]->generate_external_function_code($externalfunction);
        }
    }

    /**
     * Prepare the file describing external functions and web services.
     */
    protected function prepare_db_services() {

        $hasexternal = !empty($this->recipe['external']);
        $hasservices = !empty($this->recipe['services']);

        if (!$hasexternal && !$hasservices) {
            return;
        }

        $skeleton = $this->prepare_file_skeleton('db/services.php', 'php_internal_file', 'db_services');

        if ($hasexternal) {
            $skeleton->set_attribute('has_external');
        }

        if ($hasservices) {
            $skeleton->set_attribute('has_services');
        }
    }

    /**
     * Prepares the message providers.
     */
    protected function prepare_message_providers() {

        $this->prepare_file_skeleton('db/messages.php', 'php_internal_file', 'db_messages');

        // Adding the title lang strings.
        if (!$this->has_common_feature('lang_strings')) {
            $this->recipe['lang_strings'] = [];
        }

        foreach ($this->recipe['message_providers'] as $messageprovider) {

            if (empty($messageprovider['name'])) {
                $this->logger->warning('Message provider name not set');
                continue;
            }

            if (empty($messageprovider['title'])) {
                $this->logger->warning("Title for message provider '".$messageprovider['name']."' not set");
                continue;
            }

            $stringid = 'messageprovider:'.$messageprovider['name'];
            $this->add_lang_string($stringid, $messageprovider['title']);
        }
    }

    /**
     * Prepares the capabilities.
     */
    protected function prepare_capabilities() {

        $this->prepare_file_skeleton('db/access.php', 'php_internal_file', 'db_access');

        // Adding the title lang strings.
        if (!$this->has_common_feature('lang_strings')) {
            $this->recipe['lang_strings'] = [];
        }

        foreach ($this->recipe['capabilities'] as $capability) {
            if (empty($capability['contextlevel'])) {
                throw new exception('Capability context level not set');
            }

            if (empty($capability['name'])) {
                $this->logger->warning('Capability name not set');
                continue;
            }

            if (empty($capability['title'])) {
                $this->logger->warning("Title for capability '".$capability['name']."' not set");
                continue;
            }

            $stringid = $this->recipe['component_name'].':'.$capability['name'];
            $this->add_lang_string($stringid, $capability['title']);
        }
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

        if ($this->has_component_feature('config_ui')) {
            $this->files['auth.php']->set_attribute('has_config_form');
            $this->files['auth.php']->set_attribute('has_process_config');
        }

        $recipefeatures = [
            'can_change_password',
            'can_edit_profile',
            'prevent_local_passwords',
            'is_synchronised_with_external',
            'can_reset_password',
            'can_signup',
            'can_confirm',
            'can_be_manually_set',
            'is_internal',
        ];

        foreach ($recipefeatures as $feature) {
            if ($this->has_component_feature($feature)) {
                $this->files['auth.php']->set_attribute('has_'.$feature);
            }
        }

        if (empty($this->recipe['auth_features']['description'])) {
            $this->logger->warning("Field 'description' not set");
        } else {
            $this->add_lang_string('auth_description', $this->recipe['auth_features']['description']);
        }
    }

    /**
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

            foreach ($this->recipe['atto_features']['strings_for_js'] as $langstring) {

                if (empty($langstring['id'])) {
                    $this->logger->warning('String id not set');
                    continue;
                }

                if (empty($langstring['text'])) {
                    $this->logger->warning("Text for string '".$langstring['id']."' not set");
                    continue;
                }

                $this->add_lang_string($langstring['id'], $langstring['text']);
            }
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
     * Prepare the files for a tiny plugin.
     */
    protected function prepare_tiny_files() {

        $pluginjsfile = 'amd/src/plugin.js';
        $this->prepare_file_skeleton($pluginjsfile, 'base', 'tiny/plugin_js');

        $commonjsfile = 'amd/src/common.js';
        $this->prepare_file_skeleton($commonjsfile, 'base', 'tiny/common_js');

        $pluginfofile = 'classes/plugininfo.php';
        $this->prepare_file_skeleton($pluginfofile, 'php_single_file', 'tiny/plugininfo_php');

        $commandsjsfile = 'amd/src/commands.js';
        $addcommandsfile = function() use ($commandsjsfile) {
            $this->prepare_file_skeleton($commandsjsfile, 'base', 'tiny/commands_js');
        };
        $configjsfile = 'amd/src/configuration.js';
        $addconfigfile = function() use ($configjsfile) {
            $this->prepare_file_skeleton($configjsfile, 'base', 'tiny/configuration_js');
        };

        $interfaces = [];

        if ($this->has_component_feature('options')) {
            $optionsjsfile = 'amd/src/options.js';
            $this->prepare_file_skeleton($optionsjsfile, 'base', 'tiny/options_js');

            $options = array_map(function($value) {
                $value['ucname'] = ucfirst($value['name']);

                return $value;
            }, $this->recipe['tiny_features']['options']);

            $options = array_filter($options, function($value): bool {
                return !empty($value['ucname']);
            });

            $this->files[$pluginfofile]->set_attribute('defines_options');
            $this->files[$pluginfofile]->set_attribute('options', $options);
            $this->files[$pluginjsfile]->set_attribute('defines_options');
            $this->files[$optionsjsfile]->set_attribute('defines_options');
            $this->files[$optionsjsfile]->set_attribute('options', $options);

            $interfaces[] = 'plugin_with_configuration';
        }

        $commandnames = [];
        if ($this->has_component_feature('buttons')) {
            if (empty($this->files[$commandsjsfile])) {
                $addcommandsfile();
                $addconfigfile();
            }
            $this->files[$pluginfofile]->set_attribute('defines_buttons');
            $this->files[$commandsjsfile]->set_attribute('defines_buttons');
            $this->files[$commonjsfile]->set_attribute('defines_buttons');
            $this->files[$configjsfile]->set_attribute('defines_buttons');

            $interfaces[] = 'plugin_with_buttons';

            // Determine the button configuration.
            $buttons = [];
            $buttonsbycategory = [];

            foreach ($this->recipe['tiny_features']['buttons'] as $button) {
                if (empty($button['name'])) {
                    $this->logger->warning('Button name not set');
                    continue;
                }

                if (empty($button['text'])) {
                    $this->logger->warning("Text for button '{$button['name']}' not set");
                    continue;
                }

                if (empty($button['category'])) {
                    $this->logger->warning("category  for button '{$button['name']}' not set");
                    continue;
                }

                $button['buttonName'] = "{$button['name']}ButtonName";
                $this->add_lang_string("button_{$button['name']}", $button['text']);
                $buttons[] = $button;

                if (empty($buttonsbycategory[$button['category']])) {
                    $buttonsbycategory[$button['category']] = [];
                }
                $buttonsbycategory[$button['category']][] = $button;
                $commandnames[] = [
                    'commandName' => $button['buttonName'],
                    'name' => $button['name'],
                ];

            }

            $this->files[$commandsjsfile]->set_attribute('buttons', $buttons);
            $this->files[$configjsfile]->set_attribute('buttons', $buttons);

            $buttoncategories = [];
            foreach ($buttonsbycategory as $category => $buttonlist) {
                $buttoncategories = [
                    'category' => $category,
                    'buttons' => array_map(function($button): string {
                        return $button['buttonName'];
                    }, $buttonlist),
                ];
            }
            $this->files[$configjsfile]->set_attribute('buttonsbycategory', $buttoncategories);
        }

        if ($this->has_component_feature('menuitems')) {
            if (empty($this->files[$commandsjsfile])) {
                $addcommandsfile();
                $addconfigfile();
            }
            $this->files[$commandsjsfile]->set_attribute('defines_menuitems');
            $this->files[$commonjsfile]->set_attribute('defines_menuitems');
            $this->files[$pluginfofile]->set_attribute('defines_menuitems');
            $this->files[$configjsfile]->set_attribute('defines_menuitems');

            $interfaces[] = 'plugin_with_menuitems';

            // Determine the menu configuration.
            $menuitems = [];
            $menuitemsbycategory = [];

            foreach ($this->recipe['tiny_features']['menuitems'] as $menuitem) {
                if (empty($menuitem['name'])) {
                    $this->logger->warning('Menu item name not set');
                    continue;
                }

                if (empty($menuitem['text'])) {
                    $this->logger->warning("Text for Menu item '{$menuitem['name']}' not set");
                    continue;
                }

                if (empty($menuitem['category'])) {
                    $this->logger->warning("category  for Menu item '{$menuitem['name']}' not set");
                    continue;
                }

                $menuitem['menuItemName'] = "{$menuitem['name']}MenuItemName";
                $this->add_lang_string("menuitem_{$menuitem['name']}", $menuitem['text']);
                $menuitems[] = $menuitem;

                if (empty($buttonsbycategory[$menuitem['category']])) {
                    $menuitemsbycategory[$menuitem['category']] = [];
                }
                $menuitemsbycategory[$menuitem['category']][] = $menuitem;
                $commandnames[] = [
                    'commandName' => $menuitem['menuItemName'],
                    'name' => $menuitem['name'],
                ];
            }

            $this->files[$commandsjsfile]->set_attribute('menuitems', $menuitems);
            $this->files[$configjsfile]->set_attribute('menuitems', $menuitems);

            $menuitemcategories = [];
            foreach ($menuitemsbycategory as $category => $menuitemlist) {
                $menuitemcategories = [
                    'category' => $category,
                    'menuitems' => array_map(function($menuitem): string {
                        return $menuitem['menuItemName'];
                    }, $menuitemlist),
                ];
            }
            $this->files[$configjsfile]->set_attribute('menuitemsbycategory', $menuitemcategories);
        }

        if ($this->has_component_feature('menuitems') || $this->has_component_feature('buttons')) {
            $this->files[$pluginjsfile]->set_attribute('defines_commands');
            $this->files[$commonjsfile]->set_attribute('defines_commands');
            $this->files[$configjsfile]->set_attribute('commandnames', $commandnames);
            $this->files[$commonjsfile]->set_attribute('commandnames', $commandnames);
            $this->files[$commandsjsfile]->set_attribute('commandnames', $commandnames);
        }

        if (!empty($interfaces)) {
            $this->files[$pluginfofile]->set_attribute('interfaces', implode(', ', $interfaces));
            $this->files[$pluginfofile]->set_attribute('defines_any_interfaces');
        }
    }

    /**
     * Prepares the files for an enrolment plugin.
     */
    protected function prepare_enrol_files() {

        $this->prepare_file_skeleton('lib.php', 'lib_php_file', 'enrol/lib');

        $enrolfeatures = [
            'allow_enrol',
            'allow_unenrol',
            'allow_manage',
            'allow_unenrol_user',
        ];

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
     * @param string $capabilityname
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
            // Capability block/<blockname>:addinstance is required.
            // Capability block/<blockname>:myaddinstance is also required if applicable format 'my' is set to true.
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

        $this->prepare_file_skeleton($this->recipe['component'].'.php', 'php_single_file', 'block/block', $blockrecipe);

        if ($this->has_component_feature('edit_form')) {
            $this->prepare_file_skeleton('edit_form.php', 'php_single_file', 'block/edit_form');
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
        $this->prepare_file_skeleton($backuptaskfile, 'php_single_file', 'block/backup/moodle2/backup_block_task_class')
            ->set_attribute('has_extra_requirements');

        if ($hassettingslib) {
            $settingslibfile = 'backup/moodle2/backup_'.$componentname.'_settingslib.php';
            $this->prepare_file_skeleton($settingslibfile, 'php_single_file', 'block/backup/moodle2/backup_settingslib');
            $this->files[$backuptaskfile]->set_attribute('has_settingslib');
        }

        if ($hasbackupstepslib) {
            $stepslibfile = 'backup/moodle2/backup_'.$componentname.'_stepslib.php';
            $this->prepare_file_skeleton($stepslibfile, 'php_single_file', 'block/backup/moodle2/backup_stepslib');
            $this->files[$backuptaskfile]->set_attribute('has_stepslib');
        }

        if ($this->has_component_feature('restore_task')) {
            $restoretaskfile = 'backup/moodle2/restore_'.$componentname.'_block_task.class.php';
            $this->prepare_file_skeleton($restoretaskfile, 'php_single_file', 'block/backup/moodle2/restore_block_task_class')
                ->set_attribute('has_extra_requirements');

            if ($hasrestorestepslib) {
                $stepslibfile = 'backup/moodle2/restore_'.$componentname.'_stepslib.php';
                $this->prepare_file_skeleton($stepslibfile, 'php_single_file', 'block/backup/moodle2/restore_stepslib');
                $this->files[$restoretaskfile]->set_attribute('has_stepslib');
            }
        }
    }

    /**
     * Prepares the files for a theme.
     */
    protected function prepare_theme_files() {

        $stringids = ['choosereadme'];
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

        $stringids = [
            'pluginnamesummary',
            'pluginnameadding',
            'pluginnameediting',
            'pluginname_help',
        ];

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
    protected function verify_strings_exist(array $stringids): void {

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
      * Verifies that the specified events are present in the recipe.
      *
      * @param string[] $eventnames Sequence of event names.
      */
    protected function verify_events_exist(array $eventnames): void {

        foreach ($eventnames as $eventname) {
            $exist = false;
            if ($this->has_common_feature('events')) {
                foreach ($this->recipe['events'] as $e) {
                    if ($e['eventname'] === $eventname) {
                        $exist = true;
                        break;
                    }
                }
            }

            if (!$exist) {
                $this->logger->warning("Event '$eventname' does not exist");
            }
        }
    }

    /**
     * Prepares the files for an activity module plugin.
     */
    protected function prepare_mod_files() {

        $componentname = $this->recipe['component_name'];

        $this->verify_capability_exists('addinstance');

        $this->verify_strings_exist([
            $componentname . 'name',
            $componentname . 'name_help',
            $componentname . 'settings',
            $componentname . 'fieldset',
            'missingidandcmid',
            'modulename',
            'modulename_help',
            'modulenameplural',
            'no' . $componentname . 'instances',
            'pluginadministration',
            'view',
        ]);

        $this->verify_events_exist([
            'course_module_instance_list_viewed',
            'course_module_viewed',
        ]);

        $this->prepare_file_skeleton('index.php', 'php_web_file', 'mod/index');
        $this->prepare_file_skeleton('view.php', 'view_php_file', 'mod/view');
        $this->prepare_file_skeleton('mod_form.php', 'php_internal_file', 'mod/mod_form');
        $this->prepare_file_skeleton('lib.php', 'lib_php_file', 'mod/lib');
        $this->prepare_file_skeleton('db/install.xml', 'db_install_xml_file', 'mod/db_install_xml');

        if ($this->has_component_feature('gradebook')) {

            $gradebookfunctions = [
                'scale_used',
                'scale_used_anywhere',
                'grade_item_update',
                'grade_item_delete',
                'update_grades',
            ];

            foreach ($gradebookfunctions as $gradebookfunction) {
                $this->files['lib.php']->set_attribute('has_'.$gradebookfunction);
            }

            $this->files['lib.php']->add_supported_feature('FEATURE_GRADE_HAS_GRADE');
            $this->prepare_file_skeleton('grade.php', 'php_web_file', 'mod/grade');

            $this->files['mod_form.php']->set_attribute('has_gradebook');
        }

        if ($this->has_component_feature('file_area')) {

            $fileareafunctions = [
                'get_file_areas',
                'get_file_info',
                'pluginfile',
            ];

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

    /**
     * Prepares the files for a content bank contenttype plugin.
     */
    protected function prepare_contenttype_files() {
        $recipe = $this->recipe;

        // General checks.
        if ($this->has_common_feature('capabilities')) {
            $this->verify_capability_exists('access');
        } else {
            // Capability contentytpe/<plugin>:access is required.
            $this->logger->warning('Capabilities not defined');
        }

        $this->prepare_file_skeleton('classes/contenttype.php', 'php_single_file', 'contenttype/classes/contenttype');
        $this->prepare_file_skeleton('classes/content.php', 'php_single_file', 'contenttype/classes/content');

        // Feature dependencies.
        if ($this->has_component_feature('can_edit')) {
            $this->verify_capability_exists('useeditor');
            $menuoptions = $recipe['contenttype_features']['can_edit']['add_menu'] ?? null;
            if (empty($menuoptions)) {
                $this->logger->warning('Missing add_menu options');
            } else {
                foreach ($menuoptions as $key => $value) {
                    $checks = ['name', 'icon'];
                    foreach ($checks as $check) {
                        if (empty($value[$check])) {
                            $this->logger->warning('Missing $check on the editor option $key');
                        }
                    }
                }
            }

            $this->prepare_file_skeleton('classes/form/editor.php', 'php_single_file', 'contenttype/classes/form/editor');
            $this->files['classes/contenttype.php']->set_attribute('has_can_edit');
        }
        if ($this->has_component_feature('can_upload')) {
            $this->verify_capability_exists('upload');
            $fileextensions = $recipe['contenttype_features']['can_upload']['file_extensions'] ?? null;
            if (empty($fileextensions)) {
                $this->logger->warning('Missing file extensions to upload');
            }
            $this->files['classes/contenttype.php']->set_attribute('has_can_upload');
        }
    }

    /**
     * Prepares the files for a course format plugin.
     */
    protected function prepare_format_files() {
        $recipe = $this->recipe;
        $formatname = str_replace('format_', '', $recipe['component_name'] ?? '');

        // All formats below Moodle 4.0 are considered legacy formats as the way formats are build
        // is not the same as in Moodle 3.x.
        $requiredversion = $recipe['requires'] ?? '0';
        if (version_compare($requiredversion, '4.0', '<')) {
            $this->logger->warning('Course format require Moodle 4.0. All extra course format features will be ignored.');
            $this->prepare_file_skeleton('format.php', 'php_single_file', 'format/legacy_format');
            $this->files['format.php']->set_attribute('formatname', $formatname);
            $this->prepare_file_skeleton('lib.php', 'php_single_file', 'format/legacy_lib');
            $this->files['lib.php']->set_attribute('formatname', $formatname);
            $this->prepare_file_skeleton('renderer.php', 'php_single_file', 'format/legacy_renderer');
            $this->files['renderer.php']->set_attribute('formatname', $formatname);
            return;
        }

        $this->prepare_file_skeleton('format.php', 'php_single_file', 'format/format');
        $this->prepare_file_skeleton('lib.php', 'php_single_file', 'format/lib');
        $this->files['lib.php']->set_attribute('formatname', $formatname);
        $this->prepare_file_skeleton('classes/output/renderer.php', 'php_single_file', 'format/classes/output/renderer');

        // Feature dependencies.
        if ($this->has_component_feature('basic_outputs')) {
            $this->prepare_file_skeleton(
                'templates/local/content.mustache',
                'base',
                'format/templates/local/content'
            );
            $this->prepare_file_skeleton(
                'templates/local/content/section.mustache',
                'base',
                'format/templates/local/content/section');
            $this->prepare_file_skeleton(
                'templates/local/content/section/cmitem.mustache',
                'base',
                'format/templates/local/content/section/cmitem'
            );
            $this->prepare_file_skeleton(
                'classes/output/courseformat/content.php',
                'php_single_file',
                'format/classes/output/courseformat/content'
            );
            $this->prepare_file_skeleton(
                'classes/output/courseformat/content/section.php',
                'php_single_file',
                'format/classes/output/courseformat/content/section'
            );
            $this->prepare_file_skeleton(
                'classes/output/courseformat/content/section/cmitem.php',
                'php_single_file',
                'format/classes/output/courseformat/content/section/cmitem'
            );
        }
    }

    /**
     * Prepares the skeleton files for the 'backup_moodle2' feature for an activity module.
     */
    protected function prepare_mod_backup_moodle2() {

        $componentname = $this->recipe['component_name'];
        $hassettingslib = $this->has_component_feature('settingslib');

        $this->prepare_file_skeleton('backup/moodle2/backup_'.$componentname.'_activity_task.class.php',
                                     'backup_activity_task_file', 'mod/backup/moodle2/backup_activity_task_class');
        if ($hassettingslib) {
            $this->files['backup/moodle2/backup_'.$componentname.'_activity_task.class.php']->set_attribute('has_settingslib');
        }

        $this->prepare_file_skeleton('backup/moodle2/backup_'.$componentname.'_stepslib.php', 'php_single_file',
                                     'mod/backup/moodle2/backup_stepslib');

        if ($hassettingslib) {
            $this->prepare_file_skeleton('backup/moodle2/backup_'.$componentname.'_settingslib.php', 'php_single_file',
                                         'mod/backup/moodle2/backup_settingslib');
        }

        $this->prepare_file_skeleton('backup/moodle2/restore_'.$componentname.'_activity_task.class.php', 'php_single_file',
            'mod/backup/moodle2/restore_activity_task_class')->set_attribute('has_extra_requirements');

        $this->prepare_file_skeleton('backup/moodle2/restore_'.$componentname.'_stepslib.php', 'php_single_file',
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

                if ($isinsidenamespace !== false && (strpos($observername, $this->recipe['component']) !== false)) {
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

    /**
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
            $this->prepare_file_skeleton($eventfile, 'php_single_file', 'classes_event_event', $eventrecipe);
        }
    }

    /**
     * Prepare the file skeletons for the cli_scripts feature.
     */
    protected function prepare_cli_files() {

        foreach ($this->recipe['cli_scripts'] as $script) {
            $this->prepare_file_skeleton('cli/'.$script['filename'].'.php', 'php_cli_file', 'cli');
        }
    }

    /**
     * Prepare the file skeletongs for mustache templates.
     *
     * @return void
     */
    protected function prepare_templates() {

        foreach ($this->recipe['templates'] as $template) {
            $file = $this->prepare_file_skeleton('templates/' . $template . '.mustache', 'base', 'mustache');
            $file->set_attribute('template_name', $template);
        }
    }

    /**
     * Registers a new file skeleton
     *
     * @param string $filename
     * @param string $skeltype
     * @param string $template
     * @param string[] $recipe Recipe to be used in generating the file instead of the global recipe.
     * @return \tool_pluginskel\local\skel\base subclass instance
     */
    protected function prepare_file_skeleton($filename, $skeltype, $template, $recipe = null): \tool_pluginskel\local\skel\base {

        if (strpos($template, 'file/') !== 0) {
            $template = 'file/'.$template;
        }

        $this->logger->debug('Preparing file skeleton:',
                             ['filename' => $filename, 'skeltype' => $skeltype, 'template' => $template]);

        if (isset($this->files[$filename])) {
            throw new exception('The file has already been initialised: '.$filename);
        }

        $skelclass = '\\tool_pluginskel\\local\\skel\\'.$skeltype;

        $skel = new $skelclass();
        $skel->set_template($template);
        $skel->set_manager($this);
        $skel->set_logger($this->logger);

        if (is_null($recipe)) {
            // Skeleton will have access to the whole recipe.
            $data = $this->recipe;
        } else {
            $data = $recipe;
        }

        // Populate some additional properties.
        $data['self']['filename'] = $filename;
        $data['self']['relpath'] = $data['component_root'].'/'.$data['component_name'].'/'.$filename;
        $depth = substr_count($data['self']['relpath'], '/');
        $data['self']['pathtoconfig'] = "__DIR__.'/".str_repeat('../', $depth - 1)."config.php'";
        $data['component_type_is_'.$data['component_type']] = true;

        $skel->set_data($data);

        $this->files[$filename] = $skel;

        return $skel;
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

        $attofeatures = [
            'can_change_password',
            'can_edit_profile',
            'prevent_local_passwords',
            'is_synchronised_with_external',
            'can_reset_password',
            'can_signup',
            'can_confirm',
            'can_be_manually_set',
            'is_internal',
        ];

        foreach ($attofeatures as $attofeature) {
            if ($attofeature === $feature) {
                return isset($this->recipe[$componentfeatures][$feature]);
            }
        }

        $enrolfeatures = [
            'allow_enrol',
            'allow_unenrol',
            'allow_manage',
            'allow_unenrol_user',
        ];

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

        if ($feature === 'privacy') {
            return isset($this->recipe['privacy']['haspersonaldata']);
        }

        if ($feature === 'external') {
            return !empty($this->recipe['external']);
        }

        if ($feature === 'templates') {
            return !empty($this->recipe['templates']);
        }

        if ($feature === 'services') {
            return !empty($this->recipe['services']);
        }

        // Having the upgradelib feature automatically enables the upgrade feature.
        if ($feature === 'upgrade' && $this->has_common_feature('upgradelib')) {
            return true;
        }

        return !empty($this->recipe['features'][$feature]);
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

        if (!empty($this->recipe['component_type']) && $this->recipe['component_type'] !== $type) {
            throw new exception('Component type mismatch');
        }

        if (!empty($this->recipe['component_name']) && $this->recipe['component_name'] !== $name) {
            throw new exception('Component name mismatch');
        }

        $plugintypes = core_component::get_plugin_types();

        if (empty($plugintypes[$type])) {
            throw new exception('Unknown plugin type: '.$type);
        }

        $root = substr($plugintypes[$type], strlen($CFG->dirroot));

        if (!empty($this->recipe['component_root']) && $this->recipe['component_root'] !== $root) {
            throw new exception('Component type root location mismatch');
        }

        $this->recipe['component_type'] = $type;
        $this->recipe['component_name'] = $name;
        $this->recipe['component_root'] = $root;
    }

    /**
     * Prepare the mustache engine instance
     *
     * @param string $mustachedir Path to the base directory containing the mustache skeleton templates
     */
    protected function init_templating_engine($mustachedir = null) {
        $loader = null;
        if ($mustachedir) {
            $loader = new \Mustache_Loader_FilesystemLoader($mustachedir);
        }
        $this->mustache = new mustache(['logger' => $this->logger, 'loader' => $loader]);
    }
}
