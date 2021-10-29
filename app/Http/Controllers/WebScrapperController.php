<?php

namespace App\Http\Controllers;

use DOMXPath;
use Illuminate\Http\Request;

use Goutte\Client;

use function PHPUnit\Framework\isNull;

class WebScrapperController extends Controller
{
    public function main(Request $request)
    {
        //$url = $request->input('url');
        // $url = "https://www.marktplaats.nl/a/auto-s/honda/m1765602260-honda-accord-2-0i-elegance-automaat.html?previousPage=lr";
        $url = "https://symfony.com/blog/";
        
        if(isNull($url)){
            $this->scrape($url);
        }

        return view('webscrapper')->with("url", $url);
    }

    private function scrape($url){
        $data = $this->getPage($url);

        $PageXPath = $this->XPathOBJ($data);


    }

    private function getPage($url){
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $data = curl_exec($curl);
        curl_close($curl);

        return $data;
    }

    private function XPathOBJ($rawData){
        $PageDom = new \DomDocument();
        libxml_use_internal_errors(true);
        $PageDom->loadHTML($rawData);

        $PageXPath = new \DOMXPath($PageDom);

        $item = $PageXPath->query('//*[@id="content_wrapper"]/div[2]/div/main/div');

        $info = $item->item(0)->nodeValue;

        return $PageXPath;
    }
}