<?php

namespace App\Http\Controllers;

use DOMXPath;
use Illuminate\Http\Request;

use function PHPSTORM_META\type;
use function PHPUnit\Framework\isNull;

use App\Models\Advertisement;

class WebScrapperController extends Controller
{
    private $user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Brave Chrome/83.0.4103.116 Safari/537.36";
    private $proxy = "93.158.214.155:3128"; //93.158.214.155  Port:3128  HTTPS  Netherlands

    private $image = [500,500,80]; //height, width, quality
    private $imageThumbnail = [250,250,80]; //height, width, quality


    public function main(Request $request)
    {
        //$url = $request->input('url');
        $url = "https://www.marktplaats.nl/a/auto-s/honda/m1765602260-honda-accord-2-0i-elegance-automaat.html";
        
        if(isNull($url)){
            $data = $this->scrape($request, $url);
        }

        $this->insertToDBAdvertisement($data);
        var_dump($data['image']);

        return view('webscrapper')->with("url", $url)->with("data", $data);
    }

    private function insertToDBAdvertisement($data){
        if(!Advertisement::where('title', '=', $data['title'])->exists()){
            $advertisement = new Advertisement();

            $advertisement->title = $data['title'];
            $advertisement->year = $data['year'];
            $advertisement->mileage = $data['mileage'];
            $advertisement->price = $data['price'];
            $advertisement->make_model = $data['make_model'];
            $advertisement->fuel = $data['fuel'];
            $advertisement->body_type = $data['body_type'];
            $advertisement->views = $data['views'];
            $advertisement->description = $data['description'];

            $advertisement->save();
            return true;
        }
        else
            return false;
    }

    private function scrape(Request $request, $url){
        // $data = $this->getPage($url);

        if (!$request->session()->has('html_data1')){
            $filename = "C:\\Users\\RedenIce\\Desktop\\MySpace\\html.txt";

            $handle = fopen($filename, 'r') or die("can't open file");
            $data = fread($handle, filesize($filename));
            fclose($handle);
            $request->session()->put('html_data1', $data);
        }
        elseif(!$request->session()->has('html_data2')){
            $filename = "C:\\Users\\RedenIce\\Desktop\\MySpace\\html2.txt";

            $handle = fopen($filename, 'r') or die("can't open file");
            $data = fread($handle, filesize($filename));
            fclose($handle);
            $request->session()->put('html_data2', $data);
        }

        $data = $request->session()->get('html_data1');

        $PageXPath = $this->XPathOBJ($data);

        $dirtyData = $this->getDirtyDataFromPage($PageXPath);

        $dirtyData['image'] = $this->downloadImage($dirtyData);

        return $this->dataCleanUp($dirtyData);
    }

    private function dataCleanUp($dirtyData){
        $cleanData = array();

        if (array_key_exists('title', $dirtyData))
            $cleanData['title'] = $dirtyData['title'];
        else 
            $cleanData['title'] = null;

        if (array_key_exists('bouwjaar', $dirtyData))
            $cleanData['year'] = intval($dirtyData['bouwjaar']);
        else
            $cleanData['year'] = null;
        
        if (array_key_exists('kilometerstand', $dirtyData))
        {
            preg_match_all("/\d+\.\d+/", $dirtyData['kilometerstand'], $matches);
            $milage = floatval(str_replace('.', '', $matches[0][0]));
            $cleanData['mileage'] = intval(round($milage, 0));
        }
        else
            $cleanData['mileage'] = null;

        if (array_key_exists('price', $dirtyData)){
            preg_match_all("/\d+,\d+/", str_replace('.', '', $dirtyData['price']), $matches);
            $cleanData['price'] = floatval(str_replace('.', '', $matches[0][0]));;
        }
        else
            $cleanData['price'] = null;

        if (array_key_exists('merk', $dirtyData))
            $cleanData['make_model'] = $dirtyData['merk'];
        else
            $cleanData['make_model'] = null;

        if (array_key_exists('brandstof', $dirtyData))
            $cleanData['fuel'] = $dirtyData['brandstof'];
        else
            $cleanData['fuel'] = null;
    
        if (array_key_exists('carrosserie', $dirtyData))
            $cleanData['body_type'] = $dirtyData['carrosserie'];
        else
            $cleanData['body_type'] = null;

        if (array_key_exists('views', $dirtyData))
            $cleanData['views'] = $dirtyData['views'];
        else
            $cleanData['views'] = null;

        if (array_key_exists('description', $dirtyData))
            $cleanData['description'] = str_replace("\r\n", "", $dirtyData['description']);
        else
            $cleanData['description'] = null;

        if (array_key_exists('image', $dirtyData))
            $cleanData['image'] = $dirtyData['image'];
        else
            $cleanData['image'] = null;
        
        return $cleanData;
    }

