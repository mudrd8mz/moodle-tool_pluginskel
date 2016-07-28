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
 * File containing the step 1 form.
 *
 * @package     tool_pluginskel
 * @subpackage  util
 * @copyright   2016 Alexandru Elisei <alexandru.elisei@gmail.com>, David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Step 1 form.
 *
 * @package     tool_pluginskel
 * @copyright   2016 Alexandru Elisei <alexandru.elisei@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginskel_step1_form extends moodleform {

    /**
     * The standard form definiton.
     */
    public function definition () {
        $mform =& $this->_form;

        $recipe = $this->_customdata['recipe'];
        $component = $recipe['component'];
        $templatevars = tool_pluginskel\local\util\manager::get_component_variables($component);
        $features = tool_pluginskel\local\util\manager::get_features_variables();

        $mform->addElement('header', 'pluginhdr', get_string('pluginhdr', 'tool_pluginskel'));
        $mform->setExpanded('pluginhdr', true);

        foreach ($templatevars as $variable) {

            // The default element for a template variable is a text input.
            if (empty($variable['hint']) || $variable['hint'] == 'text') {
                $this->add_text($variable, $recipe);
                continue;
            }

            // Template variables that are arrays will be added at the bottom of the page.
            if ($variable['hint'] == 'multiple') {
                continue;
            }

            if ($variable['hint'] == 'boolean') {
                $this->add_advcheckbox($variable, $recipe, true);
            }

            if (!empty($variable['values'])) {
                $this->add_select($variable, $recipe);
            }
        }

        foreach ($features as $variable) {

            // The default element for a template variable is a text input.
            if (empty($variable['hint']) || $variable['hint'] == 'text') {
                $this->add_text($variable, $recipe);
                continue;
            }

            // Template variables that are arrays will be added at the bottom of the page.
            if ($variable['hint'] == 'multiple') {
                continue;
            }

            if ($variable['hint'] == 'boolean') {
                $this->add_advcheckbox($variable, $recipe, true);
            }

            if (!empty($variable['values'])) {
                $this->add_select($variable, $recipe);
            }
        }

        // Adding required array variables first because the fieldsets will be expanded.
        foreach ($templatevars as $variable) {
            if (!empty($variable['hint']) && $variable['hint'] == 'multiple' && !empty($variable['required'])) {
                $this->add_fieldset($variable, $recipe);
            }
        }

        // Adding optional array variables last because the fieldsets will not be expanded.
        foreach ($templatevars as $variable) {
            if (!empty($variable['hint']) && $variable['hint'] == 'multiple' && empty($variable['required'])) {
                $this->add_fieldset($variable, $recipe);
            }
        }

        $mform->getElement('component')->setValue($component);

        $mform->addElement('html', '<hr>');

        $buttonarr = array();
        $buttonarr[] =& $mform->createElement('submit', 'buttongenerate', get_string('generate', 'tool_pluginskel'));
        $buttonarr[] =& $mform->createElement('submit', 'buttonsaverecipe', get_string('saverecipe', 'tool_pluginskel'));
        $mform->addGroup($buttonarr, 'buttonarr', '', array(' '), false);
        $mform->closeHeaderBefore('buttonarr');

        $mform->addElement('hidden', 'step', '1');
        $mform->setType('step', PARAM_INT);

        $mform->addElement('hidden', 'component1', $component);
        $mform->setType('component1', PARAM_TEXT);
    }

    /**
     * Adds a select element to the form.
     *
     * @param string[] $templatevar The template variable
     * @param string[] $recipe The recipe.
     */
    protected function add_select($templatevar, $recipe) {

        $mform =& $this->_form;
        $selectname = $templatevar['name'];
        $selectvalues = $templatevar['values'];

        $mform->addElement('select', $selectname, get_string('skel'.$selectname, 'tool_pluginskel'), $selectvalues);

        if (!empty($recipe[$selectname]) && !empty($selectvalues[$recipe[$selectname]])) {
            $mform->getElement($selectname)->setSelected($recipe[$selectname]);
        }

        if (!empty($templatevar['required'])) {
            $mform->addRule($selectname, null, 'required', null, 'client', false, true);
        }
    }

    /**
     * Adds an advcheckbox element to the form.
     *
     * @param string[] $templatevar The template variable
     * @param string[] $recipe The recipe.
     * @param bool $isfeature If the variable is part of 'features'.
     */
    protected function add_advcheckbox($templatevar, $recipe, $isfeature = false) {

        $mform =& $this->_form;
        $varname = $templatevar['name'];
        $elementname = $isfeature ? 'features['.$templatevar['name'].']' : $varname;
        $values = array('false', 'true');

        $mform->addElement('advcheckbox', $elementname, get_string('skel'.$varname, 'tool_pluginskel'), '', null, $values);

        if (!empty($recipe[$varname]) && !empty($values[$recipe[$varname]])) {
            $mform->getElement($elementname)->setChecked(true);
        }

        if ($isfeature && !empty($recipe['features'][$varname])) {
            $mform->getElement($elementname)->setChecked(true);
        }

        if (!empty($templatevar['required'])) {
            $mform->addRule($elementname, null, 'required', null, 'client', false, true);
        }
    }

    /**
     * Adds a text element to the form.
     *
     * @param string[] $templatevar The template variable
     * @param string[] $recipe The recipe.
     */
    protected function add_text($templatevar, $recipe) {

        $mform =& $this->_form;
        $textname = $templatevar['name'];

        $mform->addElement('text', $textname, get_string('skel'.$textname, 'tool_pluginskel'));

        if (!empty($templatevar['hint']) && $templatevar['hint'] == 'int') {
            $mform->setType($textname, PARAM_INT);
        } else {
            $mform->setType($textname, PARAM_TEXT);
        }

        if (!empty($recipe[$textname])) {
            $mform->getElement($textname)->setValue($recipe[$textname]);
        }

        if (!empty($templatevar['required'])) {
            $mform->addRule($textname, null, 'required', null, 'client', false, true);
        }
    }

    /**
     * Adds a fieldset element to the form.
     *
     * @param string[] $templatevar The template variable
     * @param string[] $recipe The recipe.
     */
    protected function add_fieldset($templatevar, $recipe) {

        $mform =& $this->_form;
        $name = $templatevar['name'];
        $elements = $templatevar['values'];

        if (empty($this->_customdata[$name.'count'])) {
            // Create only one entry in the fieldset if more haven't been specified.
            $count = 1;
        } else {
            $count = (int) $this->_customdata[$name.'count'];
        }

        $recipevalues = array();
        // Keeping only the recipe values that are part of the template variable.
        if (!empty($recipe[$name]) && is_array($recipe[$name])) {
            foreach ($recipe[$name] as $recipevalue) {

                $currentvalue = array();
                foreach ($elements as $element) {
                    if (isset($recipevalue[$element])) {
                        $currentvalue[$element] = $recipevalue[$element];
                    }
                }

                if (!empty($currentvalue)) {
                    $recipevalues[] = $currentvalue;
                }
            }
        }

        $mform->addElement('header', $name, get_string('skel'.$name, 'tool_pluginskel'));

        if (!empty($templatevar['required']) || !empty($recipevalues)) {
            $mform->setExpanded($name, true);
        } else {
            $mform->setExpanded($name, false);
        }

        if (!empty($recipevalues)) {
            foreach ($recipevalues as $index => $value) {
                foreach ($elements as $element) {
                    $elementname = $name.'['.$index.']['.$element.']';
                    $mform->addElement('text', $elementname, get_string('skel'.$element, 'tool_pluginskel'));
                    $mform->setType($elementname, PARAM_TEXT);

                    if (!empty($value[$element])) {
                        $mform->getElement($elementname)->setValue($value[$element]);
                    }
                }
            }
        }

        $currentcount = count($recipevalues);

        while ($currentcount < $count) {
            foreach ($elements as $element) {
                $elementname = $name.'['.$currentcount.']['.$element.']';
                $mform->addElement('text', $elementname, get_string('skel'.$element, 'tool_pluginskel'));
                $mform->setType($elementname, PARAM_TEXT);
            }
            $currentcount = $currentcount + 1;
        }

        $mform->addElement('button', 'addmore_'.$name, get_string('addmore', 'tool_pluginskel'));

        $mform->addElement('hidden', $name.'count', $count);
        $mform->setType($name.'count', PARAM_INT);
    }

}
