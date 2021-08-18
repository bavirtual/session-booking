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

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

use local_availability\local\slot\data_access\slot_vault;
use local_booking\local\session\data_access\booking_vault;

global $DB;

// Get URL parameters.
$courseid     = optional_param('courseid', 0, PARAM_INT);
$exerciseid   = optional_param('exeid', 0, PARAM_INT);
$studentid    = optional_param('userid', 0, PARAM_INT);
$instructorid = optional_param('insid', 0, PARAM_INT);

require_login($courseid, false);

// Get the student slot
$bookingvault = new booking_vault();
$slotvault = new slot_vault();

$bookingobj = array_reverse((array) $bookingvault->get_booking($studentid));
$booking = array_pop($bookingobj);

// confirm the booking and redirect to the student's availability
$transaction = $DB->start_delegated_transaction();

$result = false;
// update the booking by the instructor.
if ($bookingvault->confirm_booking($studentid, $exerciseid)) {
    $strdata = [
        'exercise'  => get_exercise_name($exerciseid),
        'instructor'=> get_fullusername($instructorid),
    ];
    $bookinginfo = get_string('bookingconfirmmsg', 'local_booking', $strdata);
    $result = $slotvault->confirm_slot($booking->slotid, $bookinginfo);

    // notify the instructor of the student's confirmation
    $sessiondate = new DateTime('@' . $booking->timemodified);
    $result = $result && send_instructor_notification($studentid, $exerciseid, $sessiondate);
}

if ($result) {
    $transaction->allow_commit();
    \core\notification::success(get_string('bookingsavesuccess', 'local_booking'));
} else {
    $transaction->rollback(new moodle_exception(get_string('bookingsaveunable', 'local_booking')));
    \core\notification::ERROR(get_string('bookingsaveunable', 'local_booking'));
}

// redirect
$url = new moodle_url('/local/availability/view.php', array(
    'course'    => $courseid,
    'time'      => $booking->timemodified,
));

$PAGE->set_url($url);
redirect($url);
