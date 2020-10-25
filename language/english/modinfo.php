<?php
// $Id: modinfo.php,v 1.9 2003/04/01 22:51:22 mvandam Exp $
// Module Info

// The name of this module
define('_MI_STORIES_NAME', 'Stories');

// A brief description of this module
define('_MI_STORIES_DESC', 'Creates a Slashdot-like news section, where users can post news/comments.');

// Names of blocks for this module (Not all module has blocks)
define('_MI_STORIES_BNAME1', 'News Topics');
define('_MI_STORIES_BNAME2', 'Big Story');
define('_MI_STORIES_BNAME3', 'Top News');
define('_MI_STORIES_BNAME4', 'Waiting Stories');
define('_MI_STORIES_BNAME5', 'Recent Comments Unread Marks');
define('_MI_STORIES_BNAME6', 'Top News II');

// Sub menus in main menu block
define('_MI_STORIES_SMNAME1', 'Submit News');
define('_MI_STORIES_SMNAME2', 'Archive');

// Names of admin menu items
define('_MI_STORIES_ADMENU2', 'Topics Manager');
define('_MI_STORIES_ADMENU3', 'Post/Edit News');

// Title of config items
define('_MI_STORYHOME', 'Select the number of news items to display on top page');
define('_MI_NOTIFYSUBMIT', 'Select yes to send notification message to webmaster upon new submission');
define('_MI_DISPLAYNAV', 'Select yes to display navigation box at the top of each news page');
define('_MI_ANONPOST', 'Allow anonymous users to post news articles?');
define('_MI_AUTOAPPROVE', 'Auto approve news stories without admin intervention?');

// Description of each config items
define('_MI_STORYHOMEDSC', '');
define('_MI_NOTIFYSUBMITDSC', '');
define('_MI_DISPLAYNAVDSC', '');
define('_MI_AUTOAPPROVEDSC', '');

// Text for notifications

define('_MI_STORIES_GLOBAL_NOTIFY', 'Global');
define('_MI_STORIES_GLOBAL_NOTIFYDSC', 'Global news notification options.');

define('_MI_STORIES_STORY_NOTIFY', 'Story');
define('_MI_STORIES_STORY_NOTIFYDSC', 'Notification options that apply to the current story.');

define('_MI_STORIES_GLOBAL_NEWCATEGORY_NOTIFY', 'New Topic');
define('_MI_STORIES_GLOBAL_NEWCATEGORY_NOTIFYCAP', 'Notify me when a new topic is created.');
define('_MI_STORIES_GLOBAL_NEWCATEGORY_NOTIFYDSC', 'Receive notification when a new topic is created.');
define('_MI_STORIES_GLOBAL_NEWCATEGORY_NOTIFYSBJ', '[{X_SITENAME}] {X_MODULE} auto-notify : New news topic');

define('_MI_STORIES_GLOBAL_STORYSUBMIT_NOTIFY', 'New Story Submitted');
define('_MI_STORIES_GLOBAL_STORYSUBMIT_NOTIFYCAP', 'Notify me when any new story is submitted (awaiting approval).');
define('_MI_STORIES_GLOBAL_STORYSUBMIT_NOTIFYDSC', 'Receive notification when any new story is submitted (awaiting approval).');
define('_MI_STORIES_GLOBAL_STORYSUBMIT_NOTIFYSBJ', '[{X_SITENAME}] {X_MODULE} auto-notify : New news story submitted');

define('_MI_STORIES_GLOBAL_NEWSTORY_NOTIFY', 'New Story');
define('_MI_STORIES_GLOBAL_NEWSTORY_NOTIFYCAP', 'Notify me when any new story is posted.');
define('_MI_STORIES_GLOBAL_NEWSTORY_NOTIFYDSC', 'Receive notification when any new story is posted.');
define('_MI_STORIES_GLOBAL_NEWSTORY_NOTIFYSBJ', '[{X_SITENAME}] {X_MODULE} auto-notify : New news story');

define('_MI_STORIES_STORY_APPROVE_NOTIFY', 'Story Approved');
define('_MI_STORIES_STORY_APPROVE_NOTIFYCAP', 'Notify me when this story is approved.');
define('_MI_STORIES_STORY_APPROVE_NOTIFYDSC', 'Receive notification when this story is approved.');
define('_MI_STORIES_STORY_APPROVE_NOTIFYSBJ', '[{X_SITENAME}] {X_MODULE} auto-notify : Story approved');
