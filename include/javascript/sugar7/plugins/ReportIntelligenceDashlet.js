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
(function(app) {
    app.events.on('app:init', function() {
        app.plugins.register('ReportIntelligenceDashlet', 'view', {
            /**
             * Set the intelligence property
             * and manage the visiblity of the linked_fields dropdown
             */
            _setIntelligence: function() {
                const intelligenceEl = this.$('[data-fieldname="intelligent"]');
                const checkboxElement = this.$('[data-fieldname="intelligent"] input');

                const field = this.getField('linkedFields');

                if (!field) {
                    return;
                }

                const fieldEl = this.$('[data-name="linkedFields"]');

                // we can't use strict equal here :(
                if (checkboxElement.is(':checked') == 1) {
                    fieldEl.show();
                } else {
                    fieldEl.hide();
                }

                if (this._isNotRecordLayout() && this._isNotFocusDrawerLayout()) {
                    intelligenceEl.parent().hide();
                    fieldEl.parent().hide();
                }
            },

            /**
             * Retrieve the linked fields depending on the target report
             *
             * @param {string} reportModule
             */
            _setLinkedFields: function(reportModule) {
                if (!this.meta.config) {
                    return;
                }

                reportModule = reportModule ? reportModule : this.settings.get('module');

                const linkedField = this.getField('linkedFields');

                if (!linkedField) {
                    return;
                }

                let relationships = this._getRelationships(reportModule);
                linkedField.items = relationships;

                if (_.isEmpty(linkedField.items)) {
                    this._toggleIntelligence(false);
                    this.settings.set('linkedFields', '');

                    return;
                }

                let linkName = this.settings.get('linkedFields');
                const link = linkedField.items[linkName];

                if (_.isUndefined(link)) {
                    this.settings.set('linkedFields', '');
                }

                linkedField._render();

                if (!linkName && this.settings.get('intelligent')) {
                    linkName = _.chain(relationships).keys().first().value();

                    this.settings.set('linkedFields', linkName);

                    linkedField.$('.select2-container').select2('data', {
                        id: linkName,
                        text: relationships[linkName],
                    });
                }
            },

            /**
             * Enable or disable the input checkbox
             *
             * @param {boolean} enable
             * @param {string} fieldName
             * @param {string} title
             */
            _toggleInput: function(enable, fieldName, title) {
                if (!this.meta.config) {
                    return;
                }

                const checkboxElement = this.$(`[data-fieldname="${fieldName}"] input`);

                $(checkboxElement).prop('disabled', !enable);

                if (!enable) {
                    this.settings.set(fieldName, false);
                }

                this._toggleInputTooltip(!enable, fieldName, title);
            },

            /**
             * Enable or disable the intelligence checkbox
             *
             * @param {boolean} enable
             */
            _toggleIntelligence: function(enable) {
                if (!this.meta.config) {
                    return;
                }

                const checkboxElement = this.$('[data-fieldname="intelligent"] input');

                $(checkboxElement).prop('disabled', !enable);

                if (!enable) {
                    const fieldEl = this.$('[data-name="linkedFields"]');

                    $(checkboxElement).prop('checked', false);
                    fieldEl.hide();

                    this.settings.set({
                        linkedFields: '',
                        intelligent: false,
                    });
                }

                this._toggleIntelligenceTooltip(!enable);
            },

            /**
             * As bootstrap tooltip does not work on a disabled element
             * we have to wrap the checkbox element into a div
             *
             * @param {string} fieldName
             */
            _wrapInputEl: function(fieldName) {
                const containerFieldName = `${fieldName}Container`;

                let checkboxEl = this.$(`[data-fieldname="${fieldName}"] input`);
                let checkboxContainer = `<div class="saved-report-tooltip" data-fieldname="${containerFieldName}"` +
                    ` data-container="body">`;

                if (this.$(`[data-fieldname="${containerFieldName}"]`).length > 0) {
                    return;
                }

                checkboxEl.parent().prepend(checkboxContainer);

                let checkboxContainerEl = this.$(`[data-fieldname="${containerFieldName}"]`);
                checkboxContainerEl.append(checkboxEl.detach());
            },

            /**
             * Enable or disable the intelligence tooltip
             *
             * @param {boolean} enable
             */
            _toggleIntelligenceTooltip: function(enable) {
                const sideCtx = app.sideDrawer.currentContextDef;
                const appContext = app.controller.context;

                const module = sideCtx ? sideCtx.context.module : appContext.get('module');

                const message = app.lang.get(
                    'LBL_REPORTS_DASHLET_NO_LINKS_SELECTED',
                    null,
                    {
                        module: module,
                    }
                );

                this._toggleInputTooltip(enable, 'intelligent', message);
            },

            /**
             * Enable or disable the input tooltip
             *
             * @param {boolean} enable
             * @param {string} fieldName
             * @param {string} title
             */
            _toggleInputTooltip: function(enable, fieldName, title) {
                let checkboxContainerEl = $(`[data-fieldname="${fieldName}Container"]`);

                checkboxContainerEl.attr('rel', 'tooltip');
                checkboxContainerEl.tooltip({
                    title,
                });
                checkboxContainerEl.tooltip(enable ? 'enable' : 'disable');
            },

            /**
             * Returns object with linked fields.
             *
             * @param {string} reportModule
             *
             * @return {Object} Hash with linked fields labels.
             */
            _getRelationships: function(reportModule) {
                const sideCtx = app.sideDrawer.currentContextDef;
                const appContext = app.controller.context;

                const currentModule = sideCtx ? sideCtx.context.module : appContext.get('module');

                const fieldDefs = app.metadata.getModule(currentModule).fields;
                const subpanels = app.metadata.getLayout(currentModule, 'subpanels');

                const relates = _.filter(fieldDefs, function(field) {
                    if (!_.isUndefined(field.type) && (field.type === 'link')) {
                        return app.data.getRelatedModule(currentModule, field.name) === reportModule;
                    }

                    return false;
                }, this);

                let result = {};

                _.each(relates, function(field) {
                    result[field.name] = app.lang.get(field.vname || field.name, [currentModule, reportModule]);

                    // handle the one part of one-many relationships
                    const linkType = 'link_type';
                    const linkTypeCustom = 'link-type';

                    if (field[linkType] === 'one' || field[linkTypeCustom] === 'one') {
                        let oneField = false;

                        if (field.id_name) {
                            oneField = _.first(_.filter(fieldDefs, function(targetField) {
                                return targetField.type === 'relate' && targetField.id_name === field.id_name;
                            }, this));
                        } else {
                            oneField = _.first(_.filter(fieldDefs, function(targetField) {
                                return targetField.type === 'relate' && targetField.link === field.name;
                            }, this));
                        }

                        if (oneField && oneField.vname) {
                            result[field.name] = app.lang.get(oneField.vname, currentModule);
                        }
                    }

                    if (!subpanels || !subpanels.components) {
                        return;
                    }

                    // get the subpanel label instead of the relationship label
                    const subpanel = _.first(_.filter(subpanels.components, function getSubpanel(component) {
                        return component.context && component.context.link === field.name;
                    }, this));

                    if (subpanel && subpanel.label) {
                        result[field.name] = app.lang.get(subpanel.label, currentModule);
                    }
                }, this);

                return result;
            },

            /**
             * Check if we are in the record layout
             *
             * @return {boolean}
             */
            _isNotRecordLayout: function() {
                return app.controller.context.get('dataView') !== 'record';
            },

            /**
             * Check if we are in the focus drawer layout
             *
             * @return {boolean}
             */
            _isNotFocusDrawerLayout: function() {
                const sideDrawerContext = app.sideDrawer.currentContextDef;

                return !sideDrawerContext || (sideDrawerContext && sideDrawerContext.context.layout !== 'focus');
            },

            /**
             * Handle the display of the chart display option controls based on chart type
             */
            _toggleChartFields: function() {
                if (this.meta.config) {
                    let xOptionsFieldset = this.getField('x_label_options');
                    let yOptionsFieldset = this.getField('y_label_options');
                    let showValuesField = this.getField('showValues');
                    let showLegendField = this.getField('showLegend');

                    let showDimensionOptions = false;
                    let showBarOptions = false;
                    let showLegend = true;

                    let xOptionsLabel = app.lang.get('LBL_CHART_CONFIG_SHOW_XAXIS_LABEL');
                    let yOptionsLabel = app.lang.get('LBL_CHART_CONFIG_SHOW_YAXIS_LABEL');

                    switch (this.settings.get('chartType')) {
                        case 'pieF':
                        case 'donutF':
                        case 'funnelF':
                            showDimensionOptions = false;
                            showBarOptions = false;
                            break;

                        case 'treemapF':
                            showLegend = false;
                            showDimensionOptions = false;
                            showBarOptions = false;
                            break;

                        case 'lineF':
                            showDimensionOptions = true;
                            showBarOptions = false;
                            break;

                        case 'hBarF':
                        case 'hGBarF':
                        case 'vBarF':
                        case 'vGBarF':
                            showDimensionOptions = true;
                            showBarOptions = true;
                            break;

                        default:
                            showDimensionOptions = false;
                            showBarOptions = false;
                    }

                    if (showDimensionOptions) {
                        switch (this.settings.get('chartType')) {
                            case 'hBarF':
                            case 'hGBarF':
                                xOptionsLabel = app.lang.get('LBL_CHART_CONFIG_SHOW_YAXIS_LABEL');
                                yOptionsLabel = app.lang.get('LBL_CHART_CONFIG_SHOW_XAXIS_LABEL');
                                break;
                            default:
                                xOptionsLabel = app.lang.get('LBL_CHART_CONFIG_SHOW_XAXIS_LABEL');
                                yOptionsLabel = app.lang.get('LBL_CHART_CONFIG_SHOW_YAXIS_LABEL');
                        }
                    }

                    if (xOptionsFieldset) {
                        xOptionRecordCell = xOptionsFieldset.$el.closest('.record-cell');

                        if (showDimensionOptions) {
                            xOptionRecordCell.show();
                        } else {
                            xOptionRecordCell.hide();
                        }

                        xOptionRecordCell.find('.record-label').text(xOptionsLabel);
                    }

                    if (yOptionsFieldset) {
                        yOptionRecordCell = yOptionsFieldset.$el.closest('.record-cell');

                        if (showDimensionOptions) {
                            yOptionRecordCell.show();
                        } else {
                            yOptionRecordCell.hide();
                        }

                        yOptionRecordCell.find('.record-label').text(yOptionsLabel);
                    }

                    if (showValuesField) {
                        showValuesField.$el.closest('.record-cell').toggleClass('hidden', !showBarOptions);
                    }

                    if (showLegendField) {
                        showLegendField.$el.toggleClass('hidden', !showLegend);
                    }

                    this.settings.set('isBarChart', !!showBarOptions);
                }
            },
        });
    });
})(SUGAR.App);
