<?php
// $Id: module.textsanitizer.php,v 1.23 2003/08/15 02:53:01 mvandam Exp $
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
// Author: Kazumi Ono (http://www.myweb.ne.jp/, http://jp.xoops.org/)        //
//         Goghs Cheng (http://www.eqiao.com, http://www.devbeez.com/)       //
// Project: The XOOPS Project (https://www.xoops.org/)                        //
// ------------------------------------------------------------------------- //

/**
 * Class to "clean up" text for various uses
 *
 * <b>Singleton</b>
 *
 *
 * @author           Kazumi Ono    <onokazu@xoops.org>
 * @author           Goghs Cheng
 * @copyright    (c) 2000-2003 The Xoops Project - www.xoops.org
 */
require_once XOOPS_ROOT_PATH . '/class/module.textsanitizer.php';

class storiesTextSanitizer extends MyTextSanitizer
{
    /*
    * Constructor of this class
    *
    * Gets allowed html tags from admin config settings
    * <br> should not be allowed since nl2br will be used
    * when storing data.
    *
    * @access	private
    *
    * @todo Sofar, this does nuttin' ;-)
    */

    public function __construct()
    {
    }

    /**
     * Access the only instance of this class
     *
     * @return    object
     *
     * @static
     * @staticvar   object
     */

    public static function &getInstance()
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Replace XoopsCodes with their equivalent HTML formatting
     *
     * @param string $text
     * @param int    $allowimage    Allow images in the text?
     *                              On FALSE, uses links to images.
     * @return  string
     */

    public function xoopsCodeDecode($text, $allowimage = 1)
    {
        $patterns = [];

        $replacements = [];

        $patterns[] = "/\[code](.*)\[\/code\]/esU";

        $replacements[] = "'<div class=\"xoopsCode\"><code><pre>'.wordwrap(MyTextSanitizer::htmlSpecialChars('\\1'), 100).'</pre></code></div>'";

        // RMV: added new markup for intrasite url (allows easier site moves)

        // TODO: automatically convert other URLs to this format if XOOPS_URL matches??

        $patterns[] = "/\[siteurl=(['\"]?)([^\"'<>]*)\\1](.*)\[\/siteurl\]/sU";

        $replacements[] = '<a href="' . XOOPS_URL . '/\\2" target="_blank">\\3</a>';

        $patterns[] = "/\[url=(['\"]?)(http[s]?:\/\/[^\"'<>]*)\\1](.*)\[\/url\]/sU";

        $replacements[] = '<a href="\\2" target="_blank">\\3</a>';

        $patterns[] = "/\[url=(['\"]?)(ftp?:\/\/[^\"'<>]*)\\1](.*)\[\/url\]/sU";

        $replacements[] = '<a href="\\2" target="_blank">\\3</a>';

        $patterns[] = "/\[url=(['\"]?)([^\"'<>]*)\\1](.*)\[\/url\]/sU";

        $replacements[] = '<a href="http://\\2" target="_blank">\\3</a>';

        $patterns[] = "/\[color=(['\"]?)([a-zA-Z0-9]*)\\1](.*)\[\/color\]/sU";

        $replacements[] = '<span style="color: #\\2;">\\3</span>';

        $patterns[] = "/\[size=(['\"]?)([a-z0-9-]*)\\1](.*)\[\/size\]/sU";

        $replacements[] = '<span style="font-size: \\2;">\\3</span>';

        $patterns[] = "/\[font=(['\"]?)([^;<>\*\(\)\"']*)\\1](.*)\[\/font\]/sU";

        $replacements[] = '<span style="font-family: \\2;">\\3</span>';

        $patterns[] = "/\[email]([^;<>\*\(\)\"']*)\[\/email\]/sU";

        $replacements[] = '<a href="mailto:\\1">\\1</a>';

        $patterns[] = "/\[b](.*)\[\/b\]/sU";

        $replacements[] = '<b>\\1</b>';

        $patterns[] = "/\[i](.*)\[\/i\]/sU";

        $replacements[] = '<i>\\1</i>';

        $patterns[] = "/\[u](.*)\[\/u\]/sU";

        $replacements[] = '<u>\\1</u>';

        $patterns[] = "/\[d](.*)\[\/d\]/sU";

        $replacements[] = '<del>\\1</del>';

        $patterns[] = "/\[strike](.*)\[\/strike\]/sU";

        $replacements[] = '<strike>\\1</strike>';

        //$patterns[] = "/\[li](.*)\[\/li\]/sU";

        //$replacements[] = '<li>\\1</li>';

        $patterns[] = "/\[img align=(['\"]?)(left|center|right)\\1]([^\"\(\)\?\&'<>]*)\[\/img\]/sU";

        $patterns[] = "/\[img]([^\"\(\)\?\&'<>]*)\[\/img\]/sU";

        $patterns[] = "/\[img align=(['\"]?)(left|center|right)\\1 id=(['\"]?)([0-9]*)\\3]([^\"\(\)\?\&'<>]*)\[\/img\]/sU";

        $patterns[] = "/\[img id=(['\"]?)([0-9]*)\\1]([^\"\(\)\?\&'<>]*)\[\/img\]/sU";

        if (1 != $allowimage) {
            $replacements[] = '<a href="\\3" target="_blank">\\3</a>';

            $replacements[] = '<a href="\\1" target="_blank">\\1</a>';

            $replacements[] = '<a href="' . XOOPS_URL . '/image.php?id=\\4" target="_blank">\\4</a>';

            $replacements[] = '<a href="' . XOOPS_URL . '/image.php?id=\\2" target="_blank">\\3</a>';
        } else {
            $replacements[] = '<img src="\\3" align="\\2" alt="">';

            $replacements[] = '<img src="\\1" alt="">';

            $replacements[] = '<img src="' . XOOPS_URL . '/image.php?id=\\4" align="\\2" alt="\\4">';

            $replacements[] = '<img src="' . XOOPS_URL . '/image.php?id=\\2" alt="\\3">';
        }

        $patterns[] = "/\[quote]/sU";

        $replacements[] = _QUOTEC . '<div class="xoopsQuote"><blockquote>';

        //$replacements[] = 'Quote: <div class="xoopsQuote"><blockquote>';

        $patterns[] = "/\[\/quote]/sU";

        $replacements[] = '</blockquote></div>';

        $patterns[] = '/javascript:/si';

        $replacements[] = 'java script:';

        $patterns[] = '/about:/si';

        $replacements[] = 'about :';

        return preg_replace($patterns, $replacements, $text);
    }

