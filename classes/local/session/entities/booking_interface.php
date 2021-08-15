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

use local_availability\local\slot\entities\slot;

defined('MOODLE_INTERNAL') || die();

/**
 * Interface for a booking class.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface booking_interface {

    /**
     * Set the course exercise id for the booking.
     *
     * @return int
     */
    public function get_exerciseid();

    /**
     * Set the instructor user id of the booking.
     *
     * @return int
     */
    public function get_instructorid();

    /**
     * Set the instructor name of the booking.
     *
     * @return string
     */
    public function get_instructorname();

    /**
     * Set the studnet user id of the booking.
     *
     * @return int
     */
    public function get_studentid();

    /**
     * Set the studnet name of the booking.
     *
     * @return string
     */
    public function get_studentname();

    /**
     * Get the slot object of booking.
     *
     * @return {slot}
     */
    public function get_slot();

    /**
     * Set the status of the booking Confirmed or Tentative.
     *
     * @return string
     */
    public function confirmed();

    /**
     * Get the date timestamp of the booking.
     *
     * @return int
     */
    public function get_bookingdate();

    /**
     * Set the course exercise id for the booking.
     *
     * @return int
     */
    public function set_exerciseid(int $exerciseid);

    /**
     * Set the instructor user id of the booking.
     *
     * @return int
     */
    public function set_instructorid(int $instructorid);

    /**
     * Set the instructor name of the booking.
     *
     * @return string
     */
    public function set_instructorname(string $instructorname);

    /**
     * Set the studnet user id of the booking.
     *
     * @return int
     */
    public function set_studentid(int $studentid);

    /**
     * Set the studnet name of the booking.
     *
     * @return string
     */
    public function set_studentname(string $studentname);

    /**
     * Set the slot object of booking.
     *
     * @return slot_interface
     */
    public function set_slot(slot $slot);

    /**
     * Set the date timestamp of the booking.
     *
     * @return int
     */
    public function set_bookingdate(int $bookingdate);
}
