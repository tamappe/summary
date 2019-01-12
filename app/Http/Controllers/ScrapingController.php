<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Goutte\Client;
use App\Models\Entry;
use DOMDocument;

class ScrapingController extends Controller
{

    // RSSサイト: http://web-terminal.blogspot.com/2013/12/2chrss.html
    // http://www.netc.ne.jp/news/rss_list.html
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
            '働くモノニュース' => 'http://workingnews.blog117.fc2.com/?xml',
            '【2ch】ニュー速クオリティ' => 'http://news4vip.livedoor.biz/index.rdf',
            'ぶる速' => 'http://burusoku-vip.com/index.rdf'
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

    private function get_imge_source_atom($item) {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($item);
        libxml_clear_errors();
        // item(index)は何番のimgを取得するかを表す
        if (!is_null($dom->getElementsByTagName('img')->item(0))) {
            return $dom->getElementsByTagName('img')->item(0)->getAttribute('src');
        } else {
            return "";
        }
    }

    public function parse_xml() {
        $path = $this->scraping_rss_sites()['【2ch】ニュー速クオリティ'];
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

            $img = $this->get_imge_source_atom((string)$item->content);
            $x['img'] = $img  . '<br>';
            $data[] = $x;
            var_dump($x);
        }

        return view('scraping.index');
    }

    public function save_xml() {
        foreach ($this->scraping_rss_sites() as $key => $value) {
            $path = $value;
            // ref: https://www.softel.co.jp/blogs/tech/archives/4105
            $rss = simplexml_load_file($path);
            $data = array();

            $blog_title = $rss->channel->title;
            $blog = new Blog();
            if (!$blog->entryExists($blog_title)) {
                $blog->title = $blog_title;
                $blog->save();
                echo 'ブログタイトルの保存成功';
            } else {
                echo 'すでに作成しています';
            }
            foreach ($rss->item as $item) {
                $x = array();
                $x['link'] = (string)$item->link;
                $x['title'] = (string)$item->title;
                $x['description'] = (string)$item->description;
                $x['pubDate'] = (string)$item->children('http://purl.org/dc/elements/1.1/')->date;

                $img = $this->get_imge_source($item);
                $x['img'] = $img;
                $data[] = $x;

                $entry = new Entry();
                if (!$entry->entryExists($x['link'])) {
                    $entry->title = $x['link'];
                    $entry->link_url = $x['link'];
                    $entry->image_url = $x['img'];
                    $entry->published = $x['pubDate'];
                    $blog_info = Blog::where('title', $blog_title)->first();
                    $entry->blog_id = $blog_info->id;
                    $entry->save();
                    echo '記事データの保存成功';
                } else {
                    echo 'すでに作成しています';
                }
            }
            sleep(5);
        }

        return view('scraping.index');
    }

    public function save_xml_atom() {
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

            $img = $this->get_imge_source_atom((string)$item->content);
            $x['img'] = $img  . '<br>';
            $data[] = $x;
            var_dump($x);
        }

        return view('scraping.index');
    }

    public function form()
    {
        return view('scraping.form');
    }

    public function result_rss(Request $request)
    {

        if (!is_null($request->rss)) {
            $this->confirm_rss($request->rss);
        }
        return view('scraping.index');
    }

    public function result_atom(Request $request)
    {

        if (!is_null($request->atom)) {
            $this->confirm_atom($request->atom);
        }
        return view('scraping.index');
    }

    private function confirm_rss($path) {
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
    }

    private function confirm_atom($path) {
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

            $img = $this->get_imge_source_atom((string)$item->content);
            $x['img'] = $img  . '<br>';
            $data[] = $x;
            var_dump($x);
        }
    }
}
