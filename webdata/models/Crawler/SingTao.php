<?php

class Crawler_SingTao implements Crawler_Common
{

    public static function crawlIndex() {
        $content = '';
        $content .= Crawler::getBody('http://std.stheadline.com/breakingnews/instantnews_locfrontpage.html'); // 香港
        $content .= Crawler::getBody('http://std.stheadline.com/breakingnews/instantnews_intfrontpage.html'); // 國際
        $content .= Crawler::getBody('http://std.stheadline.com/breakingnews/instantnews_chifrontpage.html'); // 中國
        $content .= Crawler::getBody('http://std.stheadline.com/breakingnews/instantnews_finfrontpage.html'); // 經濟
        $content .= Crawler::getBody('http://std.stheadline.com/breakingnews/instantnews_profrontpage.html'); // 地產
        $content .= Crawler::getBody('http://std.stheadline.com/breakingnews/instantnews_spofrontpage.html'); // 體育
        $content .= Crawler::getBody('http://std.stheadline.com/breakingnews/instantnews_entfrontpage.html'); // 娛樂
        return $content;
    }

    public static function findLinksIn($content)
    {
        preg_match_all('/href="\/(breakingnews\/[\d\w]+\.asp)"/', $content, $matches);
        array_walk($matches[1], function(&$link) { $link = 'http://std.stheadline.com/'.$link; });
        return $matches[1];
    }

    public static function parse($body)
    {
        $ret = new StdClass;

        $doc = new DOMDocument('1.0', 'UTF-8');

        // add line break after all secion end to ensure line break exists
        // between paragraphs
        $body = preg_replace('/(\<br\/\>|\<\/td>|\<\/p\>|\<\/div\>)/', "$1\n", $body);

        // remove all captions in an ugly way
        $body = preg_replace('/\<td class="caption"[ \w"\']*\>(.+?)\<\/td\>/', '', $body);

        @$doc->loadHTML(mb_convert_encoding($body, 'HTML-ENTITIES', 'UTF-8'));

        $og_title = FALSE;
        foreach ($doc->getElementsByTagName('meta') as $meta) {
            if ($meta->getAttribute('property') == 'og:title') {
                $og_title = $meta->getAttribute('content');
                $og_title = preg_replace('/[ ]*\-[^\-]+?$/', '', $og_title); // remove everything after last hyphan
            }
        }
        $ret->title = $og_title;

        // find the body text and get raw HTML
        $finder = new DomXPath($doc);
        $itemList = $finder->query("//*[contains(@class, 'bodytext')]");
        $content = $itemList->item(0)->nodeValue;

        $ret->body = empty($content) ? FALSE : trim($content);

        // fix line break
        $ret->body = preg_replace('/[\n\r\t ]*(\n|\r)[\n\r\t ]*/', "\n\n", $ret->body);
        
        return $ret;
    }
}
