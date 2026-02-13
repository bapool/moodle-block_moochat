<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// any later version.
/**
 * Scheduled task to clean up old conversations
 *
 * @package    block_moochat
 * @copyright  2026 Brian A. Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_moochat\task;

/**
 * Scheduled task to delete conversations older than 90 days
 */
class cleanup_old_conversations extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task
     *
     * @return string
     */
    public function get_name() {
        return get_string('cleanupoldconversations', 'block_moochat');
    }

    /**
     * Execute the task
     */
    public function execute() {
        global $DB;

        // Calculate cutoff time (90 days ago)
        $cutofftime = time() - (90 * 86400);

        // Delete old conversations
        $deleted = $DB->delete_records_select(
            'block_moochat_conversations',
            'timecreated < ?',
            array($cutofftime)
        );

        if ($deleted) {
            mtrace("Deleted {$deleted} conversation messages older than 90 days");
        } else {
            mtrace("No old conversations to delete");
        }
    }
}
