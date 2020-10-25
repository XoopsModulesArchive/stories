<?php
// $Id: class.newsstory.php,v 1.10 2003/04/13 01:18:35 okazu Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <https://www.xoops.org>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------- //

require_once XOOPS_ROOT_PATH . '/class/commentrenderer.php';
require_once XOOPS_ROOT_PATH . '/modules/stories/class/stories_textsanitizer.php';

class vlCommentRenderer extends XoopsCommentRenderer
{
    /**
     * Constructor
     *
     * @param object  &$tpl
     * @param bool  $use_icons
     * @param bool  $do_iconcheck
     **/

    public function __construct(&$tpl, $use_icons = true, $do_iconcheck = false)
    {
        $this->_tpl = &$tpl;

        $this->_useIcons = $use_icons;

        $this->_doIconCheck = $do_iconcheck;

        $this->_memberHandler = xoops_getHandler('member');

        $this->_statusText = [
            XOOPS_COMMENT_PENDING => '<span style="text-decoration: none; font-weight: bold; color: #00ff00;">' . _CM_PENDING . '</span>',
            XOOPS_COMMENT_ACTIVE => '<span style="text-decoration: none; font-weight: bold; color: #ff0000;">' . _CM_ACTIVE . '</span>',
            XOOPS_COMMENT_HIDDEN => '<span style="text-decoration: none; font-weight: bold; color: #0000ff;">' . _CM_HIDDEN . '</span>',
        ];
    }

    /**
     * Access the only instance of this class
     *
     * @param object $tpl reference to a {@link Smarty} object
     * @param bool   $use_icons
     * @param bool   $do_iconcheck
     * @return \vlCommentRenderer
     */

