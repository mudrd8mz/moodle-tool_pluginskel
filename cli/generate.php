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
 * CLI script for generating a plugin.
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

// Get cli options.
list($options, $unrecognized) = cli_get_params(array(
    'recipe' => '',
    'loglevel' => 'WARNING',
    'help' => '',
),
array(
    'r' => 'recipe',
    'v' => 'loglevel',
    'h' => 'help'
));

$help =
"\nGenerate a Moodle plugin skeleton.

Options:
-r, --recipe               Recipe file location
-v, --loglevel             Set the verbosity of the logs
-h, --help                 Display the help message

Example:
\$php generate.php --recipe=example_recipe.yaml

";

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    echo $help;
    die();
}

if (empty($options['recipe'])) {
    echo("\nRecipe not specified!\n");
    echo $help;
    die();
}

$recipefile = $options['recipe'];

// Expanding '~' on Unix-like OS'es.
if ($recipefile[0] === '~') {
    $homedir = getenv('HOME');
    $recipefile = $homedir.substr($recipefile, 1);
}

$recipefile = realpath($recipefile);
if ($recipefile === false) {
    echo("\nInvalid recipe file!\n");
    echo $help;
    die();
}

$loglevels = Logger::getLevels();
$loglevel = $options['loglevel'];
if (!array_key_exists($loglevel, $loglevels)) {
    echo("\nInvalid log level!\n");
    echo $help;
    die();
}

// Create and configure the logger.
$logger = new Logger('tool_pluginskel');
$logger->pushHandler(new StreamHandler('php://stdout', constant('\Monolog\Logger::'.$loglevel)));
$logger->debug('Logger initialised');

// Load the recipe from file.
$recipe = yaml::decode_file($recipefile);

$manager = manager::instance($logger);
$manager->load_recipe($recipe);
$manager->make();

//print_r($manager->get_files_content());
