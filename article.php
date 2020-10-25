<?php
// $Id: article.php,v 1.10 2003/04/11 12:35:22 okazu Exp $
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

include '../../mainfile.php';

// error_reporting (E_ALL);

require_once 'class/class.newsstory.php';

foreach ($_POST as $k => $v) {
    ${$k} = $v;
}

$storyid = $_GET['storyid'] ?? 0;
$storyid = (int)$storyid;
if (empty($storyid)) {
    redirect_header('index.php', 2, _NW_NOSTORY);

    exit();
}

$newlv = (int)time();
if ($xoopsUser) {
    $uid = $xoopsUser->getVar('uid');

    $lvcid = sprintf('STORIES%08d%08d', $uid, $storyid);
} else {
    $lvcid = sprintf('STORIES%08d', $storyid);
}
if (isset($HTTP_COOKIE_VARS[$lvcid])) {
    $lastviewed = (int)$HTTP_COOKIE_VARS[$lvcid];
}
if ($xoopsUser) {
    $result = $xoopsDB->query('SELECT lastviewed from ' . $xoopsDB->prefix('commentstracker') . " WHERE lvcid='" . $lvcid . "'");

    $numrows = $xoopsDB->getRowsNum($result);

    if ($numrows) {
        [$lastviewed] = $xoopsDB->fetchRow($result);

        $uresult = $xoopsDB->queryF('UPDATE ' . $xoopsDB->prefix('commentstracker') . ' SET lastviewed=' . $newlv . " WHERE lvcid='" . $lvcid . "'");
    } else {
        $iresult = $xoopsDB->queryF('INSERT INTO ' . $xoopsDB->prefix('commentstracker') . " (lvcid, lastviewed) VALUES ('" . $lvcid . "'," . $newlv . ')');

        $lastviewed = 1;
    }

    setcookie($lvcid, (string)$newlv, time() + 2592000);
} else {
    setcookie($lvcid, (string)$newlv, time() + 2592000);

    $lastviewed = time() + 2592000;
}
$GLOBALS['xoopsOption']['template_main'] = 'stories_article.html';

require_once XOOPS_ROOT_PATH . '/header.php';
$myts = &storiesTextSanitizer::getInstance();
// set comment mode if not set

$article = new NewsStory($storyid);
if (0 == $article->published() || $article->published() > time()) {
    redirect_header('index.php', 2, _NW_NOSTORY);

    exit();
}
$storypage = isset($_GET['page']) ? (int)$_GET['page'] : 0;
// update counter only when viewing top page
if (empty($_GET['com_id']) && 0 == $storypage) {
    $article->updateCounter();
}
$story['id'] = $storyid;
$story['posttime'] = formatTimestamp($article->created());
$story['title'] = $article->textlink() . '&nbsp;:&nbsp;' . $article->linktitle();
$story['text'] = $article->hometext();
$bodytext = $article->bodytext();

if ('' != trim($bodytext)) {
    $articletext = explode('[pagebreak]', $bodytext);

    $story_pages = count($articletext);

    if ($story_pages > 1) {
        require_once XOOPS_ROOT_PATH . '/class/pagenav.php';

        $pagenav = new XoopsPageNav($story_pages, 1, $storypage, 'page', 'storyid=' . $storyid);

        $xoopsTpl->assign('pagenav', $pagenav->renderNav());

        //$xoopsTpl->assign('pagenav', $pagenav->renderImageNav());

        if (0 == $storypage) {
            $story['text'] .= '<br><br>' . $articletext[$storypage];
        } else {
            $story['text'] = $articletext[$storypage];
        }
    } else {
        $story['text'] .= '<br><br>' . $bodytext;
    }
}

$story['poster'] = $article->uname();
if ($story['poster']) {
    $story['posterid'] = $article->uid();

    $story['poster'] = '<a href="' . XOOPS_URL . '/userinfo.php?uid=' . $story['posterid'] . '">' . $story['poster'] . '</a>';
} else {
    $story['poster'] = '';

    $story['posterid'] = 0;

    $story['poster'] = $xoopsConfig['anonymous'];
}
$story['morelink'] = '';
$story['adminlink'] = '';
unset($isadmin);
if ($xoopsUser && $xoopsUser->isAdmin($xoopsModule->getVar('mid'))) {
    $isadmin = true;

    $story['adminlink'] = $article->adminlink();
}
$story['topicid'] = $article->topicid();
$story['imglink'] = '';
$story['align'] = '';
if ($article->topicdisplay()) {
    $story['imglink'] = $article->imglink();

    $story['align'] = $article->topicalign();
}
$story['hits'] = $article->counter();
$story['mail_link'] = 'mailto:?subject=' . sprintf(_NW_INTARTICLE, $xoopsConfig['sitename']) . '&amp;body=' . sprintf(_NW_INTARTFOUND, $xoopsConfig['sitename']) . ':  ' . XOOPS_URL . '/modules/' . $modversion['dirname'] . '/article.php?storyid=' . $article->storyid();
$story['author'] = $article->storiesauthor();
$story['storylink'] = $article->storylink();
$xoopsTpl->assign('story', $story);
$xoopsTpl->assign('lang_printerpage', _NW_PRINTERFRIENDLY);
$xoopsTpl->assign('lang_sendstory', _NW_SENDSTORY);
$xoopsTpl->assign('lang_on', _ON);
$xoopsTpl->assign('lang_postedby', _POSTEDBY);
$xoopsTpl->assign('lang_reads', _READS);
$xoopsTpl->assign('mail_link', 'mailto:?subject=' . sprintf(_NW_INTARTICLE, $xoopsConfig['sitename']) . '&amp;body=' . sprintf(_NW_INTARTFOUND, $xoopsConfig['sitename']) . ':  ' . XOOPS_URL . '/modules/' . $modversion['dirname'] . '/article.php?storyid=' . $article->storyid());
$xoopsTpl->assign('lang_source', _NW_SOURCE);

require __DIR__ . '/stories_comment_view.php';

$article->updateCommentsDate();

require XOOPS_ROOT_PATH . '/footer.php';
