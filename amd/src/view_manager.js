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
 * A javascript module to handler booking view changes.
 * Improvised from core_calendar.
 *
 * @module     local_booking/view_manager
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import * as Str from 'core/str';
import Templates from 'core/templates';
import Notification from 'core/notification';
import Pending from 'core/pending';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import CustomEvents from 'core/custom_interaction_events';
import SlotActions from 'local_booking/slot_actions';
import SummaryModal from 'local_booking/modal_logentry_summary';
import * as Repository from 'local_booking/repository';
import * as BookingsSelectors from 'local_booking/selectors';

/**
 * Register event listeners for the module.
 *
 * @param {object} root The root element.
 */
 const registerEventListeners = (root) => {
    root = $(root);

    // Bind click events to logentry exercise session.
    root.on('click', BookingsSelectors.links.logentryLink, (e) => {
        const target = e.target;
        let logentryLink = null;
        let logentryId = null;
        let courseId = null;
        let studentId = null;
        const pendingPromise = new Pending('local_booking/view_manager:logentryLink:click');

        if (target.matches(BookingsSelectors.actions.viewEvent)) {
            logentryLink = target;
        } else {
            logentryLink = target.closest(BookingsSelectors.actions.viewEvent);
        }

        if (logentryLink) {
            logentryId = logentryLink.dataset.logentryId;
            courseId = logentryLink.dataset.courseId;
            studentId = logentryLink.dataset.studentId;
        } else {
            logentryId = target.querySelector(BookingsSelectors.actions.viewEvent).dataset.logentryId;
            courseId = target.querySelector(BookingsSelectors.actions.viewEvent).dataset.courseId;
            studentId = target.querySelector(BookingsSelectors.actions.viewEvent).dataset.studentId;
        }

        if (logentryId) {
            // A link was found. Show the modal.

            e.preventDefault();
            // We've handled the event so stop it from bubbling
            // and causing the day click handler to fire.
            e.stopPropagation();

            renderLogentrySummaryModal(logentryId, courseId, studentId)
            .then(pendingPromise.resolve)
            .catch();
        } else {
            pendingPromise.resolve();
        }
    });

    root.on('click', BookingsSelectors.links.navLink, (e) => {
        const wrapper = root.find(BookingsSelectors.calendarwrapper);
        const courseId = wrapper.data('courseid');
        const categoryId = wrapper.data('categoryid');
        const link = e.currentTarget;

        changeWeek(root, link.href, link.dataset.year, link.dataset.week, link.dataset.time, courseId, categoryId);
        e.preventDefault();
    });

    const viewSelector = root.find(BookingsSelectors.viewSelector);
    CustomEvents.define(viewSelector, [CustomEvents.events.activate]);
    viewSelector.on(
        CustomEvents.events.activate,
        (e) => {
            e.preventDefault();

            const option = e.target;
            if (option.classList.contains('active')) {
                return;
            }

            const year = option.dataset.year,
                week = option.dataset.week,
                time = option.dataset.time,
                courseId = option.dataset.courseid,
                categoryId = option.dataset.categoryid;

            refreshWeekContent(root, year, week, time, courseId, categoryId, root, 'local_booking/calendar_week')
                .then(() => {
                    return window.history.pushState({}, '', '?view=user');
                }).fail(Notification.exception);
        }
    );
};

/**
 * Refresh the week content.
 *
 * @param {object} root The root element.
 * @param {number} year Year
 * @param {number} week week
 * @param {number} time The timestamp of the begining current week
 * @param {number} courseId The id of the course associated with the calendar shown
 * @param {number} categoryId The id of the category associated with the calendar shown
 * @param {object} target The element being replaced. If not specified, the calendarwrapper is used.
 * @param {string} template The template to be rendered.
 * @return {promise}
 */
 export const refreshWeekContent = (root, year, week, time, courseId, categoryId, target = null, template = '') => {
    startLoading(root);

    target = target || root.find(BookingsSelectors.calendarwrapper);
    template = template || root.attr('data-template');
    M.util.js_pending([root.get('id'), year, week, courseId].join('-'));

    const action = target.data('action');
    const view = target.data('viewall') ? 'all' : 'user';
    const studentId = target.data('student-id');
    const exerciseId = target.data('exercise-id');
    time = time == 0 ? Date.now() / 1000 : time;

    return Repository.getCalendarWeekData(year, week, time, courseId, categoryId, action, view, studentId, exerciseId)
        .then(context => {
            context.viewingmonth = true;
            return Templates.render(template, context);
        })
        .then((html, js) => {
            return Templates.replaceNode(target, html, js);
        })
        .always(() => {
            M.util.js_complete([root.get('id'), year, week, courseId].join('-'));
            SlotActions.setPasteState(root);
            SlotActions.setBookState(root, action);
            return stopLoading(root);
        })
        .fail(Notification.exception);
};

/**
 * Handle changes to the current calendar view.
 *
 * @param {object} root The container element
 * @param {string} url The calendar url to be shown
 * @param {number} year Year
 * @param {number} week week
 * @param {number} time The timestamp of the begining current week
 * @param {number} courseId The id of the course associated with the calendar shown
 * @param {number} categoryId The id of the category associated with the calendar shown
 * @return {promise}
 */
