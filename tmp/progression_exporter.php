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

defined('MOODLE_INTERNAL') || die();

use local_booking\external\exercise_name_exporter;
use local_booking\external\student_exporter;
use core\external\exporter;
use local_booking\local\slot\data_access\student_vault;
use renderer_base;
use moodle_url;

/**
 * Class for displaying students session progression view.
 *
 * @package   local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class progression_exporter extends exporter {

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

        $data['url'] = $this->url->out(false);

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
                'type' => exercise_name_exporter::read_properties_definition(),
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

        $return = [
            'exercisenames' => $this->get_exercises($output),
            'activestudents' => $this->get_active_students($output),
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
            'exercises' => 'stdClass[]?',
        );
    }

    /**
     * Get the list of day names for display, re-ordered from the first day
     * of the week.
     *
     * @param   renderer_base $output
     * @return  student_exporter[]
     */
    protected function get_active_students($output) {
        $activestudents = [];

        $vault = new student_vault();
        $students = $vault->get_active_students();

        $i = 0;
        foreach ($students as $student) {
            $i++;
            $data = [];
            $data = [
                'sequence' => $i,
                'studentid'   => $student->userid,
                'studentname' => $student->fullname,
                'simulator'   => $student->simulator,
            ];
            $student = new student_exporter($data, $this->data['courseid'], [
                'context' => \context_system::instance(),
                'courseexercises' => $this->exercisenames,
            ]);
            $activestudents[] = $student->export($output);
        }

        return $activestudents;
    }

    /**
     * Retrieves exercises for the course
     *
     * @return array
     */
    protected function get_exercises($output) {
        $this->exercisenames = get_exercise_names();

        // get titles from the plugin settings which should be delimited by comma
        $exercisetitles = explode(',', get_config('local_booking', 'exercisetitles'));

        $exerciseslabels = [];

        $i = 0;
        foreach($this->exercisenames as $name) {
            // break down each setting title by <br/> tag, until a better way is identified
            $titleitem = explode('<br/>', $exercisetitles[$i]);
            $name->title = $titleitem[0];
            $name->type = $titleitem[1];
            $data = [
                'exerciseid'    => $name->exerciseid,
                'exercisename'  => $name->exercisename,
                'exercisetitle' => $name->title,
                'exercisetype'  => $name->type,
            ];
            $i++;

            $exercisename = new exercise_name_exporter($data);
            $exerciseslabels[] = $exercisename->export($output);
        }

        return $exerciseslabels;
    }
}
