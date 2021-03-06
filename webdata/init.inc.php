<?php

error_reporting(E_ALL ^ E_STRICT ^ E_NOTICE);

// setup libraries and autoload
require_once(__DIR__ . '/stdlibs/diff_match_patch-php-master/diff_match_patch.php');
require_once(__DIR__ . '/stdlibs/pixframework/Pix/Loader.php');
set_include_path(__DIR__ . '/stdlibs/pixframework/'
    . PATH_SEPARATOR . __DIR__ . '/models'
    . PATH_SEPARATOR . __DIR__ . '/stdlibs/Dropbox-master/'
    . PATH_SEPARATOR . __DIR__ . '/stdlibs'
);
Pix_Loader::registerAutoLoad();

// TODO: 之後要搭配 geoip
date_default_timezone_set('Asia/Taipei');
mb_internal_encoding("UTF-8");

URLNormalizer::setCSVMapPath(__DIR__ . '/map.csv');

// setup with config file
if (file_exists(__DIR__ . '/config.php')) {
    include(__DIR__ . '/config.php');
}

// skip database initialization in unit test mode
if (UNITTEST_MODE !== true) {

    // get database URL from environment
    if (!getenv('DATABASE_URL')) {
        die('need DATABASE_URL');
    }
    if (!preg_match('#mysql://([^:]*):([^@]*)@([^/]*)/(.*)#', strval(getenv('DATABASE_URL')), $matches)) {
        die('mysql only');
    }

    // initialize database
    $db = new StdClass;
    $db->host = $matches[3];
    $db->username = $matches[1];
    $db->password = $matches[2];
    $db->dbname = $matches[4];
    $config = new StdClass;
    $config->master = $config->slave = $db;
    Pix_Table::setDefaultDb(new Pix_Table_Db_Adapter_MysqlConf(array($config)));
}

// define news sources
NewsSourcesCfg::setAll(array(
    /*
    1 => array(
        'name' => '蘋果',
        'class' => 'Crawler_Appledaily',
        'parsers' => array(
            'www.appledaily.com.tw' => 'parse',
        ),
    ),
    2 => array(
        'name' => '中時',
        'class' => 'Crawler_Chinatimes',
        'parsers' => array(
            'www.chinatimes.com' => 'parse',
        ),
    ),
    3 => array(
        'name' => '中央社',
        'class' => 'Crawler_CNA',
        'parsers' => array(
            'www.cna.com.tw' => 'parse',
        ),
    ),
    4 => array(
        'name' => '東森',
        'class' => 'Crawler_Ettoday',
        'parsers' => array(
            'www.ettoday.net' => 'parse',
        ),
    ),
    5 => array(
        'name' => '自由',
        'class' => 'Crawler_Libertytimes',
        'parsers' => array(
            'iservice.libertytimes.com.tw' => 'parse',
            'news.ltn.com.tw' => 'parse',
            'www.libertytimes.com.tw' => 'parse2',
        ),
    ),
    6 => array(
        'name' => '新頭殼',
        'class' => 'Crawler_Newtalk',
        'parsers' => array(
            'newtalk.tw' => 'parse',
        ),
    ),
    7 => array(
        'name' => 'NowNews',
        'class' => 'Crawler_Nownews',
        'parsers' => array(
            'www.nownews.com' => 'parse',
        ),
    ),
    8 => array(
        'name' => '聯合',
        'class' => 'Crawler_UDN',
        'parsers' => array(
            'udn.com' => 'parse',
        ),
    ),
    9 => array(
        'name' => 'TVBS',
        'class' => 'Crawler_TVBS',
        'parsers' => array(
            'news.tvbs.com.tw' => 'parse',
        ),
    ),
    10 => array(
        'name' => '中廣新聞網',
        'class' => 'Crawler_BCC',
        'parsers' => array(
            'www.bcc.com.tw' => 'parse',
        ),
    ),
    11 => array(
        'name' => '公視新聞網',
        'class' => 'Crawler_PTS',
        'parsers' => array(
            'news.pts.org.tw' => 'parse',
        ),
    ),
    12 => array(
        'name' => '台視',
        'class' => 'Crawler_TTV',
        'parsers' => array(
            'www.ttv.com.tw' => 'parse',
        ),
    ),
    13 => array(
        'name' => '華視',
        'class' => 'Crawler_CTS',
        'parsers' => array(
            'news.cts.com.tw' => 'parse',
        ),
    ),
    14 => array(
        'name' => '民視',
        'class' => 'Crawler_FTV',
        'parsers' => array(
            'news.ftv.com.tw' => 'parse',
        ),
    ),
    15 => array(
        'name' => '三立',
        'class' => 'Crawler_SETNews',
        'parsers' => array(
            'www.setnews.net' => 'parse',
        ),
    ),
    16 => array(
        'name' => '風傳媒',
        'class' => 'Crawler_StormMediaGroup',
        'parsers' => array(
            'www.stormmediagroup.com' => 'parse',
        ),
    ),
    */
    17 => array(
        'name' => 'TVB',
        'class' => 'Crawler_TVB',
        'parsers' => array(
            'news.tvb.com' => 'parse',
        ),
    ),
    18 => array(
        'name' => '文匯',
        'class' => 'Crawler_WenWeiPo',
        'parsers' => array(
            'paper.wenweipo.com' => 'parse',
        ),
    ),
    19 => array(
        'name' => '熱血時報',
        'class' => 'Crawler_PassionTimes',
        'parsers' => array(
            'passiontimes.hk' => 'parse',
            'www.passiontimes.hk' => 'parse',
        ),
    ),
    20 => array(
        'name' => '星島日報',
        'class' => 'Crawler_SingTao',
        'parsers' => array(
            'std.stheadline.com' => 'parse',
        ),
    ),

));
