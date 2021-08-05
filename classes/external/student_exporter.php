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

use local_booking\external\exercise_exporter;
use core\external\exporter;
use renderer_base;
use moodle_url;

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
    const DB_GRADES = 'grade_grades';

    /**
     * Process user enrollments table name.
     */
    const DB_GRADE_ITEMS = 'grade_items';

    /**
     * Process user  table name.
     */
    const DB_USER = 'user';

    /**
     * Constructor.
     *
     * @param mixed $data An array of student data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {
        parent::__construct($data, $related);
    }

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'studentuserid' => [
                'type' => PARAM_INT,
            ],
            'studentname' => [
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
            'exercises' => [
                'type' => exercise_exporter::read_properties_definition(),
                'multiple' => true,
            ],
            'actionbuttonname' => [
                'type' => PARAM_RAW,
                ],
            'actionbuttonurl' => [
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
        $exercises = $this->get_exercises($output);
        $action = $this->get_action();

        $return = [
            'exercises' => $exercises,
            'actionbuttonname' => $action->name,
            'actionbuttonurl' => $action->url,
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
            'context' => 'context',
        );
    }

    /**
     * Get the list of days
     * of the week.
     *
     * @return  $exercises[]
     */
    protected function get_exercises($output) {
        $type = \core_calendar\type_factory::get_calendar_instance();

        $studentgrades = $this->get_studentgrades();

        $data = [];
        $exercises = [];
        foreach ($this->data->exercisenames as $exercise) {
            $graded = false;
            // Find out if this exercise has been graded
            if (array_search($this->exerciseid, array_column($studentgrades, 'exerciseid'))) {
                $graded = true;
                $data[] = [
                    'grade'             => $studentgrades[0]->finalgrade,
                    'instructorfullname'=> $studentgrades[0]->instructorfullname,
                    'gradedate'         => $type->timestamp_to_date_array($studentgrades[0]->timemodified),
                ];
            }

            $data[] = [
                'graded'            => $graded,
                'courseid'          => $this->courseid,
                'studentid'         => $this->$data->studentid,
                'exercises'         => $this->data->exercisenames,
                'exerciseid'        => $exercise->id,
            ];

            $exercisesession = new exercise_exporter($data, $this->related);
            $exercises[] = $exercisesession->export($output);
            }

        return $exercises;
    }

    /**
     * Retrieves the action object containing
     * actino name and url
     *
     * @return {Object}
     */
    protected function get_action() {
        $actionname = get_string('grade', 'grades');
        $actionurl = new moodle_url('/mod/assign/view.php', [
            'id' => time(),
            'rownum' => 0,
            'action' => 'grader',
            'userid' => $this->data->studentid,
        ]);

        return ['name' => $actionname, 'url' => $actionurl];
    }

    /**
     * Checks if the slot date is out
     * of week lookahead bounds
     *
     * @return {Object}[]
     */
    protected function get_studentgrades() {
        global $DB;

        // Get the student's grades
        $sql = 'SELECT gi.iteminstance AS exerciseid, gr.finalgrade, ' . $DB->sql_concat('us.firstname', 'us.lastname') .
                    ' AS instructorfullname, gr.timemodified
                FROM {' . self::DB_GRADES . '} gr
                INNER JOIN {' . self::DB_GRADE_ITEMS . '} gi on gr.itemid = gi.id
                INNER JOIN {' . self::DB_USER . '} us on gr.usermodified = us.id
                WHERE gi.courseid = ' . $this->data->courseid . ', gr.userid = ' . $this->data->studentid;

        $grades = $DB->get_records_sql($sql);

        return $grades;
    }
}
