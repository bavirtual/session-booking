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

use core\external\exporter;
use local_booking\local\session\data_access\booking_vault;
use local_booking\local\participant\data_access\participant_vault;
use local_booking\local\session\entities\priority;
use renderer_base;
use moodle_url;
use DateTime;

/**
 * Class for displaying instructor's booked sessions view.
 *
 * @package   local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bookings_exporter extends exporter {

    /**
     * Warning flag of an overdue session
     */
    const OVERDUEWARNING = 1;

    /**
     * Warning flag of a late session past overdue
     */
    const LATEWARNING = 2;

    /**
     * @var array $exercisenames An array of excersice ids and names for the course.
     */
    protected $exercisenames = [];

    /**
     * Constructor.
     *
     * @param mixed $data An array of student progress data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {

        $url = new moodle_url('/local/booking/view.php', [
                'courseid' => $data['courseid'],
                'time' => time(),
            ]);

        $data['url'] = $url->out(false);

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
                'type' => booking_student_exporter::read_properties_definition(),
                'multiple' => true,
            ],
            'activebookings' => [
                'type' => booking_exporter::read_properties_definition(),
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
            'exercisenames'  => $this->get_exercises($output),
            'activestudents' => $this->get_active_students($output),
            'activebookings' => $this->get_bookings($output),
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

    /**
     * Get the list of day names for display, re-ordered from the first day
     * of the week.
     *
     * @param   renderer_base $output
     * @return  student_exporter[]
     */
    protected function get_active_students($output) {
        $activestudents = [];

        $vault = new participant_vault();
        $students = $this->prioritze($vault->get_active_students());

        $i = 0;
        foreach ($students as $student) {
            $i++;
            $sequencetooltip = [
                'score'     => $student->priority->get_score(),
                'recency'   => $student->priority->get_recency_days(),
                'slots'     => $student->priority->get_slot_count(),
                'activity'  => $student->priority->get_activity_count(false),
                'completion'=> $student->priority->get_completions(),
            ];

            $waringflag = $this->get_warning($student->userid);
            $data = [];
            $data = [
                'sequence'        => $i,
                'sequencetooltip' => get_string('sequencetooltip', 'local_booking', $sequencetooltip),
                'studentid'       => $student->userid,
                'studentname'     => $student->fullname,
                'dayssincelast'   => $student->dayssincelast,
                'overduewarning'  => $waringflag == self::OVERDUEWARNING,
                'latewarning'     => $waringflag == self::LATEWARNING,
                'simulator'       => $student->simulator,
            ];
            $student = new booking_student_exporter($data, $this->data['courseid'], [
                'context' => \context_system::instance(),
                'courseexercises' => $this->exercisenames,
            ]);
            $activestudents[] = $student->export($output);
        }

        return $activestudents;
    }

    /**
     * Prioritize the list of active students
     * based on highest scores.
     *
     * @param   array   $activestudents
     */
    protected function prioritze($activestudents) {
        // Get student booking priority
        foreach ($activestudents as $student) {
            $priority = new priority($student->userid);
            $student->priority = $priority;
            $student->dayssincelast = $priority->get_recency_days();
        }

        usort($activestudents, function($st1, $st2) {
            return $st1->priority->get_score() < $st2->priority->get_score();
        });

        return $activestudents;
    }

    /**
     * Get the list of all instructor bookings
     * of the week.
     *
     * @param   renderer_base $output
     * @return  booking_exporter[]
     */
    protected function get_bookings($output) {
        $bookings = [];

        $vault = new booking_vault();
        $bookingobjs = $vault->get_bookings(true);
        foreach ($bookingobjs as $bookingobj) {
            $data = [
                'booking' => $bookingobj,
            ];
            $booking = new booking_exporter($data, $this->related);
            $bookings[] = $booking->export($output);
        }

        return $bookings;
    }

    /**
     * Get a warning flag related to
     * when the student took the last
     * session 3x wait is overdue, and
     * 4x wait is late.
     *
     * @param   int $studentid  The student id
     * @return  int $flag       The delay flag
     */
    protected function get_warning($studentid) {
        $bookingvault = new booking_vault();
        $warning = 0;
        $today = getdate(time());
        $waitdays = get_config('local_booking', 'nextsessionwaitdays') ? get_config('local_booking', 'nextsessionwaitdays') : LOCAL_BOOKING_DAYSFROMLASTSESSION;

        // get days since last session
        $lastsession = $bookingvault->get_last_booked_session(false, $studentid);
        $lastsessiondate = new DateTime('@' . (!empty($lastsession) ? $lastsession->lastbookedsession : time()));
        $interval = $lastsessiondate->diff(new DateTime('@' . $today[0]));
        $dayssincelast = $interval->format('%d');

        if ($dayssincelast >= ($waitdays * LOCAL_BOOKING_SESSIONOVERDUEMULTIPLIER) &&  $dayssincelast < ($waitdays * LOCAL_BOOKING_SESSIONLATEMULTIPLIER)) {
            $warning = self::OVERDUEWARNING;
        } else if ($dayssincelast >= ($waitdays * LOCAL_BOOKING_SESSIONLATEMULTIPLIER)) {
            $warning = self::LATEWARNING;
        }

        return $warning;
    }
}
