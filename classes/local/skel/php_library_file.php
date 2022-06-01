<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace tool_pluginskel\local\skel;

/**
 * Class representing a Moodle PHP library file.
 *
 * Library files are internal files but without side effects, so they should not contain the MOODLE_INTERNAL check.
 *
 * @package     tool_pluginskel
 * @subpackage  skel
 * @copyright   2021 David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class php_library_file extends php_internal_file {

    /**
     * Set the data to be eventually rendered.
     *
     * @param array $data
     */
    public function set_data(array $data) {

        parent::set_data($data);
        $this->data['check_moodle_internal'] = false;
    }
}
