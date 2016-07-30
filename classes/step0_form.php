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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * General information form.
 *
 * @package     tool_pluginskel
 * @copyright   2016 Alexandru Elisei <alexandru.elisei@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_pluginskel_step0_form extends moodleform {

    /**
     * The standard form definiton.
     */
    public function definition () {
        $mform = $this->_form;

        $mform->addElement('header', 'manualhdr', get_string('manualhdr', 'tool_pluginskel'));
        $mform->setExpanded('manualhdr', true);

        $objs = array();
        $plugintypes = tool_pluginskel\local\util\manager::get_plugintype_names();
        $objs[] =& $mform->createElement('select', 'componenttype', '', $plugintypes);
        $objs[] =& $mform->createElement('submit', 'helpmechoose', get_string('helpmechoose', 'tool_pluginskel'));
        $mform->addGroup($objs, 'componentgroup', get_string('skelcomponenttype', 'tool_pluginskel'), null, false);

        $mform->addElement('text', 'componentname', get_string('skelcomponentname', 'tool_pluginskel'), null);
        $mform->setType('componentname', PARAM_TEXT);

        $mform->addElement('submit', 'proceedmanually', get_string('proceedmanually', 'tool_pluginskel'));

        $mform->addElement('header', 'recipefilehdr', get_string('recipefilehdr', 'tool_pluginskel'));
        $mform->setExpanded('recipefilehdr', true);
        $mform->addElement('filepicker', 'recipefile', get_string('recipefile', 'tool_pluginskel'),
                           null, array('maxbytes' => 50000, 'accepted_types' => '*'));
        $mform->addElement('submit', 'proceedrecipefile', get_string('proceedrecipefile', 'tool_pluginskel'));

        $mform->addElement('header', 'recipehdr', get_string('recipehdr', 'tool_pluginskel'));
        $mform->setExpanded('recipehdr', true);
        $mform->addElement('textarea', 'recipe', get_string('recipe', 'tool_pluginskel'),
                           array('wrap' => 'virtual',  'rows' => '20', 'cols' => '50'));
        $mform->addElement('submit', 'proceedrecipe', get_string('proceedrecipe', 'tool_pluginskel'));

        $mform->addElement('hidden', 'step', 0);
        $mform->setType('step', PARAM_INT);
    }
}
