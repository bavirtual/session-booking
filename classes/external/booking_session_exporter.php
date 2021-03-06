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
 * Class for displaying student sessions (assignments).
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use renderer_base;
use DateTime;
use core\external\exporter;
use local_booking\local\participant\entities\instructor;
use local_booking\local\session\entities\session;
use local_booking\local\session\entities\grade;
use local_booking\local\session\entities\booking;
use local_booking\local\slot\entities\slot;

/**
 * Class for displaying each session in progression view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking_session_exporter extends exporter {

    /**
     * @var student $student An object representing the student.
     */
    protected $student;

    /**
     * @var session $session An object containing session info.
     */
    protected $session;

    /**
     * Constructor.
     *
     * @param mixed $data An array of exercise data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {
        $this->student = $data['student'];

        if (!empty($this->session = $this->get_session($data)))
            $data = [
                'courseid'      => $this->student->get_courseid(),
                'studentid'     => $this->student->get_id(),
                'exerciseid'    => $data['exerciseid'],
                'sessionstatus' => $this->session->get_status(),
                'sessiondatets' => !empty($this->session->get_sessiondate()) ? ($this->session->get_sessiondate())->getTimestamp() : '',
                'sessionempty'  => empty($this->session),
                'sessiontooltip'=> $this->session->get_info(),
                'logentryid'    => !empty($this->session->get_logentry()) ? $this->session->get_logentry()->get_id() : 0,
            ];
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
                'default' => 0,
            ],
            'exerciseid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'sessionstatus' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'sessiondatets' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'sessionempty' => [
                'type' => PARAM_BOOL,
                'default' => true,
            ],
            'sessiontooltip' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'logentryid' => [
                'type' => PARAM_INT,
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
            'graded' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'passed' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'booked' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'tentative' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'checked' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'canlogentry' => [
                'type' => PARAM_BOOL,
                'default' => true,
            ],
            'logentrymissing' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'isquiz' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'lastbookingts' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'marknoposts' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'noposts' => [
                'type' => PARAM_RAW,
                'default' => '',
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
        $return = [];

        // get student posts
        list($nextexercise, $exercisesection) = $this->student->get_exercise(true);
        $noposts = $nextexercise == $this->data['exerciseid'] && $this->student->get_total_posts() == 0 &&
            $this->student->has_completed_lessons() ? get_string('bookingnoposts', 'local_booking') : '';

        if (!empty($this->session)) {
            $isquiz = $this->session->hasgrade() && $this->session->get_grade()->get_exercisetype() == 'quiz';
            $graded = $this->session->hasgrade();
            $passed = $this->session->haspassed() || $isquiz;
            $booked = $this->session->hasbooking() && $this->session->get_booking()->confirmed() && !$this->session->hasgrade();
            $tentative = $this->session->hasbooking() && !$this->session->get_booking()->confirmed() && !$this->session->hasgrade();
            $logentrymissing = empty($this->data['logentryid']) && $this->session->hasgrade() && $this->session->get_grade()->get_exercisetype() != 'quiz';
            $lastbookingdate = $logentrymissing ?
                slot::get_last_booking($this->data['courseid'], $this->student->get_id()) :
                $this->session->get_sessiondate()->getTimestamp();

            $return = [
                'graded'        => $graded,
                'passed'        => $passed,
                'booked'        => $booked,
                'tentative'     => $tentative,
                'canlogentry'   => $graded && !$isquiz,
                'logentrymissing' => $this->session->haspassed() && $logentrymissing,
                'isquiz'        => $isquiz,
                'lastbookingts' => $lastbookingdate
            ];
        }

        $return += [
            'marknoposts'   => !empty($noposts) && empty($this->session),
            'noposts'       => $noposts
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
     * Returns a new session from the passed data exercise id.
     *
     * @param array $data       The data array containing session information
     * @return session $session The current booking session
     */
    protected function get_session($data) {

        $grade = $this->find_grade($data['grades'], $data['exerciseid']);
        $booking = $this->find_booking($data['bookings'], $data['exerciseid']);
        $logentry = !empty($grade) ? $data['logbook']->get_logentry(0, $data['exerciseid']) : null;

        // collect session information
        $session = null;
        $sessiondate = null;
        $sessionstatus = '';
        $sessiontooltip = '';

        // get grade info of this session if available
        if ($grade !== null) {
            $sessiondate = new DateTime('@' . (!empty($booking) ? $booking->get_slot()->get_starttime() : $grade->get_gradedate()));
            $sessionstatus = 'graded';

            $gradeinfo = [
                'instructor'  => $grade->get_gradername(),
                'gradedate'   => (new \DateTime('@' . $grade->get_gradedate()))->format('j M \'y'),
                'sessiondate' => !empty($sessiondate) ? $sessiondate->format('j M \'y') . '<br/>' : '',
                'grade'       => intval($grade->get_finalgrade()) . (!empty($grade->get_totalgrade()) ? '/' . intval($grade->get_totalgrade()) : '')
            ];

            // get session tooltip for passing & progressing grades, and exam grades
            $sessiontooltip = $grade->get_exercisetype() == 'assign' ? ($grade->is_passinggrade() ? get_string('sessiongradedby', 'local_booking', $gradeinfo) :
                get_string('sessionprogressing', 'local_booking', $gradeinfo)) : get_string('sessiongradeexampass', 'local_booking', $gradeinfo);

        // get booking info of this session if a booking is available
        } else if (!empty($booking)) {
            $sessiondate = new DateTime('@' . $booking->get_slot()->get_starttime());
            $sessionstatus = $booking->confirmed() ? 'booked' : 'tentative';
            $infostatus = $booking->confirmed() ? 'statusbooked' : 'statustentative';
            $bookinginfo = [
                'instructor'    => instructor::get_fullname($booking->get_instructorid()),
                'sessiondate'   => !empty($sessiondate) ? $sessiondate->format('D, M d \@Hi\z') : 'null',
                'bookingstatus' => ucwords(get_string($infostatus, 'local_booking')),
            ];
            $sessiontooltip = get_string('sessionbookedby', 'local_booking', $bookinginfo);
        }

        // create session object
        if (!empty($grade) || !empty($booking))
            $session = new session($grade, $booking, $logentry, $sessionstatus, $sessiontooltip, $sessiondate);

        return $session;
    }

    /**
     * Returns the grade matching the passed data exercise id.
     *
     * @param array    $bookings   The array of bookings
     * @param int      $exerciseid The exercise id matching the session
     * @return grade $grade The grade object for the session
     */
    protected function find_grade($grades, $exerciseid) {
        $grade = null;
        // Get student's grade for this session if available
        if (count($grades) > 0) {
            if (array_search($exerciseid, array_column($grades, 'exerciseid')) !== false) {
                $grade = new grade(
                        $exerciseid,
                        $grades[$exerciseid]->exercisetype,
                        $grades[$exerciseid]->instructorid,
                        $grades[$exerciseid]->instructorname,
                        $this->student->get_id(),
                        $this->student->get_name(),
                        $grades[$exerciseid]->gradedate,
                        $grades[$exerciseid]->grade,
                        $grades[$exerciseid]->totalgrade);
            }
        }
        return $grade;
    }

    /**
     * Returns the booking matching the passed data exercise id.
     *
     * @param array    $bookings   The array of bookings
     * @param int      $exerciseid The exercise id matching the session
     * @return booking $booking    The booking object for the session
     */
    protected function find_booking($bookings, $exerciseid) {
        $booking = null;

        // Get student's booking for this session if available
        if (count($bookings) > 0) {
            $bookingsarr = array_filter($bookings,
                function ($b) use (&$exerciseid) {
                    return $b->get_exerciseid() == $exerciseid;
                }
            );
            if (count($bookingsarr) > 0)
                $booking = current($bookingsarr);
        }
        return $booking;
    }
}
