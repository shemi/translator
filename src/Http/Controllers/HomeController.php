<?php

namespace Shemi\Translator\Http\Controllers;

use Shemi\Translator\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{

	public function index()
	{
		return view('translator::home');
	}

}