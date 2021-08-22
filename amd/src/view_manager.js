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
 * A javascript module to handler calendar view changes.
 *
 * @module     local_booking/view_manager
 * @copyright  2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import Templates from 'core/templates';
import Notification from 'core/notification';
import * as Repository from 'local_booking/repository';
import * as BookingsSelector from 'local_booking/selectors';

/**
 * Cancel a specific booking and update UI.
 *
 * @method cancelBooking
 * @param {object} root The My Bookings root element
 * @param {object} e    The click event on the Cancel button
 * @return {object} The create modal promise
 */
export const cancelBooking = (root, e) => {
    startLoading(root);

    var target = e.target;
    // Get exercise id and the user id from the URL
    const courseId = courseId || root.find(BookingsSelector.wrapper).data('courseid');
    const bookingId = target.dataset.bookingid;

    // Send the request data to the server for processing.
    return Repository.cancelBooking(bookingId)
        .then(function(response) {
            if (response.validationerror) {
                // eslint-disable-next-line no-alert
                alert('Errors encountered: Unable to cancel booking!');
            }
            return refreshBookingsContent(root, courseId);
        }
        .bind(this))
        .always(function() {
            Notification.fetchNotifications();
            stopLoading(root);
        }
        .bind(this))
        .fail(Notification.exception);
};

/**
 * Refresh bookings content.
 *
 * @param {object} root The root element.
 * @param {number} courseId The id of the course whose events are shown
 * @param {number} categoryId The id of the category whose events are shown
 * @param {object} target The element being replaced. If not specified, the calendarwrapper is used.
 * @param {string} template The template to be rendered.
 * @return {promise}
 */
export const refreshBookingsContent = (root, courseId, categoryId, target = null, template = '') => {
    startLoading(root);

    target = target || root.find(BookingsSelector.wrapper);
    template = template || root.attr('data-template');
    M.util.js_pending([root.get('id'), courseId, categoryId].join('-'));
    return Repository.getBookingsData(courseId, categoryId)
        .then((context) => {
            context.viewingbooking = true;
            return Templates.render(template, context);
        })
        .then((html, js) => {
            return Templates.replaceNode(target, html, js);
        })
        .always(() => {
            M.util.js_complete([root.get('id'), courseId, categoryId].join('-'));
            return stopLoading(root);
        })
        .fail(Notification.exception);
};

/**
 * Set the element state to loading.
 *
 * @param {object} root The container element
 * @method startLoading
 */
 export const startLoading = (root) => {
    const loadingIconContainer = root.find(BookingsSelector.containers.loadingIcon);
    loadingIconContainer.removeClass('hidden');

    $(root).one('submit', function() {
        $(this).find('input[type="submit"]').attr('disabled', 'disabled');
    });
};

/**
 * Remove the loading state from the element.
 *
 * @param {object} root The container element
 * @method stopLoading
 */
export const stopLoading = (root) => {
    const loadingIconContainer = root.find(BookingsSelector.containers.loadingIcon);
    loadingIconContainer.addClass('hidden');

    $(root).one('submit', function() {
        $(this).find('input[type="submit"]').attr('enabled', 'enabled');
    });
};
