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

        $PAGE->requires->js_call_amd('tool_pluginskel/addmore', 'addMore', array($templatevars));

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
    $recipestub = array('component' => $component);
    $data = array('recipe' => $recipestub);

    $componentvars = tool_pluginskel\local\util\manager::get_component_variables($component);
    $featuresvars = tool_pluginskel\local\util\manager::get_features_variables();

    $templatevars = array_merge($componentvars, $featuresvars);

    // Getting the number of elements for array variables.
    foreach ($templatevars as $var) {
        if (!empty($var['hint']) && $var['hint'] == 'array') {
            $formvariable = $var['name'].'count';
            $count = (int) optional_param($formvariable, 1, PARAM_INT);
            $data[$formvariable] = $count;
        }
    }

    $mform1 = new tool_pluginskel_step1_form(null, $data);

    $formdata = (array) $mform1->get_data();
    $recipe = $mform1->get_recipe();

    if (!empty($formdata['buttondownloadskel'])) {

        $logger = new Logger('tool_pluginskel');
        $logger->pushHandler(new BrowserConsoleHandler(Logger::WARNING));

        $manager = tool_pluginskel\local\util\manager::instance($logger);
        $manager->load_recipe($recipe);
        $manager->make();

        $targetdir = make_request_directory();
        $targetdir = $targetdir.'/pluginskel';
        $manager->write_files($targetdir);

        $generatedfiles = $manager->get_files_content();

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

    } else if (!empty($formdata['buttondownloadrecipe'])) {

        $recipefile = tool_pluginskel\local\util\yaml::encode($recipe);
        $filename = 'recipe_'.time().'.yaml';
        $contentlength = strlen($recipefile);

        generate_download_header($filename, $contentlength);
        echo($recipefile);
    }
}

function get_variable_count_from_recipe($templatevars, $recipe) {

    $variablecount = array();

    foreach ($templatevars as $variable) {
        if (!empty($variable['hint']) && $variable['hint'] == 'array') {

            $variablename = $variable['name'];
            $formvariable = $variablename.'count';

            if (empty($recipe[$variablename])) {
                $count = 1;
            } else {
                $count = count($recipe[$variablename]);
            }

            $variablecount[$formvariable] = $count;
        }
    }

    return $variablecount;
}

function generate_download_header($filename, $contentlength) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: '.$contentlength);
}
