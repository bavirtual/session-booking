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
 * This module handles logbook entry events
 *
 * @module     local_booking/logbook_entries
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    return {
        created: 'booking-entries:created',
        deleted: 'booking-entries:deleted',
        deleteAll: 'booking-entries:delete_all',
        updated: 'booking-entries:updated',
        editEvent: 'booking-entries:edit_event',
        editActionEvent: 'booking-entries:edit_action_event',
        eventMoved: 'booking-entries:event_moved',
        dayChanged: 'booking-entries:day_changed',
        monthChanged: 'booking-entries:month_changed',
        moveEvent: 'booking-entries:move_event',
        filterChanged: 'booking-entries:filter_changed',
        viewUpdated: 'booking-entries:view_updated',
    };
});
