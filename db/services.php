<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// any later version.
/**
 * External services definition for block_moochat
 *
 * @package    block_moochat
 * @copyright  2025 Brian A. Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_moochat_send_message' => [
        'classname'   => 'block_moochat\external\send_message',
        'methodname'  => 'execute',
        'description' => 'Send a message to the AI chatbot',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => true,
    ],
    'block_moochat_save_conversation' => [
        'classname'   => 'block_moochat\external\save_conversation',
        'methodname'  => 'execute',
        'description' => 'Save conversation messages to database',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => true,
    ],
];
