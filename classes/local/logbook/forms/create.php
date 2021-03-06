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

use local_booking\local\logbook\entities\logbook;
use local_booking\local\participant\entities\participant;
use local_booking\local\session\entities\booking;
use local_booking\local\subscriber\entities\subscriber;

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
     * The form definition
     */
    public function definition() {
        global $PAGE, $COURSE;

        $mform = $this->_form;
        $logentry = isset($this->_customdata['logentry']) ? $this->_customdata['logentry'] : null;
        $courseid = isset($this->_customdata['courseid']) ? $this->_customdata['courseid'] : null;

        $mform->setDisableShortforms();
        $mform->disable_form_change_checker();

        // Empty string so that the element doesn't get rendered.
        $mform->addElement('header', 'general', '');

        $this->add_default_hidden_elements($mform, $COURSE->subscriber->trainingtype);
        $this->add_elements($mform, $COURSE->subscriber, $logentry);

        // Add the javascript required to enhance this mform.
        $PAGE->requires->js_call_amd('local_booking/modal_logentry_form');
    }

    /**
     * A bit of custom validation for this form
     *
     * @param array $data An assoc array of field=>value
     * @param array $files An array of files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // validate the flight date is not before the booking date
        $exercisedate = booking::get_exercise_date($data['courseid'], $data['userid'], $data['exerciseid']);
        if (!empty($exercisedate) && $data['flighttype'] != 'solo') {
            if ($data['flightdate'] < $exercisedate)
                $errors['flightdate'] = get_string('errorinvaliddate', 'local_booking');
        }

        // validate the flight departure is after arrival time if exists
        if (!empty($data['deptime']) && !empty($data['arrtime'])) {
            if ($data['arrtime'] <= $data['deptime'])
                $errors['arrtime'] = get_string('errorinvalidarrtime', 'local_booking');
        }

        return $errors;
    }

    /**
     * Set the list of element in the form based on
     * the training type.
     *
     * @param MoodleQuickForm $mform
     * @param subscriber $subscriber
     */
    protected function add_elements($mform, $subscriber, $logentry) {
        global $USER;
        $integratedpireps = $subscriber->has_integration('pireps');
        $newlogentry = empty($logentry) || empty($logentry->get_id());

        // set additional information
        if ($newlogentry) {
            $flightdate = isset($this->_customdata['flightdate']) ? $this->_customdata['flightdate'] : null;
            $p1id = $USER->id;
            $p2id = $this->_customdata['userid'];
        } else {
            $flightdate = $logentry->get_flightdate();
            $p1id = $logentry->get_p1id();
            $p2id = $logentry->get_p2id();
        }
        // P1/PIC instructor id and P2 student id
        $pilots = $this->get_pilot_ids($subscriber);

        // show flight type first
        $this->add_element($mform, 'flighttype', array($subscriber->trainingtype == 'Dual'));
        $this->add_element($mform, 'passfail');

        // show pireps only if they there is lookup integration or a PIREP for editing
        if ($integratedpireps || !$newlogentry)
            $this->add_element($mform, 'pireps', array($integratedpireps, $newlogentry));

        // add primary elements
        $this->add_element($mform, 'flightdate', array($flightdate));
        $this->add_element($mform, 'p1id', array($pilots, $p1id));
        $this->add_element($mform, 'p2id', array($pilots, $p2id));

        $this->add_element($mform, 'groundtime');
        $this->add_element($mform, 'pictime');

        // add elements for time fields
        $this->add_element($mform, 'instructortime');
        $this->add_element($mform, 'dualtime');
        $this->add_element($mform, 'multipilottime');
        $this->add_element($mform, 'copilottime');
        $this->add_element($mform, 'ifrtime');
        $this->add_element($mform, 'picustime');
        $this->add_element($mform, 'checkpilottime');

        // add secondary form elements (more...)
        // add PIREP group if there's no integration here
        if (!$integratedpireps)
            $this->add_element($mform, 'pireps', null, false);

        // default to 30 hr/mins from session time for departure and 1:30 hr/mins from session time for arrival
        $defaultdepttime = logbook::convert_time(($flightdate + (30 * 60)), 'TS_TO_TIME');
        $defaultarrttime = logbook::convert_time(($flightdate + (90 * 60)), 'TS_TO_TIME');
        $this->add_element($mform, 'departure', array($subscriber->homeicao, $defaultdepttime), false);
        $this->add_element($mform, 'arrival', array($subscriber->homeicao, $defaultarrttime), false);
        $this->add_element($mform, 'aircraft', array($subscriber->aircrafticao, [
            'SE'=>get_string('logbooksedesc', 'local_booking'),
            'ME'=>get_string('logbookmedesc', 'local_booking')
            ], $this->get_enginetype($subscriber)), false);
        $this->add_element($mform, 'nighttime', null, false);
        // add landings elements for new and edit logentries
        $this->add_element($mform, 'landingsp1', null, false);
        if ($newlogentry)
            $this->add_element($mform, 'landingsp2', null, false);

        // add remaining elements
        $this->add_element($mform, 'callsign', array($this->get_pilot_info('callsign', $subscriber->get_participant($p1id))), false);
        $this->add_element($mform, 'remarks', null, false);
        $this->add_element($mform, 'fstd', array($this->get_pilot_info('simulator', $subscriber->get_participant($p2id))), false);
    }

    /**
     * Set the passed element in the form.
     *
     * @param MoodleQuickForm $mform
     * @param string $element     The element to be set in the form
     * @param mixed  $options     Optional array of configuration data
     * @param bool   $maindisplay Whether to display the element in the main form
     * @param bool   $input       Whether the element is an input or static (label) element
     * @param mixed  $value       The value of a static element
     */
    protected function add_element($mform, $element, $options = null, $maindisplay = true, $input = true, $value = null) {

        switch ($element) {
            case 'flightdate':
                // Flight date field.
                $mform->addElement('date_time_selector', 'flightdate', get_string('flightdate', 'local_booking'), array('timezone'=>0));
                $mform->addRule('flightdate', get_string('required'), 'required', null, 'client');
                $mform->setType('flightdate', PARAM_TEXT);
                $mform->setDefault('flightdate', $options[0]);
                break;

            case 'p1id':
                // P1 name select
                $select = $mform->addElement('select', 'p1id', get_string('p1', 'local_booking'), $options[0]);
                $select->setSelected($options[1]);
                $mform->setType('p1id', PARAM_INT);
                $mform->addRule('p1id', get_string('required'), 'required', null, 'client');
                $mform->addHelpButton('p1id', 'p1', 'local_booking');
                break;

            case 'p2id':
                // P2 name select
                $select = $mform->addElement('select', 'p2id', get_string('p2', 'local_booking'), $options[0]);
                $select->setSelected($options[1]);
                $mform->setType('p2id', PARAM_INT);
                $mform->addRule('p2id', get_string('required'), 'required', null, 'client');
                $mform->addHelpButton('p2id', 'p2', 'local_booking');
                break;

            case 'flighttype':
                // Solo flight indicator
                $radioarray=array();
                $radioarray[] = $mform->createElement('radio', 'flighttype', '', get_string('flighttypetraining', 'local_booking'), 'training');
                // Add solo flight option if the training type is Dual
                if ($options[0])
                    $radioarray[] = $mform->createElement('radio', 'flighttype', '', get_string('flighttypesolo', 'local_booking'), 'solo');
                $radioarray[] = $mform->createElement('radio', 'flighttype', '', get_string('flighttypecheck', 'local_booking'), 'check');
                $mform->addGroup($radioarray, 'flighttype', get_string('flighttype', 'local_booking'), array(' '), false);
                $mform->setType('flighttype', PARAM_TEXT);
                $mform->setDefault('flighttype', 'training');
                break;

            case 'passfail':
                // Solo flight indicator
                $radioarray=array();
                $radioarray[] = $mform->createElement('radio', 'passfail', '', get_string('checkpassed', 'local_booking'), 'pass');
                $radioarray[] = $mform->createElement('radio', 'passfail', '', get_string('checkfailed', 'local_booking'), 'fail');
                $mform->addGroup($radioarray, 'passfail',  get_string('checkflight', 'local_booking'), array(' '), false);
                $mform->setType('passfail', PARAM_TEXT);
                $mform->setDefault('passfail', 'pass');
                break;

            case 'groundtime':
                // Session flight time duration
                $mform->addElement('text', 'groundtime', get_string('groundtime', 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->addRule('groundtime', get_string('required'), 'required', null, 'client');
                $mform->addHelpButton('groundtime', 'groundtime', 'local_booking');
                $mform->setType('groundtime', PARAM_TEXT);
                break;

            case 'pictime':
                // PIC flight time duration
                if ($input) {
                    $attributes = array('size' => '5', 'placeholder' => 'hh:mm');
                    $mform->addElement('text', 'pictime', get_string('pictime', 'local_booking'), $attributes);
                    $mform->addRule('pictime', get_string('required'), 'required', null, 'client');
                    $mform->addHelpButton('pictime', 'pictime', 'local_booking');
                    $mform->setType('pictime', PARAM_TEXT);
                } else $mform->addElement('static', 'description', get_string('pictime', 'local_booking'), $value);
                break;

            case 'instructortime':
                // Instructor flight time duration
                $mform->addElement('text', 'instructortime', get_string('instructortime', 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->setType('instructortime', PARAM_TEXT);
                $mform->addHelpButton('instructortime', 'instructortime', 'local_booking');
                break;

            case 'picustime':
                // PICUS flight time duration
                $mform->addElement('text', 'picustime', get_string('picustime', 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->setType('picustime', PARAM_TEXT);
                $mform->addHelpButton('picustime', 'picustime', 'local_booking');
                if (!$maindisplay)
                    $mform->setAdvanced('picustime');
                break;

            case 'dualtime':
                // Dual flight time duration
                $mform->addElement('text', 'dualtime', get_string('dualtime', 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->addHelpButton('dualtime', 'dualtime', 'local_booking');
                $mform->setType('dualtime', PARAM_TEXT);
                break;

            case 'multipilottime':
                // Multi-pilot flight time duration
                $mform->addElement('text', 'multipilottime', get_string('multipilottime', 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->setType('multipilottime', PARAM_TEXT);
                $mform->addHelpButton('multipilottime', 'multipilottime', 'local_booking');
                break;

            case 'copilottime':
                // Co-pilot flight time duration
                $mform->addElement('text', 'copilottime', get_string('copilottime', 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->setType('copilottime', PARAM_TEXT);
                $mform->addHelpButton('copilottime', 'copilottime', 'local_booking');
                break;

            case 'pireps':
                // PIREPs group P1 & P2
                // Show integrated PIREP lookup (options[0])
                if ($options[0]) {
                    // Show P1 PIREP vs PIREP depending on whether it's a new entry or not (options[1])
                    $mform->addElement('text', 'p1pirep', get_string(($options[1] ? 'instpirep' : 'pirep'), 'local_booking'));
                    $mform->addRule('p1pirep', get_string('err_numeric', 'form'), 'numeric', null, 'client');
                    if ($options[1])
                        $mform->addHelpButton('p1pirep', 'instpirep', 'local_booking');
                } else {
                    $pireps=array();
                    $pireps[] =& $mform->createElement('text', 'p1pirep', get_string('p1pirep', 'local_booking'));
                    $pireps[] =& $mform->createElement('text', 'p2pirep', get_string('p2pirep', 'local_booking'));
                    $mform->addGroup($pireps, 'pireps', get_string('pirepsgroup', 'local_booking'), '   ', false);
                    $mform->addGroupRule('pireps', array('p1pirep' => array(array(get_string('err_numeric', 'form'), 'numeric', null, 'client'))));
                    $mform->addGroupRule('pireps', array('p2pirep' => array(array(get_string('err_numeric', 'form'), 'numeric', null, 'client'))));
                }
                if (!$maindisplay)
                    $mform->setAdvanced('pireps');
                break;

            case 'departure':
                // Departure group ICAO/time
                $departure=array();
                $departure[] =& $mform->createElement('text', 'depicao', get_string('depicao', 'local_booking'), 'style="text-transform:uppercase" size="8"');
                $departure[] =& $mform->createElement('text', 'deptime', get_string('deptime', 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->addGroup($departure, 'departure', get_string('depgroup', 'local_booking'), ' ', false);
                $mform->setDefault('depicao', $options[0]);
                $mform->setDefault('deptime', $options[1]);
                if (!$maindisplay)
                    $mform->setAdvanced('departure');
                break;

            case 'arrival':
                // Arrival group ICAO/time
                $arrival=array();
                $arrival[] =& $mform->createElement('text', 'arricao', get_string('arricao', 'local_booking'), 'style="text-transform:uppercase" size="8"');
                $arrival[] =& $mform->createElement('text', 'arrtime', get_string('arrtime', 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->addGroup($arrival, 'arrival', get_string('arrgroup', 'local_booking'), ' ', false);
                $mform->setDefault('arricao', $options[0]);
                $mform->setDefault('arrtime', $options[1]);
                if (!$maindisplay)
                    $mform->setAdvanced('arrival');
                break;

            case 'aircraft':
                // Aircraft group type, registration, and engine type
                $aircraft=array();
                $aircraft[] =& $mform->createElement('select', 'aircraft', get_string('aircraft', 'local_booking'), $options[0]);
                $aircraft[] =& $mform->createElement('text', 'aircraftreg', get_string('aircraftreg', 'local_booking'), 'style="text-transform:uppercase" size="8"');
                $aircraft[] =& $mform->createElement('select', 'enginetype', get_string('enginetype', 'local_booking'), $options[1]);
                $mform->setDefault('enginetype', $options[2]);
                $mform->addGroup($aircraft, 'aircraft', get_string('aircraftgroup', 'local_booking'), ' ', false);
                $mform->addHelpButton('aircraft', 'aircraft', 'local_booking');
                if (!$maindisplay)
                    $mform->setAdvanced('aircraft');
                break;

            case 'nighttime':
                // Night flight time duration
                $mform->addElement('text', 'nighttime', get_string('nighttime', 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->setType('nighttime', PARAM_TEXT);
                $mform->addHelpButton('nighttime', 'nighttime', 'local_booking');
                if (!$maindisplay)
                    $mform->setAdvanced('nighttime');
                break;

            case 'ifrtime':
                // IFR flight time duration
                $mform->addElement('text', 'ifrtime', get_string('ifrtime', 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->setType('ifrtime', PARAM_TEXT);
                $mform->addHelpButton('ifrtime', 'ifrtime', 'local_booking');
                if (!$maindisplay)
                    $mform->setAdvanced('ifrtime');
                break;

            case 'checkpilottime':
                // Examiner flight time duration
                $mform->addElement('text', 'checkpilottime', get_string('checkpilottime', 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->setType('checkpilottime', PARAM_TEXT);
                $mform->addHelpButton('checkpilottime', 'checkpilottime', 'local_booking');
                if (!$maindisplay)
                    $mform->setAdvanced('checkpilottime');
                break;

            case 'landingsp1':
            case 'landingsp2':
                // Landings group Day/Night
                $landings=array();
                $landings[] =& $mform->createElement('text', $element . 'day', '', 'size="3"');
                $landings[] =& $mform->createElement('text', $element . 'night', '', 'size="3"');
                $mform->addGroup($landings, $element, get_string($element, 'local_booking'), '   ', false);
                $mform->addGroupRule($element, array($element . 'day' => array(array(get_string('errorlandings', 'local_booking'), 'numeric', null, 'client'))));
                $mform->addGroupRule($element, array($element . 'night' => array(array(get_string('errorlandings', 'local_booking'), 'numeric', null, 'client'))));
                $mform->setType($element . 'day', PARAM_INT);
                $mform->setDefault($element . 'day', $element=='landingsp2' ? 1 : null);
                $mform->setType($element . 'night', PARAM_INT);
                // $mform->setDefault($element . 'night', 0);
                $mform->addHelpButton($element, $element, 'local_booking');
                if (!$maindisplay)
                    $mform->setAdvanced($element);
                break;

            case 'callsign':
                // Callsign advanced element
                $mform->addElement('text', 'callsign', get_string('callsign', 'local_booking'), 'style="text-transform:uppercase" ');
                $mform->setType('callsign', PARAM_TEXT);
                $mform->setDefault('callsign', $options[0]);
                if (!$maindisplay)
                    $mform->setAdvanced('callsign');
                break;

            case 'remarks':
                // Remarks advanced element
                $mform->addElement('textarea', 'remarks', get_string("remarks", "local_booking"), 'wrap="virtual" rows="20" cols="50"');
                $mform->setType('remarks', PARAM_TEXT);
                if (!$maindisplay)
                    $mform->setAdvanced('remarks');
                break;

            case 'fstd':
                // FSTD advanced element
                $mform->addElement('text', 'fstd', get_string('fstd', 'local_booking'), 'style="text-transform:uppercase" ');
                $mform->setType('fstd', PARAM_TEXT);
                $mform->addHelpButton('fstd', 'fstd', 'local_booking');
                $mform->setDefault('fstd', $options[0]);
                if (!$maindisplay)
                    $mform->setAdvanced('fstd');
                break;
        }
    }

    /**
     * Add the list of hidden elements that should appear in this form each
     * time. These elements will never be visible to the user.
     *
     * @param MoodleQuickForm $mform
     */
    protected function add_default_hidden_elements($mform, $trainingtype) {

        // Add some hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

        $mform->addElement('hidden', 'linkedlogentryid');
        $mform->setType('linkedlogentryid', PARAM_INT);
        $mform->setDefault('linkedlogentryid', 0);

        $mform->addElement('hidden', 'linkedpirep');
        $mform->setType('linkedpirep', PARAM_INT);
        $mform->setDefault('linkedpirep', 0);

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        $mform->setDefault('userid', 0);

        $mform->addElement('hidden', 'exerciseid');
        $mform->setType('exerciseid', PARAM_INT);
        $mform->setDefault('exerciseid', 0);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', 0);

        $mform->addElement('hidden', 'contextid');
        $mform->setType('contextid', PARAM_INT);
        $mform->setDefault('contextid', 0);

        $mform->addElement('hidden', 'trainingtype');
        $mform->setType('trainingtype', PARAM_TEXT);
        $mform->setDefault('trainingtype', $trainingtype);

        $mform->addElement('hidden', 'visible');
        $mform->setType('visible', PARAM_INT);
        $mform->setDefault('visible', 1);
    }

    /**
     * Get the list of active user pilot ids.
     *
     * @param subscriber $course   The subscriber course
     * @return array $activepilots List of user ids for P1 & P2 pilots
     */
    protected function get_pilot_ids($course) {
        $pilots = $course->get_participants();

        foreach ($pilots as $pilot) {
            $activepilots[$pilot->userid] = $pilot->fullname;
        }

        return $activepilots;
    }

    /**
     * Get pilot information depending on the profile
     * information requested.
     *
     * @param string      $infotype The type of pilot information
     * @param participant $pilot    The pilot user
     * @return string    $callsign The instructor's callsign
     */
    protected function get_pilot_info($infotype, $pilot) {
        $info = '';

        switch ($infotype) {
            case 'callsign':
                $info = $pilot->get_callsign();
                break;
            case 'simulator':
                $info = $pilot->get_simulator();
                break;
        }
        return $info;
    }

    /**
     * Get the engine type of the default aircraft
     *
     * @param subscriber $course   The subscriber course
     * @return array $enginetype The engine type of the default aircraft
     */
    protected function get_enginetype($course) {
        $enginetype = 'SE';

        if (!empty($course->aircrafticao)) {
            $aircrafticao = current($course->aircrafticao);

            if ($course->has_integration('aircraft')) {
                $engintyperec = subscriber::get_integrated_data('aircraft', 'aircraftinfo', $aircrafticao);
                $enginetype = $engintyperec['engine_type'] == 'single' ? 'SE' : 'ME';
            }
        }

        return $enginetype;
    }
}
