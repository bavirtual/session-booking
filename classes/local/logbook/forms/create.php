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
 * The mform for creating a logbook entry
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\logbook\forms;

use local_booking\local\session\entities\booking;

defined('MOODLE_INTERNAL') || die();

 /**
  * Always include formslib
  */
require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The mform class for creating and editing a logbook entry
 *
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create extends \moodleform {

    /**
     * Build the editor options using the given context.
     *
     * @param \context $context A Moodle context
     * @return array
     */
    public static function build_editor_options(\context $context) {
        global $CFG;

        return [
            'context' => $context,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $CFG->maxbytes,
            'noclean' => true,
            'autosave' => false
        ];
    }

    /**
     * The form definition
     */
    public function definition() {
        global $PAGE;

        $mform = $this->_form;
        $logentry = isset($this->_customdata['logentry']) ? $this->_customdata['logentry'] : null;
        $sessiondate = isset($this->_customdata['sessiondate']) ? $this->_customdata['sessiondate'] : null;

        $mform->setDisableShortforms();
        $mform->disable_form_change_checker();

        // Empty string so that the element doesn't get rendered.
        $mform->addElement('header', 'general', '');

        $this->add_default_hidden_elements($mform);

        // Logbook entry date field.
        $mform->addElement('date_selector', 'flightdate', get_string('flightdate', 'local_booking'));
        $mform->addRule('flightdate', get_string('required'), 'required', null, 'client');
        $mform->setType('flightdate', PARAM_TEXT);
        $mform->setDefault('flightdate', $sessiondate);

        // Flight duration placeholder="HH:MM"
        $mform->addElement('text', 'flighttime', get_string('flighttime', 'local_booking'), '
            size="5"
            data-inputmask-alias="datetime"
            data-inputmask-mask="hh:MM"
            placeholder="hh:mm"
             ');
        $mform->addRule('flighttime', get_string('required'), 'required', null, 'client');
        $mform->setType('flighttime', PARAM_TEXT);

        // Session duration
        $mform->addElement('text', 'sessiontime', get_string('sessiontime', 'local_booking'), '
            size="5"
            data-inputmask-alias="datetime"
            data-inputmask-mask="hh:MM"
            placeholder="hh:mm"
             ');
        $mform->addRule('sessiontime', get_string('required'), 'required', null, 'client');
        $mform->setType('sessiontime', PARAM_TEXT);

        // Solo duration
        $mform->addElement('text', 'solotime', get_string('solotime', 'local_booking'), '
            size="5"
            data-inputmask-alias="datetime"
            data-inputmask-mask="hh:MM"
            placeholder="hh:mm"
             ');
        $mform->setType('solotime', PARAM_TEXT);

        // PIC/SIC name selector
        $options = array(
            $logentry->get_picid() => $logentry->get_picname(),
            $logentry->get_sicid() => $logentry->get_sicname()
        );
        // PIC name select
        $select = $mform->addElement('select', 'picid', get_string('pic', 'local_booking'), $options);
        $select->setSelected($logentry->get_picid());

        // SIC name select
        $select = $mform->addElement('select', 'sicid', get_string('sic', 'local_booking'), $options);
        $select->setSelected($logentry->get_sicid());

        // Aircraft advanced element
        $aircraftoptions = array(
            '0' => 'C172',
            '1' => 'P28A'
        );
        $select = $mform->addElement('select', 'aircraft', get_string('aircraft', 'local_booking'), $aircraftoptions);
        $select->setSelected('0');
        $mform->setAdvanced('aircraft');

        // PIREP advanced element
        $mform->addElement('text', 'pirep', get_string('pirep', 'local_booking'));
        $mform->setType('pirep', PARAM_TEXT);
        $mform->setAdvanced('pirep');

        // Callsign advanced element
        $mform->addElement('text', 'callsign', get_string('callsign', 'local_booking'), 'style="text-transform:uppercase" ');
        $mform->setType('callsign', PARAM_TEXT);
        $mform->setDefault('aircraft', array('text'=>'WYC24'));
        $mform->setAdvanced('callsign');

        // From ICAO advanced element
        $mform->addElement('text', 'fromicao', get_string('fromicao', 'local_booking'), 'style="text-transform:uppercase" ');
        $mform->setType('fromicao', PARAM_TEXT);
        $mform->setAdvanced('fromicao');

        // To ICAO advanced element
        $mform->addElement('text', 'toicao', get_string('toicao', 'local_booking'), 'style="text-transform:uppercase" ');
        $mform->setType('toicao', PARAM_TEXT);
        $mform->setAdvanced('toicao');

        // Add the javascript required to enhance this mform.
        $PAGE->requires->js_call_amd('local_booking/logentry_form', 'init', [$mform->getAttribute('id')]);
    }

    /**
     * A bit of custom validation for this form
     *
     * @param array $data An assoc array of field=>value
     * @param array $files An array of files
     * @return array
     */
    public function validation($data, $files) {
        $booking = new booking($data['courseid'], $data['exerciseid'], 0, $data['studentid'], $data['sessiondate']);

        $errors = parent::validation($data, $files);

        // validate the flight date is not before the booking date
        $exercisedate = $booking->get_booked_exercise_date();
        if ($exercisedate != 0) {
            if ($data['sessiondate'] < $exercisedate) {
                $errors['sessiondate'] = get_string('errorinvaliddate', 'local_booking');
            }
        } else {
            $errors['sessiondate'] = get_string('errormissingbooking', 'local_booking', get_exercise_name($data['exerciseid']));
        }

        // validate flight, session, and solo flight durations
        $this->validate_flight_duration($errors, 'flighttime', $data['flighttime']);
        $this->validate_flight_duration($errors, 'sessiontime', $data['flighttime']);
        $this->validate_flight_duration($errors, 'solotime', $data['flighttime'], false);

        return $errors;
    }

    /**
     * Validates a flight duration
     *
     * @param array $data An assoc array of field=>value
     * @param array $files An array of files
     * @return array
     */
    protected function validate_flight_duration($errors, $field, $fieldvalue, $required = true) {
        if (!empty($fieldvalue)) {
            $flighttimehrs = substr($fieldvalue, 0, strpos(':', $fieldvalue) - 1);
            if (is_numeric($flighttimehrs)) {
                // a light aircraft flight should not be over 4hrs
                if ($flighttimehrs > 4) {
                    $errors[$field] = get_string('errorflighttimetoolong', 'localbooking', get_string($field, 'local_booking'));
                }
            } else {
                $errors[$field] = get_string('errornonnumeric', 'localbooking', get_string($field, 'local_booking'));
            }
        }
    }

    /**
     * Add the list of hidden elements that should appear in this form each
     * time. These elements will never be visible to the user.
     *
     * @param MoodleQuickForm $mform
     */
    protected function add_default_hidden_elements($mform) {
        global $USER;

        // Add some hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        $mform->setDefault('userid', $USER->id);

        $mform->addElement('hidden', 'exerciseid');
        $mform->setType('exerciseid', PARAM_INT);
        $mform->setDefault('exerciseid', 0);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', 0);

        $mform->addElement('hidden', 'contextid');
        $mform->setType('contextid', PARAM_INT);
        $mform->setDefault('contextid', 0);

        $mform->addElement('hidden', 'visible');
        $mform->setType('visible', PARAM_INT);
        $mform->setDefault('visible', 1);
    }
}
