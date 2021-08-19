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

require_once(__DIR__ . '/../../local/availability/lib.php');

use \local_booking\external\progression_exporter;
use \local_booking\external\bookings_exporter;
use \local_booking\external\assigned_students_exporter;
use local_availability\local\slot\data_access\slot_vault;
use local_booking\local\session\data_access\booking_vault;

/**
 * Process user  table name.
 */
const DB_BOOKING = 'local_booking';

/**
 * Process assign table name.
 */
const DB_ASSIGN = 'assign';

/**
 * Process course modules table name.
 */
const DB_COURSE_MODULES = 'course_modules';

/**
 * Get the student's progression view output.
 *
 * @param   int     $courseid the associated course.
 * @param   int     $categoryid the course's category.
 * @return  array[array, string]
 */
function get_progression_view($courseid, $categoryid) {
    global $PAGE;

    $renderer = $PAGE->get_renderer('local_booking');

    $template = 'local_booking/progress_detailed';
    $data = [
        'courseid'  => $courseid,
        'categoryid'=> $categoryid,
    ];

    $progression = new progression_exporter($data, ['context' => \context_course::instance($courseid)]);
    $data = $progression->export($renderer);

    return [$data, $template];
}

/**
 * Get the student's progression view output.
 *
 * @param   int     $courseid the associated course.
 * @param   int     $categoryid the course's category.
 * @return  array[array, string]
 */
function get_bookings_view($courseid, $categoryid) {
    global $PAGE;

    $renderer = $PAGE->get_renderer('local_booking');

    $template = 'local_booking/bookings';
    $data = [
        'courseid'  => $courseid,
        'categoryid'=> $categoryid,
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
function get_students_view($courseid, $categoryid) {
    global $PAGE;

    $renderer = $PAGE->get_renderer('local_booking');

    $template = 'local_booking/my_students';
    $data = [
        'courseid'  => $courseid,
        'categoryid'=> $categoryid,
    ];

    $students = new assigned_students_exporter($data, ['context' => \context_course::instance($courseid)]);
    $data = $students->export($renderer);

    return [$data, $template];
}

/**
 * Respond to submission graded events
 *
 */
function booking_process_submission_graded($exerciseid, $studentid) {
    $bookingvault = new booking_vault();
    $slotvault = new slot_vault();

    $bookingvault->delete_booking($studentid, $exerciseid);
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
 * Sends an email notifying the student
 *
 * @return int  The notification message id.
 */
function send_booking_notification($studentid, $exerciseid, $sessiondate) {
    global $USER, $COURSE;

    // notification message data
    $data = (object) array(
        'coursename'    => $COURSE->shortname,
        'instructor'    => get_fullusername($USER->id),
        'sessiondate'   => $sessiondate->format('l M j \a\t H:i \z\u\l\u'),
        'exercise'      => get_exercise_name($exerciseid),
        'confirmurl'    => (new \moodle_url('/local/booking/confirm.php', array(
            'courseid'=> $COURSE->id,
            'exeid'   => $exerciseid,
            'userid'  => $studentid,
            'insid'   => $USER->id
            )))->out(false),
    );

    return send_message(
        'booking_notification',
        $studentid,
        $COURSE->id,
        get_string('emailnotify', 'local_booking', $data),
        get_string('emailnotifymsg', 'local_booking', $data),
        get_string('emailnotifyhtml', 'local_booking', $data),
        $data->confirmurl,
        get_string('studentavialability', 'local_booking'),
        array('*' => array('header' => ' testing ', 'footer' => ' testing ')));
}

/**
 * Sends an email confirming booking made by the instructor
 *
 * @return int  The notification message id.
 */
function send_instructor_confirmation($studentid, $exerciseid, $sessiondate) {
    global $USER, $COURSE;

    // confirmation message data
    $data = (object) array(
        'coursename'    => $COURSE->shortname,
        'student'       => get_fullusername($studentid),
        'sessiondate'   => $sessiondate->format('l M j \a\t H:i \z\u\l\u'),
        'exercise'      => get_exercise_name($exerciseid),
        'bookingurl'    => (new \moodle_url('/local/booking/'))->out(false),
    );

    return send_message(
            'booking_confirmation',
            $USER->id,
            $COURSE->id,
            get_string('emailconfirmsubject', 'local_booking', $data),
            get_string('emailconfirmnmsg', 'local_booking', $data),
            get_string('emailconfirmhtml', 'local_booking', $data),
            $data->bookingurl,
            get_string('pluginname', 'local_booking'),
            array('*' => array('header' => ' testing header ', 'footer' => ' testing footer')));
}

/**
 * Sends an email notifying the instructor of
 * student confirmation of booked session
 *
 * @return int  The notification message id.
 */
function send_instructor_notification($studentid, $exerciseid, $sessiondate) {
    global $COURSE;

    // notification message data
    $data = (object) array(
        'coursename'    => $COURSE->shortname,
        'student'       => get_fullusername($studentid),
        'sessiondate'   => $sessiondate->format('l M j \a\t H:i \z\u\l\u'),
        'exercise'      => get_exercise_name($exerciseid),
    );

    return send_message(
        'instructor_notification',
        $studentid,
        $COURSE->id,
        get_string('emailinstconfirmsubject', 'local_booking', $data),
        get_string('emailinstconfirmnmsg', 'local_booking', $data),
        get_string('emailinstconfirmhtml', 'local_booking', $data),
        $data->confirmurl,
        get_string('studentavialability', 'local_booking'),
        array('*' => array('header' => ' testing ', 'footer' => ' testing ')));
}

/**
 * Sends an email message
 *
 * @return bool  The result of sending a message object.
 */
function send_message($messagename, $touser, $courseid, $subject, $fullmessage, $fullmessagehtml, $url, $urlname, $content) {
    $message = new \core\message\message();
    $message->courseid          = $courseid;
    $message->component         = 'local_booking';
    $message->name              = $messagename;
    $message->userfrom          = core_user::get_noreply_user();
    $message->userto            = $touser;
    $message->fullmessageformat = FORMAT_MARKDOWN;
    $message->notification      = 1; // Because this is a notification generated from Moodle, not a user-to-user message
    $message->subject           = $subject;
    $message->fullmessage       = $fullmessage;
    $message->fullmessagehtml   = $fullmessagehtml;
    $message->smallmessage      = '';
    $message->contexturl        = $url;
    $message->contexturlname    = $urlname;
    // $message->set_additional_content('email', $content);

    return message_send($message) != 0;
}


/**
 * This function extends the navigation with the booking item
 *
 * @param global_navigation $navigation The global navigation node to extend
 */

function local_booking_extend_navigation(global_navigation $navigation) {
    global $COURSE;

    $systemcontext = context_course::instance($COURSE->id);

    if (has_capability('local/booking:view', $systemcontext)) {
    // $node = $navigation->find('booking', navigation_node::TYPE_CUSTOM);
        $node = $navigation->find('booking', navigation_node::NODETYPE_LEAF);
        if (!$node && $COURSE->id!==SITEID) {
            $parent = $navigation->find($COURSE->id, navigation_node::TYPE_COURSE);
            $node = navigation_node::create(get_string('booking', 'local_booking'), new moodle_url('/local/booking/view.php', array('courseid'=>$COURSE->id)));
            $node->key = 'booking';
            $node->type = navigation_node::NODETYPE_LEAF;
            $node->forceopen = true;
            $node->icon = new  pix_icon('i/emojicategorytravelplaces', '');  // e/table_props  e/split_cells

            $parent->add_node($node);
        }
    }
}
