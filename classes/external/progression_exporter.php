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
 * Contains event class for displaying the week view.
 *
 * @package   local_booking
 * @copyright 2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use local_booking\external\student_exporter;
use core\external\exporter;
use renderer_base;
use moodle_url;

/**
 * Class for displaying the week view.
 *
 * @package   local_booking
 * @copyright 2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class progression_exporter extends exporter {

    /**
     * Process user enrollments table name.
     */
    const DB_USER = 'user';

    /**
     * Process user enrollments table name.
     */
    const DB_USER_ENROL = 'user_enrolments';

    /**
     * Process  enrollments table name.
     */
    const DB_ENROL = 'enrol';

    /**
     * @var int $categoyid An id of the category context objects.
     */
    protected $categoyid;

    /**
     * @var array $exercisenames An array of excersice ids and names for the course.
     */
    protected $exercisenames = [];

    /**
     * @var array $activestudents An array of active users objects.
     */
    protected $activestudents = [];

    /**
     * @var moodle_url $url The URL for the events page.
     */
    protected $url;

    /**
     * Constructor.
     *
     * @param mixed $data An array of student progress data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {

        $this->url = new moodle_url('/local/booking/view.php', [
                'time' => time(),
            ]);

        $data = ['url' => $this->url->out(false)];

        parent::__construct($data, $related);
    }

    protected static function define_properties() {
        return [
            'url' => [
                'type' => PARAM_URL,
            ],
            'courseid' => [
                'type' => PARAM_INT,
            ],
            'categoryid' => [
                'type' => PARAM_INT,
                'optional' => true,
                'default' => 0,
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
            'exercisenames' => [
                'type' => PARAM_RAW,
                'multiple' => true,
            ],
            'activestudents' => [
                'type' => student_exporter::read_properties_definition(),
                'multiple' => true,
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
        $this->exercisenames = $this->get_exercisenames();
        $return = [
            'exercises' => $this->exercisenames,
            'activestudents' => $this->get_activestudents($output),
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
     * Get the list of day names for display, re-ordered from the first day
     * of the week.
     *
     * @param   renderer_base $output
     * @return  day_name_exporter[]
     */
    protected function get_exercisenames() {
        global $DB;

        $names = [];
        $exercises = $DB->get_records('mdl_assign', array('course'=>$this->data->courseid));

        foreach ($exercises as $exercisename) {
            $names['id'] = $exercisename->id;
            $names['shortname'] = $exercisename->name;
        }

        return $names;
    }

    /**
     * Get the list of day names for display, re-ordered from the first day
     * of the week.
     *
     * @param   renderer_base $output
     * @return  day_name_exporter[]
     */
    protected function get_activestudents($output) {
        global $DB;

        $activestudents = [];

        $sql = 'SELECT us.id AS userid, ' . $DB->sql_concat('us.firstname', 'us.lastname') . ' AS fullname
                FROM {' . self::DB_ENROL . '} en
                INNER JOIN {' . self::DB_USER_ENROL . '} ue on en.id = ue.enrolid
                INNER JOIN {' . self::DB_USER . '} us on ue.enrolid = us.id
                ORDER BY enrolid ASC';

        $students = $DB->get_records_sql($sql);

        $i = 0;
        foreach ($students as $student) {
            $i++;
            $data = [];
            $data[] = [
                'courseid' => $this->data->courseid,
                'exercises' => $this->exercises,
                'studentid' => $student->userid,
                'studentname' => $student->fullname,
                'sequence' => $i,
            ];
            $activestudents[] = (new student_exporter($data, $this->related))->export($output);
        }

        return $activestudents;
    }
}
