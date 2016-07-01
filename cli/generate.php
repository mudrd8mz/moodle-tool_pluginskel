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

$help = "
Generate a Moodle plugin skeleton from the recipe file.

Usage:
    \$php generate.php --recipe=<path> [--loglevel=<level>] [--target-moodle=<path> | --target-dir=<path>]
    \$php generate.php [--help | -h]

Options:
    --recipe=<path>         Recipe file location.
    --loglevel=<level>      Logging verbosity level [default: WARNING]
    --target-moodle=<path>  Full path to the root directory of the target Moodle installation.
                            [default: $CFG->dirroot].
    --target-dir=<path>     Full path to the target location of the plugin
    --help -h               Display this help message.

Valid log levels are: $loglevelnames.

By default, the plugin skeleton is generated to the current Moodle's dirroot.
You can let generate to another Moodle installation via the --target-moodle
argument, or explicitly define the target location via the --target-dir
argument.

Examples:
    \$php generate.php --recipe=myplugin.yaml --loglevel=DEBUG --target-moodle=/var/www/vhost/moodle_dev
    \$php generate.php --recipe=myplugin.yaml --target-dir=/tmp
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
    cli_writeln($help);
    cli_error("Recipe not specified!");
}

$recipefile = $options['recipe'];
$recipefile = tool_pluginskel_expand_path($recipefile);

if ($recipefile === false) {
    cli_error("Invalid recipe file!");
}

if (!is_readable($recipefile)) {
    cli_error("Recipe file not readable!");
}

// Load the recipe from file.
$recipe = yaml::decode_file($recipefile);

if (empty($recipe['component'])) {
    cli_error("The recipe does not provide the component for the plugin!");
}

list($plugintype, $pluginname) = \core_component::normalize_component($recipe['component']);

if ($plugintype === 'core') {
    cli_error("Core components not supported!");
}

if (!empty($options['target-dir']) && !empty($options['target-moodle'])) {
    cli_error("Specify either 'target-dir' or 'target-moodle'!");
}

if (!empty($options['target-dir'])) {

    $targetdir = $options['target-dir'];
    $targetdir = tool_pluginskel_expand_path($targetdir);
    if ($targetdir === false) {
        cli_error("Invalid target directory!");
    }

    if (!is_writable($targetdir)) {
        cli_error("Target plugin location is not writable!");
    }

    $targetdir = $targetdir.'/'.$pluginname;

} else {

    if (empty($options['target-moodle'])) {
        $targetdir = $CFG->dirroot;
    } else {
        $targetdir = $options['target-moodle'];
        $targetdir = tool_pluginskel_expand_path($targetdir);
        if ($targetdir === false) {
            cli_error("Invalid target directory!");
        }
    }

    if (!is_writable($targetdir)) {
        cli_error("Target plugin location is not writable!");
    }

    $plugintypes = \core_component::get_plugin_types();

    if (empty($plugintypes[$plugintype])) {
        cli_error("Unknown plugin type '$plugintype'!");
    }

    $targetdir = $targetdir.substr($plugintypes[$plugintype], strlen($CFG->dirroot));
    $targetdir = $targetdir.'/'.$pluginname;
}

if (file_exists($targetdir)) {
    cli_error("Target plugin location already exists: ".$targetdir);
}

$loglevel = $options['loglevel'];
if (!array_key_exists($loglevel, $loglevels)) {
    cli_error("Invalid log level!");
}

// Create and configure the logger.
$logger = new Logger('tool_pluginskel');
$logger->pushHandler(new StreamHandler('php://stdout', constant('\Monolog\Logger::'.$loglevel)));
$logger->debug('Logger initialised');

$manager = manager::instance($logger);
$manager->load_recipe($recipe);
$manager->make();
$manager->write_files($targetdir);
cli_writeln('Plugin skeleton files generated: '.$targetdir);
