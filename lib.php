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

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../local/booking/lib.php');

use local_booking\external\bookings_exporter;
use local_booking\external\assigned_students_exporter;
use local_booking\local\slot\data_access\slot_vault;
use local_booking\local\session\data_access\booking_vault;
use local_booking\local\session\entities\booking;
use local_booking\local\slot\entities\slot;
use local_booking\local\message\notification;
use local_booking\local\participant\data_access\participant_vault;
use local_booking\external\week_exporter;

/**
 * LOCAL_BOOKING_RECENCYWEIGHT - constant value for session recency weight multipler
 * LOCAL_BOOKING_SLOTSWEIGHT - constant value for session availability slots weight multipler
 * LOCAL_BOOKING_ACTIVITYWEIGHT - constant value for course activity weight multipler
 * LOCAL_BOOKING_COMPLETIONWEIGHT - constant value for lesson completion weight multipler
 * LOCAL_BOOKING_MAXLANES - constant value for maximum number of student slots shown in parallel a day
 * LOCAL_BOOKING_FIRSTSLOT - default value of the first slot of the day
 * LOCAL_BOOKING_LASTSLOT - default value of the first slot of the day
 * LOCAL_BOOKING_WEEKSLOOKAHEAD - default value of the first slot of the day
 * LOCAL_BOOKING_DAYSFROMLASTSESSION - default value of the days allowed to mark since last session
 * LOCAL_BOOKING_ONHOLDGROUP - constant string value for On-hold students for group quering purposes
 * LOCAL_BOOKING_GRADUATESGROUP - constant string value for graduated students for group quering purposes
 * LOCAL_BOOKING_ONHOLDWAITMULTIPLIER - constant for multiplying wait period (in days) for placing students on-hold: 3x wait period
 * LOCAL_BOOKING_SUSPENDWAITMULTIPLIER - constant for multiplying wait period (in days) for suspending inactive students: 9x wait period
 * LOCAL_BOOKING_SESSIONOVERDUEMULTIPLIER - constant for multiplying wait period (in days) for overdue sessions: 3x wait period
 * LOCAL_BOOKING_SESSIONLATEMULTIPLIER - constant for multiplying wait period (in days) for late sessions: 4x wait period
 * LOCAL_BOOKING_INSTRUCTORINACTIVEMULTIPLIER - constant for multiplying wait period (in days) for late sessions: 3x wait period
 * LOCAL_BOOKING_SLOT_COLORS - constant array of slot colors per student color assignment
 */
define('LOCAL_BOOKING_RECENCYWEIGHT', 10);
define('LOCAL_BOOKING_SLOTSWEIGHT', 10);
define('LOCAL_BOOKING_ACTIVITYWEIGHT', 1);
define('LOCAL_BOOKING_COMPLETIONWEIGHT', 10);
define('LOCAL_BOOKING_MAXLANES', 20);
define('LOCAL_BOOKING_FIRSTSLOT', 8);
define('LOCAL_BOOKING_LASTSLOT', 23);
define('LOCAL_BOOKING_WEEKSLOOKAHEAD', 4);
define('LOCAL_BOOKING_DAYSFROMLASTSESSION', 12);
define('LOCAL_BOOKING_ONHOLDGROUP', 'OnHold');
define('LOCAL_BOOKING_GRADUATESGROUP', 'Graduates');
define('LOCAL_BOOKING_ONHOLDWAITMULTIPLIER', 3);
define('LOCAL_BOOKING_SUSPENDWAITMULTIPLIER', 9);
define('LOCAL_BOOKING_SESSIONOVERDUEMULTIPLIER', 2);
define('LOCAL_BOOKING_SESSIONLATEMULTIPLIER', 3);
define('LOCAL_BOOKING_INSTRUCTORINACTIVEMULTIPLIER', 2);
define('LOCAL_BOOKING_SLOT_COLORS', array(
        'red'         => '#d50000',
        'green'       => '#689f38',
        'yellow'      => '#ffeb3b',
        'deep orange' => '#ff3d00',
        'lime'        => '#aeea00',
        'dark green'  => '#1b5e20',
        'blue'        => '#2962ff',
        'light blue'  => '#0091ea',
        'orange'      => '#ff6d00',
        'deep purple' => '#9fa8da',
        'pink'        => '#fce4ec',
        'light green' => '#00e676',
        'dark blue'   => '#0d47a1',
        'teal'        => '#00897b',
        'light purple'=> '#c5cae9',
        'brown'       => '#5d4037',
        'light indigo'=> '#dcedc8',
        'light cyan'  => '#b2ebf2',
        'dark purple' => '#4a148c',
        'light yellow'=> '#ffff00',
    ));

