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

function b_stories_top_frame_show($options)
{
    //	error_reporting(E_ALL);

    global $xoopsDB, $xoopsUser;

    $myts = MyTextSanitizer::getInstance();

    $block = [];

    $toptype = $options[0];

    $storyorder = $options[1];

    $storycount = $options[2];

    $titlelength = $options[3];

    $block['iframewidth'] = $options[4];

    $tablewidth = $options[4] - 20;

    $limitreach = $options[5];

    $reachback = $options[6];

    $block['iframeurl'] = XOOPS_URL . '/modules/stories/blocks/stories_top_iframe.php?storycount=' . $storycount . '&titlelength=' . $titlelength . '&storyorder=' . $storyorder . '&toptype=' . $toptype . '';

    if ($options[7]) {
        $tdate = mktime(0, 0, 0, date('n'), date('j'), date('Y'));

        $sql = 'SELECT '
                  . $xoopsDB->prefix('stories')
                  . '.storyid, title, comments, comdate FROM '
                  . $xoopsDB->prefix('stories')
                  . ', '
                  . $xoopsDB->prefix('vlstories')
                  . ' WHERE '
                  . $xoopsDB->prefix('stories')
                  . '.storyid='
                  . $xoopsDB->prefix('vlstories')
                  . '.storyid AND published > '
                  . $tdate
                  . ' ORDER BY counter DESC';

        $result = $xoopsDB->query($sql, 1, 0);

        [$fsid, $ftitle, $comments, $comdate] = $xoopsDB->fetchRow($result);

        $sql = 'SELECT '
                   . $xoopsDB->prefix('stories')
                   . '.storyid, title, comments, comdate FROM '
                   . $xoopsDB->prefix('stories')
                   . ', '
                   . $xoopsDB->prefix('vlstories')
                   . ' WHERE '
                   . $xoopsDB->prefix('stories')
                   . '.storyid='
                   . $xoopsDB->prefix('vlstories')
                   . '.storyid AND published > '
                   . $tdate
                   . ' AND comments > 0 ORDER BY comments DESC';

        $cresult = $xoopsDB->query($sql, 1, 0);

        [$cfsid, $cftitle, $ccomments, $ccomdate] = $xoopsDB->fetchRow($cresult);

        $unread = '';

        $cunread = '';

        if ($xoopsUser) {
            $lastviewed = (int)(time() + 2592000);

            $clastviewed = (int)(time() + 2592000);

            $uid = $xoopsUser->getVar('uid');

            if ($fsid && $comments) {
                $lvcid = sprintf('STORIES%08d%08d', $uid, $fsid);

                $result = $xoopsDB->query('SELECT lastviewed from ' . $xoopsDB->prefix('commentstracker') . " WHERE lvcid='" . $lvcid . "'");

                if ($result) {
                    $numrows = $xoopsDB->getRowsNum($result);

                    if ($numrows) {
                        [$lastviewed] = $xoopsDB->fetchRow($result);
                    } else {
                        $lastviewed = 1;
                    }
                } else {
                    $lastviewed = 1;
                }
            }

            if ($cfsid && $ccomments) {
                $clvcid = sprintf('STORIES%08d%08d', $uid, $cfsid);

                $result = $xoopsDB->query('SELECT lastviewed from ' . $xoopsDB->prefix('commentstracker') . " WHERE lvcid='" . $clvcid . "'");

                if ($result) {
                    $numrows = $xoopsDB->getRowsNum($result);

                    if ($numrows) {
                        [$clastviewed] = $xoopsDB->fetchRow($result);
                    } else {
                        $lastviewed = 1;
                    }
                } else {
                    $lastviewed = 1;
                }
            }

            if ($comdate > $lastviewed) {
                $unread = "&nbsp;(<font color='red'>*</font>)&nbsp;";
            }

            if ($ccomdate > $clastviewed) {
                $cunread = "&nbsp;(<font color='red'>*</font>)&nbsp;";
            }
        }

        $block['blocktitle'] = '';

        if ($fsid && $ftitle) {
            $block['blocktitle'] = _MB_STORIES_TS;

            if ($cfsid == $fsid) {
                $block['message'] = _MB_STORIES_TMPSI;
            } else {
                $block['message'] = _MB_STORIES_TMRSI;
            }

            $block['story_title'] = $unread . htmlspecialchars($ftitle, ENT_QUOTES | ENT_HTML5);

            if ($comments) {
                if (1 == $comments) {
                    $block['comments'] = '(1&nbsp;comment)';
                } else {
                    $block['comments'] = '(' . $comments . '&nbsp;comments)';
                }
            }

            $block['story_id'] = $fsid;
        }

        if ($cfsid && $cftitle && ($cfsid != $fsid)) {
            $block['blocktitle'] = _MB_STORIES_TS;

            $block['cmessage'] = _MB_STORIES_TMCSI;

            $block['cstory_title'] = $cunread . htmlspecialchars($cftitle, ENT_QUOTES | ENT_HTML5);

            if (1 == $ccomments) {
                $block['ccomments'] = '(1&nbsp;comment)';
            } else {
                $block['ccomments'] = '(' . $ccomments . '&nbsp;comments)';
            }

            $block['cstory_id'] = $cfsid;
        }
    }

    return $block;
}

function b_stories_top_frame_edit($options)
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

    $form .= '&nbsp;<br>' . _MB_STORIES_WIDTH . "&nbsp;<input type='text' name='options[4]' value='" . $options[4] . "'>&nbsp;" . _MB_STORIES_PIXELS . '';

    $form .= '&nbsp;<br>' . _MB_STORIES_RECONLY . '&nbsp;';

    $chk = '';

    if (1 == $options[5]) {
        $chk = ' checked';
    }

    $form .= "<input type='radio' name='options[5]' value='1'" . $chk . '>&nbsp;' . _YES;

    $chk = '';

    if (0 == $options[5]) {
        $chk = ' checked';
    }

    $form .= '&nbsp;<input type="radio" name="options[5]" value="0"' . $chk . '>' . _NO;

    $form .= '&nbsp;<br>' . _MB_STORIES_HMRECENT . "&nbsp;<input type='text' name='options[6]' value='" . $options[6] . "'>&nbsp;" . _MB_STORIES_ARTCLS . '';

    $form .= '&nbsp;<br>' . _MB_STORIES_SHOWBIG . '&nbsp;';

    $chk = '';

    if (1 == $options[7]) {
        $chk = ' checked';
    }

    $form .= "<input type='radio' name='options[7]' value='1'" . $chk . '>&nbsp;' . _YES;

    $chk = '';

    if (0 == $options[7]) {
        $chk = ' checked';
    }

    $form .= '&nbsp;<input type="radio" name="options[7]" value="0"' . $chk . '>' . _NO;

    return $form;
}
