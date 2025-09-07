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
(function register(app) {
    app.events.on('app:init', function init() {
        app.plugins.register('FilterOperatorManager', ['view'], {
            /**
             * @inheritdoc
             */
            onAttach: function() {
                this.on('init', function registerFilterOperatorManager() {
                    this.operatorController = false;
                    this.tempRelateController = false;
                });
            },

            /**
             * Setup proper input depending on qulifier type
             */
            updateFilterInput: function() {
                const qualifierName = this._filterData ? this._filterData.qualifier_name : '';
                const fieldType = this._fieldType;
                const operatorType = this.getOperatorType(qualifierName, fieldType);

                this._loading = true;
                this._inputValue = false;
                this._inputValue1 = false;
                this._inputValue2 = false;
                this._inputValue3 = false;

                this.operatorController = this.createOperatorController(
                    operatorType,
                    qualifierName,
                    fieldType
                );

                this.operatorController.getUpdatedInput(
                    _.bind(this.updateInputValues, this)
                );
            },

            /**
             * Get operator type
             *
             * @param {string} qualifierName
             * @param {string} fieldType
             *
             * @return {string}
             */
            getOperatorType: function(qualifierName, fieldType) {
                let operatorType = 'DefaultOperator';

                if (qualifierName === 'anything') {
                    operatorType = 'EmptyOperator';
                } else if (qualifierName === 'between') {
                    operatorType = 'BetweenOperator';
                } else if (_.contains(['between_dates', 'between_datetimes'], qualifierName)) {
                    operatorType = 'DateOperator';
                } else if (qualifierName.includes('_n_days')) {
                    operatorType = 'DateOperator';
                } else if (_.contains(['empty', 'not_empty'], qualifierName)) {
                    operatorType = 'EmptyOperator';
                } else if (_.contains(['date', 'datetime', 'datetimecombo', 'service-enddate'], fieldType)) {
                    operatorType = 'DateOperator';
                } else if (_.contains(['id', 'name', 'fullname', 'relate'], fieldType)) {
                    operatorType = 'RelateOperator';
                } else if (_.contains(['username', 'assigned_user_name'], fieldType)) {
                    operatorType = 'UsernameOperator';
                } else if (_.contains(
                    ['enum', 'multienum', 'parent_type', 'timeperiod', 'currency_id', 'radioenum'],
                    fieldType)
                ) {
                    operatorType = 'EnumOperator';
                } else if (fieldType === 'bool') {
                    operatorType = 'BoolOperator';
                } else {
                    operatorType = 'DefaultOperator';
                }

                return operatorType;
            },

            /**
             * Update input values
             *
             * @param {Object} updates
             */
            updateInputValues: function(updates) {
                if (this.disposed) {
                    return;
                }

                _.each(updates.properties, (inputValue, inputKey) => {
                    this[inputKey] = inputValue;
                });

                _.each(updates.events, (eventData) => {
                    this.listenTo(
                        eventData.entity,
                        eventData.eventName,
                        _.bind(this.executeCallback, this, eventData.callback),
                        this
                    );
                });

                this._loading = false;

                if (updates.needsRendering) {
                    this.render();
                } else {
                    if (!_.isEmpty(this._searchTerm)) {
                        this._setupSearchOptions();
                    }

                    this._setupSelectAll();
                }

                if (updates.needsUpdating) {
                    this.updateFilterOperatorWidget();
                }
            },

            /**
             * Execute callback
             *
             * @param {Function} callback Callback function
             *
             * @param {Object} data
             */
            executeCallback: function(callback, data) {
                const response = callback(data);

                _.each(response, (inputValue, inputKey) => {
                    this[inputKey] = inputValue;
                });

                this._initProperties();
            },

            /**
             * Create operator controller
             *
             * @param {string} operatorType
             * @param {string} qualifierName
             * @param {string} fieldType
             *
             * @return {Object}
             */
            createOperatorController: function(operatorType, qualifierName, fieldType) {
                const operatorController = new app.filterOperators[operatorType]({
                    _qualifierName: qualifierName,
                    _fieldType: fieldType,
                    _filterData: this._filterData,
                    _seedModule: this._seedModule,
                    _seedFieldDef: this._seedFieldDef,
                    _optionsList: this._optionsList,
                    _users: this._users,
                });

                return operatorController;
            },

            /**
             * Checks if the values are valid
             *
             * @return {boolean}
             */
            isValid: function() {
                const data = this._filterData;
                const fieldTypesRequiringDates = [
                    'date',
                    'date-between',
                    'datetime-between',
                    'after',
                    'before',
                    'on',
                    'datetimecombo'
                ];

                if (_.contains(fieldTypesRequiringDates, this._inputType)) {
                    let requiredValuesAreGiven;

                    if (this._inputType === 'datetime-between') {
                        requiredValuesAreGiven = data.input_name0 && data.input_name1 && data.input_name2 && data.input_name3;
                    } else if (this._inputType === 'date-between' || this._inputType === 'datetimecombo') {
                        requiredValuesAreGiven = data.input_name0 && data.input_name1;
                    } else {
                        requiredValuesAreGiven = !_.isEmpty(data.input_name0);
                    }

                    if (!requiredValuesAreGiven) {
                        return false;
                    }

                    if (!_.isEmpty(data.input_name0) && !this.isValidDatePickerFormat(this._inputValue)) {
                        return false;
                    }

                    if (!_.isEmpty(data.input_name1) &&
                        (this._inputType === 'date-between' && !this.isValidDatePickerFormat(this._inputValue1)) ||
                        (this._inputType === 'datetime-between' && !this.isValidTimePickerFormat(this._inputValue1))) {
                        return false;
                    }

                    if (!_.isEmpty(data.input_name2) &&
                        this._inputType !== 'datetimecombo' &&
                        !this.isValidDatePickerFormat(this._inputValue2)) {
                        return false;
                    }

                    if (!_.isEmpty(data.input_name3) &&
                        (this._inputType === 'date-between' && !this.isValidDatePickerFormat(this._inputValue3)) ||
                        (this._inputType === 'datetime-between' && !this.isValidTimePickerFormat(this._inputValue3))) {
                        return false;
                    }
                }

                if (!this._inputType || this._inputType === 'empty') {
                    return true;
                }

                return (data.input_name0 || data.input_name1 || data.input_name2 || data.input_name3);
            },

            /**
             * Is valid time picker format
             *
             * @param {string} time
             * @return {boolean}
             */
            isValidTimePickerFormat: function(time) {
                const timeRegex = /^(?:(?:0?\d|1[0-2]):[0-5]\d)$/;
                const meridianLength = 2;
                const validTime = time.substring(0, time.length - meridianLength);

                return timeRegex.test(validTime);
            },

            /**
             * Create a relate field controller
             */
            createTempRelateController: function() {
                this.disposeTempRelateController();

                this.tempRelateController = app.view.createField({
                    def: this._seedFieldDef,
                    view: this,
                    viewName: 'edit',
                    model: this._inputValue,
                });

                // we want to bypass all acl rules
                // as on runtime filters the user should always be able to see the records
                this.tempRelateController._checkAccessToAction = function() {
                    return true;
                };

                this.tempRelateController.render();

                this.$('[data-container="relate-input-container"]').append(this.tempRelateController.$el);
            },

            /**
             * Dispose the relate field controller
             */
            disposeTempRelateController: function() {
                if (this.tempRelateController) {
                    this.tempRelateController.dispose();

                    this.tempRelateController = false;
                }
            },

            /**
             * Returns filter summary text
             *
             * @return {string}
             */
            getSummaryText: function() {
                let filterValue = this._filterData.input_name0;
                let filterValue2 = this._filterData.input_name1;

                const qualifierName = this._filterData.qualifier_name;

                if (_.isUndefined(filterValue) || filterValue === 'undefined') {
                    filterValue = '';
                }

                if (_.isUndefined(filterValue2) || filterValue2 === 'undefined') {
                    filterValue2 = '';
                }

                let prefixLabel = _.filter(this._operators, function getOperator(label, type) {
                    return type === this._filterData.qualifier_name;
                }, this);

                let prefixEl = document.createElement('i');
                prefixEl.innerText = app.lang.get(_.first(prefixLabel), 'Reports');

                let prefix = prefixEl.outerHTML;

                if (qualifierName === filterValue) {
                    return prefix;
                }

                if (_.isArray(filterValue)) {
                    let filterValueLabels = [];

                    if (!_.isEmpty(this._optionsList)) {
                        _.each(filterValue, function getLabels(filterValueKey) {
                            const filterValueLabel = this._optionsList[filterValueKey];

                            if (filterValueLabel) {
                                filterValueLabels.push(filterValueLabel);
                            }
                        }, this);

                        filterValue = filterValueLabels;
                    } else if (!this._seedFieldDef.options && !this._seedFieldDef.enumOptions) {
                        _.each(filterValue, function translateLabels(value) {
                            filterValueLabels.push(this._users[value] || value);
                        }, this);

                        filterValue = filterValueLabels;
                    }

                    return prefix + ' ' + _.escape(_.sortBy(filterValue).join(', '));
                }

                const fieldTypesRequiringDates = [
                    'date',
                    'date-between',
                    'datetime-between',
                    'after',
                    'before',
                    'on',
                    'datetimecombo'
                ];

                if (_.contains(fieldTypesRequiringDates, this._inputType)) {
                    filterValue = this.toDisplayDate(filterValue);
                }

                if (_.contains(['text-between', 'date-between'], this._inputType)) {
                    return prefix + ' ' + _.escape(filterValue + ' ' + app.lang.get('LBL_AND') + ' ' + this._inputValue1);
                }

                if (_.contains(['datetimecombo'], this._inputType)) {
                    return prefix + ' ' + _.escape(this._inputValue + ' ' + this._inputValue1);
                }

                if (_.contains(['datetime-between'], this._inputType)) {
                    return prefix + ' ' + _.escape(this._inputValue + ' ' + this._inputValue1 + ' ' +
                        app.lang.get('LBL_AND') + ' ' + this._inputValue2 + ' ' + this._inputValue3);
                }

                return prefix + ' ' + _.escape((this._inputType === 'relate' ? filterValue2 : filterValue));
            },

            /**
             * Build tooltip description text
             *
             * @return {string}
             */
            getTooltipText: function() {
                const summaryText = this.getSummaryText();

                // tooltip text has to be html so we can meet the UX mocks
                const title = '<div class="runtime-filter-summary-tooltip">' +
                                '<b>' + this._tooltipTitle + '</b><br>' +
                                summaryText +
                            '</div>';

                return title;
            },

            /**
             * Transform filter date into user date
             *
             * @param {string} filterDate
             *
             * @return {string}
             */
            toDisplayDate(filterDate) {
                let dateFragments = filterDate.match(/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/);

                const yearIdx = 1;
                const monthIdx = 2;
                const dayIdx = 3;

                if (!dateFragments) {
                    return filterDate;
                }

                let displayDate = app.user.getPreference('datepref');

                displayDate = displayDate.replace('Y', dateFragments[yearIdx]);
                displayDate = displayDate.replace('m', dateFragments[monthIdx]);
                displayDate = displayDate.replace('d', dateFragments[dayIdx]);

                return displayDate;
            },

            /**
             * Update the time fragment of a datetime filter
             *
             * @param {string} dateKey
             * @param {string} timeKey
             * @param {string} newValue
             */
            changeFilterTime: function(dateKey, timeKey, newValue) {
                const timeFragmentIdx = 1;
                const datetimeFragments = this._filterData[dateKey].split(' ');
                const timeFragment = datetimeFragments[timeFragmentIdx];

                const time = app.date(newValue, ['h:mma']).format('HH:mm:ss');

                this._filterData[dateKey] = this._filterData[dateKey].replace(timeFragment, time);
                this._filterData[timeKey] = newValue;

                this._initProperties();
            },

            /**
             * Transform user date into filter date
             *
             * @param {string} userDate
             * @param {boolean} useTime
             * @param {string} timeKey
             *
             * @return {string}
             */
            toFilterDate: function(userDate, useTime, timeKey) {
                timeKey = timeKey || 'input_name1';

                const userDatePref = app.user.getPreference('datepref').toUpperCase();
                const unformattedDateTime = `${userDate} ${this._filterData[timeKey]}`;
                const tempFormat = `${userDatePref} h:mma`;

                const date = moment(unformattedDateTime, tempFormat);

                return useTime ? date.format('YYYY-MM-DD HH:mm:ss') : date.format('YYYY-MM-DD');
            },

            /**
             * Is valid date picker format
             *
             * @param {string} date
             *
             * @return {boolean}
             */
            isValidDatePickerFormat: function(date) {
                const dateFormatMapping = {
                    'Y-m-d': 'YYYY-MM-DD',
                    'm-d-Y': 'MM-DD-YYYY',
                    'd-m-Y': 'DD-MM-YYYY',
                    'Y/m/d': 'YYYY/MM/DD',
                    'm/d/Y': 'MM/DD/YYYY',
                    'd/m/Y': 'DD/MM/YYYY',
                    'Y.m.d': 'YYYY.MM.DD',
                    'd.m.Y': 'DD.MM.YYYY',
                    'm.d.Y': 'MM.DD.YYYY',
                };

                const formattedDate = _.first(date.split(' '));
                const userFormat = app.user.getPreference('datepref');
                const acceptedFormat = dateFormatMapping[userFormat] || userFormat;

                return moment(formattedDate, acceptedFormat, true).isValid();
            },

            /**
             * Show date pref missmatch alert
             */
            showDatePrefMissmatchAlert: function() {
                const dateFormatMapping = {
                    'Y-m-d': 'YYYY-MM-DD',
                    'm-d-Y': 'MM-DD-YYYY',
                    'd-m-Y': 'DD-MM-YYYY',
                    'Y/m/d': 'YYYY/MM/DD',
                    'm/d/Y': 'MM/DD/YYYY',
                    'd/m/Y': 'DD/MM/YYYY',
                    'Y.m.d': 'YYYY.MM.DD',
                    'd.m.Y': 'DD.MM.YYYY',
                    'm.d.Y': 'MM.DD.YYYY',
                };

                let userFormat = app.user.getPreference('datepref');
                const displayFormat = dateFormatMapping[userFormat] || userFormat;

                app.alert.show('date-format-error', {
                    level: 'warning',
                    messages: app.lang.get('LBL_RUNTIME_FILTER_DATE_PREF_MISSMATCH') + displayFormat,
                });

                this.context.trigger('dashboard:filter:broken');
            },

            /**
             * Unbind events on dispose.
             */
            onDetach: function() {
                this.operatorController = false;
                this.disposeTempRelateController();
            },
        });
    });
})(SUGAR.App);
