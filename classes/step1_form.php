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
        $componentvars = tool_pluginskel\local\util\manager::get_component_variables($component);
        $featuresvars = tool_pluginskel\local\util\manager::get_features_variables();

        $mform->addElement('header', 'pluginhdr', get_string('pluginhdr', 'tool_pluginskel'));
        $mform->setExpanded('pluginhdr', true);

        foreach ($componentvars as $variable) {

            $hint = $variable['hint'];
            $elementname = $variable['name'];

            // Template variables that are arrays will be added at the bottom of the page.
            if ($hint == 'array') {
                continue;
            }

            if ($hint == 'text' || $hint == 'int') {
                $this->add_text_element($elementname, $variable, $recipe);
            }

            if ($hint == 'boolean') {
                $this->add_advcheckbox_element($elementname, $variable, $recipe);
            }

            if ($hint == 'multiple-options') {
                $this->add_select_element($elementname, $variable, $recipe);
            }
        }

        foreach ($featuresvars as $variable) {

            $hint = $variable['hint'];

            // Array variables will be added at the bottom of the page.
            if ($hint == 'array') {
                continue;
            }

            // Features that are not arrays are always under 'features' in the recipe.
            $elementname = 'features['.$variable['name'].']';
            $recipefeatures = empty($recipe['features']) ? array() : $recipe['features'];

            if ($hint == 'text' || $hint == 'int') {
                $this->add_text_element($elementname, $variable, $recipefeatures);
            }

            if ($hint == 'boolean') {
                $this->add_advcheckbox_element($elementname, $variable, $recipefeatures);
            }

            if ($hint == 'multiple-options') {
                $this->add_select_element($elementname, $variable, $recipefeatures);
            }
        }

        // Adding required array variables first because the fieldsets will be expanded.
        foreach ($componentvars as $variable) {

            $hint = $variable['hint'];

            if ($hint == 'array' && !empty($variable['required'])) {
                $this->add_fieldset($variable, $recipe);
            }
        }

        // Adding optional array variables last because the fieldsets will not be expanded.
        foreach ($componentvars as $variable) {

            $hint = $variable['hint'];

            if ($hint == 'array' && empty($variable['required'])) {
                $this->add_fieldset($variable, $recipe);
            }
        }

        // Adding array features.
        foreach ($featuresvars as $variable) {

            $hint = $variable['hint'];

            if ($hint == 'array') {
                $this->add_fieldset($variable, $recipe);
            }
        }

        $mform->getElement('component')->setValue($component);

        $mform->addElement('html', '<hr>');

        $buttonarr = array();
        $buttonarr[] =& $mform->createElement('submit', 'buttondownloadskel', get_string('downloadskel', 'tool_pluginskel'));
        $buttonarr[] =& $mform->createElement('submit', 'buttondownloadrecipe', get_string('downloadrecipe', 'tool_pluginskel'));
        $buttonarr[] =& $mform->createElement('submit', 'buttonviewrecipe', get_string('viewrecipe', 'tool_pluginskel'));
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
     * @param string $elementname Element form name.
     * @param string[] $templatevar The template variable
     * @param string[] $recipe The recipe.
     */
    protected function add_select_element($elementname, $templatevar, $recipe) {

        $mform =& $this->_form;
        $variablename = $templatevar['name'];
        $selectvalues = $templatevar['values'];

        // Adding 'none' option to select element for optional template variable.
        if (empty($templatevar['required'])) {
            $none = array('none' => get_string('none', 'tool_pluginskel'));
            $selectvalues = array_merge($none, $selectvalues);
        }

        $mform->addElement('select', $elementname, get_string('skel'.$variablename, 'tool_pluginskel'), $selectvalues);

        if (!empty($recipe[$variablename]) && !empty($selectvalues[$recipe[$variablename]])) {
            $mform->getElement($elementname)->setSelected($recipe[$variablename]);
        }
    }

    /**
     * Adds an advcheckbox element to the form.
     *
     * @param string $elementname Element form name.
     * @param string[] $templatevar The template variable
     * @param string[] $recipe The recipe.
     */
    protected function add_advcheckbox_element($elementname, $templatevar, $recipe) {

        $mform =& $this->_form;
        $variablename = $templatevar['name'];
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
     * @param string $elementname Element form name.
     * @param string[] $templatevar The template variable
     * @param string[] $recipe The recipe.
     */
    protected function add_text_element($elementname, $templatevar, $recipe) {

        $mform =& $this->_form;
        $variablename = $templatevar['name'];

        $mform->addElement('text', $elementname, get_string('skel'.$variablename, 'tool_pluginskel'));

        if (!empty($templatevar['hint']) && $templatevar['hint'] == 'int') {
            $mform->setType($elementname, PARAM_INT);
        } else {
            $mform->setType($elementname, PARAM_RAW);
        }

        if (!empty($recipe[$variablename])) {
            $mform->getElement($elementname)->setValue($recipe[$variablename]);
        }

        if (!empty($templatevar['required'])) {
            $mform->addRule($elementname, null, 'required', null, 'client', false, true);
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
                        $elementname= $name.'['.$index.']['.$element['name'].']';
                        $this->add_text_element($elementname, $element, $value);
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
                $elementname = $name.'['.$currentcount.']['.$element['name'].']';
                $this->add_text_element($elementname, $element, array());
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

    /**
     * Constructs the recipe from the form data.
     *
     * @return string[] The recipe.
     */
    public function get_recipe() {

        $recipe = array();

        $formdata = (array) $this->get_data();
        $component = $this->_customdata['recipe']['component'];

        $componentvars = tool_pluginskel\local\util\manager::get_component_variables($component);
        $featuresvars = tool_pluginskel\local\util\manager::get_features_variables($component);

        foreach ($componentvars as $variable) {

            $variablename = $variable['name'];
            $hint = $variable['hint'];

            if (!empty($formdata[$variablename])) {
                if ($hint == 'array') {

                    $value = $this->get_array_variable($formdata[$variablename]);
                    if (!empty($value)) {
                        $recipe[$variablename] = $value;
                    }
                } else if ($hint === 'multiple-options') {

                    // Ignoring 'none' select options.
                    if ($formdata[$variablename] !== 'none') {
                        $recipe[$variablename] = $formdata[$variablename];
                    }
                } else {
                    $recipe[$variablename] = $formdata[$variablename];
                }
            }
        }

        foreach ($featuresvars as $variable) {

            $variablename = $variable['name'];
            $hint = $variable['hint'];

            // Only array features are at the root of the recipe.
            if (!empty($formdata[$variablename]) && $hint == 'array') {
                $value = $this->get_array_variable($formdata[$variablename]);
                if (!empty($value)) {
                    $recipe[$variablename] = $value;
                }
            }
        }

        if (!empty($formdata['features'])) {

            $recipe['features'] = array();
            foreach ($formdata['features'] as $feature => $value) {

                if ($value === 'true') {
                    $recipe['features'][$feature] = true;
                } else if ($value === 'false') {
                    $recipe['features'][$feature] = false;
                }
            }
        }

        $recipe['component'] = $component;

        return $recipe;
    }

    /**
     * Returns the form value of an array template variable.
     *
     * @param string[] $formvalue
     * @return string[]
     */
    protected function get_array_variable($formvalue) {

        $value = array();
        foreach ($formvalue as $arrformval) {
            $currentvalue = array();
            foreach ($arrformval as $formfield => $formvalue) {
                if (!empty($formvalue)) {
                    $currentvalue[$formfield] = $formvalue;
                }
            }

            if (!empty($currentvalue)) {
                $value[] = $currentvalue;
            }
        }

        return $value;
    }
}