    public static function &instance(&$tpl, $use_icons = true, $do_iconcheck = false)
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new self($tpl, $use_icons, $do_iconcheck);
        }

        return $instance;
    }

    /**
     * Render the comments in flat view
     *
     * @param bool $admin_view
     * @param mixed $lastviewed
     **/

    public function renderFlatView($admin_view = false, $lastviewed = 0)
    {
        $myts = &storiesTextSanitizer::getInstance();

        $count = count($this->_comments);

        for ($i = 0; $i < $count; $i++) {
            if (false !== $this->_useIcons) {
                $title = $this->_getTitleIcon($this->_comments[$i]->getVar('com_icon')) . '&nbsp;' . $this->_comments[$i]->getVar('com_title');
            } else {
                $title = $this->_comments[$i]->getVar('com_title');
            }

            $com_modified = $this->_comments[$i]->getVar('com_modified');

            if (!$com_modified) {
                $com_modified = $this->_comments[$i]->getVar('com_created');
            }

            if (isset($lastviewed) && ($lastviewed < $com_modified)) {
                $title = "<font color='red'>***</font> " . $title;
            }

            $poster = $this->_getPosterArray($this->_comments[$i]->getVar('com_uid'));

            if (false !== $admin_view) {
                $text = $this->_comments[$i]->getVar('com_text')
                        . '<div style="text-align:right; margin-top: 2px; margin-bottom: 0px; margin-right: 2px;">'
                        . _CM_STATUS
                        . ': '
                        . $this->_statusText[$this->_comments[$i]->getVar('com_status')]
                        . '<br>IP: <span style="font-weight: bold;">'
                        . $this->_comments[$i]->getVar('com_ip')
                        . '</span></div>';
            } else {
                // hide comments that are not active

                if (XOOPS_COMMENT_ACTIVE != $this->_comments[$i]->getVar('com_status')) {
                    continue;
                }  

                $text = $this->_comments[$i]->getVar('com_text');
            }

            if ($poster['attachsig']) {
                $text .= '<p><br>----------------<br>' . $myts->displayTarea($poster['signature'], 1, 1, 1) . '</p>';
            }

            $text = $myts->xoopsCodeDecode($text);

            $this->_tpl->append(
                'comments',
                [
                    'id' => $this->_comments[$i]->getVar('com_id'),
                    'title' => $title,
                    'text' => $text,
                    'date_posted' => formatTimestamp($this->_comments[$i]->getVar('com_created'), 'm'),
                    'date_modified' => formatTimestamp($this->_comments[$i]->getVar('com_modified'), 'm'),
                    'poster' => $poster,
                ]
            );
        }
    }

    /**
     * Render the comments in thread view
     *
     * This method calls itself recursively
     *
     * @param int $comment_id Should be "0" when called by client
     * @param bool $admin_view
     * @param bool $show_nav
     * @param mixed $lastviewed
     **/

    public function renderThreadView($comment_id = 0, $admin_view = false, $show_nav = true, $lastviewed = 0)
    {
        require_once XOOPS_ROOT_PATH . '/class/tree.php';

        $myts = &storiesTextSanitizer::getInstance();

        // construct comment tree

        $xot = new XoopsObjectTree($this->_comments, 'com_id', 'com_pid', 'com_rootid');

        $tree = &$xot->getTree();

        if (false !== $this->_useIcons) {
            $title = $this->_getTitleIcon($tree[$comment_id]['obj']->getVar('com_icon')) . '&nbsp;' . $tree[$comment_id]['obj']->getVar('com_title');
        } else {
            $title = $tree[$comment_id]['obj']->getVar('com_title');
        }

        $com_modified = $tree[$comment_id]['obj']->getVar('com_modified');

        $com_created = $tree[$comment_id]['obj']->getVar('com_created');

        if (!$com_modified) {
            $com_modified = $com_created;
        }

        if (isset($lastviewed) && ($lastviewed < $com_modified)) {
            $title = "<font color='red'>***</font> " . $title;
        }

        if (false !== $show_nav && 0 != $tree[$comment_id]['obj']->getVar('com_pid')) {
            $this->_tpl->assign('lang_top', _CM_TOP);

            $this->_tpl->assign('lang_parent', _CM_PARENT);

            $this->_tpl->assign('show_threadnav', true);
        } else {
            $this->_tpl->assign('show_threadnav', false);
        }

        if (false !== $admin_view) {
            // admins can see all

            $text = $tree[$comment_id]['obj']->getVar('com_text')
                    . '<div style="text-align:right; margin-top: 2px; margin-bottom: 0px; margin-right: 2px;">'
                    . _CM_STATUS
                    . ': '
                    . $this->_statusText[$tree[$comment_id]['obj']->getVar('com_status')]
                    . '<br>IP: <span style="font-weight: bold;">'
                    . $tree[$comment_id]['obj']->getVar('com_ip')
                    . '</span></div>';
        } else {
            // hide comments that are not active

            if (XOOPS_COMMENT_ACTIVE != $tree[$comment_id]['obj']->getVar('com_status')) {
                // if there are any child comments, display them as root comments

                if (isset($tree[$comment_id]['child']) && !empty($tree[$comment_id]['child'])) {
                    foreach ($tree[$comment_id]['child'] as $child_id) {
                        $this->renderThreadView($child_id, $admin_view, $show_nav, $lastviewed);
                    }
                }

                return;
            }  

            $text = $tree[$comment_id]['obj']->getVar('com_text');
        }

        $poster = $this->_getPosterArray($tree[$comment_id]['obj']->getVar('com_uid'));

        if ($poster['attachsig']) {
            $text .= '<p><br>----------------<br>' . $myts->displayTarea($poster['signature'], 1, 1, 1) . '</p>';
        }

        $text = $myts->xoopsCodeDecode($text);

        $replies = [];

        $this->_renderThreadReplies($tree, $comment_id, $replies, '&nbsp;&nbsp;', $admin_view, 0, '', $lastviewed);

        $show_replies = (count($replies) > 0) ? true : false;

        $this->_tpl->append(
            'comments',
            [
                'pid' => $tree[$comment_id]['obj']->getVar('com_pid'),
                'id' => $tree[$comment_id]['obj']->getVar('com_id'),
                'itemid' => $tree[$comment_id]['obj']->getVar('com_itemid'),
                'rootid' => $tree[$comment_id]['obj']->getVar('com_rootid'),
                'title' => $title,
                'text' => $text,
                'date_posted' => formatTimestamp($tree[$comment_id]['obj']->getVar('com_created'), 'm'),
                'date_modified' => formatTimestamp($tree[$comment_id]['obj']->getVar('com_modified'), 'm'),
                'poster' => $this->_getPosterArray($tree[$comment_id]['obj']->getVar('com_uid')),
                'replies' => $replies,
                'show_replies' => $show_replies,
            ]
        );
    }

    /**
     * Render replies to a thread
     *
     * @param array   &$thread
     * @param int      $key
     * @param array    $replies
     * @param string   $prefix
     * @param bool     $admin_view
     * @param int  $depth
     * @param string   $current_prefix
     * @param mixed $lastviewed
     *
     **/

    public function _renderThreadReplies(&$thread, $key, &$replies, $prefix, $admin_view, $depth = 0, $current_prefix = '', $lastviewed = 0)
    {
        if ($depth > 0) {
            if (false !== $this->_useIcons) {
                $title = $this->_getTitleIcon($thread[$key]['obj']->getVar('com_icon')) . '&nbsp;' . $thread[$key]['obj']->getVar('com_title');
            } else {
                $title = $thread[$key]['obj']->getVar('com_title');
            }

            $com_modified = $thread[$key]['obj']->getVar('com_modified');

            if (!$com_modified) {
                $com_modified = $thread[$key]['obj']->getVar('com_created');
            }

            if (isset($lastviewed) && ($lastviewed < $com_modified)) {
                $title = "<font color='red'>***</font> " . $title;
            }

            $title = (false !== $admin_view) ? $title . ' ' . $this->_statusText[$thread[$key]['obj']->getVar('com_status')] : $title;

            $replies[] = [
                'id' => $key,
                'prefix' => $current_prefix,
                'date_posted' => formatTimestamp($thread[$key]['obj']->getVar('com_created'), 'm'),
                'title' => $title,
                'root_id' => $thread[$key]['obj']->getVar('com_rootid'),
                'status' => $this->_statusText[$thread[$key]['obj']->getVar('com_status')],
                'poster' => $this->_getPosterName($thread[$key]['obj']->getVar('com_uid')),
            ];

            $current_prefix .= $prefix;
        }

        if (isset($thread[$key]['child']) && !empty($thread[$key]['child'])) {
            $depth++;

            foreach ($thread[$key]['child'] as $childkey) {
                if (!$admin_view && XOOPS_COMMENT_ACTIVE != $thread[$childkey]['obj']->getVar('com_status')) {
                    // skip this comment if it is not active and continue on processing its child comments instead

                    if (isset($thread[$childkey]['child']) && !empty($thread[$childkey]['child'])) {
                        foreach ($thread[$childkey]['child'] as $childchildkey) {
                            $this->_renderThreadReplies($thread, $childchildkey, $replies, $prefix, $admin_view, $depth, '', $lastviewed);
                        }
                    }
                } else {
                    $this->_renderThreadReplies($thread, $childkey, $replies, $prefix, $admin_view, $depth, $current_prefix, $lastviewed);
                }
            }
        }
    }

    public function renderNestView($comment_id = 0, $admin_view = false, $lastviewed = 0)
    {
        require_once XOOPS_ROOT_PATH . '/class/tree.php';

        $myts = &storiesTextSanitizer::getInstance();

        $xot = new XoopsObjectTree($this->_comments, 'com_id', 'com_pid', 'com_rootid');

        $tree = &$xot->getTree();

        if (false !== $this->_useIcons) {
            $title = $this->_getTitleIcon($tree[$comment_id]['obj']->getVar('com_icon')) . '&nbsp;' . $tree[$comment_id]['obj']->getVar('com_title');
        } else {
            $title = $tree[$comment_id]['obj']->getVar('com_title');
        }

        $com_modified = $tree[$comment_id]['obj']->getVar('com_modified');

        if (!$com_modified) {
            $com_modified = $tree[$comment_id]['obj']->getVar('com_created');
        }

        if (isset($lastviewed) && ($lastviewed < $com_modified)) {
            $title = "<font color='red'>***</font> " . $title;
        }

        if (false !== $admin_view) {
            $text = $tree[$comment_id]['obj']->getVar('com_text')
                    . '<div style="text-align:right; margin-top: 2px; margin-bottom: 0px; margin-right: 2px;">'
                    . _CM_STATUS
                    . ': '
                    . $this->_statusText[$tree[$comment_id]['obj']->getVar('com_status')]
                    . '<br>IP: <span style="font-weight: bold;">'
                    . $tree[$comment_id]['obj']->getVar('com_ip')
                    . '</span></div>';
        } else {
            // skip this comment if it is not active and continue on processing its child comments instead

            if (XOOPS_COMMENT_ACTIVE != $tree[$comment_id]['obj']->getVar('com_status')) {
                // if there are any child comments, display them as root comments

                if (isset($tree[$comment_id]['child']) && !empty($tree[$comment_id]['child'])) {
                    foreach ($tree[$comment_id]['child'] as $child_id) {
                        $this->renderNestView($child_id, $admin_view, $lastviewed);
                    }
                }

                return;
            }  

            $text = $tree[$comment_id]['obj']->getVar('com_text');
        }

        $poster = $this->_getPosterArray($tree[$comment_id]['obj']->getVar('com_uid'));

        if ($poster['attachsig']) {
            $text .= '<p><br>----------------<br>' . $myts->displayTarea($poster['signature'], 1, 1, 1) . '</p>';
        }

        $text = $myts->xoopsCodeDecode($text);

        $replies = [];

        $this->_renderNestReplies($tree, $comment_id, $replies, 25, $admin_view, 0, $lastviewed);

        $this->_tpl->append(
            'comments',
            [
                'pid' => $tree[$comment_id]['obj']->getVar('com_pid'),
                'id' => $tree[$comment_id]['obj']->getVar('com_id'),
                'itemid' => $tree[$comment_id]['obj']->getVar('com_itemid'),
                'rootid' => $tree[$comment_id]['obj']->getVar('com_rootid'),
                'title' => $title,
                'text' => $text,
                'date_posted' => formatTimestamp($tree[$comment_id]['obj']->getVar('com_created'), 'm'),
                'date_modified' => formatTimestamp($tree[$comment_id]['obj']->getVar('com_modified'), 'm'),
                'poster' => $this->_getPosterArray($tree[$comment_id]['obj']->getVar('com_uid')),
                'replies' => $replies,
            ]
        );
    }

    /**
     * Render replies in nested view
     *
     * @param array   $thread
     * @param int     $key
     * @param array   $replies
     * @param string  $prefix
     * @param bool    $admin_view
     * @param int $depth
     * @param mixed $lastviewed
     *
     **/

    public function _renderNestReplies(&$thread, $key, &$replies, $prefix, $admin_view, $depth = 0, $lastviewed = 0)
    {
        if ($depth > 0) {
            if (false !== $this->_useIcons) {
                $title = $this->_getTitleIcon($thread[$key]['obj']->getVar('com_icon')) . '&nbsp;' . $thread[$key]['obj']->getVar('com_title');
            } else {
                $title = $thread[$key]['obj']->getVar('com_title');
            }

            $com_modified = $thread[$key]['obj']->getVar('com_modified');

            if (!$com_modified) {
                $com_modified = $thread[$key]['obj']->getVar('com_created');
            }

            if (isset($lastviewed) && ($lastviewed < $com_modified)) {
                $title = "<font color='red'>***</font> " . $title;
            }

            $text = (false !== $admin_view) ? $thread[$key]['obj']->getVar('com_text')
                                               . '<div style="text-align:right; margin-top: 2px; margin-right: 2px;">'
                                               . _CM_STATUS
                                               . ': '
                                               . $this->_statusText[$thread[$key]['obj']->getVar('com_status')]
                                               . '<br>IP: <span style="font-weight: bold;">'
                                               . $thread[$key]['obj']->getVar('com_ip')
                                               . '</span></div>' : $thread[$key]['obj']->getVar('com_text');

            $poster = $this->_getPosterArray($thread[$key]['obj']->getVar('com_uid'));

            if ($poster['attachsig']) {
                $myts = &storiesTextSanitizer::getInstance();

                $text .= '<p><br>----------------<br>' . $myts->displayTarea($poster['signature'], 1, 1, 1) . '</p>';
            }

            $replies[] = [
                'id' => $key,
                'prefix' => $prefix,
                'pid' => $thread[$key]['obj']->getVar('com_pid'),
                'itemid' => $thread[$key]['obj']->getVar('com_itemid'),
                'rootid' => $thread[$key]['obj']->getVar('com_rootid'),
                'title' => $title,
                'text' => $text,
                'date_posted' => formatTimestamp($thread[$key]['obj']->getVar('com_created'), 'm'),
                'date_modified' => formatTimestamp($thread[$key]['obj']->getVar('com_modified'), 'm'),
                'poster' => $this->_getPosterArray($thread[$key]['obj']->getVar('com_uid')),
            ];

            $prefix += 25;
        }

        if (isset($thread[$key]['child']) && !empty($thread[$key]['child'])) {
            $depth++;

            foreach ($thread[$key]['child'] as $childkey) {
                if (!$admin_view && XOOPS_COMMENT_ACTIVE != $thread[$childkey]['obj']->getVar('com_status')) {
                    // skip this comment if it is not active and continue on processing its child comments instead

                    if (isset($thread[$childkey]['child']) && !empty($thread[$childkey]['child'])) {
                        foreach ($thread[$childkey]['child'] as $childchildkey) {
                            $this->_renderNestReplies($thread, $childchildkey, $replies, $prefix, $admin_view, $depth, $lastviewed);
                        }
                    }
                } else {
                    $this->_renderNestReplies($thread, $childkey, $replies, $prefix, $admin_view, $depth, $lastviewed);
                }
            }
        }
    }

    /**
     * Get an array with info about the poster
     *
     * @param int $poster_id
     * @return  array
     *
     **/

    public function _getPosterArray($poster_id)
    {
        $poster['id'] = (int)$poster_id;

        if ($poster['id'] > 0) {
            $com_poster = $this->_memberHandler->getUser($poster['id']);

            if (is_object($com_poster)) {
                $poster['uname'] = '<a href="' . XOOPS_URL . '/userinfo.php?uid=' . $poster['id'] . '">' . $com_poster->getVar('uname') . '</a>';

                $poster_rank = $com_poster->rank();

                $poster['rank_image'] = ('' != $poster_rank['image']) ? $poster_rank['image'] : 'blank.gif';

                $poster['rank_title'] = $poster_rank['title'];

                $poster['avatar'] = $com_poster->getVar('user_avatar');

                $poster['regdate'] = formatTimestamp($com_poster->getVar('user_regdate'), 's');

                $poster['from'] = $com_poster->getVar('user_from');

                $poster['postnum'] = $com_poster->getVar('posts');

                $poster['status'] = $com_poster->isOnline() ? _CM_ONLINE : '';

                $poster['attachsig'] = $com_poster->getVar('attachsig');

                $poster['signature'] = $com_poster->getVar('user_sig');

                return $poster;
            }
        }

        $poster['id'] = 0; // to cope with deleted user accounts

        $poster['uname'] = $GLOBALS['xoopsConfig']['anonymous'];

        $poster['rank_title'] = '';

        $poster['avatar'] = 'blank.gif';

        $poster['regdate'] = '';

        $poster['from'] = '';

        $poster['postnum'] = 0;

        $poster['status'] = '';

        $poster['attachsig'] = false;

        $poster['signature'] = '';

        return $poster;
    }
}



