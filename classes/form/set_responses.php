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
 * Security questions response form for users
 *
 * @package     tool_securityquestions
 * @copyright   Peter Burnett <peterburnett@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_securityquestions\form;

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");
require_once(__DIR__.'/../../locallib.php');

class set_responses extends \moodleform {

    public function definition() {
        global $SESSION, $USER;
        $mform = $this->_form;

        $this->generate_select($mform);

        $mform->addElement('text', 'response', get_string('formresponseentrybox', 'tool_securityquestions'), 'size="50"');
        $mform->setType('response', PARAM_TEXT);
        if (isset($SESSION->presentedresponse) && !get_config('tool_securityquestions', 'mandatory_questions')) {
            // If user is allowed to navigate away, build custom buttons
            $buttonarray = array();
            $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
            $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('formremindme', 'tool_securityquestions'));
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        } else {
            // If user must answer questions, dont show cancel button until enough answered
            if (count(tool_securityquestions_get_active_user_responses($USER)) < get_config('tool_securityquestions', 'minuserquestions')) {
                $this->add_action_buttons(false);
            } else {
                $this->add_action_buttons();
            }
        }
    }

    public function validation($data, $files) {

        $errors = parent::validation($data, $files);
        if ($data['response'] == '') {
            $errors['response'] = get_string('formresponseempty', 'tool_securityquestions');
        }
        return $errors;
    }

    // =============================================DISPLAY AND VALIDATION FUNCTIONS======================================

    private function generate_select($mform) {
        global $DB;
        global $USER;

        // Generate array for questions
        $questions = $DB->get_records('tool_securityquestions', array('deprecated' => 0));
        $qarray = array();
        foreach ($questions as $question) {
            $qarray[$question->id] = $question->content;
        }

        // Add form element
        $mform->addElement('select', 'questions', get_string('formresponseselectbox', 'tool_securityquestions'), $qarray);
    }
}