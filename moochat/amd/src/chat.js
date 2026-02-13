/* This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// any later version.
/**
 * Privacy Subsystem implementation for mod_mooproof
 *
 * @package    block_moochat
 * @copyright  2025 Brian A. Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    return {
        init: function(instanceid, strings) {

            var conversationHistory = [];
            // Var messageCount = 0;
            var remainingQuestions = -1; // -1 means unlimited

            var messagesDiv = $('#moochat-messages-' + instanceid);
            var inputField = $('#moochat-input-' + instanceid);
            var sendButton = $('#moochat-send-' + instanceid);
            var clearButton = $('#moochat-clear-' + instanceid);

            // Create remaining questions display
            var remainingDiv = $('<div class="moochat-remaining" id="moochat-remaining-' + instanceid + '"></div>');
            $('#moochat-' + instanceid).prepend(remainingDiv);

    // Update remaining questions display
    var updateRemaining = function(remaining) {
        if (remaining >= 0) {
        var message = strings.questionsremaining.replace('{$a}', remaining);
        remainingDiv.html('<p class="alert alert-info">' + message + '</p>');
        remainingDiv.show();
        } else {
        remainingDiv.hide();
        }
    };

            // Send message
            var sendMessage = function() {
                var message = inputField.val().trim();

                if (message === '') {
                    return;
                }

                // Disable input while processing
                inputField.prop('disabled', true);
                sendButton.prop('disabled', true);

                // Add user message to display
                addMessage('user', message);

                // Add to history
                conversationHistory.push({
                    role: 'user',
                    content: message
                });

                // Clear input
                inputField.val('');

                // Show thinking indicator
                var thinkingId = 'thinking-' + Date.now();
                messagesDiv.append(
                    '<div class="moochat-message moochat-assistant" id="' + thinkingId + '">' +
                    '<em>' + strings.thinking + '</em></div>'
                );
                scrollToBottom();

                // Call API using Moodle's External Service
                Ajax.call([{
                    methodname: 'block_moochat_send_message',
                    args: {
                        instanceid: instanceid,
                        message: message,
                        history: JSON.stringify(conversationHistory)
                    },
                    done: function(response) {
                        // Remove thinking indicator
                        $('#' + thinkingId).remove();

                        if (!response.success) {
                            // Check if this is a rate limit error
                            if (response.remaining !== undefined && response.remaining === 0) {
                                Notification.alert(strings.ratelimitreached_title, response.error, 'OK');
                                inputField.prop('disabled', true);
                                sendButton.prop('disabled', true);
                                updateRemaining(0);
                            } else if (response.error === strings.maxmessagesreached) {
                                Notification.alert(strings.chatlimitreached, response.error, 'OK');
                            } else {
                                Notification.alert(strings.error, response.error, 'OK');
                            }
                        } else if (response.success && response.reply) {
                            // Add assistant reply
                            addMessage('assistant', response.reply);

                            // Add to history
                            conversationHistory.push({
                                role: 'assistant',
                                content: response.reply
                            });

                            // Save conversation to database
                            Ajax.call([{
                                methodname: 'block_moochat_save_conversation',
                                args: {
                                    instanceid: instanceid,
                                    usermessage: message,
                                    assistantmessage: response.reply
                                },
                                done: function() {
                                    // Conversation saved successfully (silent)
                                },
                                fail: function() {
                                    // Failed to save (silent - don't interrupt user experience)
                                }
                            }]);

                            // MessageCount++;

                            // Update remaining questions
                            if (response.remaining !== undefined) {
                                remainingQuestions = response.remaining;
                                updateRemaining(remainingQuestions);

                                // Disable if no questions left
                                if (remainingQuestions === 0) {
                                    inputField.prop('disabled', true);
                                    sendButton.prop('disabled', true);
                                }
                            }
                        }

                        // Re-enable input (unless disabled by rate limit)
                        if (remainingQuestions !== 0) {
                            inputField.prop('disabled', false);
                            sendButton.prop('disabled', false);
                            inputField.focus();
                        }
                    },
                    fail: function() {
                        $('#' + thinkingId).remove();
                        Notification.alert(strings.error, strings.connectionerror, 'OK');
                        inputField.prop('disabled', false);
                        sendButton.prop('disabled', false);
                    }
                }]);
            };

            // Add message to display
            var addMessage = function(role, content) {
                var messageClass = role === 'user' ? 'moochat-user' : 'moochat-assistant';
                var messageHtml = '<div class="moochat-message ' + messageClass + '">' +
                                 escapeHtml(content) + '</div>';
                messagesDiv.append(messageHtml);
                scrollToBottom();
            };

            // Scroll to bottom of messages
            var scrollToBottom = function() {
                messagesDiv.scrollTop(messagesDiv[0].scrollHeight);
            };

            // Escape HTML
            var escapeHtml = function(text) {
                var div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            };

            // Clear chat (visual only - doesn't reset server counter)
            var clearChat = function() {
                conversationHistory = [];
                // MessageCount = 0;
                messagesDiv.html('<p class="moochat-welcome">' + strings.chatcleared + '</p>');
                inputField.val('').focus();
                // Note: remaining questions counter stays the same
            };

            // Event handlers
            sendButton.on('click', sendMessage);

            inputField.on('keypress', function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            clearButton.on('click', function() {
                if (confirm(strings.confirmclear)) {
                    clearChat();
                }
            });

            // Focus input on load
            inputField.focus();
        }
    };
});
