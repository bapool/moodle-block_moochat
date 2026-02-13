<?php
// This file is part of Moodle - http://moodle.org/
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

namespace block_moochat\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use context_block;

/**
 * External service for sending chat messages
 */
class send_message extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'instanceid' => new external_value(PARAM_INT, 'Block instance ID'),
            'message' => new external_value(PARAM_TEXT, 'User message'),
            'history' => new external_value(PARAM_RAW, 'Conversation history as JSON', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Send a message to the AI chatbot
     *
     * @param int $instanceid Block instance ID
     * @param string $message User's message
     * @param string $conversationhistory Conversation history JSON
     * @return array Response with success/error and AI reply
     */
    public static function execute($instanceid, $message, $conversationhistory = '') {
        global $DB, $USER;

        // Validate parameters
        $params = self::validate_parameters(self::execute_parameters(), [
            'instanceid' => $instanceid,
            'message' => $message,
            'history' => $conversationhistory,
        ]);

        // Get block instance
        $blockinstance = $DB->get_record('block_instances', ['id' => $params['instanceid']], '*', MUST_EXIST);
        $block = block_instance('moochat', $blockinstance);

        if (!$block) {
            return [
                'success' => false,
                'error' => 'Block not found',
                'reply' => '',
                'remaining' => -1,
            ];
        }

        // Validate context and require login
        $context = context_block::instance($params['instanceid']);
        self::validate_context($context);
        require_login();

        // Get block configuration
        $config = $block->config;
        $systemprompt = isset($config->systemprompt) ? $config->systemprompt : get_string('defaultprompt', 'block_moochat');
        $maxmessages = isset($config->maxmessages) ? intval($config->maxmessages) : 20;

        // Automatic cleanup: Delete records older than 7 days
        $cleanuptime = time() - (7 * 86400);
        $DB->delete_records_select('block_moochat_usage', 'lastmessage < ?', [$cleanuptime]);

        // Check rate limiting
        $ratelimit_enabled = isset($config->ratelimit_enable) ? $config->ratelimit_enable : 0;
        $remaining = -1;

        if ($ratelimit_enabled) {
            $ratelimit_period = isset($config->ratelimit_period) ? $config->ratelimit_period : 'day';
            $ratelimit_count = isset($config->ratelimit_count) ? intval($config->ratelimit_count) : 10;

            // Get or create usage record
            $usage = $DB->get_record('block_moochat_usage',
                ['instanceid' => $params['instanceid'], 'userid' => $USER->id]);

            $now = time();
            $period_seconds = ($ratelimit_period === 'hour') ? 3600 : 86400;

            if ($usage) {
                // Check if we need to reset the counter
                if (($now - $usage->firstmessage) >= $period_seconds) {
                    // Period has expired, reset counter
                    $usage->messagecount = 0;
                    $usage->firstmessage = $now;
                    $usage->lastmessage = $now;
                    $DB->update_record('block_moochat_usage', $usage);
                } else {
                    // Check if limit reached
                    if ($usage->messagecount >= $ratelimit_count) {
                        $period_string = get_string('ratelimitreached_' . $ratelimit_period, 'block_moochat');
                        return [
                            'success' => false,
                            'error' => get_string('ratelimitreached', 'block_moochat',
                                ['limit' => $ratelimit_count, 'period' => $period_string]),
                            'reply' => '',
                            'remaining' => 0,
                        ];
                    }
                }
            } else {
                // Create new usage record
                $usage = new \stdClass();
                $usage->instanceid = $params['instanceid'];
                $usage->userid = $USER->id;
                $usage->messagecount = 0;
                $usage->firstmessage = $now;
                $usage->lastmessage = $now;
                $usage->id = $DB->insert_record('block_moochat_usage', $usage);
            }
        }

        // Parse conversation history
        $history = [];
        if (!empty($params['history'])) {
            $history = json_decode($params['history'], true);
            if (!is_array($history)) {
                $history = [];
            }
        }

        // Check message limit
        if ($maxmessages > 0 && count($history) >= ($maxmessages * 2)) {
            return [
                'success' => false,
                'error' => get_string('maxmessagesreached', 'block_moochat'),
                'reply' => '',
                'remaining' => $remaining,
            ];
        }

        // Build full prompt with system instructions and conversation history
        $fullprompt = $systemprompt . "\n\n";

        // Add conversation history
        foreach ($history as $msg) {
            if ($msg['role'] === 'user') {
                $fullprompt .= "User: " . $msg['content'] . "\n";
            } else if ($msg['role'] === 'assistant') {
                $fullprompt .= "Assistant: " . $msg['content'] . "\n";
            }
        }

        // Add current message
        $fullprompt .= "User: " . $params['message'] . "\nAssistant:";

        try {
            // Create AI action using Moodle's core AI system
            $action = new \core_ai\aiactions\generate_text(
                contextid: $context->id,
                userid: $USER->id,
                prompttext: $fullprompt
            );

            // Get AI manager and process the action
            $manager = \core\di::get(\core_ai\manager::class);
            $response = $manager->process_action($action);

            if ($response->get_success()) {
                $reply = $response->get_response_data()['generatedcontent'] ?? '';

                // Update usage counter if rate limiting is enabled
                if ($ratelimit_enabled && isset($usage)) {
                    $usage->messagecount++;
                    $usage->lastmessage = time();
                    $DB->update_record('block_moochat_usage', $usage);

                    $remaining = $ratelimit_count - $usage->messagecount;
                } else {
                    $remaining = -1;
                }

                return [
                    'success' => true,
                    'error' => '',
                    'reply' => trim($reply),
                    'remaining' => $remaining,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response->get_errormessage() ?: 'AI generation failed',
                    'reply' => '',
                    'remaining' => $remaining,
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
                'reply' => '',
                'remaining' => $remaining,
            ];
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'error' => new external_value(PARAM_TEXT, 'Error message if any'),
            'reply' => new external_value(PARAM_RAW, 'AI reply'),
            'remaining' => new external_value(PARAM_INT, 'Remaining questions (-1 = unlimited)'),
        ]);
    }
}
