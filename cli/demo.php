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
 * Demonstration usage of the generator.
 *
 * @package     tool_pluginskel
 * @subpackage  cli
 * @copyright   2016 Alexandru Elisei <alexandru.elisei@gmail.com>, David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_pluginskel\local\util;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

define('CLI_SCRIPT', true);

require(__DIR__.'/../../../../config.php');
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/tool/pluginskel/vendor/autoload.php');

// Create and configure the logger.
$logger = new Logger('demo');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
$logger->debug('Logger initialised');

// Moodle dirroot location where the plugin skeleton should be generated to.
$tmpmoodleroot = tempnam(sys_get_temp_dir(), 'skel_');
unlink($tmpmoodleroot);
mkdir($tmpmoodleroot);

// Load the recipe from file.
$recipe = yaml::decode_file(__DIR__.'/demo.yaml');

// Do the job.
$manager = manager::instance($logger);
$manager->load_recipe($recipe);
$manager->make();
// $manager->write_files($tmpmoodleroot);

print_r($manager->get_files_content());
