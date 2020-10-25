<?php
// $Id: news_top.php,v 1.8 2003/03/28 04:04:51 w4z004 Exp $
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

function b_stories_top_show($options)
{
    global $xoopsDB, $xoopsUser;

    $myts = MyTextSanitizer::getInstance();

    $block = [];

    $toptype = $options[0];

    $storyorder = $options[1];

    $storycount = $options[2];

    $titlelength = $options[3];

    $limitreach = $options[4];

    $reachback = $options[5];

    if (1 == $storyorder) {
        $sqlorder = ' DESC ';
    } else {
        $sqlorder = '';
    }

    switch ($toptype) {
        case 'counter':
            $sql = 'SELECT MAX(tocid) from ' . $xoopsDB->prefix('vlstories') . ', ' . $xoopsDB->prefix('stories') . ' WHERE ' . $xoopsDB->prefix('stories') . '.storyid=' . $xoopsDB->prefix('vlstories') . '.storyid AND published > 0 AND comments > 0';
            $result = $xoopsDB->query($sql, 1, 0);
            $num_rows = $xoopsDB->getRowsNum($result);
            if ($num_rows) {
                [$maxtoc] = $xoopsDB->fetchRow($result);
            }
            if ($limitreach) {
                $mintoc = $maxtoc - $reachback;

            /*
                            $sql = "select tocid from ".$xoopsDB->prefix("vlstories").", ".$xoopsDB->prefix("stories")." WHERE ".$xoopsDB->prefix("stories").".storyid=".$xoopsDB->prefix("vlstories").".storyid AND published > 0 AND comments > 0 ORDER BY tocid ";
                            $sql .= $sqlorder;
                            $sql .= " LIMIT " . $reachback;
                            $result = $xoopsDB->query($sql);
                            for($i = 0; $i < $storycount; $i++) {
                                list($mintoc) = $xoopsDB->fetchRow($result);
                            }
            */
            } else {
                $mintoc = 0;
            }
            $sql = 'select '
                      . $xoopsDB->prefix('stories')
                      . '.storyid, title, published, expired, counter, comdate, comments FROM '
                      . $xoopsDB->prefix('vlstories')
                      . ', '
                      . $xoopsDB->prefix('stories')
                      . ' WHERE '
                      . $xoopsDB->prefix('stories')
                      . '.storyid='
                      . $xoopsDB->prefix('vlstories')
                      . '.storyid AND tocid <= '
                      . $maxtoc
                      . ' AND tocid >= '
                      . $mintoc
                      . ' AND published > 0 ORDER BY counter ';
            $sql .= $sqlorder;
            $result = $xoopsDB->query($sql);
            break;
        case 'published':
            $sql = 'select '
                   . $xoopsDB->prefix('stories')
                   . '.storyid, title, published, expired, counter, comdate, comments FROM '
                   . $xoopsDB->prefix('vlstories')
                   . ', '
                   . $xoopsDB->prefix('stories')
                   . ' WHERE '
                   . $xoopsDB->prefix('stories')
                   . '.storyid='
                   . $xoopsDB->prefix('vlstories')
                   . '.storyid AND published > 0 ORDER BY published DESC';
            // Reachback and ASC/DESC order mean nothing when ordering by publish date
            $result = $xoopsDB->query($sql);
            break;
        case 'comments':
        default:
            $sql = 'SELECT MAX(tocid) from ' . $xoopsDB->prefix('vlstories') . ', ' . $xoopsDB->prefix('stories') . ' WHERE ' . $xoopsDB->prefix('stories') . '.storyid=' . $xoopsDB->prefix('vlstories') . '.storyid AND published > 0 AND comments > 0';
            $result = $xoopsDB->query($sql, 1, 0);
            $num_rows = $xoopsDB->getRowsNum($result);
            if ($num_rows) {
                [$maxtoc] = $xoopsDB->fetchRow($result);
            }
            if ($limitreach) {
                $mintoc = $maxtoc - $reachback;

            /*
                            $sql = "select tocid from ".$xoopsDB->prefix("vlstories").", ".$xoopsDB->prefix("stories")." WHERE ".$xoopsDB->prefix("stories").".storyid=".$xoopsDB->prefix("vlstories").".storyid AND published > 0 AND comments > 0 ORDER BY tocid ";
                            $sql .= $sqlorder;
                            $sql .= " LIMIT " . $reachback;
                            $result = $xoopsDB->query($sql);
                            for($i = 0; $i < $storycount; $i++) {
                                list($mintoc) = $xoopsDB->fetchRow($result);
                            }
            */
            } else {
                $mintoc = 0;
            }
            $sql = 'SELECT ' . $xoopsDB->prefix('stories') . '.storyid, title, published, expired, counter, comdate, comments FROM ' . $xoopsDB->prefix('stories') . ', ' . $xoopsDB->prefix('vlstories') . ' WHERE published > 0 AND ' . $xoopsDB->prefix('stories') . '.storyid=' . $xoopsDB->prefix(
                'vlstories'
            ) . '.storyid AND tocid <= ' . $maxtoc . ' AND tocid >= ' . $mintoc . ' AND comments > 0 ORDER BY comments ';
            $sql .= $sqlorder . ', tocid ';
            $sql .= $sqlorder;
            $result = $xoopsDB->query($sql);
            break;
    }

    $news = [];

    for ($i = 0; $i < $storycount; $i++) {
        $myrow = $xoopsDB->fetchArray($result);

        $title = htmlspecialchars($myrow['title'], ENT_QUOTES | ENT_HTML5);

        if (!XOOPS_USE_MULTIBYTES) {
            if (mb_strlen($myrow['title']) >= $options[2]) {
                $title = htmlspecialchars(mb_substr($myrow['title'], 0, ($options[2] - 1)), ENT_QUOTES | ENT_HTML5) . '...';
            }
        }

        $news['title'] = $title;

        $news['id'] = $myrow['storyid'];

        $news['unread'] = '';

        if ($xoopsUser) {
            $tstoryid = $myrow['storyid'];

            $comdate = $myrow['comdate'];

            $uid = $xoopsUser->getVar('uid');

            $lvcid = sprintf('STORIES%08d%08d', $uid, $tstoryid);

            $tresult = $xoopsDB->query('SELECT lastviewed from ' . $xoopsDB->prefix('commentstracker') . " WHERE lvcid='" . $lvcid . "'");

            if ($tresult) {
                [$lastviewed] = $xoopsDB->fetchRow($tresult);
            } else {
                $lastviewed = 1;
            }

            if ($comdate > $lastviewed) {
                $news['unread'] = "&nbsp;<font color='red'>*</font>";
            }
        }

        switch ($toptype) {
            case 'counter':
                $news['hits'] = (string)$myrow['counter'];
                break;
            case 'published':
                $news['hits'] = formatTimestamp($myrow['published'], 's');
                break;
            case 'comments':
            default:
                $news['hits'] = (string)$myrow['comments'];
                break;
        }

        $block['stories'][] = $news;
    }

    return $block;
}

