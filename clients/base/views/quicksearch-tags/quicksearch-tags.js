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
 * @class View.Views.Base.QuicksearchTagsView
 * @alias SUGAR.App.view.views.BaseQuicksearchTagsView
 * @extends View.View
 */

({
    events: {
        'click .qs-tag a': 'handleTagSelection'
    },

    className: 'quicksearch-tags-wrapper',

    initialize: function(options) {
        this._super('initialize', [options]);

        // Initialize tag collections (search results and selected tags)
        this.tagCollection = [];
        this.selectedTags = this.layout.selectedTags || [];
        this.collection = this.layout.collection || app.data.createMixedBeanCollection();

        /**
         * Stores the index of the currently highlighted list element.
         * This is used for keyboard navigation.
         * @{number} {null}
         */
        this.activeIndex = null;

        // If the layout has `quicksearch:close` called on it, that means the
        // whole thing is hidden
        this.layout.on('quicksearch:close quicksearch:results:close', function() {
            this.activeIndex = null;
            this.$('.active').removeClass('active');
            this.disposeKeyEvents();
            this.close();
        }, this);

        this.collection.on('sync', this.quicksearchHandler, this);

        //Listener for receiving focus for up/down arrow navigation:
        this.on('navigate:focus:receive', function() {
            this.activeIndex = 0;
            this._highlightActive();
            this.attachKeyEvents();
        }, this);

        //Listener for losing focus for up/down arrow navigation:
        this.on('navigate:focus:lost', function() {
            this.activeIndex = null;
            this.$('.active').removeClass('active');
            this.disposeKeyEvents();
        }, this);

        this.listenTo(this.layout, 'quicksearch:search:underway', function() {
            this.resizeDropdown();
        }, this);
    },

    /**
     * Handles the layout event indicating quicksearch fire
     * @param collection
     */
    quicksearchHandler: function(collection) {
        var selectedTags = this.selectedTags;

        if (collection && collection.tags) {
            // Filter out tags that already exist in selectedTags
            this.tagCollection = _.filter(collection.tags, function(tag) {
                return _.isUndefined(_.find(selectedTags, function(selectedTag) {
                    return selectedTag.name === tag.name;
                }));
            });
            this.render();
            if (this.tagCollection.length) {
                this.open();
            } else {
                this.close();
            }
        } else {
            this.close();
        }
    },

    /**
     * Highlight the active element and unhighlight the rest of the elements.
     */
    _highlightActive: function() {
        if (_.isNull(this.activeIndex)) {
            return;
        }

        this.$('.active').removeClass('active');
        this.$('.qs-tag:eq(' + this.activeIndex + ')')
            .addClass('active')
            .find('a').focus();
    },

    /**
     * Handler for tag selection
     * @param {Event} e
     */
    handleTagSelection: function(e) {
        if (e.target && e.target.text) {
            var self = this;
            var selectedTag = _.find(this.tagCollection, function(tag) {
                return tag.name === e.target.text;
            });

            this.layout.trigger('quicksearch:bar:clear:term');
            this.layout.trigger('quicksearch:tag:add', selectedTag);

            // Focus back to quicksearch-bar after tag selection. Defer it to prevent enter key-up
            // from navigating away
            _.defer(function() {
                self.layout.trigger('navigate:to:component', 'quicksearch-bar')
            });
        }
    },

    /**
     * Return true if tag view contains any tags. False if not
     * @return boolean
     */
    isFocusable: function() {
        return this.tagCollection &&
            this.tagCollection.length;
    },

    /**
     * Show the tag ribbon
     */
    open: function() {
        $(window).off(`resize.${this.cid}`);
        $(window).on(`resize.${this.cid}`, _.bind(this.resizeDropdown, this));
        this.$('.quicksearch-tags').removeClass('hidden');
        this.$('.quicksearch-tags').addClass('flex');
        this.layout.trigger('quicksearch:tag:open');
    },

    /**
     * Hide the tag ribbon
     */
    close: function() {
        this.$('.quicksearch-tags').addClass('hidden');
        this.$('.quicksearch-tags').removeClass('flex');
        this.layout.trigger('quicksearch:tag:close');
        $(window).off(`resize.${this.cid}`);
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        this._super('_render');
        this.resizeDropdown();
    },

    /**
     * Resizes the dropdown to match width of search bar and module dropdown
     */
    resizeDropdown: function() {
        if (this.$el) {
            let quickFilterGroup = this.$el.siblings('.quicksearch-filter-group');
            let quickSearchModuleWrapper = quickFilterGroup.find('.quicksearch-modulelist-wrapper');
            let quickSearchTagList = quickFilterGroup.find('.quicksearch-taglist');
            let quickSearchButtonWrapper = this.$el.siblings('.quicksearch-button-wrapper');
            let moduleDropdown = quickSearchModuleWrapper.find('.module-wrapper');
            let quickSearchTags = this.$('.quicksearch-tags');

            if (app.lang.direction === 'rtl') {
                quickSearchTags.css(
                    {
                        'left': quickSearchButtonWrapper.width() - 2,
                        'right': quickFilterGroup.width() -  moduleDropdown.width() - quickSearchTagList.width() + 2
                    }
                );
            } else {
                quickSearchTags.css(
                    {
                        'left': quickFilterGroup.width() -  moduleDropdown.width() - quickSearchTagList.width() + 2,
                        'right': quickSearchButtonWrapper.width() - 2
                    }
                );
            }
        }

    },
    /**
     * Handle when the user uses their keyboard to try to navigate outside of the view. This handles both the top and
     * bottom boundaries.
     * @param {boolean} next - If true, we are checking the next element. If false, we are checking the previous.
     * @private
     */
    _handleBoundary: function(next) {
        var event = 'navigate:next:component';
        if (!next) {
            event = 'navigate:previous:component';
        }
        if (this.layout.triggerBefore(event)) {
            this.disposeKeyEvents();
            this.$('.active').removeClass('active');
            this.layout.trigger(event);
        }
    },

    moveDown: function() {
        this._handleBoundary(true);
    },

    moveRight: function() {
        var maxIndex = this.tagCollection.length;
        if (this.activeIndex < --maxIndex) {
            this.activeIndex++;
            this._highlightActive();
        }
    },

    moveLeft: function() {
        if (this.activeIndex > 0) {
            this.activeIndex--;
            this._highlightActive();
        }
    },

    moveUp: function() {
        this._handleBoundary(false);
    },

    /**
     * Handle the keydown events.
     * @param {Event} e
     */
    keydownHandler: function(e) {
        switch (e.keyCode) {
            case 40: // down arrow
                this.moveDown();
                break;
            case 39: // right arrow
                this.moveRight();
                break;
            case 38: // up arrow
                this.moveUp();
                break;
            case 37: // left arrow
                this.moveLeft();
                break;
            case 13: //enter
                e.preventDefault();
                e.stopImmediatePropagation();
                break;
        }
    },

    keyupHandler: function(e) {
        switch (e.keyCode) {
            case 13: //enter
                this.handleTagSelection(e);
                break;
        }
    },

    /**
     * Attach the keydown events for the view.
     */
    attachKeyEvents: function() {
        this.$el.on('keydown', _.bind(this.keydownHandler, this));
        this.$el.on('keyup', _.bind(this.keyupHandler, this));
    },

    /**
     * Dispose the keydown events for the view.
     */
    disposeKeyEvents: function() {
        this.$el.off('keydown keyup');
    },

    /**
     * @inheritdoc
     */
    unbind: function() {
        this.disposeKeyEvents();
        this._super('unbind');
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        $(window).off(`resize.${this.cid}`);
        this._super('_dispose');
    }
})
