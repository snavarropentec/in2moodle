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
 * Moodle custom renderer class for eudecalendar view.
 *
 * @package    local_in2moodle
 * @copyright  2017 Planificacion de Entornos Tecnologicos SL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_in2moodle\output;

defined('MOODLE_INTERNAL') || die;

use \html_writer;
use renderable;

/**
 * Renderer for eude custom actions plugin.
 *
 * @copyright  2017 Planificacion de Entornos Tecnologicos SL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class in2moodle_renderer extends \plugin_renderer_base {
    /**
     * Render the error page if user does not have the capabilities to access.
     * @return string html to output.
     */
    public function no_permissions_message() {
        $response = '';
        $response .= $this->header();

        $html = html_writer::start_div('row');
        $html .= html_writer::start_div('col-md-12');
        $html .= html_writer::tag('div', get_string('nopermissiontoshow',
                'error'), array('id' => 'nopermissions', 'name' => 'nopermissions', 'class' => 'alert alert-danger'));
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();

        $response .= $html;
        $response .= $this->footer();
        return $response;
    }
    /**
     * Form for plugin configuration.
     */
    public function get_in2moodle_form() {
        // Moodle header.
        $response = '';
        $response .= $this->header();

        // Define strings.
        $clientinfo = get_string('clientinfo', 'local_in2moodle');
        $platform = get_string('platform_label', 'local_in2moodle');
        $inputfile = get_string('inputfile_label', 'local_in2moodle');
        
        // Plugin content.
        $html = html_writer::start_div('row i2m_maincontent');
        $html .= html_writer::start_div('col-md-12');
        // Start Form.
        $html .= html_writer::start_tag('form', array('name' => 'i2mform', 'action' => '../in2moodle.php'));
        $html .= html_writer::tag('h4', $clientinfo, array('class' => 'i2m_formtitle'));
        
        // Input Platform.
        $html .= html_writer::start_div('row');
        $html .= html_writer::start_div('col-md-6 i2m_label');
        $html .= html_writer::span($platform, 'i2m_label');
        $html .= html_writer::end_div();
        $html .= html_writer::start_div('col-md-6 i2m_input');
        $html .= html_writer::start_tag('select', array('name' => 'platform'));
        $html .= html_writer::start_tag('option', array('value' => 0, 'selected'));
        $html .= html_writer::span('Select');
        $html .= html_writer::end_tag('option');
        $html .= html_writer::start_tag('option', array('value' => 1));
        $html .= html_writer::span('e-ducativa');
        $html .= html_writer::end_tag('option');
        //$html .= html_writer::start_tag('option', array('value' => 2));
        //$html .= html_writer::span('Blackboard Learn');
        //$html .= html_writer::end_tag('option');
        $html .= html_writer::end_tag('select');
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        
        // Input file.
        $html .= html_writer::start_div('row');
        $html .= html_writer::start_div('col-md-6 i2m_label');
        $html .= html_writer::span($inputfile);
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
       
        $html .= html_writer::end_tag('form');
        // End form.
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();

        // Moodle Footer.
        $response .= $html;
        $response .= $this->footer();
        return $response;
    }
}