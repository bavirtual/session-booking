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

namespace local_booking\local\logbook\entities;

use DateTime;
use local_booking\local\logbook\data_access\logbook_vault;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing a logbook.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logbook implements logbook_interface {

    /**
     * @var int $courseid The course id of this logbook.
     */
    protected $courseid;

    /**
     * @var int $studentid The student user id of this logbook.
     */
    protected $studentid;

    /**
     * Constructor.
     *
     * @param int   $courseid   The course id associated with this logbook.
     * @param int   $studentid  The student id associated with this logbook.
     */
    public function __construct($courseid, $studentid) {
        $this->courseid = $courseid;
        $this->studentid = $studentid;
    }

    /**
     * Get the logbook of a student.
     *
     * @return logentry[]
     */
    public function load() {
        $vault = new logbook_vault();
        return $vault->get_logbook($this->courseid, $this->studentid);
    }

    /**
     * Save a logbook entry.
     *
     * @return bool
     */
    public function add(logentry $logentry) {
        $vault = new logbook_vault();
        return $vault->create_logentry($this->courseid, $this->studentid, $logentry);
    }

    /**
     * Update a logbook entry.
     *
     * @return bool
     */
    public function update(logentry $logentry){
        $vault = new logbook_vault();
        return $vault->update_logentry($this->courseid, $this->studentid, $logentry);
    }

    /**
     * Load a logbook entry.
     *
     * @return logentry
     */
    public function get_logentry(int $logentryid) {
        $vault = new logbook_vault();
        return $vault->get_logentry($this->courseid, $this->studentid, $logentryid);
    }

    /**
     * Get the course id for the log entry.
     *
     * @return int
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * Get the student user id of the log entry.
     *
     * @return int
     */
    public function get_studentid() {
        return $this->studentid;
    }

    /**
     * Get the student name of the log entry.
     *
     * @return string
     */
    public function get_studentname() {
        return get_fullusername($this->studentid);
    }

    /**
     * Set the course  id for the log entry.
     *
     * @param int $courseid
     */
    public function set_courseid(int $courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Set the studnet user id of the log entry.
     *
     * @param int $studentid
     */
    public function set_studentid(int $studentid) {
        $this->studentid = $studentid;
    }
}
