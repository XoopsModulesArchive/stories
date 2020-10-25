<?php
// $Id: index.php,v 1.12 2003/03/17 05:40:40 okazu Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <https://www.xoops.org>                             //
// ------------------------------------------------------------------------- //
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
require dirname(__DIR__, 2) . '/mainfile.php';

// error_reporting (E_ALL);

$GLOBALS['xoopsOption']['template_main'] = 'stories_index.html';
require XOOPS_ROOT_PATH . '/header.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->dirname() . '/class/class.newsstory.php';

if (isset($_GET['storytopic'])) {
    $xoopsOption['storytopic'] = (int)$_GET['storytopic'];
} else {
    $xoopsOption['storytopic'] = 0;
}
if (isset($_GET['author'])) {
    $xoopsOption['author'] = $_GET['author'];
} else {
    $xoopsOption['author'] = '';
}
if (isset($_GET['storynum'])) {
    $xoopsOption['storynum'] = (int)$_GET['storynum'];

    if ($xoopsOption['storynum'] > 30) {
        $xoopsOption['storynum'] = $xoopsModuleConfig['storyhome'];
    }
} else {
    $xoopsOption['storynum'] = $xoopsModuleConfig['storyhome'];
}

if (isset($_GET['start'])) {
    $start = (int)$_GET['start'];
} else {
    $start = 0;
}

$sarray = NewsStory::getPublished($xoopsOption['storynum'], $start, $xoopsOption['storytopic'], $xoopsOption['author']);

$scount = count($sarray);

/*
$db = XoopsDatabaseFactory::getDatabaseConnection();
$recent_comments = array();
$result = $db->query("SELECT com_id, com_itemid FROM ".$db->prefix("xoopscomments")." ORDER BY com_created DESC LIMIT 20");
$i = 0;
while ( $myrow = $db->fetchArray($result) ) {
    if (! $testcookie) {
        $recent_comments[$myrow['com_id']] = time()+2592000;
    } else {
        if (isset($HTTP_COOKIE_VARS[strval($myrow['com_itemid'])])) {
            $recent_comments[$myrow['com_id']] = intval($HTTP_COOKIE_VARS[strval($myrow['com_itemid'])]);
        } else {
            $recent_comments[$myrow['com_id']] = 1;
        }
    }
    $i++;
}
*/

$lvarray = [];
for ($i = 0; $i < $scount; $i++) {
    $lastviewed = (int)(time() + 2592000);

    if ($sarray[$i]->comments()) {
        $storyid = $sarray[$i]->storyid();

        if ($xoopsUser) {
            $uid = $xoopsUser->getVar('uid');

            $lvcid = sprintf('STORIES%08d%08d', $uid, $storyid);
        } else {
            $lvcid = sprintf('STORIES%08d', $storyid);
        }

        if (isset($HTTP_COOKIE_VARS[$lvcid])) {
            $lvarray[$i] = (int)$HTTP_COOKIE_VARS[$lvcid];
        } else {
            if ($xoopsUser) {
                $lvarray[$i] = 1;

                $result = $xoopsDB->query('SELECT lastviewed from ' . $xoopsDB->prefix('commentstracker') . " WHERE lvcid='" . $lvcid . "'");

                if ($result) {
                    $numrows = $xoopsDB->getRowsNum($result);

                    if ($numrows) {
                        [$lvarray[$i]] = $xoopsDB->fetchRow($result);
                    } else {
                        $lvarray[$i] = 1;
                    }
                } else {
                    $lvarray[$i] = 1;
                }
            } else {
                $lvarray[$i] = $lastviewed;
            }
        }
    } else {
        $lvarray[$i] = $lastviewed;
    }
}

if (1 == $xoopsModuleConfig['displaynav']) {
    $xoopsTpl->assign('displaynav', true);

    $xt = new XoopsTopic($xoopsDB->prefix('topics'));

    ob_start();

    $xt->makeTopicSelBox(1, $xoopsOption['storytopic'], 'storytopic');

    $topic_select = ob_get_contents();

    ob_end_clean();

    $xoopsTpl->assign('topic_select', $topic_select);

    $storynum_options = '';

    for ($i = 5; $i <= 30; $i += 5) {
        $sel = '';

        if ($i == $xoopsOption['storynum']) {
            $sel = ' selected="selected"';
        }

        $storynum_options .= '<option value="' . $i . '"' . $sel . '>' . $i . '</option>';
    }

    $xoopsTpl->assign('storynum_options', $storynum_options);
} else {
    $xoopsTpl->assign('displaynav', false);
}

