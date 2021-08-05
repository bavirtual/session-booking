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
 * @param {string} weekSlots The URL encoded values from the form
 * @param {int} course The id of the associated course
 * @param {int} year The id of the event to update
 * @param {int} week A timestamp for some time during the target day
 * @return {promise}
 */
 export const saveSlots = (weekSlots, course, year, week) => {
    const request = {
        methodname: 'local_booking_save_slots',
        args: {
            slots: weekSlots,
            courseid: course,
            year: year,
            week: week
        }
    };

    return Ajax.call([request])[0];
};

/**
 * Change the start day for the given event id. The day timestamp
 * only has to be any time during the target day because only the
 * date information is extracted, the time of the day is ignored.
 *
 * @param {int} course The id of the associated course
 * @param {int} year The id of the event to update
 * @param {int} week A timestamp for some time during the target day
 * @return {promise}
 */
 export const clearSlots = (course, year, week) => {
    const request = {
        methodname: 'local_booking_delete_slots',
        args: {
            courseid: course,
            year: year,
            week: week
        }
    };

    return Ajax.call([request])[0];
};

/**
 * Get calendar data for the month view.
 *
 * @method getCalendarMonthData
 * @param {number} year Year
 * @param {number} week Week
 * @param {number} time Timestamp
 * @param {number} courseId The course id.
 * @param {number} categoryId The category id.
 * @param {boolean} includeNavigation Whether to include navigation.
 * @param {boolean} mini Whether the month is in mini view.
 * @return {promise} Resolved with the month view data.
 */
export const getCalendarWeekData = (year, week, time, courseId, categoryId, includeNavigation, mini) => {
    const request = {
        methodname: 'local_booking_get_weekly_view',
        args: {
            year,
            week,
            time,
            courseid: courseId,
            categoryid: categoryId,
            includenavigation: includeNavigation,
            mini,
        }
    };

    return Ajax.call([request])[0];
};
