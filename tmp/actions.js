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
 * Contain the logic for the quick add or update event modal.
 *
 * @module     local_booking/actions
 * @copyright  2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
        'jquery',
        'core/templates',
        'core/notification',
        'local_booking/repository',
        'local_booking/view_manager',
    ],
    function(
        $,
        Templates,
        Notification,
        Repository,
        ViewManager,
    ) {

    var SELECTORS = {
    CANCEL_BUTTON: "[data-region='cancel-button']",
    LOADING_ICON_CONTAINER: '[data-region="loading-icon-container"]',
    BOOKING: "[data-region='booking-info']",
    LOADING_ICON: '.loading-icon',
    BOOKING_WRAPPER: ".bookingwrapper",
    };

    /**
     * Cancel a specific booking and update UI.
     *
     * @method cancelBooking
     * @param {object} root The My Bookings root element
     * @param {object} e    The click event on the Cancel button
     * @return {object} The create modal promise
     */
     function cancelBooking(root, e) {
        ViewManager.startLoading(root);

        var target = e.target;
        // Get exercise id and the user id from the URL
        // const category = root.find(SELECTORS.BOOKING_WRAPPER).data('categoryid');
        // const course = root.find(SELECTORS.BOOKING_WRAPPER).data('courseid');
        const bookingId = target.dataset.bookingid;
        const bookingrow = document.getElementById(bookingId);
        bookingrow.style.display = 'none';

        // Send the request data to the server for processing.
        return Repository.cancelBooking(bookingId)
            .then(function(response) {
                if (response.validationerror) {
                    // eslint-disable-next-line no-alert
                    alert('Errors encountered: Unable to cancel booking!');
                }
                return;
            }
            .bind(this))
            .always(function() {
                Notification.fetchNotifications();
                ViewManager.stopLoading(root);
            }
            .bind(this))
            .fail(Notification.exception);
    }

    return {
        cancelBooking: cancelBooking,
    };
});