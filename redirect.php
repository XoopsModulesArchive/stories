<?php

/************************************************************************/

/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

require_once '../../mainfile.php';
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

$story = new NewsStory($storyid);
$location = $story->storyurl();

if ($location) {
    $story->updateCounter();

    header("Location: $location");
} else {
    $location = "article.php?storyid=$item_id";

    header("Location: $location");
}

exit();
