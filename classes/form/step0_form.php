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
 * File containing the general information form.
 *
 * @package     tool_pluginskel
 * @subpackage  util
 * @copyright   2016 Alexandru Elisei <alexandru.elisei@gmail.com>, David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_pluginskel\form;

require_once($CFG->libdir . '/formslib.php');

use core_component;
use moodleform;


/**
 * General information form.
 *
 * @package     tool_pluginskel
 * @copyright   2016 Alexandru Elisei <alexandru.elisei@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class step0_form extends moodleform
{
    var $examples_text = [];

    /**
     * The standard form definiton.
     */
    public function definition()
    {
        global $PAGE;
        $mform = $this->_form;

        $mform->addElement('header', 'manualhdr', get_string('manualhdr', 'tool_pluginskel'));
        $mform->setExpanded('manualhdr', true);

        $plugintypes = \tool_pluginskel\local\util\manager::get_plugintype_names();
        \core_collator::asort($plugintypes);
        $mform->addElement('select', 'componenttype', get_string('componenttype', 'tool_pluginskel'), $plugintypes);
        $mform->addHelpButton('componenttype', 'componenttype', 'tool_pluginskel');

        $mform->addElement('text', 'componentname', get_string('componentname', 'tool_pluginskel'), null);
        $mform->setType('componentname', PARAM_PLUGIN);
        $mform->addHelpButton('componentname', 'componentname', 'tool_pluginskel');

        $mform->addElement('submit', 'proceedmanually', get_string('proceedmanually', 'tool_pluginskel'));

        $mform->addElement('header', 'recipefilehdr', get_string('recipefilehdr', 'tool_pluginskel'));
        $mform->setExpanded('recipefilehdr', true);
        $mform->addElement('filepicker', 'recipefile', get_string('recipefile', 'tool_pluginskel'),
            null, array('maxbytes' => 50000, 'accepted_types' => '*'));
        $mform->addHelpButton('recipefile', 'recipefile', 'tool_pluginskel');
        $mform->addElement('submit', 'proceedrecipefile', get_string('proceedrecipefile', 'tool_pluginskel'));

        $mform->addElement('header', 'recipehdr', get_string('recipehdr', 'tool_pluginskel'));
        $mform->setExpanded('recipehdr', true);

        $examples = $this->get_examples($plugintypes);
        $mform->addElement('select', 'example_options', get_string('examples_recipe', 'tool_pluginskel'),
            $examples);

        $mform->addElement('textarea', 'recipe', get_string('recipe', 'tool_pluginskel'),
            array('wrap' => 'virtual', 'rows' => '20', 'cols' => '50'));
        $mform->addHelpButton('recipe', 'recipe', 'tool_pluginskel');
        $mform->addElement('submit', 'proceedrecipe', get_string('proceedrecipe', 'tool_pluginskel'));

        $mform->addElement('hidden', 'step', 0);
        $mform->setType('step', PARAM_INT);

        $PAGE->requires->js_call_amd("tool_pluginskel/example_recipe", "init", [$this->examples_text]);
    }

    /**
     * Validate the input.
     *
     * @param array $data Submitted form data (string) element name => (mixed) value
     * @param array $files Uploaded files (string) element name => (string) temporary file path
     * @return array Validation errors (string) element name => (string) validation error
     * @throws \coding_exception
     */
    public function validation($data, $files)
    {

        $errors = [];

        if (!empty($data['proceedmanually'])) {
            // The default clean_param() does not take plugin type into account.
            if (!core_component::is_valid_plugin_name($data['componenttype'], $data['componentname'])) {
                $errors['componentname'] = get_string('componentnameinvalid', 'tool_pluginskel');
            }
        }

        return $errors;
    }

    /**
     * Get examples
     *
     * @param array $plugintypes
     * @return array
     * @throws \coding_exception
     */
    private function get_examples(array $plugintypes)
    {
        $path = __DIR__ . '/../../recipes/';

        $recipe_names = get_directory_list($path, '', false, true);
        $recipe_names = array_map(function ($file) {
            return pathinfo($file, PATHINFO_FILENAME);
        }, $recipe_names);

        $elemnts = [];
        $elemnts[''] = get_string('none', 'tool_pluginskel');
        foreach ($plugintypes as $key => $name) {
            if (in_array($key, $recipe_names)) {
                $file_path = __DIR__ . "/../../recipes/" . $key . ".yaml";
                $elemnts[$key] = $name;
                $this->examples_text[$key] = file_get_contents($file_path);
            }
        }

        return $elemnts;
    }
}
