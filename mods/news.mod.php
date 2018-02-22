<?php
if (!defined('NFK_LIVE')) die();
$action = (isset($PARAMSTR[2]) && is_numeric($PARAMSTR[2])) ? 'view' : 'index';
if ($action == 'view') {
    include 'inc/news.view.php';
} else {
    $template->load_template('mod_news');
    $newsList = $db->select('*', 'news', 'ORDER BY news_id desc');
    $contentNews = null;
    foreach ($newsList as $news) {
        $template->assign_variables(array(
            'title' => $news['title'],
            'description' => $news['description'],
            'content' => $news['content'],
            'comments' => $news['comments'],
            'date' => $news['date'],
            'newsID' => $news['news_id'],
        ));
        $contentNews .= $template->build('news_row');
    }
    $template->assign_variables(array(
        'contentNews' => $contentNews,
    ));
    $content_data .= $template->build('main');
    $page_title = $page_name = $dict->data['news'];
}
