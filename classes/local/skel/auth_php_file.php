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
 * Provides tool_pluginskel\local\skel\auth_php_file class.
 *
 * @package     tool_pluginskel
 * @subpackage  skel
 * @copyright   2016 Alexandru Elisei <alexandru.elisei@gmail.com>, David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_pluginskel\local\skel;

use tool_pluginskel\local\util\exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing the auth.php file.
 *
 * @copyright 2016 David Mudrak <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_php_file extends php_internal_file {

    /**
     * Set the data to be eventually rendered.
     *
     * @param array $data
     */
    public function set_data(array $data) {

        parent::set_data($data);

        /*
        $recipevariables = array(
            'can_change_password',
            'can_edit_profile',
            'prevent_local_passwords',
            'is_synchronised_with_external',
            'can_reset_password',
            'can_signup',
            'can_confirm',
            'can_be_manually_set',
            'is_internal'
        );

        foreach ($recipevariables as $variable) {
            if (isset($this->data[$variable])) {
                $this->set_attribute('has_'.$variable);
            }
        }
         */
    }
}
