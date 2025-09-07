/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * Agenda View
 *
 * This view had to be extended because the time format was hardcoded in _renderTaskGroups
 * Current kendo v2021.2.616
 */
;(function(app) {

    kendo.ui.AgendaView = kendo.ui.AgendaView.extend({
        _renderTaskGroups: function (tasksGroups, groups) {
            var tableRows = [];
            var editable = this.options.editable;
            var showDelete = editable && editable.destroy !== false && !this._isMobile();
            var isMobile = this._isMobile();
            var sumOfItemsForDate = this._groupedView._getSumOfItemsForDate(tasksGroups);
            var groupsInDay = this._groupedView._getGroupsInDay(tasksGroups, groups);
            var groupsRowSpanIndex = 0;
            for (var taskGroupIndex = 0; taskGroupIndex < tasksGroups.length; taskGroupIndex++) {
                var date = tasksGroups[taskGroupIndex].value;
                var tasks = tasksGroups[taskGroupIndex].items;
                var today = kendo.date.isToday(date);
                for (var taskIndex = 0; taskIndex < tasks.length; taskIndex++) {
                    var task = tasks[taskIndex];
                    var tableRow = [];
                    var headerCells = !isMobile ? tableRow : [];
                    this._groupedView._renderTaskGroupsCells(
                        headerCells, groups, taskGroupIndex, taskIndex, groupsInDay, sumOfItemsForDate,
                        date, groupsRowSpanIndex
                    );
                    groupsRowSpanIndex++;
                    if (taskIndex === 0) {
                        if (isMobile) {
                            headerCells.push(kendo.format('<td class="k-scheduler-datecolumn {1}" colspan="2">{0}</td>',
                                this._dateTemplate({
                                date: date,
                                isMobile: isMobile
                            }), !this.groupedResources.length ? 'k-first' : ''));
                            tableRows.push('<tr role="row" aria-selected="false"' +
                            (today ? ' class="k-today">' : '>') + headerCells.join('') + '</tr>');
                        } else {
                            this._groupedView._renderDateCell(
                                tableRow, groups, tasks, date, taskGroupIndex, tasksGroups
                            );
                        }
                    }

                    //only changes made:
                    let userTimeFormat = app.Calendar.utils.getKendoTimeMapping(
                        app.user.attributes.preferences.timepref
                    );
                    if (task.head) {
                        task.format = `{0:${userTimeFormat}}`;
                    } else if (task.tail) {
                        task.format = `{1:${userTimeFormat}}`;
                    } else {
                        task.format = `{0:${userTimeFormat}}-{1:${userTimeFormat}}`;
                    }

                    task.resources = this.eventResources(task);
                    tableRow.push(kendo.format(
                        '<td class="k-scheduler-timecolumn {4}"><div>{0}{1}{2}</div></td><td>{3}</td>',
                        task.tail || task.middle ? '<span class="k-icon k-i-arrow-60-left"></span>' : '',
                        this._timeTemplate(task.clone({
                        start: task._startTime || task.start,
                        end: task.endTime || task.end
                    })), task.head || task.middle ? '<span class="k-icon k-i-arrow-60-right"></span>' : '',
                    this._eventTemplate(task.clone({
                        showDelete: showDelete,
                        messages: this.options.messages
                    })), !this.groupedResources.length && isMobile ? 'k-first' : ''));
                    tableRows.push('<tr role="row" aria-selected="false"' + (today ? ' class="k-today">' : '>') +
                    tableRow.join('') + '</tr>');
                }
            }
            return tableRows.join('');
        },
    });
})(SUGAR.App);
