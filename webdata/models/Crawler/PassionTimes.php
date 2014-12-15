<?php

class Crawler_PassionTimes implements Crawler_Common
{

    public static function crawlIndex() {
        $content = '';
        $content .= Crawler::getBody('http://www.passiontimes.hk/category/604/670'); //港聞
        $content .= Crawler::getBody('http://www.passiontimes.hk/category/604/671'); //國際
        $content .= Crawler::getBody('http://www.passiontimes.hk/category/604/673'); //專題
        $content .= Crawler::getBody('http://www.passiontimes.hk/category/604/1681'); //體育
        $content .= Crawler::getBody('http://www.passiontimes.hk/category/604/672'); //財經
        return $content;
    }

    public static function findLinksIn($content)
    {
        preg_match_all('/href[ ]?=[ ]?\'\/(article\/[\d\-]+\/\d+?)\'/', $content, $matches);
        array_walk($matches[1], function(&$link) { $link = 'http://www.passiontimes.hk/'.$link; });
        return $matches[1];
    }

    public static function parse($body)
    {
        $ret = new StdClass;

        $doc = new DOMDocument('1.0', 'UTF-8');
        $body = preg_replace('/(\<br\/\>|\<\/p\>|\<\/div\>)/', "$1\n", $body);

        @$doc->loadHTML($body);

        $og_title = FALSE;
        foreach ($doc->getElementsByTagName('meta') as $meta) {
            if ($meta->getAttribute('property') == 'og:title') {
                $og_title = $meta->getAttribute('content');
            }
        }
        $ret->title = ($og_title !== FALSE) ?
          preg_replace('/^(.+?) |.*$/', '$1', $og_title) : FALSE;

        $content = '';
        $finder = new DomXPath($doc);
        $itemList = $finder->query("//*[contains(@class, 'articleContent')]");
        foreach ($itemList as $item) {
          //$content .= $item->ownerDocument->saveXML($item); 
          $content .= $item->nodeValue; 
        }

        $ret->body = empty($content) ? FALSE : trim($content);

        // fix line break
        $ret->body = preg_replace('/[\n\r\t ]*(\n|\r)[\n\r\t ]*/', "\n\n", $ret->body);
        
        return $ret;
    }
}
