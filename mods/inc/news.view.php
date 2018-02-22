<?php
if (!defined('NFK_LIVE')) die();
$newsID = (isset($PARAMSTR[2]) && is_numeric($PARAMSTR[2])) ? $PARAMSTR[2] : 1;
$template->load_template('mod_news_view');
$news = $db->select('*', 'news', 'WHERE news_id = ' . $newsID);
$news = $news[0];
if (!$news) die('Invalid request');
// comments
$res = $db->select('*','comments',"WHERE materialID = '$newsID' AND moduleID = 4 ORDER BY cmtID DESC");
$commentsNum = count($res);
$contentComments = null;
foreach ($res as $row) {
    if ($row['playerID'] != 0) $plr = getPlayer($row['playerID']);
    $template->assign_variables(array(
        "CMT_AUTHOR" => ($row['playerID']<>0) ? getIcons($plr):getIcons($row,false,false,false),
        "CMT_DATE" => $row['postTime'],
        "COMMENT" => $row['comment'],
        "CMT_NUM" => $commentsNum--,
        "CMT_DELETE" => ($xdata['access']>=3) ? "<a href='/do/comment/delete/$row[cmtID]/$row[materialID]'><img src='$THEME_ROOT/images/delete_ico.gif' /></a>" : "",
    ));
    $contentComments .= $template->build('comment');
}
$template->assign_variables(array(
    "GTW_LOGIC"			=> (count($res)>0) ? (true) : (false),
    "G_MATCH_COMMENTS"	=> $contentComments,
));
$if_have_comments = $template->build('if_have_comments');
$template->assign_variables(array(
    "GTW_LOGIC" => ($xdata['playerID'] <> 0) ? (true) : (false),
    "NULL" => NULL,
));
$if_logged = $template->build('if_logged') or die("error building: match\if_logged");
$template->assign_variables(array(
    'title' => $news['title'],
    'description' => $news['description'],
    'content' => $news['content'],
    'comments' => $news['comments'],
    'date' => $news['date'],
    'newsID' => $news['news_id'],
    'MODULE_ID' => 4,
    'MATERIAL_ID' => $newsID,
    'IF_HAVE_COMMENT' => $if_have_comments,
    'IF_LOGGED' => $if_logged,
    'L_ADD_COMMENT' => $dict->data['add_comment'],
    "L_ADD"				=> $dict->data['add'],
    "L_NAME"			=> $dict->data['name'],
));
$content_data = $template->build('main');
$page_title = $dict->data['news'] . ' - ' . $news['title'];
$page_name = null;

