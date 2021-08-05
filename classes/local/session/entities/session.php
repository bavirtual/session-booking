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
 * Calendar session class.
 *
 * @package    core_calendar
 * @copyright  2017 Cameron Ball <cameron@cameron1729.xyz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\session\entities;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing a calendar session.
 *
 * @copyright 2017 Cameron Ball <cameron@cameron1729.xyz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class session implements session_interface {
    /**
     * @var int $id The session's id in the database.
     */
    protected $id;

    /**
     * @var int $userid The session's userid in the database.
     */
    protected $userid;

    /**
     * @var int $courseid The session's courseid in the database.
     */
    protected $courseid;

    /**
     * @var int $starttime The session's start time in the database.
     */
    protected $starttime;

    /**
     * @var int $endtime The session's end time in the database.
     */
    protected $endtime;

    /**
     * @var string $bookingstatus The booking status of this session.
     */
    protected $bookingstatus;

    /**
     * @var int $year The session's year in the database.
     */
    protected $year;

    /**
     * @var int $week The session's week in the database.
     */
    protected $week;

    /**
     * @var int $instructorid The session's booking instructor id in the database.
     */
    protected $instructorid;
    /**
     * Constructor.
     *
     * @param int                        $id             The session's ID in the database.
     * @param int                        $userid         The session's user id in the database.
     * @param int                        $courseid       The session's course id in the database.
     * @param int                        $starttime      The session's start time in the database.
     * @param int                        $endtime        The session's end time in the database.
     * @param string                     $bookingstatus  The session's booking status in the database.
     * @param int                        $year           The session's year in the database.
     * @param int                        $week           The session's week in the database.
     * @param int                        $instructorid   The session's booking instructor user id in the database.
     */
    public function __construct(
        $id = 0,
        $userid = 0,
        $courseid,
        $starttime,
        $endtime,
        $year,
        $week,
        $bookingstatus = null,
        $instructorid = 0
    ) {
        $this->id = $id;
        $this->userid = $userid;
        $this->courseid = $courseid;
        $this->starttime = $starttime;
        $this->endtime = $endtime;
        $this->year = $year;
        $this->week = $week;
        $this->bookingstatus = $bookingstatus;
        $this->instructorid = $instructorid;
    }

    public function get_id() {
        return $this->id;
    }

    public function get_userid() {
        return $this->userid;
    }

    public function get_courseid() {
        return $this->courseid;
    }

    public function get_starttime() {
        return $this->starttime;
    }

    public function get_endtime() {
        return $this->endtime;
    }

    public function get_year() {
        return $this->year;
    }

    public function get_week() {
        return $this->week;
    }

    public function get_bookingstatus() {
        return $this->bookingstatus;
    }

    public function get_instructorid() {
        return $this->instructorid;
    }
}
