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
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

require_once($CFG->dirroot . '/lib.php');
require_once($CFG->dirroot . '/lib/completionlib.php');

defined('MOODLE_INTERNAL') || die();

use local_booking\external\session_exporter;
use local_booking\local\session\entities\action;
use core\external\exporter;
use local_booking\local\session\data_access\booking_vault;
use local_booking\local\slot\data_access\student_vault;
use renderer_base;

/**
 * Class for displaying each student row in progression view.
 *
 * @package   local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class student_exporter extends exporter {

    /**
     * @var int $studentid An id of the student.
     */
    protected $courseid;

    /**
     * @var int $studentid A user of the student.
     */
    protected $studentid;

    /**
     * @var array $courseexercises An array of the course exercises.
     */
    protected $courseexercises;

    /**
     * @var array $studentgrades An array of the student's grades.
     */
    protected $studentgrades;

    /**
     * @var booking_vault $bookingvault A vault to access booking data.
     */
    protected $bookingvault;

    /**
     * @var student_vault $studentvault A vault to access student data.
     */
    protected $studentvault;

    /**
     * Constructor.
     *
     * @param mixed $data An array of student data.
     * @param array $related Related objects.
     */
    public function __construct($data, $courseid, $related) {
        $this->courseid = $courseid;
        $this->studentid = $data['studentid'];
        $this->courseexercises = $related['courseexercises'];
        $this->bookingvault = new booking_vault();
        $this->studentvault = new student_vault();

        parent::__construct($data, $related);
    }

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'sequence' => [
                'type' => PARAM_INT,
            ],
            'sequencetooltip' => [
                'type' => PARAM_RAW,
            ],
            'studentid' => [
                'type' => PARAM_INT,
            ],
            'studentname' => [
                'type' => PARAM_RAW,
            ],
            'simulator' => [
                'type' => PARAM_RAW,
            ],
        ];
    }

    /**
     * Return the list of additional properties.
     *
     * @return array
     */
    protected static function define_other_properties() {
        return [
            'sessions' => [
                'type' => session_exporter::read_properties_definition(),
                'multiple' => true,
            ],
            'actionurl' => [
                'type' => PARAM_URL,
            ],
            'actiontype' => [
                'type' => PARAM_RAW,
            ],
            'actionname' => [
                'type' => PARAM_RAW,
            ],
            'actionbook' => [
                'type' => PARAM_BOOL,
            ],
            'lessonincomplete' => [
                'type' => PARAM_BOOL,
            ],
        ];
    }

    /**
     * Get the additional values to inject while exporting.
     *
     * @param renderer_base $output The renderer.
     * @return array Keys are the property names, values are their values.
     */
    protected function get_other_values(renderer_base $output) {
        $hasincompletelessons = false;
        $sessions = $this->get_sessions($output);
        $action = $this->get_next_action();

        // check if the student to be book has incomplete lessons
        if ($action->get_type() == 'book') {
            $hasincompletelessons = !has_completed_lessons($this->studentid);
            if ($hasincompletelessons) { $action->set_type('disabled'); }
        }

        $return = [
            'sessions'         => $sessions,
            'actionurl'        => $action->get_url()->out(false),
            'actiontype'       => $action->get_type(),
            'actionname'       => $action->get_name(),
            'actionbook'       => $action->get_type() == 'book',
            'lessonincomplete' => $hasincompletelessons,
        ];

        return $return;
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return array(
            'context'=>'context',
            'courseexercises'=>'stdClass[]?',
        );
    }

    /**
     * Get the list of sessions for the course.
     *
     * @return  $submissions[]
     */
    protected function get_sessions($output) {
        $this->studentgrades = $this->studentvault->get_grades($this->studentid);

        foreach ($this->courseexercises as $exercise) {
            $studentinfo = [];
            $studentinfo = [
                'studentid'   => $this->studentid,
                'studentname' => $this->data['studentname'],
                'courseid'    => $this->courseid,
                'exerciseid'  => $exercise->exerciseid,
                'grades'      => $this->studentgrades,
                'booking'     => $this->bookingvault->get_student_booking($this->studentid),
            ];
            $exercisesession = new session_exporter($studentinfo, $this->related);
            $sessions[] = $exercisesession->export($output);
        }

        return $sessions;
    }

    /**
     * Retrieves the action object containing
     * action name and url. Next action for
     * students with booked session is a grading
     * action, otherwise it is a booking action
     *
     * @param  bool  $disable create a disabled action
     * @return {Object}
     */
    protected function get_next_action() {
        $exercisevalues = array_values($this->courseexercises);

        // find the next exercise for the student
        if (count($this->studentgrades) > 0) {
            $lastexercise = end($this->studentgrades)->exerciseid;
            $exerciseid = $exercisevalues[array_search($lastexercise, array_column($exercisevalues, 'exerciseid'))+1]->exerciseid;
        } else {
            $exerciseid = array_shift($exercisevalues)->exerciseid;
        }

        // next action depends if the student has any booking
        $hasbooking = !empty($this->bookingvault->get_student_booking($this->studentid));
        $actiontype = $hasbooking ? 'grade' : 'book';
        $action = new action($actiontype, $this->studentid, $exerciseid);

        return $action;
    }
}
