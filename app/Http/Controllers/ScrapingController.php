<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Goutte\Client;
use App\Models\Entry;
use DOMDocument;

class ScrapingController extends Controller
{
    //
    public function index1() {
        ScrapingController::index_1();
    }

    private function scraping_rss_sites() {
        return [
            'it速報' => 'http://blog.livedoor.jp/itsoku/index.rdf',
            'アルファルファモザイク' => 'http://alfalfalfa.com/index.rdf',
            'キニ速' => 'http://blog.livedoor.jp/kinisoku/index.rdf',
            '痛いニュース' => 'http://blog.livedoor.jp/dqnplus/index.rdf',
            '【2ch】コピペ情報局' => 'http://news.2chblog.jp/index.rdf',
            '稲妻速報' => 'http://inazumanews2.com/index.rdf',
            'ニュース２ちゃんねる' => 'http://news020.blog13.fc2.com/?xml',
            'ラジック' => 'http://rajic.2chblog.jp/index.rdf',
            '働くモノニュース' => 'http://workingnews.blog117.fc2.com/?xml'
        ];
    }

    private function scraping_rss_sites_atom() {
        return [
            'VIPPERな俺' => 'http://blog.livedoor.jp/news23vip/atom.xml',
            'ハムスター速報' => 'http://hamusoku.com/atom.xml',
            '常識的' => 'http://blog.livedoor.jp/jyoushiki43/atom.xml',
            '暇人速報' => 'http://blog.livedoor.jp/himasoku123/atom.xml',
            'ライフハック' => 'http://lifehack2ch.livedoor.biz/atom.xml',
            'カオスちゃんねる' => 'http://chaos2ch.com/atom.xml',
            '哲学ニュース' => 'http://blog.livedoor.jp/nwknews/atom.xml'
        ];
    }

    private function get_imge_source($item) {
        //contentデータ取得
        $content = $item->children('content', 'http://purl.org/rss/1.0/modules/content/');
        $html_string = $content->encoded;
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html_string);
        libxml_clear_errors();
        // item(index)は何番のimgを取得するかを表す
        if (!is_null($dom->getElementsByTagName('img')->item(0))) {
           return $dom->getElementsByTagName('img')->item(0)->getAttribute('src');
        } else {
            return "";
        }
    }

    public function parse_xml() {
        $path = $this->scraping_rss_sites()['働くモノニュース'];
        // ref: https://www.softel.co.jp/blogs/tech/archives/4105
        $rss = simplexml_load_file($path);
        $data = array();
        echo $rss->channel->title . '<br>';
        foreach ($rss->item as $item) {
            $x = array();
            $x['link'] = (string)$item->link;
            $x['title'] = (string)$item->title . '<br>';
            $x['description'] = (string)$item->description . '<br>';
            $x['pubDate'] = (string)$item->children('http://purl.org/dc/elements/1.1/')->date . '<br>';

            $img = $this->get_imge_source($item);
            $x['img'] = $img  . '<br>';
            $data[] = $x;
        }
        var_dump($data);
        return view('scraping.index');
    }

    public function parse_xml_atom() {
        $path = $this->scraping_rss_sites_atom()['ハムスター速報'];
        // ref: https://www.softel.co.jp/blogs/tech/archives/4105
        $rss = simplexml_load_file($path);
        $data = array();
        echo $rss->title . '<br>';
        foreach ($rss->entry as $item) {
            $x = array();
            $x['link'] = (string)$item->link['href'];
            $x['title'] = (string)$item->title . '<br>';
            $x['description'] = (string)$item->summary . '<br>';
            $x['pubDate'] = (string)$item->issued . '<br>';
            $data[] = $x;
            var_dump($x);
        }

        return view('scraping.index');
    }

    // nogizaka
    public static function index_1()
    {
        $client = new Client();
        // 基本形
//        $crawler = $client->request('GET', 'https://www.symfony.com/blog/');
//        $crawler->filter('h2 > a')->each(function ($node) {
//            print $node->text()."\n";
//        });
        //  乃木坂46まとめの「ま」
        $crawler = $client->request('GET', 'http://nogizaka46matomenoma.blog.jp/');
//        $blog_title = $crawler->filter('div.content-article-inner')->text();
//        echo 'blog_title: '. $blog_title;
//        echo '<br/>';
//        echo '<br/>';
        // 回したい場合
        $blog_title = $crawler->filter('header h1 a')->text();
        $blog = new Blog();
        if (!$blog->entryExists($blog_title)) {
            $blog->title = $blog_title;
            $blog->category = config('const.CATEGORIES')[0];
            $blog->save();
            echo 'ブログタイトルデータの保存成功';
        } else {
            echo 'すでに作成しています';
        }
        $crawler->filter('article.article')->each(function ($node) use ($blog_title) {
            $entry = new Entry();
            if (!$entry->entryExists($node->filter('h1 a')->text())) {
                $entry->title = $node->filter('h1 a')->text();
                $entry->link_url = $node->filter('h1 a')->attr('href');
                $entry->image_url = $node->filter('div.ArticleFirstImageThumbnail a img')->attr('src');
                $entry->published = $node->filter('header.article-header p time')->attr('datetime');
                $blog_info = Blog::where('title', $blog_title)->first();
                $entry->blog_id = $blog_info->id;
                $entry->save();
                echo '記事データの保存成功';
            } else {
                echo 'すでに作成しています';
            }
//            echo 'title: '. $node->filter('h1 a')->text();
//            echo '<br/>';
//            echo 'link: '. $node->filter('h1 a')->attr('href');
//            echo '<br/>';
//            echo 'image: '. $node->filter('div.ArticleFirstImageThumbnail a img')->attr('src');
//            echo '<br/>';
//            echo 'date: '. $node->filter('header.article-header p time')->attr('datetime');
//            echo '<br/>';

        });
        return view('scraping.index');
    }

    public function parse_xml_rss()
    {

    }
}
