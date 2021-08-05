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
 * Contains event class for displaying the week view.
 *
 * @package   local_booking
 * @copyright 2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\session\data_access;

use local_booking\local\session\entities\session;

class session_vault implements session_vault_interface {

    /** Table name for the persistent. */
    const TABLE = 'local_booking_sessions';

    /**
     * save a session
     *
     * @param string $session
     * @return bool
     */
    public function get_sessions($year = 0, $week = 0) {
        global $DB, $USER;

        $condition = [
            'userid' => $USER->id,
            'year' => $year,
            'week' => $week,
        ];

        return $DB->get_records(static::TABLE, $condition);
    }

    /**
     * remove all records for a user for a
     * specific year and week
     *
     * @param string $username The username.
     * @return bool
     */
    public function delete_sessions($course = 0, $year = 0, $week = 0) {
        global $DB, $USER;

        $condition = [
            'userid' => $USER->id,
            'courseid' => $course,
            'year' => $year,
            'week' => $week,
        ];

        return $DB->delete_records(static::TABLE, $condition);
    }

    /**
     * save a session
     *
     * @param string $session
     * @return bool
     */
    public function save(session $session) {
        global $DB, $USER;

        $sessionrecord = new \stdClass();
        $sessionrecord->userid = $USER->id;
        $sessionrecord->courseid = $session->get_courseid();
        $sessionrecord->starttime = $session->get_starttime();
        $sessionrecord->endtime = $session->get_endtime();
        $sessionrecord->year = $session->get_year();
        $sessionrecord->week = $session->get_week();

        return $DB->insert_record(static::TABLE, $sessionrecord);
    }
}