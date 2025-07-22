/**
 * BD CleanDash Dashboard JavaScript
 * Dashboard cleanup functionality
 */

/* global bdCleanDash */

(function($) {
    'use strict';

    const bdCleanDashboard = {
        
        hiddenElements: [],
        showButtons: true,
        persistentMode: true,
        
        init: function() {
            // Get settings from localized script
            if (typeof bdCleanDash !== 'undefined') {
                this.hiddenElements = bdCleanDash.hiddenElements || [];
                this.showButtons = bdCleanDash.showButtons === '1';
                this.persistentMode = bdCleanDash.persistentMode === '1';
                
                // Ensure strings are available
                if (!bdCleanDash.strings) {
                    bdCleanDash.strings = {
                        hide: 'Skjul',
                        show: 'Vis',
                        hidden: 'Skjult',
                        hide_tooltip: 'Skjul dette elementet',
                        show_tooltip: 'Vis dette elementet'
                    };
                }
                
                console.log('BD CleanDash: Initialized with settings', {
                    showButtons: this.showButtons,
                    persistentMode: this.persistentMode,
                    hiddenElements: this.hiddenElements.length
                });
            } else {
                console.log('BD CleanDash: bdCleanDash object not found, creating defaults');
                this.showButtons = true;
                this.persistentMode = true;
                
                // Create default bdCleanDash object
                window.bdCleanDash = {
                    strings: {
                        hide: 'Skjul',
                        show: 'Vis',
                        hidden: 'Skjult',
                        hide_tooltip: 'Skjul dette elementet',
                        show_tooltip: 'Vis dette elementet'
                    },
                    ajaxurl: '/wp-admin/admin-ajax.php',
                    nonce: ''
                };
            }
            
            if (!this.showButtons) {
                console.log('BD CleanDash: Show buttons is disabled');
                return;
            }
            
            this.setupObserver();
            this.addHideButtons();
            this.applyHiddenElements();
            this.initQuickActions();
            
            // Re-scan for new elements periodically
            setInterval(() => {
                this.addHideButtons();
                this.applyHiddenElements();
                this.forceButtonVisibility(); // Force visibility check
            }, 2000);
            
            console.log('BD CleanDash: Dashboard cleanup initialized');
        },

        /**
         * Setup mutation observer to detect new elements
         */
        setupObserver: function() {
            if (typeof MutationObserver === 'undefined') return;
            
            const observer = new MutationObserver((mutations) => {
                let shouldUpdate = false;
                
                mutations.forEach((mutation) => {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        mutation.addedNodes.forEach((node) => {
                            if (node.nodeType === Node.ELEMENT_NODE) {
                                const $node = $(node);
                                if ($node.hasClass('notice') || $node.hasClass('postbox') || 
                                    $node.find('.notice, .postbox').length > 0) {
                                    shouldUpdate = true;
                                }
                            }
                        });
                    }
                });
                
                if (shouldUpdate) {
                    setTimeout(() => {
                        this.addHideButtons();
                        this.applyHiddenElements();
                    }, 100);
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        },

        /**
         * Add hide buttons to elements
         */
        addHideButtons: function() {
            if (!this.showButtons) {
                console.log('BD CleanDash: Show buttons disabled');
                return;
            }
            
            const self = this;
            let addedCount = 0;
            
            // Add to notices (excluding BD CleanDash notices)
            $('.notice:not(.bd-hideable-container)').each(function() {
                const $notice = $(this);
                // Skip BD CleanDash notices - check for BD-specific classes or content
                if (!$notice.hasClass('bd-notice') && 
                    !$notice.find('.bd-').length && 
                    $notice.text().indexOf('BD CleanDash') === -1) {
                    self.addHideButtonToElement($notice, 'notice');
                    addedCount++;
                }
            });
            
            // Add to dashboard widgets
            $('.postbox:not(.bd-hideable-container)').each(function() {
                self.addHideButtonToElement($(this), 'widget');
                addedCount++;
            });
            
            // Add to other admin notices (excluding BD CleanDash notices)
            $('[class*="updated"]:not(.bd-hideable-container), [class*="error"]:not(.bd-hideable-container)').each(function() {
                const $el = $(this);
                if ($el.is(':visible') && $el.find('p').length > 0) {
                    // Skip BD CleanDash notices
                    if (!$el.hasClass('bd-notice') && 
                        !$el.find('.bd-').length && 
                        $el.text().indexOf('BD CleanDash') === -1) {
                        self.addHideButtonToElement($el, 'notice');
                        addedCount++;
                    }
                }
            });
            
            // Add to plugin row notices (excluding BD CleanDash)
            $('tr[data-plugin] .update-message:not(.bd-hideable-container)').each(function() {
                const $pluginRow = $(this).closest('tr[data-plugin]');
                const pluginSlug = $pluginRow.attr('data-plugin');
                
                // Skip BD CleanDash plugin notices
                if (pluginSlug && pluginSlug.indexOf('bd-cleandash') === -1) {
                    self.addHideButtonToElement($(this), 'notice');
                    addedCount++;
                }
            });
            
            if (addedCount > 0) {
                console.log('BD CleanDash: Added', addedCount, 'hide buttons');
            }
        },

        /**
         * Add hide button to specific element
         */
        addHideButtonToElement: function($element, type) {
            if ($element.hasClass('bd-hideable-container')) return;
            
            // Make container relative and add class
            $element.addClass('bd-hideable-container');
            if ($element.css('position') === 'static') {
                $element.css('position', 'relative');
            }
            
            const elementId = this.getElementId($element, type);
            const isHidden = this.isElementHidden(type, elementId);
            
            const buttonText = isHidden ? bdCleanDash.strings.show : bdCleanDash.strings.hide;
            const buttonClass = isHidden ? 'bd-show-button' : '';
            const tooltip = isHidden ? bdCleanDash.strings.show_tooltip : bdCleanDash.strings.hide_tooltip;
            
            const $button = $(`
                <button type="button" class="bd-hide-button ${buttonClass}" 
                        data-element-type="${type}" 
                        data-element-id="${elementId}"
                        title="${tooltip}"
                        style="position: absolute !important; top: 5px !important; right: 5px !important; z-index: 99999 !important; display: inline-block !important; visibility: visible !important;">
                    ${buttonText}
                </button>
            `);
            
            // Remove any existing buttons first
            $element.find('.bd-hide-button').remove();
            
            $element.append($button);
            
            // Bind click event
            $button.on('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleElement($element, type, elementId, $button);
            });
            
            // Force visibility
            setTimeout(() => {
                $button.css({
                    'position': 'absolute !important',
                    'top': '5px !important', 
                    'right': '5px !important',
                    'z-index': '99999 !important',
                    'display': 'inline-block !important',
                    'visibility': 'visible !important',
                    'opacity': '1 !important'
                });
            }, 100);
            
            console.log('BD CleanDash: Button added to', type, elementId, $button.length);
        },

        /**
         * Generate unique element ID
         */
        getElementId: function($element, type) {
            // Try to get existing ID
            let id = $element.attr('id');
            if (id) return id;
            
            // Generate ID based on content for notices
            if (type === 'notice') {
                const text = $element.text().trim().substring(0, 50);
                const classes = $element.attr('class') || '';
                id = 'notice-' + btoa(text + classes).replace(/[^a-zA-Z0-9]/g, '').substring(0, 20);
            }
            
            // Generate ID based on title for widgets
            if (type === 'widget') {
                const title = $element.find('h2, h3, .hndle').first().text().trim();
                id = 'widget-' + btoa(title).replace(/[^a-zA-Z0-9]/g, '').substring(0, 20);
            }
            
            // Fallback to index-based ID
            if (!id) {
                const index = $element.index();
                id = `${type}-${index}-${Date.now()}`;
            }
            
            return id;
        },

        /**
         * Check if element is hidden
         */
        isElementHidden: function(type, elementId) {
            return this.hiddenElements.some(item => 
                item.element_type === type && item.element_id === elementId
            );
        },

        /**
         * Toggle element visibility
         */
        toggleElement: function($element, type, elementId, $button) {
            const isCurrentlyHidden = $button.hasClass('bd-show-button');
            
            if (isCurrentlyHidden) {
                this.showElement($element, type, elementId, $button);
            } else {
                this.hideElement($element, type, elementId, $button);
            }
        },

        /**
         * Hide element
         */
        hideElement: function($element, type, elementId, $button) {
            const elementSelector = this.getElementSelector($element);
            const elementContent = $element.text().trim().substring(0, 200);
            
            // Visual feedback
            $element.addClass('bd-fade-out');
            $button.prop('disabled', true);
            
            // AJAX request if persistent mode
            if (this.persistentMode) {
                $.ajax({
                    url: bdCleanDash.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bd_hide_element',
                        nonce: bdCleanDash.nonce,
                        element_type: type,
                        element_id: elementId,
                        element_selector: elementSelector,
                        element_content: elementContent,
                        is_persistent: '1'
                    },
                    success: (response) => {
                        if (response.success) {
                            this.addToHiddenElements(type, elementId);
                            this.finishHideElement($element, $button);
                            this.showStatusMessage(bdCleanDash.strings.hidden);
                        } else {
                            this.revertHideElement($element, $button);
                        }
                    },
                    error: () => {
                        this.revertHideElement($element, $button);
                    }
                });
            } else {
                // Temporary hide (session only)
                this.finishHideElement($element, $button);
                this.showStatusMessage(bdCleanDash.strings.hidden + ' (denne økten)');
            }
        },

        /**
         * Show element
         */
        showElement: function($element, type, elementId, $button) {
            $button.prop('disabled', true);
            
            $.ajax({
                url: bdCleanDash.ajaxurl,
                type: 'POST',
                data: {
                    action: 'bd_show_element',
                    nonce: bdCleanDash.nonce,
                    element_type: type,
                    element_id: elementId
                },
                success: (response) => {
                    if (response.success) {
                        this.removeFromHiddenElements(type, elementId);
                        this.finishShowElement($element, $button);
                        this.showStatusMessage('Vist');
                    } else {
                        $button.prop('disabled', false);
                    }
                },
                error: () => {
                    $button.prop('disabled', false);
                }
            });
        },

        /**
         * Finish hiding element
         */
        finishHideElement: function($element, $button) {
            setTimeout(() => {
                $element.removeClass('bd-fade-out').addClass('bd-temp-hidden');
                $button.removeClass('bd-hide-button').addClass('bd-show-button')
                       .text(bdCleanDash.strings.show)
                       .attr('data-tooltip', bdCleanDash.strings.show_tooltip)
                       .prop('disabled', false);
            }, 300);
        },

        /**
         * Finish showing element
         */
        finishShowElement: function($element, $button) {
            $element.removeClass('bd-temp-hidden bd-hidden-element').addClass('bd-fade-in');
            $button.removeClass('bd-show-button').addClass('bd-hide-button')
                   .text(bdCleanDash.strings.hide)
                   .attr('data-tooltip', bdCleanDash.strings.hide_tooltip)
                   .prop('disabled', false);
            
            setTimeout(() => {
                $element.removeClass('bd-fade-in');
            }, 300);
        },

        /**
         * Revert hide element on error
         */
        revertHideElement: function($element, $button) {
            $element.removeClass('bd-fade-out');
            $button.prop('disabled', false);
            this.showStatusMessage('Feil ved skjuling', 'error');
        },

        /**
         * Get CSS selector for element
         */
        getElementSelector: function($element) {
            let selector = $element.prop('tagName').toLowerCase();
            
            const id = $element.attr('id');
            if (id) {
                selector += '#' + id;
                return selector;
            }
            
            const classes = $element.attr('class');
            if (classes) {
                const classList = classes.split(' ').filter(cls => 
                    cls && !cls.startsWith('bd-') && cls !== 'bd-hideable-container'
                ).slice(0, 3);
                if (classList.length > 0) {
                    selector += '.' + classList.join('.');
                }
            }
            
            return selector;
        },

        /**
         * Add to hidden elements array
         */
        addToHiddenElements: function(type, elementId) {
            if (!this.isElementHidden(type, elementId)) {
                this.hiddenElements.push({
                    element_type: type,
                    element_id: elementId
                });
            }
        },

        /**
         * Remove from hidden elements array
         */
        removeFromHiddenElements: function(type, elementId) {
            this.hiddenElements = this.hiddenElements.filter(item => 
                !(item.element_type === type && item.element_id === elementId)
            );
        },

        /**
         * Apply hidden elements on page load
         */
        applyHiddenElements: function() {
            this.hiddenElements.forEach(item => {
                const selector = this.buildElementSelector(item);
                const $elements = $(selector);
                
                $elements.each((index, element) => {
                    const $element = $(element);
                    if (!$element.hasClass('bd-temp-hidden') && !$element.hasClass('bd-hidden-element')) {
                        $element.addClass('bd-temp-hidden');
                        
                        // Update button if it exists
                        const $button = $element.find('.bd-hide-button');
                        if ($button.length) {
                            $button.removeClass('bd-hide-button').addClass('bd-show-button')
                                   .text(bdCleanDash.strings.show);
                        }
                    }
                });
            });
        },

        /**
         * Build selector for hidden element
         */
        buildElementSelector: function(item) {
            // Try ID-based selector first
            if (item.element_id.startsWith('#') || 
                /^[a-zA-Z][a-zA-Z0-9_-]*$/.test(item.element_id)) {
                return '#' + item.element_id.replace('#', '');
            }
            
            // Use data attribute selector
            return `[data-element-id="${item.element_id}"]`;
        },

        /**
         * Initialize quick actions
         */
        initQuickActions: function() {
            // Create quick actions bar if there are hidden elements
            if (this.hiddenElements.length > 0) {
                this.createQuickActionsBar();
            }
        },

        /**
         * Create quick actions bar
         */
        createQuickActionsBar: function() {
            const $quickActions = $(`
                <div class="bd-quick-actions">
                    <button class="bd-quick-action-btn" data-action="show-all" title="Vis alle skjulte elementer">
                        👁️
                    </button>
                    <button class="bd-quick-action-btn" data-action="hide-all" title="Skjul alle elementer igjen">
                        🙈
                    </button>
                </div>
            `);
            
            $('body').append($quickActions);
            
            setTimeout(() => {
                $quickActions.addClass('active');
            }, 1000);
            
            // Bind actions
            $quickActions.find('[data-action="show-all"]').on('click', () => {
                this.showAllElements();
            });
            
            $quickActions.find('[data-action="hide-all"]').on('click', () => {
                this.hideAllElements();
            });
        },

        /**
         * Show all hidden elements temporarily
         */
        showAllElements: function() {
            $('.bd-temp-hidden').removeClass('bd-temp-hidden').addClass('bd-temp-visible');
            this.showStatusMessage('Alle elementer vist midlertidig');
        },

        /**
         * Hide all elements again
         */
        hideAllElements: function() {
            $('.bd-temp-visible').removeClass('bd-temp-visible').addClass('bd-temp-hidden');
            this.showStatusMessage('Alle elementer skjult igjen');
        },

        /**
         * Show status message
         */
        showStatusMessage: function(message, type = 'success') {
            // Remove existing messages
            $('.bd-status-indicator').remove();
            
            const $indicator = $(`<div class="bd-status-indicator ${type}">${message}</div>`);
            $('body').append($indicator);
            
            setTimeout(() => {
                $indicator.addClass('show');
            }, 100);
            
            setTimeout(() => {
                $indicator.removeClass('show');
                setTimeout(() => {
                    $indicator.remove();
                }, 300);
            }, 2000);
        },

        /**
         * Force button visibility (fallback method)
         */
        forceButtonVisibility: function() {
            $('.bd-hide-button').each(function() {
                const $btn = $(this);
                if (!$btn.is(':visible') || $btn.css('display') === 'none' || $btn.css('visibility') === 'hidden') {
                    console.log('BD CleanDash: Forcing button visibility', $btn);
                    $btn.css({
                        'display': 'inline-block !important',
                        'visibility': 'visible !important',
                        'opacity': '1 !important',
                        'position': 'absolute !important',
                        'top': '5px !important',
                        'right': '5px !important',
                        'z-index': '999999 !important'
                    }).show();
                }
            });
        },

        /**
         * Debug function to check button state
         */
        debugButtons: function() {
            console.log('BD CleanDash Debug Info:');
            console.log('- Show buttons enabled:', this.showButtons);
            console.log('- Persistent mode:', this.persistentMode);
            console.log('- Hidden elements count:', this.hiddenElements.length);
            
            const containers = $('.bd-hideable-container');
            console.log('- Hideable containers found:', containers.length);
            
            const buttons = $('.bd-hide-button');
            console.log('- Hide buttons found:', buttons.length);
            
            buttons.each(function(i) {
                const $btn = $(this);
                console.log(`  Button ${i+1}:`, {
                    visible: $btn.is(':visible'),
                    display: $btn.css('display'),
                    visibility: $btn.css('visibility'),
                    opacity: $btn.css('opacity'),
                    zIndex: $btn.css('z-index'),
                    position: $btn.css('position'),
                    text: $btn.text()
                });
            });
            
            const notices = $('.notice, .postbox, .updated, .error');
            console.log('- Total potential targets:', notices.length);
        },

    };

    // Initialize when document is ready
    $(document).ready(function() {
        console.log('BD CleanDash: DOM ready, initializing...');
        
        // Immediate initialization
        bdCleanDashboard.init();
        
        // Wait a bit for other scripts to load, then re-scan
        setTimeout(function() {
            console.log('BD CleanDash: Second scan after 1 second');
            bdCleanDashboard.addHideButtons();
        }, 1000);
        
        // Final scan for late-loaded elements
        setTimeout(function() {
            console.log('BD CleanDash: Final scan after 3 seconds');
            bdCleanDashboard.addHideButtons();
            bdCleanDashboard.forceButtonVisibility();
            bdCleanDashboard.debugButtons(); // Add debug output
        }, 3000);
    });
    
    // Also initialize on window load as backup
    $(window).on('load', function() {
        console.log('BD CleanDash: Window loaded, re-scanning...');
        setTimeout(function() {
            bdCleanDashboard.addHideButtons();
        }, 500);
        
        // Additional scan after window load
        setTimeout(function() {
            bdCleanDashboard.addHideButtons();
            bdCleanDashboard.forceButtonVisibility();
        }, 2000);
    });
    
    // Make available globally for admin.js and manual debugging
    window.bdCleanDashboard = bdCleanDashboard;
    
    // Add global debug function
    window.bdDebugButtons = function() {
        bdCleanDashboard.debugButtons();
        bdCleanDashboard.addHideButtons();
        bdCleanDashboard.forceButtonVisibility();
        console.log('BD CleanDash: Manual debug completed');
    };

})(jQuery);
