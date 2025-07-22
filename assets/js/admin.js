/**
 * BD CleanDash Admin Scripts
 *
 * @package BD_CleanDash
 * @since 1.0.0
 */

/* global bdCleanDashAdmin */

jQuery(document).ready(function($) {
    'use strict';
    
    console.log('BD CleanDash: Admin.js file loaded and initialized');
    console.log('BD CleanDash: jQuery version:', $.fn.jquery);

    try {

    const BDCleanDashAdmin = {
        
        init: function() {
            this.initTabs();
            this.initToggleSwitch();
            this.initFileImport();
            this.initButtons();
            this.loadBlacklist();
            this.loadUserSettings();
            this.bindEvents();
            this.initGitHubUpdates();
            this.initNotifications();
        },

        /**
         * Initialize tab functionality
         */
        initTabs: function() {
            $('.bd-tab-button').on('click', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const tabId = $button.data('tab');
                
                // Update active states
                $('.bd-tab-button').removeClass('active');
                $('.bd-tab-content').removeClass('active');
                
                $button.addClass('active');
                $(`#tab-${tabId}`).addClass('active');
                
                // Load tab-specific content
                BDCleanDashAdmin.handleTabSwitch(tabId);
            });
        },

        /**
         * Handle tab switch
         */
        handleTabSwitch: function(tabId) {
            switch(tabId) {
                case 'blacklist':
                    this.loadBlacklist();
                    break;
                case 'users':
                    this.loadUserSettings();
                    break;
                case 'tools':
                    this.loadStatistics();
                    break;
            }
        },

        /**
         * Initialize toggle switch
         */
        initToggleSwitch: function() {
            $('#bd-quick-toggle').on('click', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const isActive = $button.hasClass('active');
                const newState = isActive ? '0' : '1';
                
                $.ajax({
                    url: bdCleanDashAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bd_toggle_enabled',
                        enabled: newState,
                        nonce: bdCleanDashAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            if (newState === '1') {
                                $button.addClass('active');
                                $button.find('.toggle-text').text(bdCleanDashAdmin.strings.enabled);
                            } else {
                                $button.removeClass('active');
                                $button.find('.toggle-text').text(bdCleanDashAdmin.strings.disabled);
                            }
                        }
                    }
                });
            });
        },

        /**
         * Initialize file import functionality
         */
        initFileImport: function() {
            $('#import-file').on('change', function() {
                const hasFile = this.files && this.files.length > 0;
                $('#import-blacklist').prop('disabled', !hasFile);
            });
        },

        /**
         * Initialize buttons
         */
        initButtons: function() {
            // Check GitHub updates
            $('#check-github-updates').on('click', function(e) {
                e.preventDefault();
                BDCleanDashAdmin.checkGitHubUpdates();
            });
            
            // Export blacklist
            $('#export-blacklist').on('click', function(e) {
                e.preventDefault();
                BDCleanDashAdmin.exportBlacklist();
            });
            
            // Import blacklist
            $('#import-blacklist').on('click', function(e) {
                e.preventDefault();
                BDCleanDashAdmin.importBlacklist();
            });
            
            // Clear user blacklist
            $('#clear-user-blacklist').on('click', function(e) {
                e.preventDefault();
                BDCleanDashAdmin.clearUserBlacklist();
            });
            
            // Clear all blacklists
            $('#clear-all-blacklists').on('click', function(e) {
                e.preventDefault();
                BDCleanDashAdmin.clearAllBlacklists();
            });
        },

        /**
         * Load blacklist data
         */
        loadBlacklist: function() {
            const $container = $('#blacklist-table-container');
            
            if (!$container.length) return;
            
            $container.html('<div class="bd-loading"><span>' + bdCleanDashAdmin.strings.loading_blacklist + '</span></div>');
            
            $.ajax({
                url: bdCleanDashAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'bd_get_blacklist',
                    nonce: bdCleanDashAdmin.nonce
                },
                success: function(response) {
                    console.log('BD CleanDash: Blacklist response received:', response);
                    if (response.success) {
                        console.log('BD CleanDash: Blacklist data:', response.data);
                        BDCleanDashAdmin.renderBlacklistTable(response.data, $container);
                    } else {
                        $container.html('<div class="bd-error">' + bdCleanDashAdmin.strings.could_not_load_blacklist + '</div>');
                    }
                },
                error: function() {
                    $container.html('<div class="bd-error">' + bdCleanDashAdmin.strings.network_error + '</div>');
                }
            });
        },

        /**
         * Render blacklist table
         */
        renderBlacklistTable: function(data, $container) {
            console.log('BD CleanDash: Rendering blacklist table with data:', data);
            
            let html = '<div class="bd-blacklist-table">';
            
            if (data.length === 0) {
                html += '<div class="bd-empty-state">';
                html += '<p>' + bdCleanDashAdmin.strings.no_blacklist_items + '</p>';
                html += '<p>' + bdCleanDashAdmin.strings.blacklist_instructions + '</p>';
                html += '</div>';
            } else {
                html += '<table class="wp-list-table widefat fixed striped">';
                html += '<thead><tr>';
                html += '<th>' + bdCleanDashAdmin.strings.type + '</th>';
                html += '<th>' + bdCleanDashAdmin.strings.element_id + '</th>';
                html += '<th>' + bdCleanDashAdmin.strings.user + '</th>';
                html += '<th>' + bdCleanDashAdmin.strings.created + '</th>';
                html += '<th>' + bdCleanDashAdmin.strings.actions + '</th>';
                html += '</tr></thead><tbody>';
                
                data.forEach(function(item) {
                    console.log('BD CleanDash: Processing blacklist item:', item);
                    html += '<tr>';
                    html += '<td><span class="bd-badge bd-badge-' + item.element_type + '">' + 
                           (item.element_type === 'notice' ? bdCleanDashAdmin.strings.notice : bdCleanDashAdmin.strings.widget) + '</span></td>';
                    html += '<td>' + item.element_id + '</td>';
                    html += '<td>';
                    if (item.is_global === '1') {
                        html += '<span class="bd-badge bd-badge-global">' + bdCleanDashAdmin.strings.global + '</span>';
                    } else if (item.role) {
                        html += '<span class="bd-badge bd-badge-role">' + item.role + '</span>';
                    } else if (item.user_name) {
                        console.log('BD CleanDash: Using user_name:', item.user_name);
                        html += '<span class="bd-badge bd-badge-user">' + item.user_name + '</span>';
                    } else {
                        console.log('BD CleanDash: Fallback to user ID:', item.user_id);
                        html += '<span class="bd-badge bd-badge-user">' + bdCleanDashAdmin.strings.user_prefix + item.user_id + '</span>';
                    }
                    html += '</td>';
                    html += '<td>' + BDCleanDashAdmin.formatDate(item.created_at) + '</td>';
                    html += '<td>';
                    html += '<button class="button button-secondary bd-button bd-button-danger bd-remove-item" data-id="' + item.id + '">';
                    html += '🗑️ ' + bdCleanDashAdmin.strings.remove + '</button>';
                    html += '</td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
            }
            
            html += '</div>';
            $container.html(html);
            
            // Bind remove buttons
            $container.find('.bd-remove-item').on('click', function(e) {
                e.preventDefault();
                const itemId = $(this).data('id');
                BDCleanDashAdmin.removeBlacklistItem(itemId);
            });
        },

        /**
         * Remove blacklist item
         */
        removeBlacklistItem: function(itemId) {
            if (!confirm(bdCleanDashAdmin.strings.confirm_remove_item)) {
                return;
            }
            
            $.ajax({
                url: bdCleanDashAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'bd_remove_blacklist_item',
                    item_id: itemId,
                    nonce: bdCleanDashAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        BDCleanDashAdmin.loadBlacklist(); // Reload the list
                    } else {
                        alert(bdCleanDashAdmin.strings.could_not_remove + ': ' + (response.data || bdCleanDashAdmin.strings.unknown_error));
                    }
                },
                error: function() {
                    alert(bdCleanDashAdmin.strings.network_error);
                }
            });
        },

        /**
         * Load user settings
         */
        loadUserSettings: function() {
            const $container = $('#users-settings-container');
            
            if (!$container.length) return;
            
            $container.html('<div class="bd-loading"><span>' + bdCleanDashAdmin.strings.loading_user_settings + '</span></div>');
            
            // Render user statistics table
            $.ajax({
                url: bdCleanDashAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'bd_get_blacklist',
                    nonce: bdCleanDashAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        BDCleanDashAdmin.renderUserStats(response.data, $container);
                    } else {
                        $container.html('<div class="bd-error">' + bdCleanDashAdmin.strings.could_not_load_user_data + '</div>');
                    }
                },
                error: function() {
                    $container.html('<div class="bd-error">' + bdCleanDashAdmin.strings.network_error + '</div>');
                }
            });
        },

        /**
         * Render user statistics
         */
        renderUserStats: function(data, $container) {
            console.log('BD CleanDash: Rendering user stats with data:', data);
            
            // Group data by user
            const userStats = {};
            data.forEach(function(item) {
                const userId = item.user_id;
                const userName = item.user_name || item.user_display_name || item.user_login || ('Bruker #' + userId);
                
                if (!userStats[userId]) {
                    userStats[userId] = {
                        name: userName,
                        total: 0,
                        notices: 0,
                        widgets: 0
                    };
                }
                
                userStats[userId].total++;
                if (item.element_type === 'notice') {
                    userStats[userId].notices++;
                } else {
                    userStats[userId].widgets++;
                }
            });
            
            let html = '<div class="bd-user-stats">';
            html += '<h4>' + bdCleanDashAdmin.strings.user_statistics + '</h4>';
            
            if (Object.keys(userStats).length === 0) {
                html += '<div class="bd-empty-state">';
                html += '<p>' + bdCleanDashAdmin.strings.no_user_data + '</p>';
                html += '</div>';
            } else {
                html += '<table class="wp-list-table widefat fixed striped">';
                html += '<thead><tr>';
                html += '<th>' + bdCleanDashAdmin.strings.user + '</th>';
                html += '<th>' + bdCleanDashAdmin.strings.total_hidden + '</th>';
                html += '<th>' + bdCleanDashAdmin.strings.notices + '</th>';
                html += '<th>' + bdCleanDashAdmin.strings.widgets + '</th>';
                html += '</tr></thead><tbody>';
                
                Object.values(userStats).forEach(function(stats) {
                    html += '<tr>';
                    html += '<td><span class="bd-badge bd-badge-user">' + stats.name + '</span></td>';
                    html += '<td>' + stats.total + '</td>';
                    html += '<td>' + stats.notices + '</td>';
                    html += '<td>' + stats.widgets + '</td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
            }
            
            html += '</div>';
            $container.html(html);
        },

        /**
         * Load statistics
         */
        loadStatistics: function() {
            // Statistics are already loaded in the PHP template
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Admin bar role selection toggle
            this.initAdminBarRoleToggles();
        },

        /**
         * Initialize admin bar role selection toggles
         */
        initAdminBarRoleToggles: function() {
            const $adminBarToggle = $('input[name="bd_cleandash_hide_admin_bar"]');
            const $frontendToggle = $('input[name="bd_cleandash_hide_admin_bar_frontend"]');
            
            // Function to toggle role checkboxes
            const toggleRoleCheckboxes = function($parentToggle, rolePrefix) {
                const $roleCheckboxes = $('input[name="' + rolePrefix + '[]"]');
                const $roleContainer = $roleCheckboxes.closest('.bd-role-selection');
                
                if ($parentToggle.is(':checked')) {
                    $roleCheckboxes.prop('disabled', false);
                    $roleContainer.removeClass('disabled');
                } else {
                    $roleCheckboxes.prop('disabled', true).prop('checked', false);
                    $roleContainer.addClass('disabled');
                }
            };
            
            // Initialize states
            toggleRoleCheckboxes($adminBarToggle, 'bd_cleandash_hide_admin_bar_roles');
            toggleRoleCheckboxes($frontendToggle, 'bd_cleandash_hide_admin_bar_frontend_roles');
            
            // Bind change events
            $adminBarToggle.on('change', function() {
                toggleRoleCheckboxes($(this), 'bd_cleandash_hide_admin_bar_roles');
            });
            
            $frontendToggle.on('change', function() {
                toggleRoleCheckboxes($(this), 'bd_cleandash_hide_admin_bar_frontend_roles');
            });
        },

        /**
         * Initialize notification auto-hide functionality
         */
        initNotifications: function() {
            // Auto-hide success notifications after 4 seconds
            this.autoHideNotifications();
        },

        /**
         * Auto-hide notification messages
         */
        autoHideNotifications: function() {
            $('.notice.notice-success.bd-notice').each(function() {
                const $notice = $(this);
                
                // Check if notice is dismissible and add click handler
                if ($notice.hasClass('is-dismissible')) {
                    $notice.find('.notice-dismiss').on('click', function() {
                        $notice.addClass('fade-out');
                        setTimeout(function() {
                            $notice.remove();
                        }, 600);
                    });
                }
                
                // Add fade-out animation after 4 seconds (only if not manually dismissed)
                setTimeout(function() {
                    if ($notice.is(':visible') && !$notice.hasClass('fade-out')) {
                        $notice.addClass('fade-out');
                        // Remove from DOM after animation completes
                        setTimeout(function() {
                            $notice.remove();
                        }, 600); // Match CSS transition duration
                    }
                }, 4000);
            });
        },

        /**
         * Show a temporary notification (for AJAX responses)
         */
        showNotification: function(message, type = 'success') {
            const $notification = $('<div class="notice notice-' + type + ' bd-notice is-dismissible">' +
                '<p>' + message + '</p>' +
                '<button type="button" class="notice-dismiss">' +
                '<span class="screen-reader-text">Dismiss this notice.</span>' +
                '</button>' +
                '</div>');
            
            $('.bd-admin-container').prepend($notification);
            
            // Add dismiss functionality
            $notification.find('.notice-dismiss').on('click', function() {
                $notification.addClass('fade-out');
                setTimeout(function() {
                    $notification.remove();
                }, 600);
            });
            
            // Auto-hide after 4 seconds
            setTimeout(function() {
                if ($notification.is(':visible') && !$notification.hasClass('fade-out')) {
                    $notification.addClass('fade-out');
                    setTimeout(function() {
                        $notification.remove();
                    }, 600);
                }
            }, 4000);
        },

        /**
         * Initialize GitHub updates functionality
         */
        initGitHubUpdates: function() {
            // GitHub updates initialization
        },

        /**
         * Check for GitHub updates
         */
        checkGitHubUpdates: function() {
            const $button = $('#check-github-updates');
            const $status = $('#github-update-status');
            
            $button.prop('disabled', true).text(bdCleanDashAdmin.strings.checking_updates);
            $status.html('<p>' + bdCleanDashAdmin.strings.checking_for_updates + '</p>');
            
            $.ajax({
                url: bdCleanDashAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'bd_check_github_updates',
                    nonce: bdCleanDashAdmin.nonce
                },
                success: function(response) {
                    $button.prop('disabled', false).text(bdCleanDashAdmin.strings.check_updates);
                    
                    if (response.success) {
                        $status.html('<p style="color: green;">' + response.data.message + '</p>');
                    } else {
                        $status.html('<p style="color: red;">' + bdCleanDashAdmin.strings.update_check_error + ': ' + (response.data || bdCleanDashAdmin.strings.unknown_error) + '</p>');
                    }
                },
                error: function() {
                    $button.prop('disabled', false).text(bdCleanDashAdmin.strings.check_updates);
                    $status.html('<p style="color: red;">' + bdCleanDashAdmin.strings.update_network_error + '</p>');
                }
            });
        },

        /**
         * Export blacklist
         */
        exportBlacklist: function() {
            $.ajax({
                url: bdCleanDashAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'bd_export_blacklist',
                    scope: 'user',
                    nonce: bdCleanDashAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Create download link
                        const blob = new Blob([JSON.stringify(response.data.data, null, 2)], {
                            type: 'application/json'
                        });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = response.data.filename;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                    } else {
                        alert(bdCleanDashAdmin.strings.could_not_export);
                    }
                },
                error: function() {
                    alert(bdCleanDashAdmin.strings.network_error);
                }
            });
        },

        /**
         * Import blacklist
         */
        importBlacklist: function() {
            const fileInput = document.getElementById('import-file');
            const file = fileInput.files[0];
            
            if (!file) {
                alert(bdCleanDashAdmin.strings.select_file_first);
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const importData = JSON.parse(e.target.result);
                    
                    $.ajax({
                        url: bdCleanDashAdmin.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'bd_import_blacklist',
                            import_data: JSON.stringify(importData),
                            user_id: 0, // Import for current user
                            nonce: bdCleanDashAdmin.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(response.data.message);
                                BDCleanDashAdmin.loadBlacklist(); // Reload the list
                                fileInput.value = ''; // Clear file input
                                $('#import-blacklist').prop('disabled', true);
                            } else {
                                alert(bdCleanDashAdmin.strings.import_failed + ': ' + (response.data || bdCleanDashAdmin.strings.unknown_error));
                            }
                        },
                        error: function() {
                            alert(bdCleanDashAdmin.strings.network_error);
                        }
                    });
                } catch (error) {
                    alert(bdCleanDashAdmin.strings.invalid_json);
                }
            };
            reader.readAsText(file);
        },

        /**
         * Clear user blacklist
         */
        clearUserBlacklist: function() {
            if (!confirm(bdCleanDashAdmin.strings.confirm_clear_personal)) {
                return;
            }
            
            $.ajax({
                url: bdCleanDashAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'bd_clear_blacklist',
                    scope: 'user',
                    nonce: bdCleanDashAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        BDCleanDashAdmin.loadBlacklist(); // Reload the list
                    } else {
                        alert(bdCleanDashAdmin.strings.could_not_clear);
                    }
                },
                error: function() {
                    alert(bdCleanDashAdmin.strings.network_error);
                }
            });
        },

        /**
         * Clear all blacklists
         */
        clearAllBlacklists: function() {
            if (!confirm(bdCleanDashAdmin.strings.confirm_clear_all)) {
                return;
            }
            
            $.ajax({
                url: bdCleanDashAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'bd_clear_blacklist',
                    scope: 'all',
                    nonce: bdCleanDashAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        BDCleanDashAdmin.loadBlacklist(); // Reload the list
                    } else {
                        alert(bdCleanDashAdmin.strings.could_not_clear_all);
                    }
                },
                error: function() {
                    alert(bdCleanDashAdmin.strings.network_error);
                }
            });
        },

        /**
         * Format date
         */
        formatDate: function(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('nb-NO', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    };

    // Make globally available for debugging
    window.BDCleanDashAdmin = BDCleanDashAdmin;
    
    // Initialize immediately since we're already in document ready
    console.log('BD CleanDash: About to initialize admin object');
    BDCleanDashAdmin.init();
    console.log('BD CleanDash: Admin object initialized successfully');

    } catch (error) {
        console.error('BD CleanDash: Error initializing admin scripts:', error);
        console.error('BD CleanDash: Error stack:', error.stack);
    }

});