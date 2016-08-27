<?php

namespace Shemi\Translator;

use Aza\Components\PhpGen\PhpGen;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Events\Dispatcher;
use Shemi\Translator\Models\Translation;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;

class Manager
{

	const CONFIG_KEY = 'translator';
	
	/** @var \Illuminate\Foundation\Application */
	protected $app;
	/** @var \Illuminate\Filesystem\Filesystem */
	protected $files;
	/** @var \Illuminate\Events\Dispatcher */
	protected $events;

	private $config;

	public function __construct(Application $app, Filesystem $files, Dispatcher $events)
	{
		$this->app = $app;
		$this->files = $files;
		$this->events = $events;
		$this->config = config(self::CONFIG_KEY);
	}

	public static function createKeys($group, Collection $keys)
	{
		$createdKeys = collect([]);
		
		foreach($keys as $key) {
			if($created = static::missingKey($group, $key)) {
				$createdKeys->push($created->first());
			}
		}
		
		return $createdKeys;
	}
	
	public static function missingKey($group, $key)
	{
		$translations = collect([]);
		
		if( static::isInBlackList($group) ) {
			return false;
		}

		foreach(Locale::appLocales() as $locale) {
			$localeTrans = Translation::firstOrCreate([
				'locale' => $locale->code,
				'group'  => $group,
				'key'    => $key,
			    'status' => Translation::STATUS_CHANGED
			]);
			
			$translations->push($localeTrans);
		}
		
		return $translations;
	}

	public function importTranslations($replace = false)
	{
		$counter = 0;

		foreach( $this->files->directories($this->app->langPath()) as $langPath ) {
			$locale = basename($langPath);

			foreach( $this->files->allfiles($langPath) as $file ) {

				$info = pathinfo($file);
				$group = $info['filename'];

				if( in_array($group, $this->config['exclude_groups']) ) {
					continue;
				}

				$subLangPath = str_replace("{$langPath}\\", "", $info['dirname']);

				if( $subLangPath != $langPath ) {
					$group = $subLangPath . "/" . $group;
				}

				$translations = \Lang::getLoader()->load($locale, $group);

				if( ! $translations && ! is_array($translations) ) {
					continue;
				}

				foreach( array_dot($translations) as $key => $value ) {
					// process only string values
					if( is_array($value) ) {
						continue;
					}

					$value = (string) $value;

					$translation = Translation::firstOrNew([
						'locale' => $locale,
						'group'  => $group,
						'key'    => $key,
					]);

					// Check if the database is different then the files
					$newStatus = $translation->value === $value ? Translation::STATUS_SAVED :Translation::STATUS_CHANGED;

					if( $newStatus !== (int) $translation->status ) {
						$translation->status = $newStatus;
					}

					// Only replace when empty, or explicitly told so
					if( $replace || ! $translation->value ) {
						$translation->value = $value;
					}

					$translation->save();

					$counter++;
				}

			}
		}

		return $counter;
	}

	public function exportTranslations($group)
	{
		if( in_array($group, $this->config['exclude_groups']) ) {
			return false;
		}

		if( $group == '*' ) {
			return $this->exportAllTranslations();
		}
		
		$translationsQry = Translation::where('group', $group)->whereNotNull('value');
		$translationsToUpdate = $translationsQry->get();
		$tree = $this->makeTree($translationsToUpdate);
		
		$phpGen = PhpGen::instance();
		$langPath = $this->app->langPath();
		
		foreach( $tree as $locale => $groups ) {
			
			if( ! isset($groups[$group])) {
				continue;
			}

			$translations = $groups[$group];
			
			$path = "{$langPath}/{$locale}/{$group}.php";
			
			$translations = $phpGen->getCode($translations);
			
			$output = "<?php\n\n";
			$output .= "return ";
			$output .= "{$translations}\n";
			
			$this->files->put($path, $output);
		}

		$translationsQry->update(['status' => Translation::STATUS_SAVED]);
		
		return $translationsToUpdate->pluck('key');
	}

	public function exportAllTranslations()
	{
		$groups = Translation::whereNotNull('value')->select(DB::raw('DISTINCT `group`'))->get('group');

		foreach( $groups as $group ) {
			$this->exportTranslations($group->group);
		}
		
		return true;
	}

	public function cleanTranslations()
	{
		Translation::whereNull('value')->delete();
	}

	public function truncateTranslations()
	{
		Translation::truncate();
	}

	protected function makeTree($translations)
	{
		$array = [];
		foreach( $translations as $translation ) {
			array_set($array[ $translation->locale ][ $translation->group ], $translation->key, $translation->value);
		}

		return $array;
	}

	public static function getConfig($key = null, $default = null)
	{
		$configKey = self::CONFIG_KEY;
		
		if( $key == null ) {
			return config($configKey, $default);
		} else {
			return config("{$configKey}.{$key}", $default);
		}
	}

	public static function isInBlackList($group)
	{
		return in_array($group, static::getConfig('exclude_groups'));
	}
	
	/**
	 * @param $str
	 *
	 * @return Collection
	 */
	public static function stringToKeys($str)
	{
		$re = "/^(.*[:])(.+)$/";
		$keys = explode("\n", $str);
		$newKeys = collect([]);
		
		foreach($keys as $key) {
			$key = trim($key);
			
			if(! $key) {
				continue;
			}
			
			if(! preg_match($re, $key, $matches)) {
				$newKeys->push($key);
				
				continue;
			}
			
			$key = trim($matches[1], " :");
			$subKeys = explode(',', $matches[2]);
			
			
			if(! $key || empty($subKeys)) {
				continue;
			}
			
			foreach($subKeys as $subKey) {
				$subKey = trim($subKey);
				
				if(! $subKey) {
					continue;
				}
				
				$newKeys->push("{$key}.{$subKey}");
				
			}
			
		}
		
		return $newKeys->unique();
	}
	
}