/**
 * Process user  table name.
 */
const DB_USER = 'user';

/**
 * Process assign table name.
 */
const DB_ASSIGN = 'assign';

/**
 * Process course modules table name.
 */
const DB_COURSE_MODULES = 'course_modules';

/**
 * Get the calendar view output.
 *
 * @param   \calendar_information $calendar The calendar being represented
 * @param   array   $actiondata The action type and associated data
 * @param   bool    $skipevents Whether to load the events or not
 * @return  array[array, string]
 */
function get_weekly_view(\calendar_information $calendar, $actiondata, $view = 'user') {
    global $PAGE;

    $renderer = $PAGE->get_renderer('core_calendar');
    $type = \core_calendar\type_factory::get_calendar_instance();

    $related = [
        'type' => $type,
    ];

    $week = new week_exporter($calendar, $type, $actiondata, $view, $related);
    $data = $week->export($renderer);
    $data->viewingmonth = true;
    $template = 'local_booking/calendar_week';

    return [$data, $template];
}

/**
 * Get the student's progression view output.
 *
 * @param   int     $courseid the associated course.
 * @param   int     $categoryid the course's category.
 * @return  array[array, string]
 */
function get_bookings_view($courseid) {
    global $PAGE;

    $renderer = $PAGE->get_renderer('local_booking');

    $template = 'local_booking/bookings';
    $data = [
        'courseid'  => $courseid,
    ];

    $bookings = new bookings_exporter($data, ['context' => \context_course::instance($courseid)]);
    $data = $bookings->export($renderer);

    return [$data, $template];
}

/**
 * Get instructor assigned students view output.
 *
 * @param   int     $courseid the associated course.
 * @param   int     $categoryid the course's category.
 * @return  array[array, string]
 */
function get_students_view($courseid) {
    global $PAGE;

    $renderer = $PAGE->get_renderer('local_booking');

    $template = 'local_booking/my_students';
    $data = [
        'courseid'  => $courseid,
    ];

    $students = new assigned_students_exporter($data, ['context' => \context_course::instance($courseid)]);
    $data = $students->export($renderer);

    return [$data, $template];
}

/**
 * Save a new booking for a student by the instructor.
 *
 * @param   array   $params an array containing the webservice parameters
 * @return  bool
 */
function save_booking($params) {
    global $DB, $USER;

    $slottobook = $params['bookedslot'];
    $courseid   = $params['courseid'];
    $exerciseid = $params['exerciseid'];
    $studentid  = $params['studentid'];

    $result = false;
    $bookingvault = new booking_vault();
    $slotvault = new slot_vault();
    $courseid = get_course_id($exerciseid);

    require_login($courseid, false);

    $transaction = $DB->start_delegated_transaction();

    // add a new tentatively booked slot for the student.
    $sessiondata = [
        'exercise'  => get_exercise_name($exerciseid),
        'instructor'=> get_fullusername($USER->id),
        'status'    => ucwords(get_string('statustentative', 'local_booking')),
    ];

    // add new booked slot for the user
    // remove all week's slots for the user to avoid having to update
    if ($bookingvault->delete_student_booking($studentid, $exerciseid)) {
        $slotobj = new slot(0,
            $studentid,
            $courseid,
            $slottobook['starttime'],
            $slottobook['endtime'],
            $slottobook['year'],
            $slottobook['week'],
            get_string('statustentative', 'local_booking'),
            get_string('bookinginfo', 'local_booking', $sessiondata)
        );
        $bookedslot = $slotvault->get_slot($slotvault->save($slotobj));

        // add new booking by the instructor.
        if (!empty($bookedslot)) {
            if ($bookingvault->save_booking(new booking($courseid, $exerciseid, $bookedslot, $studentid, $slottobook['starttime']))) {
                // send emails to both student and instructor
                $sessiondate = new DateTime('@' . $slottobook['starttime']);
                $message = new notification();
                if ($message->send_booking_notification($studentid, $exerciseid, $sessiondate)) {
                    $result = $message->send_instructor_confirmation($studentid, $exerciseid, $sessiondate);
                }
            }
        }
    }

    if ($result) {
        $transaction->allow_commit();
        $sessiondata['sessiondate'] = $sessiondate->format('D M j\, H:i');
        $sessiondata['studentname'] = get_fullusername($studentid);
        \core\notification::success(get_string('bookingsavesuccess', 'local_booking', $sessiondata));
    } else {
        $transaction->rollback(new moodle_exception(get_string('bookingsaveunable', 'local_booking')));
        \core\notification::warning(get_string('bookingsaveunable', 'local_booking'));
    }

    return $result;
}

