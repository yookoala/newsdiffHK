<?php

class Crawler_RTHK implements Crawler_Common
{
    public static function crawlIndex()
    {
        $content = Crawler::getBody('http://rthk.hk/rthk/news/rss/c_expressnews.xml'); //即時新聞
        $content .= Crawler::getBody('http://rthk.hk/rthk/news/rss/englishnews.xml');  //Instant News
        $content .= Crawler::getBody('http://rthk.hk/rthk/news/rss/c_finance.xml');    //財經新聞
        $content .= Crawler::getBody('http://rthk.hk/rthk/news/rss/e_finance.xml');    //Finance News
        return $content;
    }

    public static function findLinksIn($content)
    {
       preg_match_all('/\<link\>\<!\[CDATA\[(.+?\.htm)\]\]\><\/link\>/', $content, $matches);
       return array_unique($matches[1]);
    }

    public static function parse($body)
    {

        // preprocess the HTML body
        $body = str_replace('<head>', '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">', $body);
        $body = str_replace("\r", '', $body);
        $body = preg_replace('/(\<span[^\>]*?\>|<\/span>)/', "\n", $body);
        $body = preg_replace('/(\<br[^\>]*?\/\>|\<p\>|\<\/p\>|\<\/div\>)/', "$1\n", $body);
        $body = str_replace('<p>', "\n\n<p>", $body);

        // modify HTML
        $body = str_replace('<!-- content -->', '<!-- content --><div id="newsdiff-content">', $body);
        $body = str_replace('<!-- content end -->', '</div><!-- content end -->', $body);

        //
        $ret = new StdClass;
        $doc = new DOMDocument('1.0', 'UTF-8');
        @$doc->loadHTML($body);

        // read the first og:title
        $og_title = FALSE;
        foreach ($doc->getElementsByTagName('meta') as $meta) {
            if (($meta->getAttribute('name') == 'og:title') ||
                ($meta->getAttribute('property') == 'og:title')) {
                $og_title = trim($meta->getAttribute('content'));
                break;
            }
        }
        
        // identify content cell and parse
        $content = $doc->getElementById('newsdiff-content');
        $body = trim($content->nodeValue);
        $body = preg_replace('/(?<!\n)\n(?!\n)/', ' ', $body); // remove line breaks
        $body = preg_replace('/[\n\r\t ]*(\n|\r)[\n\r\t ]*/', "\n\n", $body);
        $body = preg_replace('/  [ ]*/', ' ', $body); // replace multiple space with single

        // return the proper result
        $ret->title = self::removeSpace($og_title);
        $ret->body = self::removeSpace($body);
        return $ret;
    }

    // remove space between Chinese characters
    private static function removeSpace($content) {
        $non_zh = '[\w\!#"\$%&\'\(\)\*\+,\-\.\/:;\<=\>\?@\[\\\\\]^_`{|}~]';
        return preg_replace('/(?<!'.$non_zh.') (?!'.$non_zh.')/', '', $content);
    }

}
