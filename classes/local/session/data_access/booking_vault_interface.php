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

use local_booking\local\session\entities\session;

/**
 * Interface for an session vault class
 *
 * @copyright  2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface session_vault_interface {

    /**
     * Get all sessions for a user that fall on a specific year and week.
     *
     * @param int|null              $userid     sessions for this user
     * @param int|null              $year       sessions that fall in this year
     * @param int|null              $week       sessions that fall in this week
     *
     * @return session_interface[]     Array of session_interfaces.
     */
    public function get_sessions(
        $year = 0,
        $week = 0
    );

    /**
     * Delete all sessions for a user that fall on a specific year and week.
     *
     * @param int|null              $userid     sessions for this user
     * @param int|null              $year       sessions that fall in this year
     * @param int|null              $week       sessions that fall in this week
     *
     * @return result               result
     */
    public function delete_sessions(
        $year = 0,
        $week = 0
    );

    /**
     * Saves the passed session
     *
     * If using this function for pagination then you can provide the last session that you've seen
     * ($aftersession) and it will be used to appropriately offset the result set so that you don't
     * receive the same sessions again.
     *
     * @param session_interface $session
     */
    public function save(session $session);
}
