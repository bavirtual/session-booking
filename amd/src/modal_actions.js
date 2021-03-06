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
 * This module handles additional logentry modal form action.
 *
 * @module     local_booking/modal_actions
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'core/str',
    'core/notification',
    'core/modal_factory',
    'core/modal_events',
    'core/pending',
    'local_booking/repository',
    'local_booking/events',
    'local_booking/selectors',
    'local_booking/booking_actions',
],
function(
    $,
    Str,
    Notification,
    ModalFactory,
    ModalEvents,
    Pending,
    Repository,
    BookingSessions,
    BookingSelectors,
    BookingActions,
) {

    /**
     * Prepares the action for the summary modal's delete action.
     *
     * @method  confirmDeletion
     * @param   {Number} logentryId The ID of the logentry.
     * @param   {Number} userId The user of the logentry.
     * @param   {Number} courseId The course of the logentry.
     * @return  {Promise}
     */
    var confirmDeletion = (logentryId, userId, courseId) => {
        var pendingPromise = new Pending('local_booking/booking_actions:confirmDeletion');
        var deleteStrings = [
            {
                key: 'deletelogentry',
                component: 'local_booking'
            },
        ];

        var deletePromise;
        deleteStrings.push({
            key: 'confirmlogentrydelete',
            component: 'local_booking'
        });


        deletePromise = ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
        });

        var stringsPromise = Str.get_strings(deleteStrings);

        // Setup modal delete prompt form
        var finalPromise = $.when(stringsPromise, deletePromise)
        .then(function(strings, deleteModal) {
            deleteModal.setRemoveOnClose(true);
            deleteModal.setTitle(strings[0]);
            deleteModal.setBody(strings[1]);
            deleteModal.setSaveButtonText(strings[0]);

            deleteModal.show();

            deleteModal.getRoot().on(ModalEvents.save, function() {
                var pendingPromise = new Pending('local_booking/booking_actions:initModal:deletedlogentry');
                Repository.deleteLogentry(logentryId, userId, courseId)
                    .then(function() {
                        $('body').trigger(BookingSessions.deleted, [logentryId, false]);
                        return;
                    })
                    .then(pendingPromise.resolve)
                    .always(function() {
                        Notification.fetchNotifications();
                    })
                    .catch(Notification.exception);
            });

            return deleteModal;
        })
        .then(function(modal) {
            pendingPromise.resolve();

            return modal;
        })
        .catch(Notification.exception);

        return finalPromise;
    };

    /**
     * Register the listeners required to delete the logentry.
     *
     * @method  registerDelete
     * @param   {jQuery} root
     */
     var registerDelete = (root) => {
        root.on('click', BookingSelectors.actions.deleteLogentry, function(e) {
            // Fetch the logentry title, and pass them into the new dialogue.
            var logentrySource = root.find(BookingSelectors.logentryitem),
                logentryId = logentrySource.data('logentryId'),
                userId = logentrySource.data('userId'),
                courseId = logentrySource.data('courseId');
            confirmDeletion(logentryId, userId, courseId);

            e.preventDefault();
        });
    };

    /**
     * Register the listeners required to redirect to
     * exercise (assignment) grading page.
     *
     * @method  registerRedirect
     * @param   {jQuery} root
     */
     const registerRedirect = function(root) {
        root.on('click', BookingSelectors.actions.gotoFeedback, function(e) {
            // Call redirect to assignment feedback page
            BookingActions.gotoFeedback(root, e);

            e.preventDefault();
        });
    };

    return {
        registerDelete: registerDelete,
        registerRedirect: registerRedirect
    };
});