    private function getDirtyDataFromPage($PageXPath){
        $data = array();

        #title
        $query = '//*[@id="title"]';
        $data['title'] = $PageXPath->query($query)[0]->nodeValue;

        #views
        $query = '//*[@id="content"]/section/section[1]/section[1]/section[1]/div[1]/span[1]/span[3]';
        $data['views'] = $PageXPath->query($query)[0]->nodeValue;

        #price
        $query = '//*[@id="vip-ad-price-container"]/span';
        $data['price'] = $PageXPath->query($query)[0]->nodeValue;

        #image URL
        $query = '//*[@id="vip-gallery-thumbs"]//img/@src';
        $item = "https:" . $PageXPath->query($query)[0]->value;
        $data['imageURL'] = $item;

        #identify what kind of advertisement it is
        $query = '//*[@id="content"]/section/section[1]/section[4]/div[2]/div[.]/h2';
        $advertisementType = $PageXPath->query($query)[0]->nodeValue;
        preg_match_all('/\S+/', $advertisementType, $type);

        // for car
        if (strtolower($type[0][0]) == "samenvatting"){
            $data = array_merge($data, $this->extractCar($PageXPath));
        }
        // for camper
        elseif(strtolower($type[0][0]) == "kenmerken"){
            $data = array_merge($data, $this->extractCamper($PageXPath));

        }

        #item description
        $query = '//*[@id="vip-ad-description"]/text()';
        $item = $PageXPath->query($query);
        $desc = "";
        foreach ($item as $node) {
            $desc = $desc . $node->nodeValue . "\n";
        }
        $data['description'] = $desc;

        return $data;
    }

    private function extractCar($PageXPath){
        $data = array();
        $query = '//*[@id="car-attributes"]/div[1]/div';
        $itemsCount = $PageXPath->query($query);

        for ($i=1; $i < count($itemsCount); $i++) { 
            $new_query = '//*[@id="car-attributes"]/div[1]/div[' . $i . ']';
            $info = $PageXPath->query($new_query);
            $item = explode(':', $info[0]->nodeValue);

            if(preg_match_all("/.erk/", $item[0], $value)){
                $data[strtolower($value[0][0])] = trim(str_replace("\r\n", "", $item[1]));
            }
            else{
                $data[strtolower(trim(str_replace("\r\n", "", $item[0])))] = trim(str_replace("\r\n", "", $item[1]));
            }
        }

        return $data;
    }

    private function extractCamper($PageXPath){
        $data = array();
        $nameQuery = '//*[@class="name"]';
        $nameData = $PageXPath->query($nameQuery);
        
        $valueQuery = '//*[@class="value"]';
        $valueData = $PageXPath->query($valueQuery);

        for ($i=0; $i < count($nameData); $i++) { 
            $data[strtolower($nameData->item($i)->nodeValue)] = $valueData->item($i)->nodeValue;
        }

        return $data;
    }

    private function getPage($url){
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_PROXY, $this->proxy);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->user_agent);

        $data = curl_exec($curl);
        curl_close($curl);

        return $data;
    }

    private function downloadImage($data){
        $title = str_replace('.','', $data['title']);
        $extension = explode(".", $data['imageURL']);
        $fileName = "" . $title . "." . strtolower($extension[count($extension)-1]);

        if(!Advertisement::where('title', '=', $data['title'])->exists()){
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $data['imageURL']);
            curl_setopt($curl, CURLOPT_PROXY, $this->proxy);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_USERAGENT, $this->user_agent);

            $image = curl_exec($curl);
            curl_close($curl);

            $path = "images/AdvertisementThumbnails/";

            file_put_contents($path . $fileName, $image);

            $this->rescaleImage($fileName, $path, $this->image);
            $this->rescaleImage($fileName, $path, $this->imageThumbnail, TRUE);
        }

        return $fileName;
    }

    private function rescaleImage($fileName, $path, $params, $thumbnail = FALSE){
        $newWidth = $params[0];
        $newHeight = $params[1];
        $quality = $params[2];
        $file = $path . $fileName;

        $info = getimagesize($file);

        if($info['mime']=='image/png') { 
            $srcImg = imagecreatefrompng($file);
        }
        if($info['mime']=='image/jpg' || $info['mime']=='image/jpeg' || $info['mime']=='image/pjpeg') {
            $srcImg = imagecreatefromjpeg($file);
        }   

        $oldX = imageSX($srcImg);
        $oldY = imageSY($srcImg);

        if($oldX > $oldY) 
        {
            $imgW = $newWidth;
            $imgH = $oldY*($newHeight / $oldX);
        }
        else if($oldX < $oldY) 
        {
            $imgW = $oldX*($newWidth / $oldY);
            $imgH = $newHeight;
        }
        elseif($oldX == $oldY) 
        {
            $imgW = $newWidth;
            $imgH = $newHeight;
        }

        $destImg = ImageCreateTrueColor($imgW,$imgH);

        imagecopyresampled($destImg, $srcImg, 0, 0, 0, 0, $imgW, $imgH, $oldX, $oldY); 

        // New save location
        if($thumbnail)
            $newPath = "" . $path . "Thumbnail " . $fileName ;
        else
            $newPath = "" . $path . $fileName;

        if($info['mime']=='image/png') {
            $result = imagepng($destImg, $newPath, $quality);
        }
        if($info['mime']=='image/jpg' || $info['mime']=='image/jpeg' || $info['mime']=='image/pjpeg') {
            $result = imagejpeg($destImg, $newPath, $quality);
        }

        imagedestroy($destImg); 
        imagedestroy($srcImg);

        return $result;
    }

    private function XPathOBJ($rawData){
        $PageDom = new \DomDocument();
        libxml_use_internal_errors(true);
        $PageDom->loadHTML($rawData);

        $PageXPath = new \DOMXPath($PageDom);

        return $PageXPath;
    }
}