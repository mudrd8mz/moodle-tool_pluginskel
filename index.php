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
 * Web interface for generating plugins.
 *
 * @package    tool_pluginkenobi
 * @copyright  2016 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use Monolog\Logger;
use Monolog\Handler\BrowserConsoleHandler;

require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/moodlelib.php');
require_once(__DIR__.'/vendor/autoload.php');

admin_externalpage_setup('tool_pluginskel');

$url = new moodle_url('/admin/tool/pluginskel/index.php');
$PAGE->set_url($url);
$PAGE->set_title(get_string('generateskel', 'tool_pluginskel'));
$PAGE->set_heading(get_string('generateskel', 'tool_pluginskel'));

$step = optional_param('step', '0', PARAM_INT);
$component = optional_param('component1', '', PARAM_TEXT);

$returnurl = new moodle_url('/admin/tool/pluginskel/index.php');

if ($step == 0) {

    $mform0 = new tool_pluginskel_step0_form();
    $formdata = $mform0->get_data();

    if (!empty($formdata)) {

        $data = array();
        $recipe = array();

        $componentvars = tool_pluginskel\local\util\manager::get_component_variables($component);
        $featuresvars = tool_pluginskel\local\util\manager::get_features_variables($component);

        $templatevars = array_merge($componentvars, $featuresvars);

        if (!empty($formdata->proceedmanually)) {

            if (empty($formdata->componentname)) {
                throw new moodle_exception('emptypluginname', 'tool_pluginskel', $returnurl);
            }

            $recipe['component'] = $formdata->componenttype.'_'.$formdata->componentname;

        } else {

            if (!empty($formdata->proceedrecipefile)) {
                $recipestring = $mform0->get_file_content('recipefile');
            } else if (!empty($formdata->proceedrecipe)) {
                $recipestring = $formdata->recipe;
            }

            if (empty($recipestring)) {
                throw new moodle_exception('emptyrecipecontent', 'tool_pluginskel', $returnurl);
            }

            $recipe = tool_pluginskel\local\util\yaml::decode_string($recipestring);
        }

        $data = get_variable_count_from_recipe($templatevars, $recipe);
        $data['recipe'] = $recipe;

        $mform1 = new tool_pluginskel_step1_form(null, $data);

        $arrayvariables = get_array_template_variables($templatevars);
        $PAGE->requires->js_call_amd('tool_pluginskel/addmore', 'addMore', $arrayvariables);

        echo $OUTPUT->header();
        $mform1->display();
        echo $OUTPUT->footer();

    } else {

        echo $OUTPUT->header();
        $mform0->display();
        echo $OUTPUT->footer();

    }

} else if ($step == 1) {

    // Reconstructing the form elements.
    $componentvars = tool_pluginskel\local\util\manager::get_component_variables($component);
    $featuresvars = tool_pluginskel\local\util\manager::get_features_variables();

    $templatevars = array_merge($componentvars, $featuresvars);

    // Getting the number of elements for array variables.
    $data = get_variable_count_from_form($templatevars);
    $data['recipe'] = array('component' => $component);

    $mform1 = new tool_pluginskel_step1_form(null, $data);
    $formdata = (array) $mform1->get_data();

    $recipe = $mform1->get_recipe();

    if (!empty($formdata['buttondownloadskel'])) {

        download_plugin_skeleton($recipe);

    } else if (!empty($formdata['buttondownloadrecipe'])) {

        $recipestring = tool_pluginskel\local\util\yaml::encode($recipe);
        download_recipe($recipestring);

    } else if (!empty($formdata['buttonviewrecipe'])) {

        $data = array('recipe' => $recipe);
        $mform2 = new tool_pluginskel_step2_form(null, $data);

        echo $OUTPUT->header();
        $mform2->display();
        echo $OUTPUT->footer();
    }

} else if ($step == 2) {

    $mform2 = new tool_pluginskel_step2_form();
    $formdata = (array) $mform2->get_data();

    $recipestring = $formdata['recipe'];

    if (!empty($formdata['buttondownloadrecipe'])) {

        download_recipe($recipestring);

    } else if (!empty($formdata['buttondownloadskel'])) {

        $recipe = tool_pluginskel\local\util\yaml::decode_string($recipestring);
        download_plugin_skeleton($recipe);

    } else if (!empty($formdata['buttonback'])) {

        $recipe = tool_pluginskel\local\util\yaml::decode_string($recipestring);

        $componentvars = tool_pluginskel\local\util\manager::get_component_variables($component);
        $featuresvars = tool_pluginskel\local\util\manager::get_features_variables($component);
        $templatevars = array_merge($componentvars, $featuresvars);

        $data = get_variable_count_from_recipe($templatevars, $recipe);
        $data['recipe'] = $recipe;
        $mform1 = new tool_pluginskel_step1_form(null, $data);

        $arrayvars = get_array_template_variables($templatevars);
        $PAGE->requires->js_call_amd('tool_pluginskel/addmore', 'addMore', $arrayvars);

        echo $OUTPUT->header();
        $mform1->display();
        echo $OUTPUT->footer();
    }
}

