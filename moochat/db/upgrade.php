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

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for mod_moochat
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool always true
 */
function xmldb_moochat_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Add display field
    if ($oldversion < 2025103002) {
        $table = new xmldb_table('moochat');
        $field = new xmldb_field('display', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'introformat');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_mod_savepoint(true, 2025103002, 'moochat');
    }

    // Add include_section_content and include_hidden_content fields
    if ($oldversion < 2025103003) {
        $table = new xmldb_table('moochat');
        
        $field1 = new xmldb_field('include_section_content', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'display');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        
        $field2 = new xmldb_field('include_hidden_content', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'include_section_content');
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        
        upgrade_mod_savepoint(true, 2025103003, 'moochat');
    }

    // Add chatsize field
    if ($oldversion < 2025103004) {
        $table = new xmldb_table('moochat');
        $field = new xmldb_field('chatsize', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'medium', 'include_hidden_content');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_mod_savepoint(true, 2025103004, 'moochat');
    }

    return true;
}