export const changeWeek = (root, url, year, week, time, courseId, categoryId) => {
    return refreshWeekContent(root, year, week, time, courseId, categoryId, null, '')
        .then((...args) => {
            if (url.length && url !== '#') {
                window.history.pushState({}, '', url);
            }
            return args;
        });
};

/**
 * Reload the current month view data.
 *
 * @param {object} root The container element.
 * @param {number} courseId The id of the course associated with the calendar shown
 * @param {number} categoryId The id of the category associated with the calendar shown
 * @return {promise}
 */
export const reloadCurrentMonth = (root, courseId = 0, categoryId = 0) => {
    const year = root.find(BookingsSelectors.calendarwrapper).data('year');
    const week = root.find(BookingsSelectors.calendarwrapper).data('week');
    const time = root.find(BookingsSelectors.calendarwrapper).data('time');

    courseId = courseId || root.find(BookingsSelectors.calendarwrapper).data('courseid');
    categoryId = categoryId || root.find(BookingsSelectors.calendarwrapper).data('categoryid');

    return refreshWeekContent(root, year, week, time, courseId, categoryId, null, '');
};

/**
 * Refresh student progression content.
 *
 * @param {object} root The root element.
 * @param {number} courseId The id of the course associated with the progression view shown
 * @param {number} categoryId The id of the category associated with the progression view shown
 * @param {object} target The element being replaced. If not specified, the bookingwrapper is used.
 * @param {string} template The template to be rendered.
 * @return {promise}
 */
export const refreshProgressionContent = (root, courseId, categoryId, target = null, template = '') => {
    startLoading(root);

    target = target || root.find(BookingsSelectors.progressionwrapper);
    courseId = courseId || root.find(BookingsSelectors.progressionwrapper).data('courseid');
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
 * Refresh my bookings content.
 *
 * @param {object} root The root element.
 * @param {number} courseId The id of the course associated with the progression view shown
 * @param {number} categoryId The id of the category associated with the progression view shown
 * @param {object} target The element being replaced. If not specified, the bookingwrapper is used.
 * @param {string} template The template to be rendered.
 * @return {promise}
 */
 export const refreshMyBookingsContent = (root, courseId) => {
    startLoading(root);

    const target = root.find(BookingsSelectors.mybookingswrapper);
    courseId = courseId || root.find(BookingsSelectors.progressionwrapper).data('courseid');
    M.util.js_pending([root.get('id'), courseId].join('-'));
    return Repository.getBookingsData(courseId)
        .then((context) => {
            context.viewingbooking = true;
            return Templates.render('local_booking/my_bookings', context);
        })
        .then((html, js) => {
            return Templates.replaceNode(target, html, js);
        })
        .always(() => {
            M.util.js_complete([root.get('id'), courseId].join('-'));
            return stopLoading(root);
        })
        .fail(Notification.exception);
};

/**
 * Render the logentry summary modal.
 *
 * @param {Number} logentryId The graded session logbook entry id.
 * @param {Number} courseId The graded session course id.
 * @param {Number} studentId The graded session student id.
 * @returns {Promise}
 */
 export const renderLogentrySummaryModal = (logentryId, courseId, studentId) => {
    const pendingPromise = new Pending('local_booking/view_manager:renderLogentrySummaryModal');

    // Calendar repository promise.
    return Repository.getLogentryById(logentryId, courseId, studentId)
    .then((getEventResponse) => {
        if (!getEventResponse.logentry) {
            throw new Error(Str.get_string('errorlogentryfetch', 'local_booking') + logentryId);
        }

        return getEventResponse.logentry;
    })
    .then(logentryData => {
        // Build the modal parameters from the logentry data.
        const modalParams = {
            title: Str.get_string('logentry', 'local_booking'),
            type: SummaryModal.TYPE,
            body: Templates.render('local_booking/logentry_summary_body', logentryData),
            templateContext: {
                canedit: logentryData.canedit,
                candelete: logentryData.candelete,
                isactionevent: logentryData.isactionevent,
                url: logentryData.url,
                action: logentryData.action
            }
        };

        // Create the modal.
        return ModalFactory.create(modalParams);
    })
    .then(modal => {
        // Handle hidden event.
        modal.getRoot().on(ModalEvents.hidden, function() {
            // Destroy when hidden.
            modal.destroy();
        });

        // Finally, render the modal!
        modal.show();

        return modal;
    })
    .then(modal => {
        pendingPromise.resolve();

        return modal;
    })
    .catch(Notification.exception);
};

/**
 * Set the element state to loading.
 *
 * @param {object} root The container element
 * @method startLoading
 */
 export const startLoading = (root) => {
    const loadingIconContainer = root.find(BookingsSelectors.containers.loadingIcon);
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
    const loadingIconContainer = root.find(BookingsSelectors.containers.loadingIcon);
    loadingIconContainer.addClass('hidden');

    $(root).one('submit', function() {
        $(this).find('input[type="submit"]').attr('enabled', 'enabled');
    });
};

export const init = (root, view) => {
    registerEventListeners(root, view);
};