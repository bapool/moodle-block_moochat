==============================================================================
MooChat Activity Module for Moodle
==============================================================================

Author: Brian A. Pool
Organization: National Trail Local Schools
Version: 1.1
License: GNU GPL v3 or later
Moodle Required: 4.0 or higher

==============================================================================
DESCRIPTION
==============================================================================

MooChat is an AI-powered chat activity module that brings intelligent 
conversation capabilities to your Moodle courses. Teachers can create 
customizable AI assistants with unique personalities, avatars, and purposes - 
from subject tutors to historical figures.

Unlike sidebar blocks, MooChat provides a full-featured activity that can be 
displayed inline on the course page or as a dedicated activity page, making 
it perfect for central course interactions.

==============================================================================
KEY FEATURES
==============================================================================

- Custom AI Personalities - Define unique system prompts for each chatbot
- Flexible Display Modes - Choose between inline (embedded on course page) 
  or separate page display
- Avatar Support - Upload custom images with adjustable sizing (48-128px)
- Adjustable Chat Size - Three size options (Small, Medium, Large) to fit 
  your layout needs
- Section Content Integration - AI can access course materials (pages, books, 
  labels, assignments, URLs, glossary) from the current section
- Hidden Content Option - Choose whether AI can reference hidden course 
  materials
- Rate Limiting - Prevent AI resource abuse with configurable question limits 
  (per hour or per day)
- Server-Side Tracking - Students cannot bypass limits by clearing chat
- Auto-Cleanup - Usage records automatically purge after 7 days
- Professional Layout - Horizontal design with avatar/info on left, chat on 
  right
- Message Formatting - Long AI responses are formatted with paragraphs, line 
  breaks, and bullet points for readability

==============================================================================
SYSTEM REQUIREMENTS
==============================================================================

- Moodle 4.0 or higher
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+ or PostgreSQL 9.6+
- Moodle Core AI Subsystem configured with at least one AI provider

IMPORTANT: This plugin requires Moodle's core AI subsystem to be configured. 
You must have at least one AI provider enabled (OpenAI, Anthropic, Azure 
OpenAI, or local models via Ollama). No external API keys or services beyond 
what Moodle provides are required.

==============================================================================
INSTALLATION
==============================================================================

OPTION 1: Manual Installation
------------------------------
1. Download the plugin package
2. Extract the contents to: [moodleroot]/mod/moochat
3. Navigate to: Site Administration > Notifications
4. Click "Upgrade Moodle database now"
5. Follow the on-screen instructions

OPTION 2: Via Moodle Plugin Installer
--------------------------------------
1. Go to: Site Administration > Plugins > Install plugins
2. Upload the plugin ZIP file
3. Click "Install plugin from the ZIP file"
4. Follow the on-screen instructions

POST-INSTALLATION:
------------------
1. Ensure Moodle's AI subsystem is configured:
   Site Administration > AI > AI providers
2. Enable and configure at least one AI provider
3. Test the configuration with a sample activity

==============================================================================
CONFIGURATION
==============================================================================

ACTIVITY SETTINGS:

General Settings:
- Chat Name - Give your AI assistant a descriptive name
- Introduction - Describe the purpose of this chat (optional)
- Display Mode - Choose "Separate page" or "Inline on course page"
- Chat Interface Size - Select Small, Medium, or Large
- Include Section Content - Enable to let AI access course materials
- Include Hidden Content - Enable to include hidden materials (teachers only)

AI Personality:
- System Prompt - Define how the AI should behave and respond
  Examples:
  - "You are a friendly math tutor who explains step-by-step"
  - "You are Benjamin Franklin discussing your inventions"
  - "You are a Python programming expert"

Avatar:
- Upload an image to represent your chatbot
- Choose avatar size (48px to 128px)

Rate Limiting:
- Enable Rate Limiting - Turn on to prevent abuse
- Rate Limit Period - Choose "Per Hour" or "Per Day"
- Maximum Questions - Set the number of questions allowed

Advanced Settings:
- Maximum Messages per Session - Deprecated, use Rate Limiting instead
- Temperature - Control AI creativity (0.1-0.9)

==============================================================================
USAGE
==============================================================================

