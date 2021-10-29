<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Goutte\Client;

class WebScrapperController extends Controller
{
    public function main()
    {
        return view('webscrapper');
    }
}
