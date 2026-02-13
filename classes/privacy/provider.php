<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// any later version.
/**
 * Privacy Subsystem implementation for block_moochat
 *
 * @package    block_moochat
 * @copyright  2026 Brian A. Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_moochat\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy Subsystem for block_moochat implementing metadata and plugin providers.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        
        $collection->add_database_table(
            'block_moochat_usage',
            [
                'userid' => 'privacy:metadata:block_moochat_usage:userid',
                'instanceid' => 'privacy:metadata:block_moochat_usage:instanceid',
                'messagecount' => 'privacy:metadata:block_moochat_usage:messagecount',
                'firstmessage' => 'privacy:metadata:block_moochat_usage:firstmessage',
                'lastmessage' => 'privacy:metadata:block_moochat_usage:lastmessage',
            ],
            'privacy:metadata:block_moochat_usage'
        );

        $collection->add_database_table(
            'block_moochat_conversations',
            [
                'userid' => 'privacy:metadata:block_moochat_conversations:userid',
                'instanceid' => 'privacy:metadata:block_moochat_conversations:instanceid',
                'role' => 'privacy:metadata:block_moochat_conversations:role',
                'message' => 'privacy:metadata:block_moochat_conversations:message',
                'timecreated' => 'privacy:metadata:block_moochat_conversations:timecreated',
            ],
            'privacy:metadata:block_moochat_conversations'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        // Usage data
        $sql = "SELECT ctx.id
                  FROM {block_moochat_usage} u
                  JOIN {block_instances} bi ON bi.id = u.instanceid
                  JOIN {context} ctx ON ctx.instanceid = bi.id AND ctx.contextlevel = :contextblock
                 WHERE u.userid = :userid";

        $contextlist->add_from_sql($sql, ['userid' => $userid, 'contextblock' => CONTEXT_BLOCK]);

        // Conversation data
        $sql = "SELECT ctx.id
                  FROM {block_moochat_conversations} c
                  JOIN {block_instances} bi ON bi.id = c.instanceid
                  JOIN {context} ctx ON ctx.instanceid = bi.id AND ctx.contextlevel = :contextblock
                 WHERE c.userid = :userid";

        $contextlist->add_from_sql($sql, ['userid' => $userid, 'contextblock' => CONTEXT_BLOCK]);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_BLOCK) {
            return;
        }

        // Usage data
        $sql = "SELECT u.userid
                  FROM {block_moochat_usage} u
                  JOIN {block_instances} bi ON bi.id = u.instanceid
                  JOIN {context} ctx ON ctx.instanceid = bi.id AND ctx.contextlevel = :contextblock
                 WHERE ctx.id = :contextid";

        $userlist->add_from_sql('userid', $sql, ['contextid' => $context->id, 'contextblock' => CONTEXT_BLOCK]);

        // Conversation data
        $sql = "SELECT c.userid
                  FROM {block_moochat_conversations} c
                  JOIN {block_instances} bi ON bi.id = c.instanceid
                  JOIN {context} ctx ON ctx.instanceid = bi.id AND ctx.contextlevel = :contextblock
                 WHERE ctx.id = :contextid";

        $userlist->add_from_sql('userid', $sql, ['contextid' => $context->id, 'contextblock' => CONTEXT_BLOCK]);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_BLOCK) {
                continue;
            }

            $instanceid = $context->instanceid;

            // Export usage data
            $usage = $DB->get_record('block_moochat_usage', [
                'instanceid' => $instanceid,
                'userid' => $user->id
            ]);

            if ($usage) {
                $data = (object) [
                    'messagecount' => $usage->messagecount,
                    'firstmessage' => \core_privacy\local\request\transform::datetime($usage->firstmessage),
                    'lastmessage' => \core_privacy\local\request\transform::datetime($usage->lastmessage),
                ];
                writer::with_context($context)->export_data(['moochat_usage'], $data);
            }

            // Export conversation data
            $conversations = $DB->get_records('block_moochat_conversations', [
                'instanceid' => $instanceid,
                'userid' => $user->id
            ], 'timecreated ASC');

            if ($conversations) {
                $conversationdata = [];
                foreach ($conversations as $conv) {
                    $conversationdata[] = (object) [
                        'role' => $conv->role,
                        'message' => $conv->message,
                        'timecreated' => \core_privacy\local\request\transform::datetime($conv->timecreated),
                    ];
                }
                writer::with_context($context)->export_data(['moochat_conversations'], (object)['conversations' => $conversationdata]);
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_BLOCK) {
            return;
        }

        $instanceid = $context->instanceid;
        
        $DB->delete_records('block_moochat_usage', ['instanceid' => $instanceid]);
        $DB->delete_records('block_moochat_conversations', ['instanceid' => $instanceid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_BLOCK) {
                continue;
            }

            $instanceid = $context->instanceid;
            
            $DB->delete_records('block_moochat_usage', [
                'instanceid' => $instanceid,
                'userid' => $user->id
            ]);
            
            $DB->delete_records('block_moochat_conversations', [
                'instanceid' => $instanceid,
                'userid' => $user->id
            ]);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_BLOCK) {
            return;
        }

        $instanceid = $context->instanceid;
        $userids = $userlist->get_userids();

        if (empty($userids)) {
            return;
        }

        list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        
        $DB->delete_records_select('block_moochat_usage', 
            "instanceid = :instanceid AND userid $usersql",
            array_merge(['instanceid' => $instanceid], $userparams));
        
        $DB->delete_records_select('block_moochat_conversations', 
            "instanceid = :instanceid AND userid $usersql",
            array_merge(['instanceid' => $instanceid], $userparams));
    }
}
