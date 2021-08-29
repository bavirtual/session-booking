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
 * slot vault interface
 *
 * @package    local_booking
 * @copyright  2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\slot\data_access;

defined('MOODLE_INTERNAL') || die();

use local_booking\local\slot\entities\slot;

/**
 * Interface for an slot vault class
 *
 * @copyright  2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface slot_vault_interface {

    /**
     * Get all slots for a user that fall on a specific year and week.
     *
     * @param int|null              $userid     slots for this user
     * @param int|null              $year       slots that fall in this year
     * @param int|null              $week       slots that fall in this week
     *
     * @return slot_interface[]     Array of slot_interfaces.
     */
    public function get_slots(
        $userid,
        $year = 0,
        $week = 0
    );

    /**
     * Delete all slots for a user that fall on a specific year and week.
     *
     * @param int|null              $userid     slots for this user
     * @param int|null              $year       slots that fall in this year
     * @param int|null              $week       slots that fall in this week
     *
     * @return result               result
     */
    public function delete_slots(
        $course = 0,
        $year = 0,
        $week = 0,
        $userid = 0,
        $useredits = true
    );

    /**
     * Saves the passed slot
     *
     * @param slot_interface $slot
     */
    public function save(slot $slot);

    /**
     * Update the specified slot
     *
     * @param int $slotid
     */
    public function confirm_slot(int $slotid, string $bookinginfo);

    /**
     * Get the date of the last posted availability slot
     *
     * @param int $studentid
     */
    public function get_last_posted_slot(int $studentid);
}
