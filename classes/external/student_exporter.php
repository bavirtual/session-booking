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
 * Contains event class for displaying the day on month view.
 *
 * @package   local_booking
 * @copyright 2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use local_booking\external\session_exporter;
use local_booking\local\session\entities\action;
use core\external\exporter;
use renderer_base;

/**
 * Class for displaying the day on month view.
 *
 * @package   local_booking
 * @copyright 2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class student_exporter extends exporter {

    /**
     * Process user enrollments table name.
     */
    const DB_GRADES = 'assign_grades';

    /**
     * Process user enrollments table name.
     */
    const DB_ASSIGNMENTS = 'assign';

    /**
     * Process user  table name.
     */
    const DB_USER = 'user';

    /**
     * Process user enrollments table name.
     */
    const DB_BOOKINGS = 'local_booking';

    /**
     * @var int $studentid An id of the student.
     */
    protected $courseid;

    /**
     * Constructor.
     *
     * @param mixed $data An array of student data.
     * @param array $related Related objects.
     */
    public function __construct($data, $courseid, $related) {
        $this->courseid = $courseid;
        parent::__construct($data, $related);
    }

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'studentid' => [
                'type' => PARAM_INT,
            ],
            'studentname' => [
                'type' => PARAM_RAW,
            ],
            'simulator' => [
                'type' => PARAM_RAW,
            ],
            'sequence' => [
                'type' => PARAM_INT,
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
            'actiontype' => [
                'type' => PARAM_RAW,
                ],
            'actionurl' => [
                'type' => PARAM_URL,
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
        $sessions = $this->get_sessions($output);
        $action = $this->get_next_action();

        $return = [
            'sessions' => $sessions,
            'actionurl' => $action->get_url(),
            'actiontype' => $action->get_type(),
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
     * Get the list of days
     * of the week.
     *
     * @return  $submissions[]
     */
    protected function get_sessions($output) {

        $studentgrades = $this->get_student_grades();
        $courseexercises = $this->related['courseexercises'];

        foreach ($courseexercises as $exercise) {
            $studentinfo = [];
            $studentinfo = [
                'studentid'   => $this->data['studentid'],
                'studentname' => $this->data['studentname'],
                'courseid'    => $this->courseid,
                'exerciseid'  => $exercise->id,
                'grades'      => $studentgrades,
                'bookings'    => null,
            ];
            $exercisesession = new session_exporter($studentinfo, $this->related);
            $sessions[] = $exercisesession->export($output);
        }

        return $sessions;
    }

    /**
     * Checks if the slot date is out
     * of week lookahead bounds
     *
     * @return {Object}[]
     */
    protected function get_student_grades() {
        global $DB;

        // Get the student's grades
        $sql = 'SELECT a.id AS exerciseid, ag.grade, ag.grader as instructorid,
                    ' . $DB->sql_concat('us.firstname', '" "','us.lastname') .
                    ' AS instructorname, ag.timemodified
                FROM {' . self::DB_GRADES . '} ag
                INNER JOIN {' . self::DB_ASSIGNMENTS . '} a on ag.assignment = a.id
                INNER JOIN {' . self::DB_USER . '} us on ag.grader = us.id
                WHERE a.course = ' . $this->courseid . ' AND ag.userid = ' . $this->data['studentid'];

        return $DB->get_records_sql($sql);
    }

    /**
     * Retrieves the action object containing
     * action name and url. Next action for
     * students with booked session is a grading
     * action, otherwise it is a booking action
     *
     * @return {Object}
     */
    protected function get_next_action() {
        global $DB;

        $hasbookings = $DB->count_records(self::DB_BOOKINGS, ['studentid' => $this->data['studentid']]) > 0;
        $action = new action($hasbookings ? 'grade' : 'book', $this->data['studentid']);

        return $action;
    }
}
