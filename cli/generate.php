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
require_once($CFG->dirroot.'/'.$CFG->admin.'/tool/pluginskel/locallib.php');

// Get cli options.
list($options, $unrecognized) = cli_get_params(array(
    'recipe' => '',
    'loglevel' => 'WARNING',
    'target-moodle' => '',
    'target-dir' => '',
    'help' => false,
),
array(
    'h' => 'help'
));

$loglevels = Logger::getLevels();
$loglevelnames = implode(', ', array_keys($loglevels));

$help =
"\nGenerate a Moodle plugin skeleton.

Options:
    --recipe               Recipe file location
    --loglevel             Set the verbosity of the logs. The default is WARNING.
-h, --help                 Display the help message

Valid log levels are: $loglevelnames.

Example:
\$php generate.php --recipe=example_recipe.yaml --loglevel=INFO
";

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    cli_writeln($help);
    die();
}

if (empty($options['recipe'])) {
    cli_writeln("\nRecipe not specified!");
    cli_writeln($help);
    die();
}

$recipefile = $options['recipe'];

$recipefile = tool_pluginskel_expand_path($recipefile);
if ($recipefile === false) {
    cli_writeln("\nInvalid recipe file!");
    cli_writeln($help);
    die();
}

if (!is_readable($recipefile)) {
    cli_writeln("\nRecipe file not readable!");
    cli_writeln($help);
    die();
}

// Load the recipe from file.
$recipe = yaml::decode_file($recipefile);

if (empty($recipe['component'])) {
    cli_writeln("\nThe recipe does not provide the component for the plugin!");
    cli_writeln($help);
    die();
}

list($plugintype, $pluginname) = \core_component::normalize_component($recipe['component']);

if ($plugintype === 'core') {
    cli_writeln("\nCore components not supported!");
    cli_writeln($help);
    die();
}

if (!empty($options['target-dir']) && !empty($options['target-moodle'])) {
    cli_writeln("\nSpecify one of 'target-dir' or 'target-moodle'!");
    cli_writeln($help);
    die();
}

if (!empty($options['target-dir'])) {

    $targetdir = $options['target-dir'];
    $targetdir = tool_pluginskel_expand_path($targetdir);
    if ($targetdir === false) {
        cli_writeln("\nInvalid target directory!");
        cli_writeln($help);
        die();
    }

    if (!is_writable($targetdir)) {
        cli_writeln("\nTarget plugin location is not writable!");
        cli_writeln($help);
        die();
    }

    $targetdir = $targetdir.'/'.$pluginname;

} else {

    if (empty($options['target-moodle'])) {
        $targetdir = $CFG->dirroot;
    } else {
        $targetdir = $options['target-moodle'];
        $targetdir = tool_pluginskel_expand_path($targetdir);
        if ($targetdir === false) {
            cli_writeln("\nInvalid target directory!");
            cli_writeln($help);
            die();
        }
    }

    if (!is_writable($targetdir)) {
        cli_writeln("\nTarget plugin location is not writable!");
        cli_writeln($help);
        die();
    }

    $plugintypes = \core_component::get_plugin_types();

    if (empty($plugintypes[$plugintype])) {
        cli_writeln("\nUnknown plugin type '$plugintype'!");
        cli_writeln($help);
        die();
    }

    $targetdir = $targetdir.substr($plugintypes[$plugintype], strlen($CFG->dirroot));
    $targetdir = $targetdir.'/'.$pluginname;
}

if (file_exists($targetdir)) {
    cli_writeln("\nTarget plugin location exists!");
    cli_writeln($help);
    die();
}

$loglevel = $options['loglevel'];
if (!array_key_exists($loglevel, $loglevels)) {
    cli_writeln("\nInvalid log level!");
    cli_writeln($help);
    die();
}

// Create and configure the logger.
$logger = new Logger('tool_pluginskel');
$logger->pushHandler(new StreamHandler('php://stdout', constant('\Monolog\Logger::'.$loglevel)));
$logger->debug('Logger initialised');

$manager = manager::instance($logger);
$manager->load_recipe($recipe);
$manager->make();
$manager->write_files($targetdir);

//print_r($manager->get_files_content());
