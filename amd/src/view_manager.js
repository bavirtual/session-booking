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
import * as CalendarRepository from 'local_booking/repository';
import CalendarEvents from 'local_booking/events';
import * as CalendarSelectors from 'local_booking/selectors';
import SlotActions from 'local_booking/slot_actions';
import CustomEvents from 'core/custom_interaction_events';

/**
 * Register event listeners for the module.
 *
 * @param {object} root The root element.
 */
const registerEventListeners = (root) => {
    root = $(root);

    root.on('click', CalendarSelectors.links.navLink, (e) => {
        const wrapper = root.find(CalendarSelectors.wrapper);
        const courseId = wrapper.data('courseid');
        const categoryId = wrapper.data('categoryid');
        const link = e.currentTarget;

        changeWeek(root, link.href, link.dataset.year, link.dataset.week, link.dataset.time, courseId, categoryId);
        e.preventDefault();
    });

    const viewSelector = root.find(CalendarSelectors.viewSelector);
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
                    return window.history.pushState({}, '', '?view=week');
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
 * @param {number} courseId The id of the course whose events are shown
 * @param {number} categoryId The id of the category whose events are shown
 * @param {object} target The element being replaced. If not specified, the calendarwrapper is used.
 * @param {string} template The template to be rendered.
 * @return {promise}
 */
export const refreshWeekContent = (root, year, week, time, courseId, categoryId, target = null, template = '') => {
    startLoading(root);

    target = target || root.find(CalendarSelectors.wrapper);
    template = template || root.attr('data-template');
    M.util.js_pending([root.get('id'), year, week, courseId].join('-'));
    const includenavigation = root.data('includenavigation');
    const mini = root.data('mini');
    time = time == 0 ? Date.now() / 1000 : time;
    return CalendarRepository.getCalendarWeekData(year, week, time, courseId, categoryId, includenavigation, mini)
        .then(context => {
            context.viewingmonth = true;
            return Templates.render(template, context);
        })
        .then((html, js) => {
            return Templates.replaceNode(target, html, js);
        })
        .then(() => {
            document.querySelector('body').dispatchEvent(new CustomEvent(CalendarEvents.viewUpdated));
            return;
        })
        .always(() => {
            M.util.js_complete([root.get('id'), year, week, courseId].join('-'));
            SlotActions.setPasteState(root);
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
 * @param {number} courseId The id of the course whose events are shown
 * @param {number} categoryId The id of the category whose events are shown
 * @return {promise}
 */
export const changeWeek = (root, url, year, week, time, courseId, categoryId) => {
    return refreshWeekContent(root, year, week, time, courseId, categoryId, null, '')
        .then((...args) => {
            if (url.length && url !== '#') {
                window.history.pushState({}, '', url);
            }
            return args;
        })
        .then((...args) => {
            $('body').trigger(CalendarEvents.monthChanged, [year, week, courseId, categoryId]);
            return args;
        });
};

/**
 * Reload the current month view data.
 *
 * @param {object} root The container element.
 * @param {number} courseId The course id.
 * @param {number} categoryId The id of the category whose events are shown
 * @return {promise}
 */
export const reloadCurrentMonth = (root, courseId = 0, categoryId = 0) => {
    const year = root.find(CalendarSelectors.wrapper).data('year');
    const week = root.find(CalendarSelectors.wrapper).data('week');
    const time = root.find(CalendarSelectors.wrapper).data('time');

    courseId = courseId || root.find(CalendarSelectors.wrapper).data('courseid');
    categoryId = categoryId || root.find(CalendarSelectors.wrapper).data('categoryid');

    return refreshWeekContent(root, year, week, time, courseId, categoryId, null, '');
};

/**
 * Set the element state to loading.
 *
 * @param {object} root The container element
 * @method startLoading
 */
 export const startLoading = (root) => {
    const loadingIconContainer = root.find(CalendarSelectors.containers.loadingIcon);
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
    const loadingIconContainer = root.find(CalendarSelectors.containers.loadingIcon);
    loadingIconContainer.addClass('hidden');

    $(root).one('submit', function() {
        $(this).find('input[type="submit"]').attr('enabled', 'enabled');
    });
};

/**
 * Reload the current week view data.
 *
 * @param {object} root The container element.
 * @param {number} courseId The course id.
 * @param {number} categoryId The id of the category whose events are shown
 * @param {object} target The element being replaced. If not specified, the calendarwrapper is used.
 * @param {string} template The template to be rendered.
 * @return {promise}
 */
export const reloadCurrentUpcoming = (root, courseId = 0, categoryId = 0, target = null, template = '') => {
    startLoading(root);

    target = target || root.find(CalendarSelectors.wrapper);
    template = template || root.attr('data-template');
    courseId = courseId || root.find(CalendarSelectors.wrapper).data('courseid');
    categoryId = categoryId || root.find(CalendarSelectors.wrapper).data('categoryid');

    return CalendarRepository.getCalendarUpcomingData(courseId, categoryId)
        .then((context) => {
            context.viewingupcoming = true;
            return Templates.render(template, context);
        })
        .then((html, js) => {
            return Templates.replaceNode(target, html, js);
        })
        .then(() => {
            document.querySelector('body').dispatchEvent(new CustomEvent(CalendarEvents.viewUpdated));
            return;
        })
        .always(function() {
            return stopLoading(root);
        })
        .fail(Notification.exception);
};

export const init = (root, view) => {
    registerEventListeners(root, view);
};
