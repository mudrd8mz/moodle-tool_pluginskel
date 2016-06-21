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
 * Provides tool_pluginskel\local\skel\version_php_file class.
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
 * Class representing the plugin's version.php file.
 *
 * @copyright 2016 David Mudrak <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class version_php_file extends php_internal_file {

    /**
     * Set the data to be eventually rendered.
     *
     * @param array $data
     */
    public function set_data(array $data) {

        parent::set_data($data);

        if (empty($this->data['version'])) {
            $this->data['version'] = date('Ymd').'00';
        }

        if (!empty($this->data['dependencies'])) {
            $this->data['has_dependencies'] = true;
        }

        $moodleversions = [
            '3.1' => '2016052300',
            '3.0' => '2015111600',
            '2.9' => '2015051100',
            '2.8' => '2014111000',
            '2.7' => '2014051200',
            '2.6' => '2013111800',
            '2.5' => '2013051400',
            '2.4' => '2012120300',
            '2.3' => '2012062500',
            '2.2' => '2011120500',
        ];

        if (!empty($this->data['requires'])) {
            if (!preg_match('/^2[0-9]{3}(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])[0-9]{2}$/', $this->data['requires'])) {
                if (isset($moodleversions[$this->data['requires']])) {
                    $this->data['requires'] = $moodleversions[$this->data['requires']];
                } else {
                    throw new exception('Unknown required Moodle version: '.$this->data['requires']);
                }
            }
        }
    }
}
