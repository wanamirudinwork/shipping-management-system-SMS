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
        app.plugins.register('ReportExport', 'view', {
            /**
             * Export to PDF
             *
             * @param {Object} options
             */
            exportToPdf: function(options) {
                if (!options) {
                    options = {};
                }

                let queryParams = {};

                const reportData = this.context.get('data');
                const hasChart = this.context.get('reportHasChart');

                if (reportData && _.has(reportData, 'orderBy')) {
                    queryParams.orderBy = reportData.orderBy;
                }

                if (hasChart) {
                    queryParams.shouldHaveChartCanvas = true;
                }

                let reportId = this.context.get('model').get('id');

                if (!reportId && this.layout && _.has(this.layout, 'model')) {
                    reportId = this.layout.model.get('report_id');
                }

                let url = app.api.buildURL(
                    'Reports',
                    reportId + '/' + 'base64',
                    null,
                    queryParams
                );

                app.alert.show('export-to-pdf', {
                    level: 'process',
                    title: app.lang.get('LBL_EXPORT_PDF', 'Reports'),
                });

                this.$('[data-content=report-export-modal]').hide();

                const completeCallback = options.completeCallback || this.exportCompleteCallback;
                const errorCallback = options.exportErrorCallback || this.exportErrorCallback;

                app.api.call('read', url, {}, {
                    complete: _.bind(completeCallback, this, 'pdf'),
                    error: _.bind(errorCallback, this, 'pdf'),
                });
            },

            /**
            * Export to CSV
            *
            * @param {Object} options
            */
            exportToCsv: function(options) {
                if (!options) {
                    options = {};
                }

                let queryParams = {};

                const reportData = this.context.get('data');

                if (reportData && _.has(reportData, 'orderBy')) {
                    queryParams.orderBy = reportData.orderBy;
                }

                let reportId = this.context.get('model').get('id');

                if (!reportId && this.layout && _.has(this.layout, 'model')) {
                    reportId = this.layout.model.get('report_id');
                }

                let url = app.api.buildURL(
                    'Reports',
                    reportId + '/' + 'csv',
                    null,
                    queryParams
                );

                app.alert.show('export-to-csv', {
                    level: 'process',
                    title: app.lang.get('LBL_EXPORT_CSV', 'Reports'),
                });

                this.$('[data-content=report-export-modal]').hide();

                const completeCallback = options.completeCallback || this.exportCompleteCallback;
                const errorCallback = options.exportErrorCallback || this.exportErrorCallback;

                app.api.call('read', url, {}, {
                    complete: _.bind(completeCallback, this, 'csv'),
                    error: _.bind(errorCallback, this, 'csv'),
                });
            },

            /**
             * Downloads a file on the file system
             *
             * @param {string} filename
             * @param {string} content
             * @param {string} contentType
             */
            downloadFileLocally: function(filename, content, contentType) {
                const dataURIToBlob = function(dataURI) {
                    let binStr = contentType === 'csv' ? dataURI : atob(dataURI);
                    let len = binStr.length;
                    let arr = new Uint8Array(len);

                    for (let i = 0; i < len; i++) {
                        arr[i] = binStr.charCodeAt(i);
                    }

                    let blob = new Blob([arr], {
                        type: 'application/octet-stream',
                    });

                    if (contentType === 'csv') {
                        const bom = new Uint8Array([0xEF, 0xBB, 0xBF]);

                        blob = new Blob([bom, binStr], {
                            type: 'text/plain;charset=utf-8',
                        });
                    }

                    return blob;
                };

                const blob = dataURIToBlob(content);
                const url = URL.createObjectURL(blob);

                let element = document.createElement('a');
                element.setAttribute('href', url);
                element.setAttribute('download', filename);

                element.style.display = 'none';
                document.body.appendChild(element);

                element.click();

                document.body.removeChild(element);
            },

            /**
             * Format current date to string
             *
             * @return {string}
             */
            formatDateToString: function() {
                const currentDate = moment();

                return currentDate.format('YYYY_MM_DD_HH_mm');
            },

            /**
             * Export error callback
             *
             * @param {string} type
             * @param {Object} error
             */
            exportErrorCallback: function(type, error) {
                if (error && (error.errorThrown === 'timeout' || error.textStatus === 'timeout')) {
                    app.alert.show(`export-to-${type}-failed`, {
                        level: 'error',
                        messages: app.lang.get('LBL_EXPORT_FAILED', 'Reports'),
                    });
                    if (_.isFunction(this.closeModal)) {
                        this.closeModal();
                    }
                    app.alert.dismiss(`export-to-${type}-failed`);
                }
            },

            /**
             * Export callback
             *
             * @param {string} type
             * @param {Object} data
             */
            exportCompleteCallback: function(type, data) {
                if (this.disposed) {
                    return;
                }

                const reportModelName = this._buildReportName();

                const currentTime = this.formatDateToString();
                const userName = app.user.get('user_name');
                const reportName = reportModelName.replace(/ /g,'_');
                const name = `${reportName}_${userName}_${currentTime}.${type}`;

                this.downloadFileLocally(name, data.xhr.responseText, type);

                if (_.isFunction(this.closeModal)) {
                    this.closeModal();
                }

                const typeUpper = type.toUpperCase();

                app.alert.dismiss(`export-to-${type}`);
                app.alert.show(`export-to-${type}-success`, {
                    level: 'success',
                    messages: app.lang.get(`LBL_EXPORT_${typeUpper}_SUCCESS`, 'Reports'),
                    autoClose: true
                });
            },

            /**
             * Builds the name of the exported report.
             *
             * If it is from the report dashlet it will search it on the layout model instead of context
             */
            _buildReportName: function() {
                let reportModelName = this.context.get('model').get('name');
                if (!reportModelName && this.layout) {
                    // used in report dashlet
                    reportModelName = this.layout.model.get('report_name');
                }

                if (!reportModelName) {
                    reportModelName = 'Report';
                }
                return reportModelName;
            },
        });
    });
})(SUGAR.App);
