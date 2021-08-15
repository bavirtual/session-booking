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
 * session vault interface
 *
 * @package    local_booking
 * @copyright  2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\session\data_access;

defined('MOODLE_INTERNAL') || die();

use local_booking\local\session\entities\booking;

/**
 * Interface for an session vault class
 *
 * @copyright  2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface booking_vault_interface {

    /**
     * Get all booked sessions for the instructor.
     *
     * @return booing[]             Array of session_interfaces.
     */
    public function get_bookings();

    /**
     * Get all booked sessions for a user that fall on a specific student.
     *
     * @param int                   $userid of the student in the booking.
     * @return booking              A student booking.
     */
    public function get_booking($userid);

    /**
     * Delete all sessions for a specific student.
     *
     * @param   int                 $userid     sessions for this user
     * @return  result              $result
     */
    public function delete_booking($userid);

    /**
     * Saves the passed booked session
     *
     * @param   booking_interface   $session
     * @return  result              $result
     */
    public function save_booking(booking $booking);
}
