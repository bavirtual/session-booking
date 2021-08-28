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

namespace local_booking\local\message;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for sending session booking notifications.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification extends \core\message\message {

    /**
     * Constructor.
     *
     */
    public function __construct() {
        global $COURSE;

        $this->courseid          = $COURSE->id;
        $this->component         = 'local_booking';
        $this->userfrom          = \core_user::get_noreply_user();
        $this->fullmessageformat = FORMAT_MARKDOWN;
        $this->notification      = 1; // Because this is a notification generated from Moodle, not a user-to-user message
        $this->smallmessage      = '';
        $this->set_additional_content('email', array('*' => array(
            'header' => get_string('pluginname', 'local_booking'),
            'footer' => get_string('pluginname', 'local_booking'))));
    }

    /**
     * Sends an email notifying the student
     *
     * @return int  The notification message id.
     */
    public function send_booking_notification($studentid, $exerciseid, $sessiondate) {
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

        $this->name              = 'booking_notification';
        $this->userto            = $studentid;
        $this->subject           = get_string('emailnotify', 'local_booking', $data);
        $this->fullmessage       = get_string('emailnotifymsg', 'local_booking', $data);
        $this->fullmessagehtml   = get_string('emailnotifyhtml', 'local_booking', $data);
        $this->contexturl        = $data->confirmurl;
        $this->contexturlname    = get_string('studentavialability', 'local_booking');

        return message_send($this) != 0;
    }

    /**
     * Sends an email confirming booking made by the instructor
     *
     * @return int  The notification message id.
     */
    public function send_instructor_confirmation($studentid, $exerciseid, $sessiondate) {
        global $USER, $COURSE;

        // confirmation message data
        $data = (object) array(
            'coursename'    => $COURSE->shortname,
            'student'       => get_fullusername($studentid),
            'sessiondate'   => $sessiondate->format('l M j \a\t H:i \z\u\l\u'),
            'exercise'      => get_exercise_name($exerciseid),
            'bookingurl'    => (new \moodle_url('/local/booking/'))->out(false),
        );

        $this->name              = 'booking_confirmation';
        $this->userto            = $USER->id;
        $this->subject           = get_string('emailconfirmsubject', 'local_booking', $data);
        $this->fullmessage       = get_string('emailconfirmnmsg', 'local_booking', $data);
        $this->fullmessagehtml   = get_string('emailconfirmhtml', 'local_booking', $data);
        $this->contexturl        = $data->bookingurl;
        $this->contexturlname    = get_string('pluginname', 'local_booking');

        return message_send($this) != 0;
    }

    /**
     * Sends an email notifying the instructor of
     * student confirmation of booked session
     *
     * @return int  The notification message id.
     */
    public function send_instructor_notification($studentid, $exerciseid, $sessiondate, $instructorid, $url) {
        global $COURSE;

        // notification message data
        $data = (object) array(
            'coursename'        => $COURSE->shortname,
            'student'           => get_fullusername($studentid),
            'sessiondate'       => $sessiondate->format('l M j \a\t H:i \z\u\l\u'),
            'exercise'          => get_exercise_name($exerciseid),
            'bookingurl'   => $url->out(false),
        );

        $this->name              = 'instructor_notification';
        $this->userto            = $instructorid;
        $this->subject           = get_string('emailinstconfirmsubject', 'local_booking', $data);
        $this->fullmessage       = get_string('emailinstconfirmnmsg', 'local_booking', $data);
        $this->fullmessagehtml   = get_string('emailinstconfirmhtml', 'local_booking', $data);
        $this->contexturl        = $data->bookingurl;
        $this->contexturlname    = get_string('studentavialability', 'local_booking');

        return message_send($this) != 0;
    }

    /**
     * Sends an email notifying the student of
     * the cancelled session.
     *
     * @return int  The notification message id.
     */
    public function send_session_cancellation($studentid, $exerciseid, $sessiondate) {
        global $USER, $COURSE;

        // notification message data
        $data = (object) array(
            'coursename'    => $COURSE->shortname,
            'instructor'    => get_fullusername($USER->id),
            'sessiondate'   => $sessiondate->format('l M j \a\t H:i \z\u\l\u'),
            'exercise'      => get_exercise_name($exerciseid),
            'courseurl'    => (new \moodle_url('/course/view.php', array('id'=> $COURSE->id)))->out(false),
        );

        $this->name              = 'session_cancellation';
        $this->userto            = $studentid;
        $this->subject           = get_string('emailcancel', 'local_booking', $data);
        $this->fullmessage       = get_string('emailcancelmsg', 'local_booking', $data);
        $this->fullmessagehtml   = get_string('emailcancelhtml', 'local_booking', $data);
        $this->contexturl        = $data->courseurl;
        $this->contexturlname    = get_string('studentavialability', 'local_booking');

        return message_send($this) != 0;
    }
}