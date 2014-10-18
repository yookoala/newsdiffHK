<?php

class Crawler_TVB
{
    public static function crawl($insert_limit)
    {
        $content = Crawler::getBody('http://news.tvb.com/list/focus');
        //echo $content;
        preg_match_all('#href[ ]?=[ ]?"(\/local\/[0-9A-Za-z]*)\/#', $content, $matches);
        //var_dump($matches);
        $links = array_unique($matches[1]);
        $insert = $update = 0;
        foreach ($links as $link) {
            $update ++;
            $link = 'http://news.tvb.com' . $link;
            echo $link."\n";
            $insert += News::addNews($link, 17);
            if ($insert_limit <= $insert) {
                break;
            }
        }
        
        return array($update, $insert);
    }

    public static function parse($body)
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $body = str_replace('<head>', '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">', $body);

        @$doc->loadHTML($body);
        
        if (!$h4_dom = $doc->getElementsByTagName('h4')) {
            return null;
        }
        if (!$content_dom = $doc->getElementById('c1_afterplayer')) {
            return null;
        }
        
        //Remove the time from the title
        $timeSpan = $doc->getElementsByTagName('h4')->item(0)->getElementsByTagName('span')->item(0);
        $doc->getElementsByTagName('h4')->item(0)->removeChild($timeSpan);

        $ret = new StdClass;
        $ret->title = trim($doc->getElementsByTagName('h4')->item(0)->nodeValue);

        $ret->body = trim($doc->getElementById('c1_afterplayer')->getElementsByTagName('pre')->item(0)->nodeValue);

        return $ret;
    }
}
