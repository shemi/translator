<?php

namespace Shemi\Translator;

use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder as StorageFinder;

class Finder
{
	
	const TRANS_FUNCTIONS = [
		'trans',
		'trans_choice',
		'Lang::get',
		'Lang::choice',
		'Lang::trans',
		'Lang::transChoice',
		'@lang',
		'@choice',
	];
	
	const SCOPES = [
	    'resources' => [
	    	'views/**',
	    ],
	    'config' => [
	    	'*'
	    ],
		'app' => [
	        'Console/**',
	        'Events/**',
		    'Http/Controllers/**',
		    'Http/Requests/**',
		    '*',
		],
	];
	
	public $basePath;
	
	private $keys;
	
	public function __construct()
	{
		$this->basePath = base_path();
		$this->keys = collect([]);
	}
	
	private function getPattern()
	{
		$functions = implode('|', self::TRANS_FUNCTIONS);
		
		return                   // See http://regexr.com/392hu
			"[^\w|>]" .          // Must not have an alphanum or _ or > before real method
			"({$functions})" .   // Must start with one of the functions
			"\(" .               // Match opening parenthese
			"[\'\"]" .           // Match " or '
			"(" .                // Start a new group to match:
			"[a-zA-Z0-9_-]+" .   // Must start with group
			"([.][^\1)]+)+" .    // Be followed by one or more items/keys
			")" .                // Close group
			"[\'\"]" .           // Closing quote
			"[\),]";             // Close parentheses or new parameter
	}
	
	public function find($scopes)
	{
		$keys = collect([]);
		list($errors, $files) = $this->scopesToFiles($scopes);
		
		/** @var \Symfony\Component\Finder\SplFileInfo $file */
		foreach( $files as $file ) {
			if( preg_match_all("/{$this->getPattern()}/siU", $file->getContents(), $matches) ) {
				foreach( $matches[2] as $key ) {
					$keys->push($key);
				}
			}
		}
		
		return [$errors, $this->createFoundKeys($keys->unique())];
	}
	
	/**
	 *
	 * @param $scopes
	 *
	 * @return array
	 */
	private function scopesToFiles($scopes)
	{
		$scopes = collect($scopes);
		$files = collect([]);
		$errors = [];
		
		foreach($scopes as $scope) {
			$path = "{$this->basePath}/{$scope}";
			$finder = new StorageFinder();
			
			if(ends_with($scope, '**')) {
				$path = str_replace('/**', '', $path);
			}
			
			if(ends_with($scope, '*')) {
				$path = str_replace('*', '', $path);
			}
			
			try {
				$finder->in($path);
			} catch(\InvalidArgumentException $e) {
				$errors[] = $e->getMessage();
				continue;
			}
			
			if(starts_with($scope, 'resource')) {
				$finder->exclude('lang');
			}
			
			$finder->name('*.php')->files();
			
			foreach($finder as $file) {
				$files->push($file);
			}
			
		}
		
		$files = $files->unique(function($file) {
			return $file->getPath() . $file->getFilename();
		});
		
		return [$errors, $files];
	}
	
	/**
	 * @param Collection $keys
	 *
	 * @return Collection
	 */
	private function createFoundKeys(Collection $keys)
	{
		$created = collect([]);
		
		$groups = $keys->transform(function($key) {
			list($group, $key) = explode('.', $key, 2);
			
			return compact('group', 'key');
		})->groupBy('group');
		
		foreach($groups as $group => $keys) {
			$created->merge(Manager::createKeys($group, $keys->pluck('key')));
		}
		
		return $created;
	}
	
	public function hint($folder, $path, $searchMode = false)
	{
		$searchWord = "";
		$folders = collect([]);
		$localPath = trim($path, '/');
		
		$path = $this->getHintPath($folder, $path, $searchMode);
		
		$finder = new StorageFinder();
		
		if($searchMode) {
			$last = explode('/', $localPath);
			$searchWord = array_pop($last);
		}
		
		try {
			$finder->in($path)->depth('== 0')->directories();
		} catch(\InvalidArgumentException $e) {
			if(! $searchMode) {
				return $this->hint($folder, $localPath, true);
			} else {
				return $folders;
			}
		}
		
		if(ends_with($path, '*')) {
			return $folders;
		}
		
		if(! $searchMode) {
			$folders->push([
				'name' => '*',
			    'path' => $localPath ? "{$localPath}/*" : "*",
			    'description' => 'Files in this folder'
			]);
			
			$folders->push([
				'name' => '**',
				'path' => $localPath ? "{$localPath}/**" : "**",
				'description' => 'Files and sub folders in this path'
			]);
		}
		
		$folderPath = $searchMode ?
			trim(str_replace_last($searchWord, '', $localPath), '/') :
			$localPath;
		
		foreach($finder as $folder) {
			$folders->push([
				'name' => $folder->getFilename(),
				'path' => $folderPath ?
					"{$folderPath}/{$folder->getFilename()}/" :
					"{$folder->getFilename()}/",
			]);
		}
		
		if($searchMode && $searchWord) {
			$folders = $folders->filter(function($folder) use ($searchWord) {
				return starts_with($folder['name'], $searchWord);
			});
			
			$folders = $folders->values();
		}
		
		return $folders;
	}
	
	private function getHintPath($folder, $path, $searchMode = false)
	{
		$path = trim($path, '/');
		$folder = trim($folder, '/');
		$fullPath = $folder ? "{$this->basePath}/{$folder}/{$path}" : "{$this->basePath}/{$path}";
		$path = starts_with($path, trim($this->basePath, '/')) ? $path : $fullPath;
		
		if($searchMode) {
			$pathParts = explode('/', $path);
			array_pop($pathParts);
			$path = implode('/', $pathParts);
		}
		
		return $path;
	}
	
}