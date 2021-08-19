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
 * A javascript module to handle calendar ajax actions.
 *
 * @module     local_booking/repository
 * @copyright  2017 Simey Lameze <lameze@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Ajax from 'core/ajax';

/**
 * Change the start day for the given event id. The day timestamp
 * only has to be any time during the target day because only the
 * date information is extracted, the time of the day is ignored.
 *
 * @param {int} courseId    The id of the associated course
 * @param {int} bookingId   The booking id to cancel
 * @return {promise}
 */
 export const cancelBooking = (courseId, bookingId) => {
    const request = {
        methodname: 'local_booking_cancel',
        args: {
            courseid: courseId,
            bookingid: bookingId,
        }
    };

    return Ajax.call([request])[0];
};

/**
 * Get calendar data for the month view.
 *
 * @method getBookingsData
 * @param {number} courseId The course id.
 * @param {number} categoryId The category id.
 * @return {promise} Resolved with the month view data.
 */
export const getBookingsData = (courseId, categoryId) => {
    const request = {
        methodname: 'local_booking_get_mybookings_view',
        args: {
            courseid: courseId,
            categoryid: categoryId,
        }
    };

    return Ajax.call([request])[0];
};
