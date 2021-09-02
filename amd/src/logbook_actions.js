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
 * This module handles logbook entry add and edits
 *
 * @module     local_booking/logbook_actions
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'core/notification',
    'core/modal_factory',
    'local_booking/modal_entry_form',
    'local_booking/logbook_entries',
    'local_booking/selectors',
    'core/pending',
],
function(
    $,
    Notification,
    ModalFactory,
    ModalLogbookForm,
    CalendarEvents,
    BookingSelectors,
    Pending
) {

    /**
     * Create the logbook form modal for creating new
     * logbook entries and editing existing entries.
     *
     * @method registerEntryFormModal
     * @param {object} root The booking root element
     * @return {object} The create modal promise
     */
    var registerEntryFormModal = function(root) {
        var eventFormPromise = ModalFactory.create({
            type: ModalLogbookForm.TYPE,
            large: true
        });

        // Bind click event on the new event button.
        root.on('click', BookingSelectors.actions.create, function(e) {
            eventFormPromise.then(function(modal) {
                var wrapper = root.find(BookingSelectors.wrapper);

                var categoryId = wrapper.data('categoryid');
                if (typeof categoryId !== 'undefined') {
                    modal.setCategoryId(categoryId);
                }

                // Attempt to find the cell for today.
                // If it can't be found, then use the start time of the first day on the calendar.
                var today = root.find(BookingSelectors.today);
                var firstDay = root.find(BookingSelectors.day);
                if (!today.length && firstDay.length) {
                    modal.setStartTime(firstDay.data('newEventTimestamp'));
                }

                modal.setContextId(wrapper.data('contextId'));
                modal.setCourseId(wrapper.data('courseid'));
                modal.show();
                return;
            })
            .fail(Notification.exception);

            e.preventDefault();
        });

        root.on('click', BookingSelectors.actions.edit, function(e) {
            e.preventDefault();
            var target = $(e.currentTarget),
                calendarWrapper = target.closest(BookingSelectors.wrapper),
                eventWrapper = target.closest(BookingSelectors.eventItem);

            eventFormPromise.then(function(modal) {
                // When something within the calendar tells us the user wants
                // to edit an event then show the event form modal.
                modal.setEventId(eventWrapper.data('eventId'));

                modal.setContextId(calendarWrapper.data('contextId'));
                modal.show();

                e.stopImmediatePropagation();
                return;
            }).fail(Notification.exception);
        });


        return eventFormPromise;
    };

    /**
     * Register the listeners required to edit the event.
     *
     * @param   {jQuery} root
     * @param   {Promise} eventFormModalPromise
     * @returns {Promise}
     */
    function registerEditListeners(root, eventFormModalPromise) {
        var pendingPromise = new Pending('local_booking/logbook_actions:registerEditListeners');

        return eventFormModalPromise
        .then(function(modal) {
            // When something within the calendar tells us the user wants
            // to edit an event then show the event form modal.
            $('body').on(CalendarEvents.editEvent, function(e, eventId) {
                var calendarWrapper = root.find(BookingSelectors.wrapper);
                modal.setEventId(eventId);
                modal.setContextId(calendarWrapper.data('contextId'));
                modal.show();

                e.stopImmediatePropagation();
            });
            return modal;
        })
        .then(function(modal) {
            pendingPromise.resolve();

            return modal;
        })
        .catch(Notification.exception);
    }

    return {
        registerEditListeners: registerEditListeners,
        registerEntryFormModal: registerEntryFormModal
    };
});
