<?php
/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Booking
$string['crontask'] = 'Background processing for session booking';
$string['enroldate'] = 'Enrol Date';
$string['exercise'] = 'Exercise';
$string['incompletelessontooltip'] = 'The student has incomplete lessons';
$string['pluginname'] = 'Session Booking';
$string['progression'] = 'Students Progression';
$string['messageprovider:booking_notification'] = 'Session booked notification';
$string['messageprovider:booking_confirmation'] = 'Booked session instructor confirmation';
$string['messageprovider:instructor_notification'] = 'Student confirmation of booked session';
$string['mystudents'] = 'My Assigned Trainees';
$string['sequencetooltip'] = 'Score: {$a->score}<br/>Last session: {$a->recency} days<br/>Course activity: {$a->activity} views
<br/>Availability: {$a->slots} posts<br/>Completion: {$a->completion} lessons';
$string['sessionaction'] = 'Action';
$string['sessiondate'] = 'Session Date';
$string['sessionbookedby'] = 'Session date: {$a->sessiondate} {$a->bookingstatus}: booked by \'{$a->instructor}\'';
$string['sessiongradeddby'] = 'Graded date: {$a->sessiondate} Graded by \'{$a->instructor}\'';
$string['simulator'] = 'Sim';
$string['statusbooked'] = 'confirmed';
$string['statustentative'] = 'tentative';
$string['studentavialability'] = 'Student Availability';
$string['students'] = 'Trainees';
$string['title'] = 'Session Booking';
$string['zulutime'] = 'Zulu';
$string['book'] = 'Book';
$string['booking'] = 'Booking';
$string['bookingactive'] = 'My Active Bookings';
$string['bookingcancel'] = 'Cancel';
$string['bookingcanceledsuccess'] = 'Booking with \'{$a->studentname}\' cancelled!';
$string['bookingcanceledunable'] = 'Unable to cancel booking!';
$string['booking:view'] = 'View session bookings';
$string['booking:emailnotify'] = 'Booking notification';
$string['booking:emailconfirm'] = 'Session booking confirmation';
$string['bookingconfirmmsg'] = '{$a->status} session on \'{$a->exercise}\' with instructor {$a->instructor}.';
$string['bookinginfo'] = '{$a->status} session on \'{$a->exercise}\' with instructor {$a->instructor}';
$string['bookingconfirmsuccess'] = 'Booking confirmed for \'{$a->exercise}\' with \'{$a->instructor}\' on \'{$a->sessiondate}\' zulu.';
$string['bookingconfirmunable'] = 'Unable to confirm booking!';
$string['bookingsavesuccess'] = 'Booking saved for \'{$a->exercise}\' with \'{$a->studentname}\' on \'{$a->sessiondate}\' zulu.';
$string['bookingsaveunable'] = 'Unable to save booking!';
$string['booksave'] = 'Save Booking';

// email to student: session tentative
$string['emailnotify'] = '{$a->coursename} session booking notification: \'{$a->exercise}\'';
$string['emailnotifymsg'] = '{$a->instructor} has booked a session for your availability on {$a->sessiondate} for \'{$a->exercise}\'.
Please confirm this booking by clicking on this link: {$a->confirmurl}';
$string['emailnotifyhtml'] = '<font face="sans-serif"><p>{$a->coursename} -> Assignment -> {$a->exercise}</p><hr /><p>{$a->instructor} has booked a session for your availability on <strong>{$a->sessiondate}</strong> for \'<i>{$a->exercise}</i>\'.</p><p>Please <a href=\'{$a->confirmurl}\'>confirm</a> this booking.</p></font><hr />';

// email to instructor: confirming session tentative by him/her
$string['emailconfirm'] = 'Session booked';
$string['emailconfirmsubject'] = '{$a->coursename} session booked: \'{$a->exercise}\'';
$string['emailconfirmnmsg'] = '\'{$a->exercise}\' session booked on {$a->sessiondate} for {$a->student}.';
$string['emailconfirmhtml'] = '<font face="sans-serif"><p>{$a->coursename} -> Assignment -> {$a->exercise}</p><hr /><p>Session booked on <strong>{$a->sessiondate}</strong> for \'<i>{$a->exercise}</i>\' with <strong>{$a->student}</strong>.</p></p><hr />';

