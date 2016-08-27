<?php

namespace Shemi\Translator\Http\Controllers;

use Illuminate\Http\Request;
use Shemi\Translator\Finder;
use Shemi\Translator\Manager;

class FinderController extends Controller
{
	private $finder;
	
	public function __construct(Manager $manager)
	{
		parent::__construct($manager);
		
		$this->finder = new Finder();
	}
	
	public function find(Request $request)
	{
		list($errors, $founds) = $this->finder->find($request->input('scopes'));
		
		return $this->respond(compact('errors', 'founds'));
	}
	
	public function hint(Request $request)
	{
		$hints = $this->finder->hint($request->input('folder'), $request->input('path'));
		
		return $this->respond($hints);
	}
	
}