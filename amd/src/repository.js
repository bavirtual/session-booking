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
 * Get sesison booking, my bookings, and my students data to view.
 *
 * @method getBookingsData
 * @param {number} courseId The course id.
 * @param {number} categoryId The category id.
 * @return {promise} Resolved with the month view data.
 */
 export const getBookingsData = (courseId) => {
    const request = {
        methodname: 'local_booking_get_bookings_view',
        args: {
            courseid: courseId,
        }
    };

    return Ajax.call([request])[0];
};

/**
 * Cancel a sepcific booking for a student.
 *
 * @param {int} bookingId   The booking id to cancel
 * @return {promise}
 */
 export const cancelBooking = (bookingId) => {
    const request = {
        methodname: 'local_booking_cancel_booking',
        args: {
            bookingid: bookingId,
        }
    };

    return Ajax.call([request])[0];
};
