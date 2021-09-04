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
 * Logbook data access
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\logbook\data_access;

defined('MOODLE_INTERNAL') || die();

use local_booking\local\logbook\entities\logentry;

interface logbook_vault_interface {

    /**
     * Get a student's logbook.
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $studentid  The student id associated with the logbook.
     * @return logentries[]     Array of logentry_interfaces.
     */
    public function get_logbook(int $courseid, int $studentid);

    /**
     * Get a specific logbook entry.
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $studentid  The student id associated with the logbook.
     * @param int $logentry     The logentry id.
     * @return logentry         A logentry_insterface.
     */
    public function get_logentry(int $courseid, int $studentid, int $logentryid);

    /**
     * Create a student's logbook entry
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $studentid  The student id associated with the logbook.
     * @param logentry  $logentry A logbook entry of the student.
     * @return bool     $result of the database add operation.
     */
    public function create_logentry(int $courseid, int $studentid, logentry $logentry);

    /**
     * Update a student's logbook entry
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $studentid  The student id associated with the logbook.
     * @param logentry  $logentry A logbook entry of the student.
     * @return bool     $result of the database update operation.
     */
    public function update_logentry(int $courseid, int $studentid, logentry $logentry);
}