/**
 * Confirm a booking for of a tentative session
 *
 * @param   int   $exerciseid   The exercise id being confirmed.
 * @param   int   $instructorid The instructor id that booked the session.
 * @param   int   $studentid    The student id being of the confirmed session.
 * @return  array An array containing the result and confirmation message string.
 */
function confirm_booking($exerciseid, $instructorid, $studentid) {
    global $DB;

    // Get the student slot
    $bookingvault = new booking_vault();
    $slotvault = new slot_vault();

    $bookingobj = array_reverse((array) $bookingvault->get_student_booking($studentid));
    $booking = array_pop($bookingobj);

    // confirm the booking and redirect to the student's availability
    $transaction = $DB->start_delegated_transaction();

    $result = false;
    // update the booking by the instructor.
    if ($bookingvault->confirm_booking($studentid, $exerciseid)) {
        $strdata = [
            'exercise'  => get_exercise_name($exerciseid),
            'instructor'=> get_fullusername($instructorid),
            'status'    => ucwords(get_string('statusbooked', 'local_booking')),
        ];
        $bookinginfo = get_string('bookingconfirmmsg', 'local_booking', $strdata);
        $result = $slotvault->confirm_slot($booking->slotid, $bookinginfo);

        $slot = $slotvault->get_slot($booking->slotid);

        // notify the instructor of the student's confirmation
        $slotvault = new slot_vault;
        $sessiondate = $slotvault->get_session_date($booking->slotid);
        $strdata['sessiondate'] = $sessiondate->format('D M j\, H:i');
        $message = new notification();
        $result = $result && $message->send_instructor_notification($studentid, $exerciseid, $sessiondate, $instructorid);
    }

    if ($result) {
        $transaction->allow_commit();
        \core\notification::success(get_string('bookingconfirmsuccess', 'local_booking', $strdata));
    } else {
        $transaction->rollback(new moodle_exception(get_string('bookingconfirmunable', 'local_booking')));
        \core\notification::ERROR(get_string('bookingconfirmunable', 'local_booking'));
    }

    return [$result, $slot->starttime, $slot->week];
}

/**
 * Cancel an instructor's existing booking
 * @param   int   $booking  The booking id being cancelled.
 * @return  bool  $resut    The cancellation result.
 */
function cancel_booking($bookingid) {
    global $DB, $COURSE;

    require_login($COURSE->id, false);

    $vault = new booking_vault();
    $slotvault = new slot_vault();

    // get the booking to be deleted
    $booking = ($vault->get_booking($bookingid))[$bookingid];

    // get the associated slot to delete
    $slot = $slotvault->get_slot($booking->slotid);

    // start a transaction
    $transaction = $DB->start_delegated_transaction();

    $result = false;

    if ($vault->delete_booking($bookingid)) {
        // delete the slot
        $result = $slotvault->delete_slot($slot->id);
    }

    $sessiondate = new DateTime('@' . $slot->starttime);
    $cancellationmsg = [
        'studentname' => get_fullusername($booking->studentid),
        'sessiondate' => $sessiondate->format('D M j\, H:i'),
    ];

    if ($result) {
        $transaction->allow_commit();
        // send email notification to the student of the booking cancellation
        $message = new notification();
        if ($message->send_session_cancellation($booking->studentid, $booking->exerciseid, $sessiondate)) {
            \core\notification::success(get_string('bookingcanceledsuccess', 'local_booking', $cancellationmsg));
        }
    } else {
        $transaction->rollback(new moodle_exception(get_string('bookingsaveunable', 'local_booking')));
        \core\notification::warning(get_string('bookingcanceledunable', 'local_booking'));
    }

    return $result;
}

/**
 * Respond to submission graded events
 *
 */
