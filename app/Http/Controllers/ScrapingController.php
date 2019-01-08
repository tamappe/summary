<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Goutte\Client;
use App\Models\Entry;

class ScrapingController extends Controller
{
    //
    public function index1() {
        ScrapingController::index_1();
    }

    public function parse_xml() {
        ScrapingController::parse_xml_rss();
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

    public static function parse_xml_rss()
    {
        echo "a";
        return view('scraping.index');
    }
}
