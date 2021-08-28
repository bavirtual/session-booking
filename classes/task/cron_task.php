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

namespace local_booking\task;
defined('MOODLE_INTERNAL') || die();

use local_booking\local\slot\data_access\student_vault;
use local_booking\local\message\notification;

/**
 * A schedule task for student and instructor status cron.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'local_booking');
    }

    /**
     * Run session booking cron.
     */
    public function execute() {
        $message = new notification();
        $vault = new student_vault();

        // get wait days
        $waitdays = get_config('local_booking', 'restrictionend') ? get_config('local_booking', 'restrictionend') : local_booking_DAYSFROMLASTSESSION;

        // get active students
        $activestudents = $vault->get_active_students();

        // consider on-hold and suspension
        foreach ($activestudents as $student) {
            // passed wait period w/o availability posting
            // send notification of upcoming placement on-hold

            if ($message->            // place on hold
                // notify of being placed on hold
            // suspend when passed on-hold by 9x wait days
                // notify of suspension
        }

        // get instructors
        $instructors =
        foreach ($instructors as $instructor) {
            // consider instructor activity

        }

        return true;
    }
}
