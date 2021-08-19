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

define([
            'jquery',
            'core/templates',
            'core/notification',
            'local_booking/repository',
            'local_booking/view_manager',
            'local_booking/selectors',
        ],
        function(
            $,
            Templates,
            Notification,
            Repository,
            ViewManager,
            BookingSelectors,
        ) {

    var SELECTORS = {
        ROOT: "[data-region='calendar']",
        DAY: "[data-region='day']",
        CANCEL_BUTTON: "[data-region='cancel-button']",
        LOADING_ICON_CONTAINER: '[data-region="loading-icon-container"]',
        NEW_EVENT_BUTTON: "[data-action='new-event-button']",
        BOOKING: "[data-region='booking-data']",
        LOADING_ICON: '.loading-icon',
        BOOKING_WRAPPER: ".bookingwrapper",
        TODAY: '.today',
    };

    /**
     * Register event listeners for the module.
     *
     * @param {object} root The calendar root element
     */
    var registerEventListeners = function(root) {
        // Listen the click on the Cancel booking buttons.
        root.on('click', SELECTORS.CANCEL_BUTTON, function() {
            ViewManager.startLoading(root);

            // Get exercise id and the user id from the URL
            const category = root.find(SELECTORS.BOOKING_WRAPPER).data('categoryid');
            const course = root.find(SELECTORS.BOOKING_WRAPPER).data('courseid');
            const booking = root.find(SELECTORS.BOOKING).data('bookingid');
alert('cancelling booking::' + booking);
            // Send the form data to the server for processing.
            return Repository.cancelBooking(course, booking)
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
                    return ViewManager.refreshBookingsContent(root, course, category);
                }
                .bind(this))
                .fail(Notification.exception);
        });

        // Remove loading
        const loadingIconContainer = root.find(BookingSelectors.containers.loadingIcon);
        loadingIconContainer.addClass('hidden');

    };

    return {
        init: function(root) {
            root = $(root);
            registerEventListeners(root);
        }
    };
});
