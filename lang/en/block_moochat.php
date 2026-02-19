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
 
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'MooChat';
$string['moochat'] = 'MooChat';
$string['moochat:addinstance'] = 'Add a new MooChat block';
$string['moochat:myaddinstance'] = 'Add a new MooChat block to Dashboard';
$string['moochat:configure'] = 'Configure MooChat settings';
$string['avatar'] = 'Chatbot Avatar Image';
$string['avatar_help'] = 'Upload a small image to represent your chatbot (e.g., historical figure, mascot, etc.). Recommended size: 50x50 pixels.';

// Teacher configuration
$string['teacherconfig'] = 'Teacher Configuration';
$string['confighelp'] = 'Configure how the AI chatbot will interact with students in this block.';
$string['editconfiguration'] = 'Edit Configuration';
$string['currentsystemprompt'] = 'Current AI Personality:';
$string['defaultprompt'] = 'You are a helpful educational assistant.';
$string['blocktitle'] = 'Chatbot Name';
$string['blocktitle_help'] = 'Give your chatbot a custom name (e.g., "Math Tutor", "Historical Figure Chat", "Science Helper")';
$string['avatarsize'] = 'Avatar Size';
$string['avatarsize_help'] = 'Choose the size of the avatar image displayed in the block title.';

// Configuration form
$string['systemprompt'] = 'System Prompt (AI Personality)';
$string['systemprompt_help'] = 'Define how the AI should behave. Example: "You are a friendly math tutor who explains concepts step-by-step" or "You are a historical figure from ancient Rome."';
$string['modelselection'] = 'AI Model';
$string['modelselection_help'] = 'Choose which AI model to use. Smaller models are faster, larger models are more capable.';
$string['maxmessages'] = 'Maximum Messages per Session';
$string['maxmessages_help'] = 'Limit how many messages a student can send in one session (0 = unlimited).';
$string['temperature'] = 'Creativity Level';
$string['temperature_help'] = 'Lower values (0.1-0.3) make responses more focused and consistent. Higher values (0.7-1.0) make responses more creative and varied.';
$string['advancedsettings'] = 'Advanced Settings';

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
$string['questionsremaining'] = 'Questions remaining: {$a}';
$string['ratelimitreached'] = 'You have reached your limit of {$a->limit} questions {$a->period}. Please try again later.';
$string['ratelimitreached_hour'] = 'per hour';
$string['ratelimitreached_day'] = 'per day';
$string['ratelimitreached_title'] = 'Rate Limit Reached';

// Chat interface
$string['startchat'] = 'Hello, I am here if you want to chat.';
$string['startchat_named'] = 'Hello, my name is {$a}, would you like to chat?';
$string['typemessage'] = 'Type your message here...';
$string['send'] = 'Send';
$string['clear'] = 'Clear Chat';
$string['chatlimitreached'] = 'Chat Limit Reached';
$string['maxmessagesreached'] = 'You have reached the maximum number of messages for this chat session. Please clear the chat to start a new conversation.';
$string['thinking'] = 'Thinking...';
$string['chatcleared'] = 'Chat cleared. Start a new conversation!';
$string['confirmclear'] = 'Clear all messages? (Your question limit will not reset)';
$string['error'] = 'Error';
$string['connectionerror'] = 'Failed to connect to AI service';

// Conversation tracking
$string['viewconversations'] = 'View Conversations';
$string['studentconversations'] = 'Student Conversations';
$string['conversationwith'] = 'Conversation with {$a}';
$string['messagecount'] = 'Messages';
$string['messages'] = 'messages';
$string['lastmessage'] = 'Last Message';
$string['viewdetails'] = 'View Details';
$string['backtolist'] = 'Back to List';
$string['noconversations'] = 'No conversations found for this student.';
$string['nostudentconversations'] = 'No student conversations yet.';
$string['student'] = 'Student';
$string['assistant'] = 'Assistant';
$string['conversations'] = 'Conversations';
$string['deleteallconversations'] = 'Delete all MooChat conversations';
$string['expandall'] = 'Expand All';
$string['collapseall'] = 'Collapse All';

// Settings
$string['ollama_endpoint'] = 'Ollama API Endpoint';
$string['ollama_endpoint_desc'] = 'URL of your Ollama server API endpoint (e.g., https://blazerai.nationaltrail.us/ollama/v1/chat/completions)';
$string['default_model'] = 'Default AI Model';
$string['default_model_desc'] = 'Default model to use (e.g., llama2:latest, tinyllama:latest, gemma2:latest)';

// Privacy API
$string['privacy:metadata:block_moochat_usage'] = 'Information about the user\'s chat usage and rate limiting.';
$string['privacy:metadata:block_moochat_usage:userid'] = 'The ID of the user.';
$string['privacy:metadata:block_moochat_usage:instanceid'] = 'The block instance ID.';
$string['privacy:metadata:block_moochat_usage:messagecount'] = 'Number of messages sent by the user.';
$string['privacy:metadata:block_moochat_usage:firstmessage'] = 'Timestamp of the first message in the current period.';
$string['privacy:metadata:block_moochat_usage:lastmessage'] = 'Timestamp of the last message sent.';
$string['privacy:metadata:block_moochat_conversations'] = 'Stores chat conversations between students and the AI assistant.';
$string['privacy:metadata:block_moochat_conversations:userid'] = 'The ID of the user.';
$string['privacy:metadata:block_moochat_conversations:instanceid'] = 'The block instance ID.';
$string['privacy:metadata:block_moochat_conversations:role'] = 'The role of the message sender (user or assistant).';
$string['privacy:metadata:block_moochat_conversations:message'] = 'The message content.';
$string['privacy:metadata:block_moochat_conversations:timecreated'] = 'When the message was created.';
