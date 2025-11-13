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

$string['pluginname'] = 'MooChat';
$string['modulename'] = 'MooChat';
$string['modulenameplural'] = 'MooChats';
$string['modulename_help'] = 'The MooChat activity allows you to create an AI-powered chatbot for your course. Students can interact with the AI assistant to get help, ask questions, or engage with course content.';
$string['moochat:addinstance'] = 'Add a new MooChat activity';
$string['moochat:view'] = 'View MooChat activity';
$string['moochat:submit'] = 'Submit messages to MooChat';

// Settings
$string['chatname'] = 'Chat Name';
$string['chatname_help'] = 'Give your AI assistant a name (e.g., "Math Tutor", "Historical Figure Chat", "Science Helper")';
$string['display'] = 'Display Mode';
$string['display_help'] = 'Choose how the chat interface is displayed:<br>
<strong>Separate page:</strong> Students click the activity link to open the chat on its own page.<br>
<strong>Inline on course page:</strong> The chat interface appears directly on the course page.';
$string['display_page'] = 'Separate page';
$string['display_inline'] = 'Inline on course page';
$string['chatsize'] = 'Chat Interface Size';
$string['chatsize_help'] = 'Choose the size of the chat interface. Larger sizes provide more space for conversations and are recommended for separate page display mode.';
$string['chatsize_small'] = 'Small (300px messages, 400px total)';
$string['chatsize_medium'] = 'Medium (400px messages, 500px total)';
$string['chatsize_large'] = 'Large (600px messages, 700px total)';
$string['include_section_content'] = 'Include Section Content';
$string['include_section_content_help'] = 'When enabled, the AI will have access to all content in the current course section, including pages, books, labels, assignments, URLs, and glossary entries. This allows the chatbot to answer questions about course materials in context.';
$string['include_hidden_content'] = 'Include Hidden Content';
$string['include_hidden_content_help'] = 'When enabled, hidden activities and resources will be included in the section content provided to the AI. This allows the chatbot to answer questions about upcoming or draft content. Note: Students will still see responses based on hidden content if this is enabled.';
$string['avatar'] = 'Avatar Image';
$string['avatar_help'] = 'Upload an image to represent your chatbot (e.g., historical figure, mascot, etc.). Recommended size: 128x128 pixels.';
$string['avatarsize'] = 'Avatar Size';
$string['avatarsize_help'] = 'Choose the size of the avatar image displayed.';
$string['systemprompt'] = 'System Prompt (AI Personality)';
$string['systemprompt_help'] = 'Define how the AI should behave. Example: "You are a friendly math tutor who explains concepts step-by-step" or "You are a historical figure from ancient Rome."';
$string['defaultprompt'] = 'You are a helpful educational assistant designed to help students learn.';

// Rate limiting
$string['ratelimiting'] = 'Rate Limiting';
$string['ratelimit_enable'] = 'Enable Rate Limiting';
$string['ratelimit_enable_help'] = 'When enabled, students will be limited to a specific number of questions per time period. This prevents them from clearing the chat and starting over.';
$string['ratelimit_period'] = 'Rate Limit Period';
$string['ratelimit_period_help'] = 'Choose whether to limit questions per hour or per day.';
$string['ratelimit_count'] = 'Maximum Questions';
$string['ratelimit_count_help'] = 'Number of questions a student can ask during the selected time period. Example: "10 questions per day" means students can ask 10 questions, then must wait until the next day.';
$string['period_hour'] = 'Per Hour';
$string['period_day'] = 'Per Day';
$string['conversationsremaining'] = 'Conversations remaining: {$a}';
$string['messagesremaining'] = 'Messages left in this conversation: {$a}';
$string['ratelimitreached'] = 'You have reached your limit of {$a->limit} questions {$a->period}. Please try again later.';
$string['ratelimitreached_hour'] = 'per hour';
$string['ratelimitreached_day'] = 'per day';

// Advanced settings
$string['advancedsettings'] = 'Advanced Settings';
$string['modelselection'] = 'AI Model';
$string['modelselection_help'] = 'Choose which AI model to use. Smaller models are faster, larger models are more capable.';
$string['missingidandcmid'] = 'You must specify a course module ID';
$string['maxmessages'] = 'Maximum Messages per Session';
$string['maxmessages_help'] = 'Limit how many messages a student can send in one session (0 = unlimited).';
$string['temperature'] = 'Creativity Level';
$string['temperature_help'] = 'Lower values (0.1-0.3) make responses more focused and consistent. Higher values (0.7-1.0) make responses more creative and varied.';

// Chat interface
$string['startchat'] = 'Start chatting with the AI assistant!';
$string['typemessage'] = 'Type your message here...';
$string['send'] = 'Send';
$string['clear'] = 'Clear Chat';
$string['maxmessagesreached'] = 'You have reached the maximum number of messages for this chat session. Please clear the chat to start a new conversation.';
$string['maxmessagesreached_title'] = 'Chat Limit Reached';
$string['thinking'] = 'Thinking...';

// Privacy
$string['privacy:metadata:moochat_usage'] = 'Information about user interactions with MooChat activities';
$string['privacy:metadata:moochat_usage:userid'] = 'The ID of the user';
$string['privacy:metadata:moochat_usage:messagecount'] = 'Number of messages sent';
$string['privacy:metadata:moochat_usage:firstmessage'] = 'Timestamp of first message';
$string['privacy:metadata:moochat_usage:lastmessage'] = 'Timestamp of last message';
// JavaScript strings
$string['conversationsremaining_js'] = 'Conversations remaining today';
$string['messagesremaining_js'] = 'Messages remaining in this conversation';
$string['thinking_js'] = 'Thinking...';
$string['ratelimitreached_title'] = 'Rate Limit Reached';
$string['error_title'] = 'Error';
$string['connectionerror'] = 'Failed to connect to AI service';
$string['chatcleared'] = 'Chat cleared. Start a new conversation!';
$string['confirmclear'] = 'Clear all messages? (Your question limit will not reset)';
