<?php
// $Id: system_blocks.php,v 1.40 2003/09/26 14:26:06 okazu Exp $
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
// URL: http://www.myweb.ne.jp/, https://www.xoops.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //

function b_stories_waiting()
{
    $xoopsDB = XoopsDatabaseFactory::getDatabaseConnection();

    //	$moduleHandler = xoops_getHandler('module');

    $block = [];

    $result = $xoopsDB->query('SELECT COUNT(*) FROM ' . $xoopsDB->prefix('stories') . ' WHERE published=0');

    if ($result) {
        $block['adminlink'] = XOOPS_URL . '/modules/stories/admin/index.php?op=newarticle';

        [$block['pendingnum']] = $xoopsDB->fetchRow($result);

        $block['lang_linkname'] = _MB_STORIES_SUBMS;
    }

    return $block;
}

function b_recent_comments($options)
{
    global $xoopsUser, $xoopsDB;

    error_reporting(E_ALL);

    $block = [];

    require_once XOOPS_ROOT_PATH . '/include/comment_constants.php';

    $commentHandler = xoops_getHandler('comment');

    $criteria = new CriteriaCompo(new Criteria('com_status', XOOPS_COMMENT_ACTIVE));

    $criteria->setLimit((int)$options[0]);

    $criteria->setSort('com_created');

    $criteria->setOrder('DESC');

    $comments = &$commentHandler->getObjects($criteria, true);

    $memberHandler = xoops_getHandler('member');

    $moduleHandler = xoops_getHandler('module');

    $modules = $moduleHandler->getObjects(new Criteria('hascomments', 1), true);

    $comment_config = [];

    foreach (array_keys($comments) as $i) {
        $mid = $comments[$i]->getVar('com_modid');

        $com['module'] = '<a href="' . XOOPS_URL . '/modules/' . $modules[$mid]->getVar('dirname') . '/">' . $modules[$mid]->getVar('name') . '</a>';

        if (!isset($comment_config[$mid])) {
            $comment_config[$mid] = $modules[$mid]->getInfo('comments');
        }

        $com['id'] = $i;

        $com['title'] = '<a href="' . XOOPS_URL . '/modules/' . $modules[$mid]->getVar('dirname') . '/' . $comment_config[$mid]['pageName'] . '?' . $comment_config[$mid]['itemName'] . '=' . $comments[$i]->getVar('com_itemid') . '&amp;com_id=' . $i . '&amp;com_rootid=' . $comments[$i]->getVar(
            'com_rootid'
        ) . '&amp;com_mode=thread&amp;' . $comments[$i]->getVar('com_exparams') . '#comment' . $i . '">' . $comments[$i]->getVar('com_title') . '</a>';

        $com['icon'] = $comments[$i]->getVar('com_icon');

        $com['icon'] = ('' != $com['icon']) ? $com['icon'] : 'icon1.gif';

        $com['time'] = formatTimestamp($comments[$i]->getVar('com_created'), 'm');

        $created = $comments[$i]->getVar('com_created');

        $modified = $comments[$i]->getVar('com_modified');

        $comdate = $created;

        if ($modified > $created) {
            $comdate = $modified;
        }

        if ($xoopsUser) {
            $tstoryid = $comments[$i]->getVar('com_itemid');

            $uid = $xoopsUser->getVar('uid');

            $lvcid = sprintf('STORIES%08d%08d', $uid, $tstoryid);

            $tresult = $xoopsDB->query('SELECT lastviewed from ' . $xoopsDB->prefix('commentstracker') . " WHERE lvcid='" . $lvcid . "'");

            if ($tresult) {
                [$lastviewed] = $xoopsDB->fetchRow($tresult);
            } else {
                $lastviewed = 1;
            }

            if ($comdate > $lastviewed) {
                $com['title'] .= " <font color='red'>*</font>";
            }
        }

        if ($comments[$i]->getVar('com_uid') > 0) {
            $poster = $memberHandler->getUser($comments[$i]->getVar('com_uid'));

            if (is_object($poster)) {
                $com['poster'] = '<a href="' . XOOPS_URL . '/userinfo.php?uid=' . $comments[$i]->getVar('com_uid') . '">' . $poster->getVar('uname') . '</a>';
            } else {
                $com['poster'] = $GLOBALS['xoopsConfig']['anonymous'];
            }
        } else {
            $com['poster'] = $GLOBALS['xoopsConfig']['anonymous'];
        }

        $block['comments'][] = &$com;

        unset($com);
    }

    return $block;
}

function b_recent_comments_edit($options)
{
    $inputtag = "<input type='text' name='options[]' value='" . (int)$options[0] . "'>";

    $form = sprintf(_MB_STORIES_DISPLAYC, $inputtag);

    return $form;
}
