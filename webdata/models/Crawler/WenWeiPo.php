<?php

class Crawler_WenWeiPo
{
    public static function crawl($insert_limit)
    {
        $next = null;
        $url = "http://paper.wenweipo.com/003HK/";  //Hong Kong News
        $content = "";
        
        //Crawling
        do {
            $content .= Crawler::getBody($url);
            
            if ($next = preg_match_all('#<a href="([A-Za-z0-9.\/-]*)">下一頁<\/a>#', $content, $matches)) {
                $url = "http://paper.wenweipo.com".$matches[1][0];    
            }
        } while ($next);
        
        preg_match_all('#(http:\/\/paper.wenweipo.com\/[0-9\/A-Za-z.]*)" target="_blank#', $content, $matches);
        //var_dump($matches);
        $links = array_unique($matches[1]);
        $insert = $update = 0;
        foreach ($links as $link) {
            $update ++;
            //echo $link."\n";
            $insert += News::addNews($link, 18);
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
        
        if ($h1_dom = $doc->getElementsByTagName('h1')->item(0)->getElementsByTagName('font')->item(0)) {
            $ret->title = trim($h1_dom->nodeValue);
        } else {
            $ret->title = null;
        }

        $xpath = new DOMXPath($doc);
        $content = "";
        foreach ($xpath->query('//p[contains(attribute::class, "content_p")]') as $e) {
            $content .= $e->nodeValue;
        }

        if ($content == "") {
            $ret->body = null;
        } else {
            $ret->body = trim($content);
        }

        return $ret;
    }
}
