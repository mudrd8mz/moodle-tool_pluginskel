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
 * File containing tests for generating a Tiny plugin type.
 *
 * @package     tool_pluginskel
 * @copyright   2022 Andrew Lyons <andrew@nicols.co.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_pluginskel;

use Monolog\Logger;
use Monolog\Handler\NullHandler;
use tool_pluginskel\local\util\manager;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/setuplib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/pluginskel/vendor/autoload.php');

/**
 * Tiny test class.
 *
 * @package     tool_pluginskel
 * @copyright   2022 Andrew Lyons <andrew@nicols.co.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tiny_test extends \advanced_testcase {

    /**
     * Get test based on settings.
     *
     * @param array $settings
     * @return array
     */
    protected function get_test(array $settings = []): array {
        $settings = array_merge([
            'general' => true,
            'options' => false,
            'anyinterface' => false,
            'commands' => false,
            'buttons' => false,
            'menuitems' => false,
        ], $settings);

        if ($settings['buttons'] || $settings['menuitems']) {
            $settings['commands'] = true;
            $settings['anyinterface'] = true;
        }

        if ($settings['options']) {
            $settings['anyinterface'] = true;
        }

        $base = [
            'amd/src/plugin.js' => [
                'options' => [
                    preg_quote("import {register as registerOptions} from './options';", "/"),
                    preg_quote("registerOptions", "/"),
                ],

                'commands' => [
                    preg_quote("import {getSetup as getCommandSetup} from './commands';", "/"),
                    // This covers both setupCommands, and setupCommand.register.
                    preg_quote("setupCommands", "/"),
                    preg_quote("getCommandSetup(),", "/"),
                ],
            ],
            'amd/src/common.js' => [
                'general' => [
                    preg_quote("const component = 'tiny_test';", "/"),
                    preg_quote("  component,", "/"),
                    // phpcs:ignore
                    preg_quote('  pluginName: `${component}/plugin`,', "/"),
                ],
                'buttons' => [
                    preg_quote('ButtonName', "/"),
                    preg_quote('icon:', "/"),
                ],
                'menuitems' => [
                    preg_quote('MenuItemName', "/"),
                ],
            ],
            'classes/plugininfo.php' => [
                'general' => [
                    preg_quote('namespace tiny_test;', '/'),
                    preg_quote('class plugininfo extends plugin', '/') ,
                ],
                'options' => [
                    preg_quote('plugin_with_configuration', '/'),
                    preg_quote('get_plugin_configuration_for_context', '/'),
                ],
                'buttons' => [
                    preg_quote('plugin_with_buttons', '/'),
                    preg_quote('get_available_buttons', '/'),
                ],
                'menuitems' => [
                    preg_quote('plugin_with_menuitems', '/'),
                    preg_quote('get_available_menuitems', '/'),
                ],
                'anyinterface' => [
                    preg_quote('implements', '/'),
                ],
            ],
        ];

        $result = [];
        foreach ($base as $file => $groups) {
            $result[$file] = [];
            foreach ($groups as $group => $tests) {
                foreach ($tests as $test) {
                    $result[$file][$test] = $settings[$group];
                }
            }
        }

        return $result;
    }

    /**
     * Get recipe based on settings.
     *
     * @param array $settings
     * @return array
     */
    protected function get_recipe(array $settings = []) {
        $settings = array_merge([
            'options' => [],
            'buttons' => false,
            'menuitems' => false,
        ], $settings);

        $recipe = [
            'component' => 'tiny_test',
            'name'      => 'Example plugin',
            'copyright' => '2022 Andrew Lyons <andrew@nicols.co.uk>',
            'features'  => [],
            'tiny_features' => [],
        ];

        if ($settings['buttons']) {
            $recipe['tiny_features']['buttons'] = [
                [
                    'name' => 'mybutton',
                    'text' => 'My button',
                    'category' => 'format',
                ],
            ];
        }

        if ($settings['menuitems']) {
            $recipe['tiny_features']['menuitems'] = [
                [
                    'name' => 'mymenuitem',
                    'text' => 'My Menu Item',
                    'category' => 'file',
                ],
            ];
        }

        if ($settings['options']) {
            $recipe['tiny_features']['options'] = $settings['options'];
        }

        return $recipe;
    }

    /**
     * Get list of expected files.
     *
     * @param array $include
     * @return array
     */
    protected function get_expected_files(array $include = []): array {
        $include = array_merge([
            'general' => true,
            'options' => false,
            'commands' => false,
            'buttons' => false,
            'menuitems' => false,
        ], $include);

        if ($include['buttons'] || $include['menuitems']) {
            $include['commands'] = true;
        }

        $groups = [
            'general' => [
                'amd/src/plugin.js',
                'amd/src/common.js',
                'classes/plugininfo.php',
                'version.php',
                'lang/en/tiny_test.php',
            ],
            'options' => [
                'amd/src/options.js',
            ],
            'commands' => [
                'amd/src/commands.js',
            ],
        ];

        $files = [];
        foreach ($groups as $groupname => $groupfiles) {
            $expected = !empty($include[$groupname]);
            foreach ($groupfiles as $file) {
                $files[$file] = $expected;
            }
        }

        return $files;
    }

    /**
     * Recipes data provider.
     *
     * @return array
     */
    public function recipe_provider(): array {
        return [
            'base' => [
                'recipe' => $this->get_recipe(),
                'files' => $this->get_expected_files(),
                'contentchecks' => $this->get_test(),
            ],
            'buttons' => [
                'recipe' => $this->get_recipe([
                    'buttons' => true,
                ]),
                'files' => $this->get_expected_files([
                    'buttons' => true,
                ]),
                'contentchecks' => $this->get_test([
                    'buttons' => true,
                ]),
            ],
            'menuitems' => [
                'recipe' => $this->get_recipe([
                    'menuitems' => true,
                ]),
                'files' => $this->get_expected_files([
                    'menuitems' => true,

                ]),
                'contentchecks' => $this->get_test([
                    'menuitems' => true,

                ]),
            ],
            'options' => [
                'recipe' => $this->get_recipe([
                    'options' => [[
                        'name' => 'draftItemId',
                        'type' => 'string',
                    ]],
                ]),
                'files' => $this->get_expected_files([
                    'options' => true,
                ]),
                'contentchecks' => $this->get_test([
                    'options' => true,
                ]),
            ],
        ];
    }


    /**
     * Test the generation of a Tiny plugin with a variety of recipes.
     *
     * @dataProvider recipe_provider
     * @param array $recipe The recipe to test
     * @param array $files A list of files and whether they should be generated
     */
    public function test_recipes(array $recipe, array $files, array $contentchecks): void {
        $logger = new Logger(get_class($this));
        $logger->pushHandler(new NullHandler());
        $manager = manager::instance($logger);

        // Make the recipe.
        $manager->load_recipe($recipe);
        $manager->make();

        // Check the files that were created match the expected files.
        $generatedfiles = $manager->get_files_content();
        foreach ($files as $file => $exists) {
            if ($exists) {
                $this->assertArrayHasKey($file, $generatedfiles);
            } else {
                $this->assertArrayNotHasKey($file, $generatedfiles);
            }
        }

        foreach ($generatedfiles as $file => $filecontent) {
            if (!isset($contentchecks[$file])) {
                continue;
            }
            $checks = $contentchecks[$file];
            foreach ($checks as $regex => $expected) {
                if ($expected) {
                    $this->assertMatchesRegularExpression("/{$regex}/", $filecontent);
                } else {
                    $this->assertDoesNotMatchRegularExpression("/{$regex}/", $filecontent);
                }
            }
        }
    }
}
