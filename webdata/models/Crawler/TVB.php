<?php

class Crawler_TVB
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

    public static function crawl($insert_limit)
    {
        $content = self::crawlIndex();
        //echo $content;
        preg_match_all('#href[ ]?=[ ]?"(\/(local|world|finance|sports|weather)\/[0-9A-Za-z]*)\/#', $content, $matches);
        //var_dump($matches);
        $links = array_unique($matches[1]);
        $insert = $update = 0;
        foreach ($links as $link) {
            $update ++;
            $link = 'http://news.tvb.com' . $link;
            //echo $link."\n";
            $insert += News::addNews($link, 17);
            if ($insert_limit <= $insert) {
                break;
            }
        }

        return array($update, $insert);
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