    /*
    *  Filters both textbox and textarea form data before display
    *  For internal use
    */

    public function sanitizeForDisplay($text, $allowhtml = 0, $smiley = 1, $bbcode = 1)
    {
        //$text = $this->stripSlashesGPC($text);

        if (0 == $allowhtml) {
            $text = $this->htmlSpecialChars($text);
        } else {
            $config = &$GLOBALS['xoopsConfig'];

            $allowed = $config['allowed_html'];

            $text = strip_tags($text, $allowed);

            $text = $this->makeClickable($text);
        }

        if (1 == $smiley) {
            $text = $this->smiley($text);
        }

        if (1 == $bbcode) {
            $text = $this->xoopsCodeDecode($text);
        }

        $text = $this->nl2Br($text);

        return $text;
    }

    /*
    *  Filters both textbox and textarea form data before preview
    *  For internal use
    */

    public function sanitizeForPreview($text, $allowhtml = 0, $smiley = 1, $bbcode = 1)
    {
        $text = $this->stripSlashesGPC($text);

        if (0 == $allowhtml) {
            $text = $this->htmlSpecialChars($text);
        } else {
            $config = &$GLOBALS['xoopsConfig'];

            $allowed = $config['allowed_html'];

            $text = strip_tags($text, $allowed);

            $text = $this->makeClickable($text);
        }

        if (1 == $smiley) {
            $text = $this->smiley($text);
        }

        if (1 == $bbcode) {
            $text = $this->xoopsCodeDecode($text);
        }

        $text = $this->nl2Br($text);

        return $text;
    }

    /*
    *  Used for displaying textbox form data from DB.
    *  Smilies can also be used.
    */

    public function makeTboxData4Show($text, $smiley = 0)
    {
        $text = $this->displayTarea($text, 0, $smiley, 0); //do HtmlSpecialChars

        return $text;
    }

    /*
    *  Called when previewing textbox form data submitted from a form.
    *  Smilies can be used if needed
    *  Use makeTboxData4PreviewInForm when textbox data is to be
    *  previewed in textbox again
    */

    public function makeTboxData4Preview($text, $smiley = 0)
    {
        $text = $this->previewTarea($text, 0, $smiley, 0); //do HtmlSpecialChars

        return $text;
    }

    /*
    *   Called before displaying textarea form data from DB
    */

    public function displayTarea($text, $allowhtml = 1, $smiley = 0, $bbcode = 0)
    {
        $text = $this->displayTarea($text, $allowhtml, $smiley, $bbcode);

        return $text;
    }

    /*
    *   Called when previewing textarea data which was submitted
    *   via an html form
    */

    public function makeTareaData4Preview($text, $allowhtml = 1, $smiley = 0, $bbcode = 0)
    {
        $text = $this->previewTarea($text, $allowhtml, $smiley, $bbcode);

        return $text;
    }
}
