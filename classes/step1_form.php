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

        $generalvars = tool_pluginskel\local\util\manager::get_general_variables();
        $componentvars = tool_pluginskel\local\util\manager::get_component_variables($component);
        $featuresvars = tool_pluginskel\local\util\manager::get_features_variables();

        $mform->addElement('header', 'generalhdr', get_string('generalhdr', 'tool_pluginskel'));
        $mform->setExpanded('generalhdr', true);

        foreach ($generalvars as $variable) {

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
                // Non-required boolean variable can also be missing from the recipe.
                // The select element adds an extra field 'none' to discard it from the recipe.
                if (empty($variable['required'])) {
                    $variable['values'] = array('true' => 'true', 'false' => 'false');
                    $this->add_select_element($elementname, $variable, $recipe);
                } else {
                    $this->add_advcheckbox_element($elementname, $variable, $recipe);
                }
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

        $mform->addElement('header', 'componenthdr', get_string('componenthdr', 'tool_pluginskel'));
        $mform->setExpanded('componenthdr', true);

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

                // Non-required boolean variable can also be missing from the recipe.
                // The select element adds an extra field 'none' to discard it from the recipe.
                if (empty($variable['required'])) {
                    $variable['values'] = array('true' => 'true', 'false' => 'false');
                    $this->add_select_element($elementname, $variable, $recipe);
                } else {
                    $this->add_advcheckbox_element($elementname, $variable, $recipe);
                }
            }

            if ($hint == 'multiple-options') {
                $this->add_select_element($elementname, $variable, $recipe);
            }
        }

        foreach ($componentvars as $variable) {
            if ($variable['hint'] == 'array') {
                $this->add_fieldset($variable, $recipe);
            }
        }

        foreach ($generalvars as $variable) {
            if ($variable['hint'] == 'array') {
                $this->add_fieldset($variable, $recipe);
            }
        }

        // Adding array features.
        foreach ($featuresvars as $variable) {
            if ($variable['hint'] == 'array') {
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

        if ($templatevar['hint'] == 'int') {
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
        $fieldsetname = $templatevar['name'];
        $templatevalues = $templatevar['values'];

        if (empty($this->_customdata[$fieldsetname.'count'])) {
            // Create only one entry in the fieldset if more haven't been specified.
            $count = 1;
        } else {
            $count = (int) $this->_customdata[$fieldsetname.'count'];
        }

        // Constructing the fieldset field values from the recipe.
        $recipevalues = array();
        if (!empty($recipe[$fieldsetname]) && is_array($recipe[$fieldsetname])) {
            $recipevalues = $this->get_fieldset_values_from_recipe($fieldsetname, $templatevalues,
                                                                   $recipe[$fieldsetname]);
        }

        $mform->addElement('header', $fieldsetname, get_string('skel'.$fieldsetname, 'tool_pluginskel'));
        if (!empty($templatevar['required']) || !empty($recipevalues)) {
            $mform->setExpanded($fieldsetname, true);
        } else {
            $mform->setExpanded($fieldsetname, false);
        }

        $this->add_fieldset_elements($fieldsetname, $templatevalues, $recipevalues, $count);

        $buttonarr = array();
        $buttonarr[] =& $mform->createElement('button', 'addmore_'.$fieldsetname,
                                              get_string('addmore_'.$fieldsetname, 'tool_pluginskel'));
        $buttonarr[] =& $mform->createElement('button', 'delete_'.$fieldsetname,
                                              get_string('delete_'.$fieldsetname, 'tool_pluginskel'));
        $mform->addGroup($buttonarr, 'buttons_'.$fieldsetname, '', array('    '), false);

        $mform->addElement('hidden', $fieldsetname.'count', $count);
        $mform->setType($fieldsetname.'count', PARAM_INT);
    }

    /**
     * Returns the values of all the the elements of a fieldset.
     *
     * @param string $fieldsetname The name of the fieldset.
     * @param string $templatefieldset The template variables associated with the fieldset.
     * @param string[] $fieldsetrecipe The part of the recipe that contains the values for the fieldset.
     * @param string[]
     */
    protected function get_fieldset_values_from_recipe($fieldsetname, $templatefieldset, $fieldsetrecipe) {

        $ret = array();

        foreach ($fieldsetrecipe as $fieldsetvalues) {
            $currentvalue = array();
            foreach ($templatefieldset as $field) {
                $fieldname = $field['name'];

                // Use only the recipe fields that are part of the template.
                if (isset($fieldsetvalues[$fieldname])) {
                    $currentvalue[$fieldname] = $fieldsetvalues[$fieldname];
                }
            }

            if (!empty($currentvalue)) {
                $ret[] = $currentvalue;
            }
        }

        return $ret;
    }

    /**
     * Adds an element to a fieldset.
     *
     * @param string $elementname
     * @param string $variable The template variable the element represents.
     * @param string[] $fieldsetvalues The values for all the fieldset elements.
     */
    protected function add_fieldset_element($elementname, $variable, $fieldsetvalues) {

        $hint = $variable['hint'];

        if ($hint == 'text' || $hint == 'int') {
            $this->add_text_element($elementname, $variable, $fieldsetvalues);
        }

        if ($hint == 'multiple-options') {
            $this->add_select_element($elementname, $variable, $fieldsetvalues);
        }

        if ($hint == 'boolean') {
            if ($variable['required'] === true) {
                $this->add_advcheckbox_element($elementname, $variable, $fieldsetvalues);
            } else {
                $variable['values'] = array('true' => 'true', 'false' => 'false');
                $this->add_select_element($elementname, $variable, $fieldsetvalues);
            }
        }
    }

    /**
     * Adds the elements of a fieldset based on the template variables and the recipe.
     *
     * @param string $fieldsetname The name of the fieldset.
     * @param string[] $templatevars The template variables to add.
     * @param string[] $recipevalues The values for the elements taken from recipe.
     * @param int $count The number of elements to add.
     * @param bool $isnested If the fieldset is nested inside another fieldset.
     */
    protected function add_fieldset_elements($fieldsetname, $templatevars, $recipevalues, $count) {

        $mform =& $this->_form;

        // Adding elements which have values specified in the recipe.
        if (!empty($recipevalues)) {
            foreach ($recipevalues as $index => $fieldsetvalues) {

                foreach ($templatevars as $variable) {

                    if ($variable['hint'] == 'array') {
                        $this->add_nested_array_variable($fieldsetname, $index, $variable, $fieldsetvalues);
                    } else {
                        $elementname= $fieldsetname.'['.$index.']['.$variable['name'].']';
                        $this->add_fieldset_element($elementname, $variable, $fieldsetvalues);
                    }
                }

                // Add a newline between array values.
                if ($index < count($recipevalues) - 1) {
                    $mform->addElement('html', '<br/>');
                }
            }
        }

        // Adding empty elements until we have the required number of elements in the fieldset.
        $currentcount = count($recipevalues);
        while ($currentcount < $count) {
            foreach ($templatevars as $variable) {

                if ($variable['hint'] == 'array') {
                    $this->add_nested_array_variable($fieldsetname, $currentcount, $variable, array());
                } else {
                    $elementname = $fieldsetname.'['.$currentcount.']['.$variable['name'].']';
                    $this->add_fieldset_element($elementname, $variable, array());
                }
            }

            $currentcount += 1;

            // Add a newline between groups of elements.
            if ($currentcount < $count) {
                $mform->addElement('html', '<br/>');
            }
        }
    }

    /**
     * Adds an array variable nested inside a fieldset.
     *
     * @param string $parentfieldset The name of the top level fieldset.
     * @param int $index The index of the parent variable relative to the parent fieldset.
     * @param string[] $nestedvariable The variable.
     * @param string[] $nestedrecipe The recipe for the nested variable.
     */
    protected function add_nested_array_variable($parentvariablename, $index, $nestedvariable, $nestedrecipe) {

        $mform =& $this->_form;
        $variablename = $nestedvariable['name'];
        $variablearrname = $parentvariablename.'['.$index.']'.'['.$variablename.']';
        $templatevalues = $nestedvariable['values'];

        $variablecountvar = $parentvariablename.'_'.$index.'_'.$variablename.'count';
        if (empty($this->_customdata[$variablecountvar])) {
            // Create only one entry in the fieldset if more haven't been specified.
            $count = 1;
        } else {
            $count = (int) $this->_customdata[$variablecountvar];
        }

        $mform->addElement('static', $variablearrname, get_string('skel'.$variablename, 'tool_pluginskel').':');

        $recipevalues = array();
        if (!empty($nestedrecipe[$variablename]) && is_array($nestedrecipe[$variablename])) {
            $recipevalues = $this->get_fieldset_values_from_recipe($variablename, $templatevalues,
                                                                   $nestedrecipe[$variablename]);
        }

        $this->add_fieldset_elements($variablearrname, $templatevalues, $recipevalues, $count);

        $mform->addElement('button', 'addmore_'.$variablearrname, get_string('addmore_'.$variablename, 'tool_pluginskel'));

        $mform->addElement('hidden', $variablecountvar, $count);
        $mform->setType($variablecountvar, PARAM_INT);
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

        $generalvars = tool_pluginskel\local\util\manager::get_general_variables();
        $componentvars = tool_pluginskel\local\util\manager::get_component_variables($component);
        $featuresvars = tool_pluginskel\local\util\manager::get_features_variables();

        foreach ($generalvars as $variable) {

            $variablename = $variable['name'];
            $hint = $variable['hint'];

            if (!empty($formdata[$variablename])) {

                if ($hint == 'array') {
                    $value = $this->get_array_variable($formdata[$variablename], $variable);
                    if (!empty($value)) {
                        $recipe[$variablename] = $value;
                    }
                } else if ($hint === 'multiple-options') {
                    // Ignoring 'none' select options.
                    if ($formdata[$variablename] !== 'none') {
                        $recipe[$variablename] = $formdata[$variablename];
                    }
                } else if ($hint === 'boolean' && empty($variable['required'])) {
                    if ($formdata[$variablename] !== 'none') {
                        $recipe[$variablename] = $formdata[$variablename];
                    }
                } else {
                    $recipe[$variablename] = $formdata[$variablename];
                }
            }
        }

        foreach ($componentvars as $variable) {

            $variablename = $variable['name'];
            $hint = $variable['hint'];

            if (!empty($formdata[$variablename])) {

                if ($hint == 'array') {
                    $value = $this->get_array_variable($formdata[$variablename], $variable);
                    if (!empty($value)) {
                        $recipe[$variablename] = $value;
                    }
                } else if ($hint === 'multiple-options') {
                    // Ignoring 'none' select options.
                    if ($formdata[$variablename] !== 'none') {
                        $recipe[$variablename] = $formdata[$variablename];
                    }
                } else if ($hint === 'boolean' && empty($variable['required'])) {
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

                $value = $this->get_array_variable($formdata[$variablename], $variable);

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
     * Returns the value of an array form variable.
     *
     * @param string[] $formvariable The form value of the array variable.
     * @param string[] $templatevariable The variable definition from the template.
     * @return string[]
     */
    protected function get_array_variable($formvariable, $templatevariable) {

        $ret = array();
        foreach ($formvariable as $formvalues) {

            $currentvalue = array();

            foreach ($formvalues as $field => $value) {

                if (!empty($value)) {

                    $ismultipleoptions = false;
                    $isrequired = false;
                    $isarray = false;

                    foreach ($templatevariable['values'] as $templatefield) {

                        if ($templatefield['name'] == $field) {

                            if ($templatefield['hint'] == 'multiple-options') {
                                $ismultipleoptions = true;
                                $isrequired = !empty($templatefield['required']);
                            }

                            if ($templatefield['hint'] == 'boolean' && empty($templatefield['required'])) {
                                $ismultipleoptions = true;
                                $isrequired = false;
                            }

                            if ($templatefield['hint'] == 'array') {
                                $isarray = true;
                                $value = $this->get_array_variable($value, $templatefield);
                            }

                            break;
                        }
                    }

                    if ($isarray) {
                        if (!empty($value)) {
                            $currentvalue[$field] = $value;
                        }
                    } else if ($ismultipleoptions && !$isrequired && $value == 'none') {
                        // If the field is a select element with the value 'none' then ignore it.
                        continue;
                    } else {
                        $currentvalue[$field] = $value;
                    }
                }
            }

            if (!empty($currentvalue)) {
                $ret[] = $currentvalue;
            }
        }

        return $ret;
    }
}
