<?php

class Crawler_TVB implements Crawler_Common
{
    public static function crawlIndex()
    {
        $content = Crawler::getBody('http://news.tvb.com/list/focus'); //頭版
        $content .= Crawler::getBody('http://news.tvb.com/list/local/'); //港聞
        $content .= Crawler::getBody('http://news.tvb.com/list/world/'); //兩岸國際
        $content .= Crawler::getBody('http://news.tvb.com/list/finance/'); //財經
        $content .= Crawler::getBody('http://news.tvb.com/list/sports/'); //體育
        $content .= Crawler::getBody('http://news.tvb.com/list/weather/'); //天氣
        return $content;
    }

    public static function findLinksIn($content)
    {
        preg_match_all('#href[ ]?=[ ]?"(\/(local|world|finance|sports|weather)\/[0-9A-Za-z]*)\/#', $content, $matches);
        array_walk($matches[1], function(&$link) { $link = 'http://news.tvb.com' . $link; });
       return array_unique($matches[1]);
    }

    public static function parse($body)
    {
        $ret = new StdClass;

        $doc = new DOMDocument('1.0', 'UTF-8');
        $body = str_replace('<head>', '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">', $body);

        @$doc->loadHTML($body);
        
        if ($h4_dom = $doc->getElementsByTagName('h4')->item(0)) {
            //Remove the time from the title
            $timeSpan = $doc->getElementsByTagName('h4')->item(0)->getElementsByTagName('span')->item(0);
            $doc->getElementsByTagName('h4')->item(0)->removeChild($timeSpan);
            $ret->title = trim($h4_dom->nodeValue);
        } else {
            $ret->title = null;
        }

        if ($content_dom = $doc->getElementById('c1_afterplayer')) {
            if ($content_dom = $content_dom->getElementsByTagName('pre')->item(0)) {
                $ret->body = trim($content_dom->nodeValue);
            } else {
                $ret->body = null;
            }
        }
        
        return $ret;
    }
}
