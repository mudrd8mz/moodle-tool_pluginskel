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
            if (empty($variable['hint']) || $variable['hint'] == 'text' || $variable['hint'] == 'int') {
                $this->add_text($variable, $recipe);
                continue;
            }

            // Template variables that are arrays will be added at the bottom of the page.
            if ($variable['hint'] == 'array') {
                continue;
            }

            if ($variable['hint'] == 'boolean') {
                $this->add_advcheckbox($variable, $recipe);
            }

            if ($variable['hint'] == 'multiple-options') {
                $this->add_select($variable, $recipe);
            }
        }

        foreach ($features as $variable) {

            if (empty($variable['hint']) || $variable['hint'] == 'text' || $variable['hint'] == 'int') {
                $this->add_text($variable, $recipe);
                continue;
            }

            // Template variables that are arrays will be added at the bottom of the page.
            if ($variable['hint'] == 'array') {
                continue;
            }

            if ($variable['hint'] == 'boolean') {
                // Features that are only true or false are in the 'features' part of the recipe.
                $eltformname = 'features['.$variable['name'].']';
                $features = empty($recipe['features']) ? array() : $recipe['features'];

                $this->add_advcheckbox($variable, $features, $eltformname);
            }

            if ($variable['hint'] == 'multiple-options') {
                $this->add_select($variable, $recipe);
            }
        }

        // Adding required array variables first because the fieldsets will be expanded.
        foreach ($templatevars as $variable) {
            if (!empty($variable['hint']) && $variable['hint'] == 'array' && !empty($variable['required'])) {
                $this->add_fieldset($variable, $recipe);
            }
        }

        // Adding optional array variables last because the fieldsets will not be expanded.
        foreach ($templatevars as $variable) {
            if (!empty($variable['hint']) && $variable['hint'] == 'array' && empty($variable['required'])) {
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
     * @param string $eltformname Element form name, used when adding the text element as part of an array.
     */
    protected function add_select($templatevar, $recipe, $eltformname = null) {

        $mform =& $this->_form;
        $variablename = $templatevar['name'];
        $selectname = empty($eltformname) ? $variablename : $eltformname;
        $selectvalues = $templatevar['values'];

        $mform->addElement('select', $selectname, get_string('skel'.$variablename, 'tool_pluginskel'), $selectvalues);

        if (!empty($recipe[$variablename]) && !empty($selectvalues[$recipe[$variablename]])) {
            $mform->getElement($selectname)->setSelected($recipe[$variablename]);
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
     * @param string $eltformname Element form name, used when adding the text element as part of an array.
     */
    protected function add_advcheckbox($templatevar, $recipe, $eltforname = null) {

        $mform =& $this->_form;
        $variablename = $templatevar['name'];
        $elementname = empty($eltformname) ? $variablename : $eltformname;
        $values = array('false', 'true');

        $mform->addElement('advcheckbox', $elementname, get_string('skel'.$variablename, 'tool_pluginskel'), '', null, $values);

        if (!empty($recipe[$variablename]) && !empty($values[$recipe[$variablename]])) {
            $mform->getElement($elementname)->setChecked(true);
        }

        if (!empty($recipe[$variablename])) {
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
     * @param string $eltformname Text element form name, used when adding the text element as part of an array.
     */
    protected function add_text($templatevar, $recipe, $eltformname = null) {

        $mform =& $this->_form;
        $textname = empty($eltformname) ? $templatevar['name'] : $eltformname;
        $variablename = $templatevar['name'];

        $mform->addElement('text', $textname, get_string('skel'.$variablename, 'tool_pluginskel'));

        if (!empty($templatevar['hint']) && $templatevar['hint'] == 'int') {
            $mform->setType($textname, PARAM_INT);
        } else {
            $mform->setType($textname, PARAM_TEXT);
        }

        if (!empty($recipe[$variablename])) {
            $mform->getElement($textname)->setValue($recipe[$variablename]);
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

        // Keeping only the recipe values that are part of the template variable.
        $recipevalues = array();
        if (!empty($recipe[$name]) && is_array($recipe[$name])) {
            foreach ($recipe[$name] as $recipevalue) {
                $currentvalue = array();
                foreach ($elements as $element) {
                    $elementname = $element['name'];
                    if (isset($recipevalue[$elementname])) {
                        $currentvalue[$elementname] = $recipevalue[$elementname];
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
                    if (empty($element['hint']) || $element['hint'] == 'text' || $element['hint'] == 'int') {
                        $eltformname = $name.'['.$index.']['.$element['name'].']';
                        $this->add_text($element, $value, $eltformname);
                    }
                }

                // Add a newline between array values.
                if ($index < count($recipevalues) - 1) {
                    $mform->addElement('html', '<br/>');
                }
            }
        }

        $currentcount = count($recipevalues);

        while ($currentcount < $count) {
            foreach ($elements as $element) {
                $eltformname = $name.'['.$currentcount.']['.$element['name'].']';
                $this->add_text($element, array(), $eltformname);
            }

            $currentcount = $currentcount + 1;

            // Add a newline between arrays.
            if ($currentcount < $count) {
                $mform->addElement('html', '<br/>');
            }
        }

        $mform->addElement('button', 'addmore_'.$name, get_string('addmore', 'tool_pluginskel'));

        $mform->addElement('hidden', $name.'count', $count);
        $mform->setType($name.'count', PARAM_INT);
    }

}