function b_stories_top_edit($options)
{
    $form = '' . _MB_STORIES_ORDER . "&nbsp;<select name='options[0]'>";

    $form .= "<option value='published'";

    if ('published' == $options[0]) {
        $form .= " selected='selected'";
    }

    $form .= '>' . _MB_STORIES_DATE . "</option>\n";

    $form .= "<option value='counter'";

    if ('counter' == $options[0]) {
        $form .= " selected='selected'";
    }

    $form .= '>' . _MB_STORIES_HITS . "</option>\n";

    $form .= "<option value='comments'";

    if ('comments' == $options[0]) {
        $form .= " selected='selected'";
    }

    $form .= '>' . _MB_STORIES_COMMENTS . "</option>\n";

    $form .= "</select>\n";

    $chk = '';

    $form .= '&nbsp;<br>' . _MB_STORIES_ASCDESC . '&nbsp;';

    if (1 == $options[1]) {
        $chk = ' checked';
    }

    $form .= "<input type='radio' name='options[1]' value='1'" . $chk . '>&nbsp;' . _MB_STORIES_DESC;

    $chk = '';

    if (0 == $options[1]) {
        $chk = ' checked';
    }

    $form .= '&nbsp;<input type="radio" name="options[1]" value="0"' . $chk . '>' . _MB_STORIES_ASC;

    $form .= '&nbsp;<br>' . _MB_STORIES_DISP . "&nbsp;<input type='text' name='options[2]' value='" . $options[2] . "'>&nbsp;" . _MB_STORIES_ARTCLS . '';

    $form .= '&nbsp;<br>' . _MB_STORIES_CHARS . "&nbsp;<input type='text' name='options[3]' value='" . $options[3] . "'>&nbsp;" . _MB_STORIES_LENGTH . '';

    $form .= '&nbsp;<br>' . _MB_STORIES_RECONLY . '&nbsp;';

    $chk = '';

    if (1 == $options[4]) {
        $chk = ' checked';
    }

    $form .= "<input type='radio' name='options[4]' value='1'" . $chk . '>&nbsp;' . _YES;

    $chk = '';

    if (0 == $options[4]) {
        $chk = ' checked';
    }

    $form .= '&nbsp;<input type="radio" name="options[4]" value="0"' . $chk . '>' . _NO;

    $form .= '&nbsp;<br>' . _MB_STORIES_HMRECENT . "&nbsp;<input type='text' name='options[5]' value='" . $options[5] . "'>&nbsp;" . _MB_STORIES_ARTCLS . '';

    return $form;
}