FOR TEACHERS:

Creating a MooChat Activity:
1. Turn editing on in your course
2. Click "Add an activity or resource"
3. Select "MooChat" from the Activities section
4. Configure the activity settings:
   - Give it a name and description
   - Choose display mode and size
   - Upload an avatar (optional)
   - Write a system prompt to define AI personality
   - Enable section content if you want AI to reference course materials
   - Set rate limits if desired
5. Save and display

Best Practices:
- Write clear, specific system prompts
- Test the chatbot before students use it
- Use rate limiting to manage AI usage
- Enable section content for context-aware responses
- Use inline display for always-available assistance
- Use separate page for focused chat sessions

FOR STUDENTS:

Using MooChat:
1. Navigate to the course section with MooChat
2. If inline mode: Chat appears directly on the course page
   If separate page: Click the activity link to open
3. Type your question in the text box
4. Press Enter or click "Send"
5. Wait for the AI to respond
6. Continue the conversation as needed
7. Click "Clear Chat" to start a new conversation

Tips:
- Ask clear, specific questions
- Reference course materials when enabled
- Be patient - AI responses may take a few seconds
- Your question limit (if set) persists across sessions

==============================================================================
SECTION CONTENT INTEGRATION
==============================================================================

When "Include Section Content" is enabled, the AI has access to:

- Pages (mod_page) - Full text content
- Books (mod_book) - All chapters and content
- Labels - Text displayed on course page
- Assignments - Assignment descriptions
- URLs - Link titles and descriptions
- Glossary - All terms and definitions

This allows students to ask questions like:
- "What are the main points from the reading?"
- "Can you explain the assignment requirements?"
- "Define the terms from the glossary"
- "Summarize what we learned in this section"

PRIVACY NOTE: Students can ask the AI about hidden content if the teacher 
enables "Include Hidden Content". Use this feature carefully.

==============================================================================
TROUBLESHOOTING
==============================================================================

AI Not Responding:
- Check that Moodle AI subsystem is configured
- Verify at least one AI provider is enabled and working
- Check PHP error logs for API connection issues
- Ensure network allows connections to AI provider

Rate Limit Not Working:
- Verify rate limiting is enabled in activity settings
- Check database table exists: [prefix]_moochat_usage
- Students may need to wait until the time period expires

Section Content Not Loading:
- Verify "Include Section Content" is enabled
- Check that content exists in the same section as MooChat
- Review PHP error logs for database query issues

Formatting Issues:
- Clear Moodle cache: Site Administration > Development > Purge all caches
- Clear browser cache and refresh page
- Check that JavaScript is enabled in browser

==============================================================================
DATABASE TABLES
==============================================================================

This plugin creates two tables:

[prefix]_moochat
- Stores activity instances and configuration

[prefix]_moochat_usage  
- Tracks student usage for rate limiting
- Automatically cleaned every 7 days

==============================================================================
SUPPORT & DEVELOPMENT
==============================================================================

Author: Brian A. Pool
Organization: National Trail Local Schools

For issues, feature requests, or contributions:
- Report bugs via email to the author
- Feature suggestions welcome
- Contributions and improvements appreciated

==============================================================================
LICENSE
==============================================================================

This program is free software: you can redistribute it and/or modify it under 
the terms of the GNU General Public License as published by the Free Software 
Foundation, either version 3 of the License, or (at your option) any later 
version.

This program is distributed in the hope that it will be useful, but WITHOUT 
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with 
this program. If not, see <https://www.gnu.org/licenses/>.

==============================================================================
ACKNOWLEDGMENTS
==============================================================================

Special thanks to:
- Anthropic for AI assistance in development
- The Moodle community for ongoing support and inspiration
- National Trail Local Schools for supporting innovative educational technology

==============================================================================
CHANGELOG
==============================================================================

Version 1.0 (2025-10-30)
- Initial release
- Core chat functionality with AI integration
- Display modes: inline and separate page
- Section content integration
- Rate limiting with server-side tracking
- Avatar support with size options
- Adjustable chat interface sizes
- Message formatting for readability
- Hidden content option for teachers
- Auto-cleanup of usage data

==============================================================================
