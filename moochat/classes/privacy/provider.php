<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Define all the restore steps that will be used by the restore_moochat_activity_task
 *
 * @package    mod_moochat
 * @copyright  2025 Brian A. Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_moochat\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

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
            'moochat_usage',
            [
                'userid' => 'privacy:metadata:moochat_usage:userid',
                'messagecount' => 'privacy:metadata:moochat_usage:messagecount',
                'firstmessage' => 'privacy:metadata:moochat_usage:firstmessage',
                'lastmessage' => 'privacy:metadata:moochat_usage:lastmessage',
            ],
            'privacy:metadata:moochat_usage'
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
        $sql = "SELECT c.id
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {moochat} mc ON mc.id = cm.instance
            INNER JOIN {moochat_usage} mu ON mu.moochatid = mc.id
                 WHERE mu.userid = :userid";

        $params = [
            'modname' => 'moochat',
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ];

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $sql = "SELECT mu.userid
                  FROM {course_modules} cm
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {moochat} mc ON mc.id = cm.instance
            INNER JOIN {moochat_usage} mu ON mu.moochatid = mc.id
                 WHERE cm.id = :cmid";

        $params = [
            'cmid' => $context->instanceid,
            'modname' => 'moochat',
        ];

        $userlist->add_from_sql('userid', $sql, $params);
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

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT cm.id AS cmid,
                       mu.messagecount,
                       mu.firstmessage,
                       mu.lastmessage,
                       mc.name
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {moochat} mc ON mc.id = cm.instance
            INNER JOIN {moochat_usage} mu ON mu.moochatid = mc.id
                 WHERE c.id {$contextsql}
                       AND mu.userid = :userid
              ORDER BY cm.id";

        $params = [
            'modname' => 'moochat',
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $user->id,
        ] + $contextparams;

        $usages = $DB->get_records_sql($sql, $params);

        foreach ($usages as $usage) {
            $context = \context_module::instance($usage->cmid);
            $data = [
                'name' => $usage->name,
                'messagecount' => $usage->messagecount,
                'firstmessage' => \core_privacy\local\request\transform::datetime($usage->firstmessage),
                'lastmessage' => \core_privacy\local\request\transform::datetime($usage->lastmessage),
            ];
            writer::with_context($context)->export_data([], (object)$data);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('moochat', $context->instanceid);
        if (!$cm) {
            return;
        }

        $DB->delete_records('moochat_usage', ['moochatid' => $cm->instance]);
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

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id('moochat', $context->instanceid);
            if (!$cm) {
                continue;
            }
            $DB->delete_records('moochat_usage', ['moochatid' => $cm->instance, 'userid' => $userid]);
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

        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('moochat', $context->instanceid);
        if (!$cm) {
            return;
        }

        $userids = $userlist->get_userids();
        list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        $select = "moochatid = :moochatid AND userid $usersql";
        $params = ['moochatid' => $cm->instance] + $userparams;
        $DB->delete_records_select('moochat_usage', $select, $params);
    }
}