for ($i = 0; $i < $scount; $i++) {
    $story = [];

    $story['id'] = $sarray[$i]->storyid();

    $story['poster'] = $sarray[$i]->uname();

    if (false !== $story['poster']) {
        $story['poster'] = "<a href='" . XOOPS_URL . '/userinfo.php?uid=' . $sarray[$i]->uid() . "'>" . $story['poster'] . '</a>';
    } else {
        $story['poster'] = $xoopsConfig['anonymous'];
    }

    $story['posttime'] = formatTimestamp($sarray[$i]->published());

    $story['text'] = $sarray[$i]->hometext();

    $introcount = mb_strlen($story['text']);

    $fullcount = mb_strlen($sarray[$i]->bodytext());

    $totalcount = $introcount + $fullcount;

    $morelink = '';

    if ($fullcount > 1) {
        $morelink .= '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->dirname() . '/article.php?storyid=' . $sarray[$i]->storyid() . '';

        $morelink .= '">' . _NW_READMORE . '</a> | ';

        $morelink .= sprintf(_NW_BYTESMORE, $totalcount);

        $morelink .= ' | ';
    }

    $ccount = $sarray[$i]->comments();

    $morelink .= '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->dirname() . '/article.php?storyid=' . $sarray[$i]->storyid() . '';

    $morelink2 = '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->dirname() . '/article.php?storyid=' . $sarray[$i]->storyid() . '';

    if (0 == $ccount) {
        $morelink .= '">' . _NW_COMMENTS . '</a>';
    } else {
        if ($fullcount < 1) {
            if (1 == $ccount) {
                $morelink .= '">' . _NW_READMORE . '</a> | ' . $morelink2 . '">' . _NW_ONECOMMENT . '</a>';
            } else {
                $morelink .= '">' . _NW_READMORE . '</a> | ' . $morelink2 . '">';

                $morelink .= sprintf(_NW_NUMCOMMENTS, $ccount);

                $morelink .= '</a>';
            }

            $sarray[$i]->updateCommentsDate();

            $comdate = $sarray[$i]->comdate();

            if ($comdate > $lvarray[$i]) {
                $morelink .= '<font color="red"> *** </font>';
            }
        } else {
            if (1 == $ccount) {
                $morelink .= '">' . _NW_ONECOMMENT . '</a>';
            } else {
                $morelink .= '">';

                $morelink .= sprintf(_NW_NUMCOMMENTS, $ccount);

                $morelink .= '</a>';
            }

            $comdate = $sarray[$i]->comdate();

            if ($comdate > $lvarray[$i]) {
                $morelink .= '<font color="red"> *** </font>';
            }
        }
    }

    $story['morelink'] = $morelink;

    $story['adminlink'] = '';

    if ($xoopsUser && $xoopsUser->isAdmin($xoopsModule->mid())) {
        $story['adminlink'] = $sarray[$i]->adminlink();
    }

    $story['mail_link'] = 'mailto:?subject=' . sprintf(_NW_INTARTICLE, $xoopsConfig['sitename']) . '&amp;body=' . sprintf(_NW_INTARTFOUND, $xoopsConfig['sitename']) . ':  ' . XOOPS_URL . '/modules/' . $xoopsModule->dirname() . '/article.php?storyid=' . $sarray[$i]->storyid();

    $story['imglink'] = '';

    $story['align'] = '';

    if ($sarray[$i]->topicdisplay()) {
        $story['imglink'] = $sarray[$i]->imglink();

        $story['align'] = $sarray[$i]->topicalign();
    }

    $story['title'] = $sarray[$i]->textlink() . '&nbsp;|&nbsp;' . $sarray[$i]->linktitle();

    /*
        if ($lvarray[$i] == 1) {
            $story['title'] = $sarray[$i]->textlink().'&nbsp;|&nbsp;'.$sarray[$i]->linktitle()."<font color='yellow'>&nbsp;&nbsp;&nbsp;(unread)</font>";
        } else {
            $story['title'] = $sarray[$i]->textlink().'&nbsp;|&nbsp;'.$sarray[$i]->linktitle();
        }
        if ($lvarray[$i] == 1) {
            $story['title'] = $sarray[$i]->textlink().'&nbsp;<font color="cyan"><blink>|</blink></font>&nbsp;'."<a href='".XOOPS_URL."/modules/".$xoopsModule->dirname()."/article.php?storyid=".$sarray[$i]->storyid()."'>".$sarray[$i]->title()."</a>";
        } else {
            $story['title'] = $sarray[$i]->textlink().'&nbsp;|&nbsp;'."<a href='".XOOPS_URL."/modules/".$xoopsModule->dirname()."/article.php?storyid=".$sarray[$i]->storyid()."'>".$sarray[$i]->title()."</a>";
        }
    */

    $story['hits'] = $sarray[$i]->counter();

    // The line below can be used to display a Permanent Link image

    // $story['title'] .= "&nbsp;&nbsp;<a href='".XOOPS_URL."/modules/".$xoopsModule->dirname()."/article.php?storyid=".$sarray[$i]->storyid()."'><img src='".XOOPS_URL."/modules/".$xoopsModule->dirname()."/images/x.gif' alt='Permanent Link'></a>";

    $xoopsTpl->append('stories', $story);

    unset($story);
}
$totalcount = NewsStory::countPublished($xoopsOption['storytopic'], $xoopsOption['author']);
if ($totalcount > $scount) {
    require_once XOOPS_ROOT_PATH . '/class/pagenav.php';

    $pagenav = new XoopsPageNav($totalcount, $xoopsOption['storynum'], $start, 'start', 'storytopic=' . $xoopsOption['storytopic']);

    $xoopsTpl->assign('pagenav', $pagenav->renderNav());

//$xoopsTpl->assign('pagenav', $pagenav->renderImageNav());
} else {
    $xoopsTpl->assign('pagenav', '');
}
$xoopsTpl->assign('lang_go', _GO);
$xoopsTpl->assign('lang_on', _ON);
$xoopsTpl->assign('lang_printerpage', _NW_PRINTERFRIENDLY);
$xoopsTpl->assign('lang_sendstory', _NW_SENDSTORY);
$xoopsTpl->assign('lang_postedby', _POSTEDBY);
$xoopsTpl->assign('lang_reads', _READS);
$xoopsTpl->assign('lang_source', _NW_SOURCE);

require_once XOOPS_ROOT_PATH . '/footer.php';
