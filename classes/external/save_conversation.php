<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// any later version.
/**
 * External service for saving chat conversations
 *
 * @package    block_moochat
 * @copyright  2026 Brian A. Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_moochat\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use context_block;

/**
 * External service for saving chat conversations
 */
class save_conversation extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'instanceid' => new external_value(PARAM_INT, 'Block instance ID'),
            'usermessage' => new external_value(PARAM_TEXT, 'User message'),
            'assistantmessage' => new external_value(PARAM_TEXT, 'Assistant reply'),
        ]);
    }

    /**
     * Save a conversation exchange to the database
     *
     * @param int $instanceid Block instance ID
     * @param string $usermessage User's message
     * @param string $assistantmessage Assistant's reply
     * @return array Response with success status
     */
    public static function execute($instanceid, $usermessage, $assistantmessage) {
        global $DB, $USER;

        // Validate parameters
        $params = self::validate_parameters(self::execute_parameters(), [
            'instanceid' => $instanceid,
            'usermessage' => $usermessage,
            'assistantmessage' => $assistantmessage,
        ]);

        // Validate context and require login
        $context = context_block::instance($params['instanceid']);
        self::validate_context($context);
        require_login();

        $now = time();

        // Save user message
        $userrecord = new \stdClass();
        $userrecord->instanceid = $params['instanceid'];
        $userrecord->userid = $USER->id;
        $userrecord->role = 'user';
        $userrecord->message = $params['usermessage'];
        $userrecord->timecreated = $now;
        $DB->insert_record('block_moochat_conversations', $userrecord);

        // Save assistant message
        $assistantrecord = new \stdClass();
        $assistantrecord->instanceid = $params['instanceid'];
        $assistantrecord->userid = $USER->id;
        $assistantrecord->role = 'assistant';
        $assistantrecord->message = $params['assistantmessage'];
        $assistantrecord->timecreated = $now;
        $DB->insert_record('block_moochat_conversations', $assistantrecord);

        return [
            'success' => true,
        ];
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
        ]);
    }
}