function booking_process_submission_graded($exerciseid, $studentid) {
    $bookingvault = new booking_vault();
    $slotvault = new slot_vault();

    // update the booking status from active to inactive
    $bookingvault->set_booking_inactive($studentid, $exerciseid);
    $slotvault->delete_slots(get_course_id($exerciseid), 0, 0, $studentid, false);
}

/**
 * Returns exercise assignment name
 *
 * @return string  The BAV exercise name.
 */
function get_exercise_name($exerciseid) {
    global $DB;

    // Get the student's grades
    $sql = 'SELECT a.name AS exercisename
            FROM {' . DB_ASSIGN . '} a
            INNER JOIN {' . DB_COURSE_MODULES . '} cm on a.id = cm.instance
            WHERE cm.id = ' . $exerciseid;

    return $DB->get_record_sql($sql)->exercisename;
}


/**
 * Retrieves exercises for the course
 *
 * @return array
 */
function get_exercise_names() {
    global $DB;

    // get assignments for this course based on sorted course topic sections
    $sql = 'SELECT cm.id AS exerciseid, a.name AS exercisename
            FROM {' . DB_ASSIGN . '} a
            INNER JOIN {' . DB_COURSE_MODULES . '} cm on a.id = cm.instance
            WHERE module = 1
            ORDER BY cm.section;';

    return $DB->get_records_sql($sql);
}

/**
 * Return the days of the week where $date falls in.
 *
 * @return array array of days
 */
function get_active_student_slots($weekno, $year, $studentid = 0) {
    $slotvault = new slot_vault();
    $studentvault = new participant_vault();

    $activestudents = [];
    // get a single or all students records
    if ($studentid != 0) {
        $students = $studentvault->get_student($studentid);
    } else {
        $students = $studentvault->get_active_students();
    }

    $i = 0;
    // get slots for each student
    foreach ($students as $student) {
        $slots = $slotvault->get_slots($student->userid, $year, $weekno);
        // $color = '#' . random_color();
        $color = array_values(LOCAL_BOOKING_SLOT_COLORS)[$i % LOCAL_BOOKING_MAXLANES];
        // add random color to each student
        foreach ($slots as $slot) {
            $slot->slotcolor = $color;
        }
        $i++;

        $activestudents[$student->userid] = $slots;
    }

    return $activestudents;
}

/**
 * Return the days of the week where $date falls in.
 *
 * @return array array of days
 */
function get_week_days($date) {

    $days = [];
    // Calculate which day number is the first day of the week.
    $type = \core_calendar\type_factory::get_calendar_instance();
    $daysinweek = count($type->get_weekdays());
    $week_start = get_week_start($date);

    // add first day of the week
    $days[] = $type->timestamp_to_date_array(date_timestamp_get($week_start));

    // add remaining days of the week
    for ($i = 0; $i < $daysinweek-1; $i++) {
        date_add($week_start, date_interval_create_from_date_string("1 days"));
        $days[] = $type->timestamp_to_date_array(date_timestamp_get($week_start));
    }

    return $days;
}

/**
 * Return the days of the week where $date falls in.
 *
 * @return DateTime array of days
 */
function get_week_start($date) {
    $week_start_date = new DateTime();
    date_timestamp_set($week_start_date, $date[0]);
    $week_start_date->setISODate($date['year'], strftime('%W', $date[0]))->format('Y-m-d');
    return $week_start_date;
}

/**
 * Checks if the student completed
 * all pending lessons before marking
 * availability for an instructor session.
 *
 * @param   int     The student id
 * @return  bool
 */
function has_completed_lessons($studentid) {
    global $COURSE;
    $vault = new participant_vault();
    list($nextexercise, $exercisesection) = $vault->get_next_exercise($studentid, $COURSE->id);
    $completedlessons = $vault->get_lessons_complete($studentid, $COURSE->id, $exercisesection);

    return $completedlessons;
}

/**
 * Returns the date of the last booked
 * session or today if unavailable.
 *
 * @param   int     The student id
 * @return  DateTime
 */
function get_next_allowed_session_date($studentid) {
    $daysfromlast = (get_config('local_booking', 'nextsessionwaitdays')) ? get_config('local_booking', 'nextsessionwaitdays') : LOCAL_BOOKING_DAYSFROMLASTSESSION;
    $vault = new slot_vault();

    $lastsession = $vault->get_last_posted_slot($studentid);
    $sessiondatets = !empty($lastsession) ? $lastsession->starttime : time();
    $sessiondate = new DateTime('@' . $sessiondatets);
    date_add($sessiondate, date_interval_create_from_date_string($daysfromlast . ' days'));

    return $sessiondate;
}