/**
 * Returns only those template variables which are arrays.
 *
 * @param string[] $templatevars All of the template variables.
 * @return string[] Only those variables which are arrays.
 */
function get_array_template_variables($templatevars) {

    $arrayvars = array();
    foreach ($templatevars as $variable) {
        if ($variable['hint'] == 'array') {
            $arrayvars[] = $variable;
        }
    }

    return array($arrayvars);
}

/**
 * Returns the number of values for each variable array by examining the recipe.
 *
 * @param string[] $templatevars The template variables.
 * @param string[] $recipe
 * @return string[]
 */
function get_variable_count_from_recipe($templatevars, $recipe) {

    $variablecount = array();

    foreach ($templatevars as $variable) {
        if ($variable['hint'] == 'array') {

            $variablename = $variable['name'];
            $variablecountvar = $variablename.'count';

            if (empty($recipe[$variablename])) {
                $count = 1;
            } else {
                $count = count($recipe[$variablename]);
            }

            $variablecount[$variablecountvar] = $count;

            if (empty($recipe[$variablename])) {
                continue;
            }

            foreach ($variable['values'] as $nestedvariable) {
                if ($nestedvariable['hint'] == 'array') {
                    for ($i = 0; $i < $count; $i += 1) {
                        $nestedvariablecount = get_variable_count_from_recipe($variable['values'], $recipe[$variablename][$i]);
                        $nestedvariablecountvar = $variablename.'_'.$i.'_'.$nestedvariable['name'].'count';
                        $variablecount[$nestedvariablecountvar] = $nestedvariablecount[$nestedvariable['name'].'count'];
                    }
                }
            }
        }
    }

    return $variablecount;
}

/**
 * Returns the number of values for each variable array by examining the form.
 *
 * @param string[] $templatevars The template variables.
 * @param string $prefix The prefix used for nested arrays.
 * @return string[]
 */
function get_variable_count_from_form($templatevars) {

    $variablecount = array();

    foreach ($templatevars as $variable) {

        if ($variable['hint'] == 'array') {

            $variablecountvar = $variable['name'].'count';
            $count = (int) optional_param($variablecountvar, 1, PARAM_INT);
            $variablecount[$variablecountvar] = $count;

            foreach ($variable['values'] as $nestedvariable) {

                if ($nestedvariable['hint'] == 'array') {

                    for ($i = 0; $i < $count; $i += 1) {

                        $nestedname = $variable['name'].'_'.$i.'_'.$nestedvariable['name'];
                        $nestedcountvar = $nestedname.'count';

                        $count = (int) optional_param($nestedcountvar, 1, PARAM_INT);

                        $variablecount[$nestedcountvar] = $count;
                    }
                }
            }
        }
    }

    return $variablecount;
}


/**
 * Generates the download header.
 *
 * @param string $filename
 * @param int $contentlength
 */
function generate_download_header($filename, $contentlength) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: '.$contentlength);
}

/**
 * Downloads the recipe.
 *
 * @param string $recipestring The recipe is a YAML string.
 */
function download_recipe($recipestring) {

    $filename = 'recipe_'.time().'.yaml';
    $contentlength = strlen($recipestring);

    generate_download_header($filename, $contentlength);
    echo($recipestring);
}

/**
 * Downloads the plugin skeleton as a zip file.
 *
 * @param string[] $recipe
 */
function download_plugin_skeleton($recipe) {

    $logger = new Logger('tool_pluginskel');
    $logger->pushHandler(new BrowserConsoleHandler(Logger::WARNING));

    $manager = tool_pluginskel\local\util\manager::instance($logger);
    $manager->load_recipe($recipe);
    $manager->make();

    $targetdir = make_request_directory();
    $targetdir = $targetdir.'/pluginskel';
    $manager->write_files($targetdir);

    $generatedfiles = $manager->get_files_content();

    $component = $recipe['component'];
    list($componenttype, $componentname) = core_component::normalize_component($component);
    $zipfiles = array();
    foreach ($generatedfiles as $filename => $notused) {
        $zipfiles[$componentname.'/'.$filename] = $targetdir.'/'.$filename;
    }

    $packer = get_file_packer('application/zip');
    $archivefile = $targetdir.'/'.$component.'_'.time().'.zip';
    $packer->archive_to_pathname($zipfiles, $archivefile);

    $filename = basename($archivefile);
    $contentlength = filesize($archivefile);

    generate_download_header($filename, $contentlength);
    readfile($archivefile);
    unlink($targetdir);
}
