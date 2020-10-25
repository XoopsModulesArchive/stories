<?php
// $Id: comment_view.php,v 1.15 2003/09/04 12:21:12 okazu Exp $
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
//  ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: https://www.xoops.org/ http://jp.xoops.org/  http://www.myweb.ne.jp/  //
// Project: The XOOPS Project (https://www.xoops.org/)                        //
// ------------------------------------------------------------------------- //

// error_reporting(E_ALL);

if (!defined('XOOPS_ROOT_PATH') || !is_object($xoopsModule)) {
    exit();
}
require_once XOOPS_ROOT_PATH . '/include/comment_constants.php';
if (XOOPS_COMMENT_APPROVENONE != $xoopsModuleConfig['com_rule']) {
    require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/comment.php';

    $comment_config = $xoopsModule->getInfo('comments');

    $com_itemid = ('' != trim($comment_config['itemName']) && isset($_GET[$comment_config['itemName']])) ? (int)$_GET[$comment_config['itemName']] : 0;

    if ($com_itemid > 0) {
        $com_mode = isset($_GET['com_mode']) ? htmlspecialchars(trim($_GET['com_mode']), ENT_QUOTES) : '';

        if ('' == $com_mode) {
            if (is_object($xoopsUser)) {
                $com_mode = $xoopsUser->getVar('umode');
            } else {
                $com_mode = $xoopsConfig['com_mode'];
            }
        }

        $xoopsTpl->assign('comment_mode', $com_mode);

        if (!isset($_GET['com_order'])) {
            if (is_object($xoopsUser)) {
                $com_order = $xoopsUser->getVar('uorder');
            } else {
                $com_order = $xoopsConfig['com_order'];
            }
        } else {
            $com_order = (int)$_GET['com_order'];
        }

        if (XOOPS_COMMENT_OLD1ST != $com_order) {
            $xoopsTpl->assign(['comment_order' => XOOPS_COMMENT_NEW1ST, 'order_other' => XOOPS_COMMENT_OLD1ST]);
        } else {
            $xoopsTpl->assign(['comment_order' => XOOPS_COMMENT_OLD1ST, 'order_other' => XOOPS_COMMENT_NEW1ST]);
        }

        // admins can view all comments and IPs, others can only view approved(active) comments

        if (is_object($xoopsUser) && $xoopsUser->isAdmin($xoopsModule->getVar('mid'))) {
            $admin_view = true;
        } else {
            $admin_view = false;
        }

        $com_id = isset($_GET['com_id']) ? (int)$_GET['com_id'] : 0;

        $com_rootid = isset($_GET['com_rootid']) ? (int)$_GET['com_rootid'] : 0;

        $com_dborder = (XOOPS_COMMENT_OLD1ST != $com_order) ? 'DESC' : 'ASC';

        $commentHandler = xoops_getHandler('comment');

        if ('flat' == $com_mode) {
            $comments = $commentHandler->getByItemId($xoopsModule->getVar('mid'), $com_itemid, $com_dborder);

            require_once XOOPS_ROOT_PATH . '/modules/stories/class/stories_commentrenderer.php';

            $renderer = &vlCommentRenderer::instance($xoopsTpl);

            $renderer->setComments($comments);

            $renderer->renderFlatView($admin_view, $lastviewed);
        } elseif ('thread' == $com_mode) {
            // RMV-FIX... added extraParam stuff here

            $comment_url = $comment_config['pageName'] . '?';

            if (isset($comment_config['extraParams']) && is_array($comment_config['extraParams'])) {
                $extra_params = '';

                foreach ($comment_config['extraParams'] as $extra_param) {
                    $extra_params .= isset(${$extra_param}) ? $extra_param . '=' . ${$extra_param} . '&amp;' : $extra_param . '=&amp;';
                }

                $comment_url .= $extra_params;
            }

            $xoopsTpl->assign('comment_url', $comment_url . $comment_config['itemName'] . '=' . $com_itemid . '&amp;com_mode=thread&amp;com_order=' . $com_order);

            if (!empty($com_id) && !empty($com_rootid) && ($com_id != $com_rootid)) {
                // Show specific thread tree

                $comments = $commentHandler->getThread($com_rootid, $com_id);

                if (false !== $comments) {
                    require_once XOOPS_ROOT_PATH . '/modules/stories/class/stories_commentrenderer.php';

                    $renderer = &vlCommentRenderer::instance($xoopsTpl);

                    $renderer->setComments($comments);

                    $renderer->renderThreadView($com_id, $admin_view, true, $lastviewed);
                }
            } else {
                // Show all threads

                $top_comments = $commentHandler->getTopComments($xoopsModule->getVar('mid'), $com_itemid, $com_dborder);

                $c_count = count($top_comments);

                if ($c_count > 0) {
                    for ($i = 0; $i < $c_count; $i++) {
                        $comments = $commentHandler->getThread($top_comments[$i]->getVar('com_rootid'), $top_comments[$i]->getVar('com_id'));

                        if (false !== $comments) {
                            require_once XOOPS_ROOT_PATH . '/modules/stories/class/stories_commentrenderer.php';

                            $renderer = &vlCommentRenderer::instance($xoopsTpl);

                            $renderer->setComments($comments);

                            $renderer->renderThreadView($top_comments[$i]->getVar('com_id'), $admin_view, true, $lastviewed);
                        }

                        unset($comments);
                    }
                }
            }
        } else {
            // Show all threads

            $top_comments = $commentHandler->getTopComments($xoopsModule->getVar('mid'), $com_itemid, $com_dborder);

            $c_count = count($top_comments);

            if ($c_count > 0) {
                for ($i = 0; $i < $c_count; $i++) {
                    $comments = $commentHandler->getThread($top_comments[$i]->getVar('com_rootid'), $top_comments[$i]->getVar('com_id'));

                    require_once XOOPS_ROOT_PATH . '/modules/stories/class/stories_commentrenderer.php';

                    $renderer = &vlCommentRenderer::instance($xoopsTpl);

                    $renderer->setComments($comments);

                    $renderer->renderNestView($top_comments[$i]->getVar('com_id'), $admin_view, $lastviewed);
                }
            }
        }

        // assign comment nav bar

        $navbar = '
<form method="get" action="' . $comment_config['pageName'] . '">
<table width="95%" class="outer" cellspacing="1">
  <tr>
    <td class="even" align="center"><select name="com_mode"><option value="flat"';

        if ('flat' == $com_mode) {
            $navbar .= ' selected="selected"';
        }

        $navbar .= '>' . _FLAT . '</option><option value="thread"';

        if ('thread' == $com_mode || '' == $com_mode) {
            $navbar .= ' selected="selected"';
        }

        $navbar .= '>' . _THREADED . '</option><option value="nest"';

        if ('nest' == $com_mode) {
            $navbar .= ' selected="selected"';
        }

        $navbar .= '>' . _NESTED . '</option></select> <select name="com_order"><option value="' . XOOPS_COMMENT_OLD1ST . '"';

        if (XOOPS_COMMENT_OLD1ST == $com_order) {
            $navbar .= ' selected="selected"';
        }

        $navbar .= '>' . _OLDESTFIRST . '</option><option value="' . XOOPS_COMMENT_NEW1ST . '"';

        if (XOOPS_COMMENT_NEW1ST == $com_order) {
            $navbar .= ' selected="selected"';
        }

        unset($postcomment_link);

        $navbar .= '>' . _NEWESTFIRST . '</option></select><input type="hidden" name="' . $comment_config['itemName'] . '" value="' . $com_itemid . '"> <input type="submit" value="' . _CM_REFRESH . '" class="formButton">';

        if (!empty($xoopsModuleConfig['com_anonpost']) || is_object($xoopsUser)) {
            $postcomment_link = 'comment_new.php?com_itemid=' . $com_itemid . '&amp;com_order=' . $com_order . '&amp;com_mode=' . $com_mode;

            $xoopsTpl->assign('anon_canpost', true);
        }

        $link_extra = '';

        if (isset($comment_config['extraParams']) && is_array($comment_config['extraParams'])) {
            foreach ($comment_config['extraParams'] as $extra_param) {
                if (isset(${$extra_param})) {
                    $link_extra .= '&amp;' . $extra_param . '=' . ${$extra_param};

                    $hidden_value = htmlspecialchars(${$extra_param}, ENT_QUOTES);

                    $navbar .= '<input type="hidden" name="' . $extra_param . '" value="' . $hidden_value . '">';
                }
            }
        }

        if (isset($postcomment_link)) {
            $navbar .= '&nbsp;<input type="button" onclick="self.location.href=\'' . $postcomment_link . '' . $link_extra . '\'" class="formButton" value="' . _CM_POSTCOMMENT . '">';
        }

        $navbar .= '
    </td>
  </tr>
</table>
</form>';

        $xoopsTpl->assign(
            [
                'commentsnav' => $navbar,
                'editcomment_link' => 'comment_edit.php?com_itemid=' . $com_itemid . '&amp;com_order=' . $com_order . '&amp;com_mode=' . $com_mode . '' . $link_extra,
                'deletecomment_link' => 'comment_delete.php?com_itemid=' . $com_itemid . '&amp;com_order=' . $com_order . '&amp;com_mode=' . $com_mode . '' . $link_extra,
                'replycomment_link' => 'comment_reply.php?com_itemid=' . $com_itemid . '&amp;com_order=' . $com_order . '&amp;com_mode=' . $com_mode . '' . $link_extra,
            ]
        );

        // assign some lang variables

        $xoopsTpl->assign(
            [
                'lang_from' => _CM_FROM,
                'lang_joined' => _CM_JOINED,
                'lang_posts' => _CM_POSTS,
                'lang_poster' => _CM_POSTER,
                'lang_thread' => _CM_THREAD,
                'lang_edit' => _EDIT,
                'lang_delete' => _DELETE,
                'lang_reply' => _REPLY,
                'lang_subject' => _CM_REPLIES,
                'lang_posted' => _CM_POSTED,
                'lang_updated' => _CM_UPDATED,
                'lang_notice' => _CM_NOTICE,
            ]
        );
    }
}
