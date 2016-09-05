<?php

require_once 'vendor/autoload.php';
use Symfony\Component\DomCrawler\Crawler;

if(isset($_POST['username']))
    $username = $_POST['username'];

if (empty($username)) {
    $data['id'] = null;
    $data['title'] = null;
    $data['desc'] = null;
    $data['poster'] = null;
} else {
    $anime = getRandom();
    if($anime['id'] != null) {
        $data['id'] = $anime['id'];
        $data['title'] = $anime['title'];
        $data['desc'] = $anime['desc'];
        $data['poster'] = $anime['poster'];
    } else {
        $data['id'] = null;
        $data['title'] = null;
        $data['desc'] = null;
        $data['poster'] = null;
    }
}

header("Content-Type: application/json");
echo json_encode($data);

function getData() {
    global $username;
    $content = file_get_contents('http://myanimelist.net/malappinfo.php?u='. $username .'&status=all&type=anime');
    return $content;
}

function parseXML() {
    $data = getData();

    $crawler = new Crawler();
    $crawler->addXmlContent($data);

    $result['id'] = array();
    $result['title'] = array();

    if($crawler->filterXPath('//myanimelist/error')->count() == 0) {
        $ids = $crawler->filterXPath('//myanimelist/anime[not(series_status=3) and my_status=6]/series_animedb_id/text()');
        $titles = $crawler->filterXPath('//myanimelist/anime[not(series_status=3) and my_status=6]/series_title/text()');

        $i = 0;
        foreach ($ids as $id) {
            $result['id'][$i] = $id->nodeValue;
            $result['title'][$i] = $titles->getNode($i)->nodeValue;
            $i++;
        }
    }

    return $result;
}

function getRandom() {
    $data = parseXML();
    $entries = count($data['id']);

    if($entries < 1) {
        $entry['id'] = null;
        $entry['title'] = null;
    } else {
        $random = mt_rand(0, $entries - 1);
        $entry = array();
        $entry['id'] = $data['id'][$random];
        $entry['title'] = $data['title'][$random];

        $descPoster = getDescAndPoster($entry['id']);

        $entry['desc'] = $descPoster['desc'];
        $entry['poster'] = $descPoster['poster'];
    }

    return $entry;
}

function getDescAndPoster($id) {
    $descPoster = array();

    $content = file_get_contents('http://myanimelist.net/anime/' . $id);

    $crawler = new Crawler();
    $crawler->addHtmlContent($content, 'UTF-8');

    $descPoster['poster'] = $crawler->filterXPath("//meta[@property='og:image']")->attr("content");
    $descPoster['desc'] = $crawler->filterXPath("//span[@itemprop='description']")->html();

    return $descPoster;
}