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
        app.plugins.register('Chart', ['view', 'field'], {

            chart_loaded: false,
            chartCollection: null,
            chart: null,
            total: 0,

            /**
             * Attach code for when the plugin is registered on a view or layout
             *
             * @param component
             * @param plugin
             */
            onAttach: function(component, plugin) {
                if (component.skipLifecycleMethods) {
                    return;
                }
                this.on('init', function() {
                    if (this.meta.config) {
                        return;
                    }
                    // This event fires when a preview is closed.
                    // We listen to this event to call the chart resize method
                    // in case the window was resized while the preview was open.
                    app.events.on('preview:close', function() {
                        if (_.isUndefined(app.drawer) || app.drawer.isActive(this.$el)) {
                            this.resize();
                        }
                    }, this);
                    // This event fires when the dashlet is collapsed or opened.
                    // We listen to this event to call the chart resize method
                    // in case the window was resized while the dashlet was closed.
                    this.layout.on('dashlet:collapse', function(collapse) {
                        if (!collapse) {
                            this.resize();
                        }
                    }, this);
                    // This event fires when the dashlet is dragged and dropped.
                    // We listen to this event to call the chart resize method
                    // because the size of the dashlet can change in the dashboard.
                    this.layout.context.on('dashlet:draggable:stop', function() {
                        this.resize();
                    }, this);
                    // Resize chart on window resize.
                    // This event also fires when the sidebar is collapsed or opened.
                    // We listen to this event to call the chart resize method
                    // in case the window was resized while the sidebar was closed.
                    $(window).on('resize.' + this.cid, _.debounce(_.bind(this.resize, this), 100));
                    // Resize chart on print.
                    this.handlePrinting('on');

                    // If the chartResize method is not defined in the component
                    // set it to the default method below
                    if (!_.isFunction(this.chartResize)) {
                        this.chartResize = this._chartResize;
                    }
                    // If the hasChartData method is not defined in the component
                    // set it to the default method below
                    if (!_.isFunction(this.hasChartData)) {
                        this.hasChartData = this._hasChartData;
                    }

                    const sidebarLayout = this.closestComponent('sidebar');
                    if (sidebarLayout) {
                        this.listenTo(sidebarLayout, 'sidebar:state:changed', (state) => {
                            if (state === 'open') {
                                this.render();
                            }
                        });
                    }

                    this.listenTo(this.layout, 'dashlet:expand', this.render);
                }, this);

                this.on('render', function() {
                    if (this.chart && this.chart.dispatch) {
                        // This on click event is required to dismiss the dropdown legend
                        this.$('.sc-chart').on('click', _.bind(function() {
                            this.chart.dispatch.call('chartClick', this);
                        }, this));
                    }

                    this.renderChart();
                }, this);
            },

            /**
             * A default function for determining if chart has data.
             * Can be overridden in views by defining hasChartData method.
             */
            _hasChartData: function() {
                return this.total !== 0;
            },

            /**
             * A default function for calling the chart update method.
             * Can be overridden in views by defining chartResize method.
             */
            _chartResize: function() {
                this.chart.update();
            },

            /**
             * Destroy tooltips on dispose.
             */
            onDetach: function(component, plugin) {
                if (component.skipLifecycleMethods) {
                    return;
                }
                if (this.meta.config) {
                    return;
                }
                if (this.layout) {
                    this.layout.off(null, null, this);
                }
                if (this.layout && this.layout.context) {
                    this.layout.context.off(null, null, this);
                }
                $(window).off('resize.' + this.cid);
                this.handlePrinting('off');
                this.stopListening();

                if (this.chart && this.chart.dispatch) {
                    this.$('.sc-chart').off('click');
                }
            },

            /**
             * Checks to see if the chart model and data are available before rendering
             */
            isChartReady: function() {
                if (this.meta.config || this.disposed) {
                    return false;
                }

                if (!this.$el || (this.$el.parents().length > 0 && !this.$el.is(':visible'))) {
                    return false;
                }

                if (!_.isFunction(this.chart) || !this.hasChartData()) {
                    this.chart_loaded = false;
                    this.displayNoData(true);
                    return false;
                }

                this.displayNoData(false);
                return true;
            },


            /**
             * Checks to see if the data are available for chartjs before rendering
             */
            isChartJSReady: function() {
                if (this.meta.config || this.disposed) {
                    return false;
                }

                if (!this.$el || (this.$el.parents().length > 0 && !this.$el.is(':visible'))) {
                    return false;
                }

                if (!this.hasChartData()) {
                    this.chart_loaded = false;
                    this.displayNoData(true);
                    return false;
                }

                this.displayNoData(false);
                return true;
            },

            /**
             * Checks to see if the chart is available and is displayed before resizing
             */
            resize: function() {
                if (!this.chart_loaded) {
                    return;
                }
                // This handles the case of preview open and dashlet collapsed.
                // We don't need to handle the case of collapsed sidepane
                // because charts can resize when inside an invisible container.
                // It is being inside a display:none container that causes problems.
                if (!this.$el || (this.$el.parents().length > 0 && !this.$el.is(':visible'))) {
                    return;
                }

                this.chartResize();
            },

            /**
             * Attach and detach a resize method to the print event
             * @param {string} The state of print handling.
             */
            handlePrinting: function(state) {
                var self = this,
                    mediaQueryList = window.matchMedia && window.matchMedia('print');
                var pausecomp = function(millis) {
                        // www.sean.co.uk
                        var date = new Date(),
                            curDate = null;
                        do {
                            curDate = new Date();
                        } while (curDate - date < millis);
                    };
                var printResize = function(mql) {
                        if (mql.matches) {
                            if (!_.isUndefined(self.chart.legend) && _.isFunction(self.chart.legend.showAll)) {
                                self.chart.legend.showAll(true);
                            }
                            self.chart.width(640).height(320);
                            self.resize();
                            pausecomp(200);
                        } else {
                            browserResize();
                        }
                    };
                var browserResize = function() {
                        if (!_.isUndefined(self.chart.legend) && _.isFunction(self.chart.legend.showAll)) {
                            self.chart.legend.showAll(false);
                        }
                        self.chart.width(null).height(null);
                        self.resize();
                    };

                if (state === 'on') {
                    if (window.matchMedia) {
                        mediaQueryList.addListener(printResize);
                    } else if (window.attachEvent) {
                        window.attachEvent('onbeforeprint', printResize);
                        window.attachEvent('onafterprint', browserResize);
                    } else {
                        window.onbeforeprint = printResize;
                        window.onafterprint = browserResize;
                    }
                } else {
                    if (window.matchMedia) {
                        mediaQueryList.removeListener(printResize);
                    } else if (window.detachEvent) {
                        window.detachEvent('onbeforeprint', printResize);
                        window.detachEvent('onafterprint', browserResize);
                    } else {
                        window.onbeforeprint = null;
                        window.onafterprint = null;
                    }
                }
            },

            /**
             * Toggle display of dashlet content and NoData message
             * @param {boolean} state The visibilty state of the dashlet content.
             */
            displayNoData: function(state) {
                this.$('[data-content="chart"]').toggleClass('hide', state);
                this.$('[data-content="nodata"]').toggleClass('hide', !state);
            },

            /**
             * Function to convert hex color to RGBA format
             *
             * @return {string} converted color
            */
            hexToRgba: function(hex, alpha) {
                hex = hex.replace(/^#/, '');

                let r = parseInt(hex.slice(0, 2), 16);
                let g = parseInt(hex.slice(2, 4), 16);
                let b = parseInt(hex.slice(4, 6), 16);

                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            },

            /**
             * Create and append a tooltip element to the body
             *
             * @return {jQuery}
             */
            createTooltipElement: function() {
                const tooltipEl = $('<div class="chartjs-tooltip"></div>');

                $('body').append(tooltipEl);

                return tooltipEl;
            },

            /**
             * Calculate the position of the tooltip element
             *
             * @param {Object} context
             * @param {jQuery} tooltipEl
             * @param {number} x
             * @param {number} y
             * @return {Object}
             */
            getTooltipPosition: function(context, tooltipEl, x = 0, y = 0) {
                const canvas = context.chart.canvas;
                const canvasArea = canvas.getBoundingClientRect();
                const canvasWidth = canvas.clientWidth;
                const canvasLeft = canvasArea.left;
                const canvasTop = canvasArea.top;

                const tooltipHeight = tooltipEl.outerHeight();
                const tooltipWidth = tooltipEl.outerWidth();

                let targetX = 0;
                let targetY = 0;

                if (x && y) {
                    targetX = x;
                    targetY = y;
                } else if (context.tooltip &&
                    context.tooltip._active.length &&
                    context.tooltip._active[0].element
                ) {
                    const activeElement = context.tooltip._active[0].element;
                    targetX = activeElement.x;
                    targetY = activeElement.y;
                }

                const positionX = canvasArea.x + targetX;
                const positionY = canvasArea.y + targetY;

                const space = 5; // Space between the caret and the element.
                let left = 0;
                let top = 0;

                left = positionX - (tooltipWidth / 2);

                const chartRightPosition = canvasWidth + canvasLeft;
                const tooltipRightPosition = left + tooltipWidth;

                if (tooltipRightPosition > chartRightPosition) {
                    left = chartRightPosition - tooltipWidth;
                }

                if (left < canvasLeft) {
                    left = canvasLeft;
                }

                top = positionY - tooltipHeight - space;

                if (top < canvasTop) {
                    top += tooltipHeight + space;
                }

                return {
                    left,
                    top
                };
            },

            /**
             * Interpolate a number between two values
             *
             * @param {number} a The starting value
             * @param {number} b The ending value
             * @return {Function}
             */
            interpolateNumber: function(a, b) {
                return a = +a, b -= a, function(t) {
                    return a + b * t;
                };
            },

            /**
             * Deinterpolate a number between two values
             *
             * @param {number} a The starting value
             * @param {number} b The ending value
             * @return {Function}
             */
            deinterpolateLinear: function(a, b) {
                return (b -= (a = +a)) ?
                    function(x) { return (x - a) / b; } :
                    function() { return b; }; // Return a constant function if a == b
            },

            /**
             * Get the domain values of the datasets
             *
             * @param {Array} values
             * @return {Array}
             */
            getDomainValues: function(values) {
                return [Math.min(...values), Math.max(...values)];
            },

            /**
             * Get the range values of the datasets
             *
             * @param {number} index
             * @param {number} groupsLength
             * @return {Array}
             */
            getRangeValues: function(index, groupsLength) {
                const maxRange = 1024;
                const maxHeight = 1000;
                const maxBubbleSize = Math.sqrt(maxRange / Math.PI);
                const gHeight = maxHeight / groupsLength;
                const gOffset = maxBubbleSize;

                return [gHeight * index + gOffset, gHeight * (index + 1) - gOffset];
            },

            /**
             * Calculate the range of months between two dates
             * @param {Date} startDate
             * @param {Date} endDate
             *
             * @return {Array} Array of dates
            */
            monthRange: function(startDate, endDate) {
                const startMonth = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
                const endMonth = new Date(endDate.getFullYear(), endDate.getMonth(), 1);
                const range = [];

                while (startMonth <= endMonth) {
                    range.push(new Date(startMonth));
                    startMonth.setMonth(startMonth.getMonth() + 1);
                }

                return range;
            },

            /**
             * Get the number of days in a given month of a given year
             * @param {Date} date
             *
             * @return {number}
            */
            daysInMonth: function(date) {
                return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
            },

            /**
             * Add days to a given date
             *
             * @param {Date} date
             * @param {number} days
             * @return {Date}
             */
            addDays: function(date, days) {
                date.setDate(date.getDate() + days);

                return date;
            },

            /**
             * Get the scale border style for the chart
             */
            getScaleStyle: function() {
                return {
                    color: 'rgba(155, 161, 166, 0.5)',
                    width: 2,
                };
            },

            /**
             * Get the text color for the chart
             */
            getTextColor: function() {
                return app.utils.isDarkMode() ? '#cbd5e1' : '#1d283a';
            },
        });
    });
})(SUGAR.App);
