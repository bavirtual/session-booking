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

namespace local_booking\local\session\entities;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing a course exercise booking.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking implements booking_interface {

    /**
     * @var int $exercise The course exercise id of this bookng.
     */
    protected $exercisid;

    /**
     * @var int $instructorid The instructor user id of this booking.
     */
    protected $instructorid;

    /**
     * @var string $instructorname The instructor name of this booking.
     */
    protected $instructorname;

    /**
     * @var int $studentid The user id of the student of this booking.
     */
    protected $studentid;

    /**
     * @var string $instructorname The student name of this booking.
     */
    protected $studentname;

    /**
     * @var string $slots The booked slots' timestamps comma delimited.
     */
    protected $booingslots;

    /**
     * @var bool $confirmed The booking is confirmed.
     */
    protected $confirmed;

    /**
     * @var int $bookingdate The date timestamp of this booking.
     */
    protected $bookingdate;

    /**
     * Constructor.
     *
     * @param event_interface  $event  The event to delegate to.
     * @param action_interface $action The action associated with this event.
     */
    public function __construct(
        $exerciseid,
        $bookedslots,
        $studentid,
        $studentname    = '',
        $instructorid   = 0,
        $instructorname = '',
        $confirmed      = false,
        $bookingdate    = 0,
        ) {
        $this->exerciseid       = $exerciseid;
        $this->bookedslots      = $bookedslots;
        $this->studentid        = $studentid;
        $this->studentname      = $studentname;
        $this->instructorid     = $instructorid;
        $this->instructorname   = $instructorname;
        $this->confirmed        = $confirmed;
        $this->bookingdate      = $bookingdate;
    }

    // Getter functions

    public function get_exerciseid() {
        return $this->exerciseid;
    }

    public function get_instructorid() {
        return $this->instructorid;
    }

    public function get_instructorname() {
        return $this->instructorname;
    }

    public function get_studentid() {
        return $this->studentid;
    }

    public function get_studentname() {
        return $this->studentname;
    }

    public function get_bookedslots() {
        return $this->bookedslots;
    }

    public function confirmed() {
        return $this->confirmed;
    }

    public function get_bookingdate() {
        return $this->bookingdate;
    }

    // Setter functions

    /**
     * Set the course exercise id for the booking.
     *
     * @return int
     */
    public function set_exerciseid(int $exerciseid) {
        $this->exerciseid = $exerciseid;
    }

    /**
     * Set the instructor user id of the booking.
     *
     * @return int
     */
    public function set_instructorid(int $instructorid) {
        $this->instructorid = $instructorid;
    }

    /**
     * Set the instructor name of the booking.
     *
     * @return string
     */
    public function set_instructorname(string $instructorname) {
        $this->instructorname = $instructorname;
    }

    /**
     * Set the studnet user id of the booking.
     *
     * @return int
     */
    public function set_studentid(int $studentid) {
        $this->studentid = $studentid;
    }

    /**
     * Set the studnet name of the booking.
     *
     * @return string
     */
    public function set_studentname(string $studentname) {
        $this->studentname = $studentname;
    }

    /**
     * Set the string of booked slots.
     *
     * @return array
     */
    public function set_bookedslots(string $bookedslots) {
        $this->bookedslots = $bookedslots;
    }

    /**
     * Set the date array of the booking.
     *
     * @return array
     */
    public function set_bookingdate(int $bookingdate) {
        $this->bookingdate = $bookingdate;
    }
}
