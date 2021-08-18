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

$string['book'] = 'Book';
$string['booking'] = 'Booking';
$string['booking:view'] = 'View session bookings';
$string['booking:emailnotify'] = 'Booking notification';
$string['booking:emailconfirm'] = 'Session booking confirmation';
$string['bookingconfirmmsg'] = 'Session confirmed for \'{$a->exercise}\' with instructor {$a->instructor}.';
$string['bookinginfo'] = '{$a->status} session on \'{$a->exercise}\' with instructor {$a->instructor}';
$string['bookingsavesuccess'] = 'Booking saved.';
$string['bookingsaveunable'] = 'Unable to save booking!';
$string['booksave'] = 'Save Booking';
$string['emailnotify'] = '{$a->coursename} session booking notification: \'{$a->exercise}\'';
$string['emailnotifymsg'] = '{$a->instructor} has booked a session for your availability on {$a->sessiondate} for \'{$a->exercise}\'.
Please confirm this booking by clicking on this link: {$a->confirmurl}';
$string['emailnotifyhtml'] = '<font face="sans-serif"><p>{$a->coursename} -> Assignment -> {$a->exercise}</p><hr /><p>{$a->instructor} has booked a session for your availability on <strong>{$a->sessiondate}</strong> for \'<i>{$a->exercise}</i>\'.</p><p>Please <a href=\'{$a->confirmurl}\'>confirm</a> this booking.</p></font><hr />';
$string['emailconfirm'] = 'Session booked';
$string['emailconfirmsubject'] = '{$a->coursename} session booked: \'{$a->exercise}\'';
$string['emailconfirmnmsg'] = '\'{$a->exercise}\' session booked on {$a->sessiondate} for {$a->student}.';
$string['emailconfirmhtml'] = '<font face="sans-serif"><p>{$a->coursename} -> Assignment -> {$a->exercise}</p><hr /><p>Session booked on <strong>{$a->sessiondate}</strong> for \'<i>{$a->exercise}</i>\' with <strong>{$a->student}</strong>.<hr />';
$string['emailinstconfirm'] = 'Booked session confirmed by Student';
$string['emailinstconfirmsubject'] = '{$a->coursename} - Student confirmed booking: \'{$a->exercise}\'';
$string['emailinstconfirmnmsg'] = '{$a->student} confirmed session booked on {$a->sessiondate} for \'{$a->exercise}\'.';
$string['emailinstconfirmhtml'] = '<font face="sans-serif"><p>{$a->coursename} -> Assignment -> {$a->exercise}</p><hr /><p><strong>{$a->student}</strong> confirmed booked session on <strong>{$a->sessiondate}</strong> for \'<i>{$a->exercise}</i>\'.';
$string['exercisetitles'] = 'Course exercise titles:';
$string['exercisetitlesdesc'] = 'titles delimited by commas';
$string['pluginname'] = 'Session Booking';
$string['processingresult'] = 'The processing result';
$string['processingresult'] = 'Unable to save booking!';
$string['progression'] = 'Students Progression';
$string['messageprovider:booking_notification'] = 'Session booked notification';
$string['messageprovider:booking_confirmation'] = 'Booked session instructor confirmation';
$string['messageprovider:instructor_notification'] = 'Student confirmation of booked session';
$string['sessionaction'] = 'Action';
$string['sessionbookedby'] = 'Session date: {$a->sessiondate} {$a->bookingstatus}: booked by \'{$a->instructor}\'';
$string['sessiongradeddby'] = 'Session date: {$a->sessiondate} Graded by \'{$a->instructor}\'';
$string['simulator'] = 'Sim';
$string['statusbooked'] = 'confirmed';
$string['statustentative'] = 'tentative';
$string['studentavialability'] = 'Student Availability';
$string['students'] = 'Trainees';
$string['title'] = 'Session Booking';