// email to instructor: session confirmed by student
$string['emailinstconfirm'] = 'Booked session confirmed by Student';
$string['emailinstconfirmsubject'] = '{$a->coursename} - Student confirmed booking: \'{$a->exercise}\'';
$string['emailinstconfirmnmsg'] = '{$a->student} confirmed session booked on {$a->sessiondate} for \'{$a->exercise}\'.';
$string['emailinstconfirmhtml'] = '<font face="sans-serif"><p>{$a->coursename} -> Assignment -> {$a->exercise}</p><hr /><p><strong>{$a->student}</strong> confirmed booked session on <strong>{$a->sessiondate}</strong> for \'<i>{$a->exercise}</i>\'.</p></p><hr />';

// email to student: session cancellation
$string['emailcancel'] = '{$a->coursename} session booking cancellation: \'{$a->exercise}\'';
$string['emailcancelmsg'] = '{$a->instructor} has cancelled your booked session scheduled for {$a->sessiondate} on \'{$a->exercise}\'.';
$string['emailcancelhtml'] = '<font face="sans-serif"><p>{$a->coursename} -> Assignment -> {$a->exercise}</p><hr /><p>{$a->instructor} has cancelled your booked session scheduled for <strong>{$a->sessiondate}</strong> on \'<i>{$a->exercise}</i>\'.</p></p></p></font><hr />';

// settings
$string['exercisetitles'] = 'Course exercise titles:';
$string['exercisetitlesdesc'] = 'titles delimited by commas';
$string['recencydaysweight'] = 'Recency prioritization weight multiplier';
$string['recencydaysweightdesc'] = 'multipler to calculate prioritization for session recency';
$string['slotcountweight'] = 'Slot count weight multiplier';
$string['slotcountweightdesc'] = 'multiplier to calculate prioritization for availability slots';
$string['activitycountweight'] = 'Course activity weight multiplier';
$string['activitycountweightdesc'] = 'multiplier to calculate prioritization for course activity';
$string['completionweight'] = 'Lesson completion weight multiplier';
$string['completionweightdesc'] = 'multiplier to calculate prioritization of lesson completion';

// Availability posting
$string['availability'] = 'Availability';
$string['availability:view'] = 'View availability slots';
$string['availability:viewall'] = 'View all students availability slots';
$string['availabilityallview'] = 'View everyone\'s availability';
$string['buttonsave'] = 'Save';
$string['buttoncopy'] = 'Copy';
$string['buttonpaste'] = 'Paste';
$string['buttonclear'] = 'Clear';
$string['defaultmake'] = 'Make default';
$string['defaultload'] = 'Load default';
$string['firstsession'] = 'First session time:';
$string['firstsessiondesc'] = 'first allowable session time';
$string['flightsim'] = 'Flight Simulation';
$string['lastsession'] = 'Last session time:';
$string['lastsessiondesc'] = 'last allowable session time';
$string['lessonsincomplete'] = 'Lesson incomplete: Please complete pending lessons, othewise instructors will not see your availability.';
$string['restrictionend'] = 'Next session restriction:';
$string['restrictionenddesc'] = 'days from last booked session';
$string['local'] = 'Local';
$string['loading'] = 'Loading slots...';
$string['processingresult'] = 'The processing result';
$string['save'] = 'Save Availability';
$string['slotsdeletesuccess'] = 'Slots deleted.';
$string['slotsdeleteunable'] = 'Unable to delete slots!';
$string['slotssavesuccess'] = 'Slots saved.';
$string['slotssaveunable'] = 'Unable to save slots!';
$string['slotsstatusbooked'] = 'Booked';
$string['slotsstatustentative'] = 'Tentative';
$string['strftimeweekinyear'] = 'Week %W';
$string['week'] = 'Week';
$string['weeklytitle'] = 'Weekly Availability';
$string['weeksahead'] = 'Weeks Lookahead:';
$string['weeksaheaddesc'] = 'allowable weeks lookahead of availability recording. 0=Unlimited';
$string['weekprev'] = 'Previous week';
$string['weeknext'] = 'Next week';
