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
     * @var int $course id A course id to be rendered.
     */
    protected $courseid;

    /**
     * @var int $student id A user id to be rendered.
     */
    protected $studentid;

    /**
     * @var int $student name A user fullname to be rendered.
     */
    protected $studentname;

    /**
     * @var int $sequence id A sequence number for each student to be rendered.
     */
    protected $sequence;

    /**
     * @var array $actions An array for the action.
     */
    protected $actions = [];

    /**
     * Constructor.
     *
     * @param \calendar_information $calendar The calendar information for the period being displayed
     * @param mixed $data Either an stdClass or an array of values.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {
        $this->courseid     = $data->courseid;
        $this->exerciscount = $data->exerciscount;
        $this->studentid    = $data->studentid;
        $this->studentname  = $data->studentname;
        $this->sequence     = $data->sequence;

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
                'type' => student_exporter::read_properties_definition(),
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
        $action = $this->get_action();

        $return = [
            'exercises' => $this->get_exercises($output),
            'actionbuttonname' => $action->name,
            'actionbuttonurl' => $action->url,
        ];

        return $return;
    }

    /**
     * Get the list of days
     * of the week.
     *
     * @return  $days[]
     */
    protected function get_exercises($output) {
        $exercises = [];

        for ($i = 0; $i < $this->exerciscount; $i++) {
            // get week day exporter based on a timestamp that matches the time slot for each day of the week
            $slotdaydata = $type->timestamp_to_date_array(gmmktime($this->hour, 0, 0, $daydata['mon'], $daydata['mday'], $daydata['year']));
            $slotdaydata['istoday'] = $this->is_today($daydata);
            $slotdaydata['isweekend'] = $this->is_weekend($daydata);
            $slotdaydata['available'] = !$this->is_slot_unavailable($daydata);
            $slotdaydata['marked'] = $this->is_marked($slotdaydata);
            $slotdaydata['bookedstatus'] = $this->get_status($slotdaydata);

            $exercise = new exercise_exporter($this->calendar, $slotdaydata, [
                'events' => $events,
                'cache' => $this->related['cache'],
                'type' => $this->related['type'],
            ]);

            $exercises[] = $exercise->export($output);
        }

        return $exercises;

    }

    /**
     * Checks if the slot date is out
     * of week lookahead bounds
     *
     * @return  {Object}
     */
    protected function get_action() {
        $actionname = get_string('grade', 'grades');
        $actionurl = new moodle_url('/mod/assign/view.php', [
            'id' => time(),
            'rownum' => 0,
            'action' => 'grader',
            'userid' => $this->studentid,
        ]);

        return ['name' => $actionname, 'url' => $actionurl];
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
}
