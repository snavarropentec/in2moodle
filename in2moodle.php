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
 * Custom Teacher Control Panel
 *
 * @package    local_in2moodle
 * @copyright  2017 Planificacion de Entornos Tecnologicos SL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/pagelib.php');

require_login(null, false, null, false, true);

global $USER;
global $OUTPUT;
global $CFG;

// Set up the page.
$title = get_string('in2moodle_title', 'local_in2moodle');
$pagetitle = $title;
$url = new moodle_url("/local/in2moodle/in2moodle.php");

$PAGE->set_context(context_system::instance());
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('standard');
$PAGE->requires->css("/local/in2moodle/style/style.css");

// Load the renderer of the page.
$output = $PAGE->get_renderer('local_in2moodle', 'in2moodle');

// Access control.
if (!has_capability('moodle/site:config', context_system::instance())) {
    echo $output->no_permissions_message();
} else {
    echo $output->get_in2moodle_form();
}