/**
 * Returns the timestamp of the first
 * nonbooked availability slot for
 * the student.
 *
 * @param   int     The student id
 * @return  DateTime
 */
function get_first_posted_slot($studentid) {
    $vault = new slot_vault();

    $firstsession = $vault->get_first_posted_slot($studentid);
    $sessiondatets = !empty($firstsession) ? $firstsession->starttime : time();
    $sessiondate = new DateTime('@' . $sessiondatets);

    return $sessiondate;
}

/**
 * Returns full username
 *
 * @return string  The full BAV username (first, last, and BAWID)
 */
function get_fullusername(int $userid, bool $BAVname = true) {
    global $DB;

    // Get the student's grades
    $sql = 'SELECT ' . $DB->sql_concat('u.firstname', '" "',
                'u.lastname', '" "', 'u.alternatename') . ' AS bavname, '
                . $DB->sql_concat('u.firstname', '" "',
                'u.lastname') . ' AS username
            FROM {' . DB_USER . '} u
            WHERE u.id = ' . $userid;

    $userinfo = $DB->get_record_sql($sql);
    return $BAVname ? $userinfo->bavname : $userinfo->username;
}

/**
 * Returns a random color.
 *
 * @return string   hex color
 */
function random_color_part() {
    return str_pad( dechex( mt_rand( 20, 235 ) ), 2, '0', STR_PAD_LEFT);
}

/**
 * Returns a hash of the random color for a student slot in group view.
 *
 * @return string   hex color
 */
function random_color() {
    return random_color_part() . random_color_part() . random_color_part();
}

/**
 * Returns course id of the passed course
 *
 * @return string  The BAV exercise name.
 */
function get_course_id($exerciseid) {
    global $DB;

    // Get the student's grades
    $sql = 'SELECT cm.course AS courseid
            FROM {' . DB_COURSE_MODULES . '} cm
            WHERE cm.id = ' . $exerciseid;

    return $DB->get_record_sql($sql)->courseid;
}

/**
 * This function extends the navigation with the booking item
 *
 * @param global_navigation $navigation The global navigation node to extend
 */

function local_booking_extend_navigation(global_navigation $navigation) {
    global $COURSE, $USER;

    $context = context_course::instance($COURSE->id);

    // Add student availability navigation node
    if (has_capability('local/booking:availabilityview', $context)) {
        $node = $navigation->find('availability', navigation_node::NODETYPE_LEAF);
        if (!$node && $COURSE->id!==SITEID) {
            // form URL and parameters
            $params = array('course'=>$COURSE->id);
            // view all capability for instructors
            if (has_capability('local/booking:view', $context)) {
                $params['view'] = 'all';
            } else {
                $params['time'] = (get_next_allowed_session_date($USER->id))->getTimestamp();
            }
            $url = new moodle_url('/local/booking/availability.php', $params);

            $parent = $navigation->find($COURSE->id, navigation_node::TYPE_COURSE);
            $node = navigation_node::create(get_string('availability', 'local_booking'), $url);
            $node->key = 'availability';
            $node->type = navigation_node::NODETYPE_LEAF;
            $node->forceopen = true;
            // $node->icon = new  image_icon('local_booking:e/insert', '');
            $node->icon = new  pix_icon('e/insert_date', '');
            // $node->icon = new  pix_icon('calendar', '', 'local_booking', ['class' => 'icon fa fa-calendar fa-fw', 'data-test' => 'something']);
            $parent->add_node($node);
        }
    }

    // Add instructor booking navigation node
    if (has_capability('local/booking:view', $context)) {
        $node = $navigation->find('booking', navigation_node::NODETYPE_LEAF);
        if (!$node && $COURSE->id!==SITEID) {
            $parent = $navigation->find($COURSE->id, navigation_node::TYPE_COURSE);
            $node = navigation_node::create(get_string('booking', 'local_booking'), new moodle_url('/local/booking/view.php', array('courseid'=>$COURSE->id)));
            $node->key = 'booking';
            $node->type = navigation_node::NODETYPE_LEAF;
            $node->forceopen = true;
            $node->icon = new  pix_icon('i/emojicategorytravelplaces', '');  // pix_icon('i/book', '');

            $parent->add_node($node);
        }
    }
}
