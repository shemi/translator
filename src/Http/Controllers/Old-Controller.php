<?php

namespace Shemi\Translator\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Shemi\Translator\Manager;
use Shemi\Translator\Models\Translation;
use Illuminate\Support\Collection;

class theController extends BaseController
{
	/** @var \Shemi\Translator\Manager */
	protected $manager;

	public function __construct(Manager $manager)
	{
		$this->manager = $manager;

		$appName = config('app.name', env('APP_NAME'));
		$appName = $appName ? "The {$appName} Translator" :"Translator";
		view()->share('appName', $appName);
	}

	public function getIndex($group = null)
	{
		$locales = $this->loadLocales();
		$groups = Translation::groupBy('group');

		$excludedGroups = $this->manager->getConfig('exclude_groups');

		if( $excludedGroups ) {
			$groups->whereNotIn('group', $excludedGroups);
		}

		$groups = $groups->lists('group', 'group');

		if( $groups instanceof Collection ) {
			$groups = $groups->pl->all();
		}

		$groups = ['' => 'Choose a group'] + $groups;
		$numChanged = Translation::where('group', $group)->where('status', Translation::STATUS_CHANGED)->count();

		$allTranslations = Translation::where('group', $group)->orderBy('key', 'asc')->get();
		$numTranslations = count($allTranslations);
		$translations = [];

		foreach( $allTranslations as $translation ) {
			$translations[ $translation->key ][ $translation->locale ] = $translation;
		}

		return view('translator::index')->with('translations', $translations)->with('locales', $locales)->with('groups', $groups)->with('group', $group)->with('numTranslations', $numTranslations)->with('numChanged', $numChanged)->with('editUrl', action('\Shemi\Translator\Http\Controllers\Controller@postEdit', [$group]))->with('deleteEnabled', $this->manager->getConfig('delete_enabled'));
	}

	public function getView($group, $sub_group = null)
	{
		if( $sub_group ) {
			return $this->getIndex($group . '/' . $sub_group);
		}

		return $this->getIndex($group);
	}

	protected function loadLocales()
	{
		//Set the default locale as the first one.
		$locales = Translation::groupBy('locale')->lists('locale');

		if( $locales instanceof Collection ) {
			$locales = $locales->all();
		}

		$locales = array_merge([config('app.locale')], $locales);

		return array_unique($locales);
	}

	public function postAdd(Request $request, $group, $sub_group = null)
	{
		$keys = explode("\n", $request->get('keys'));

		if( $sub_group ) {
			$group = $group . "/" . $sub_group;
		}

		foreach( $keys as $key ) {
			$key = trim($key);
			
			if( $group && $key ) {
				$this->manager->missingKey('*', $group, $key);
			}
		}

		return redirect()->back();
	}

	public function postEdit(Request $request, $group, $sub_group = null)
	{
		if( in_array($group, $this->manager->getConfig('exclude_groups')) ) {
			return false;
		}

		$name = $request->get('name');
		$value = $request->get('value');

		list( $locale, $key ) = explode('|', $name, 2);
		$translation = Translation::firstOrNew([
			'locale' => $locale,
			'group'  => $sub_group ? $group . "/" . $sub_group :$group,
			'key'    => $key,
		]);

		$translation->value = (string) $value ?:null;
		$translation->status = Translation::STATUS_CHANGED;
		$translation->save();

		return ['status' => 'ok'];
	}

	public function postDelete($group, $key, $sub_group = null)
	{
		if( in_array($group, $this->manager->getConfig('exclude_groups')) && ! $this->manager->getConfig('delete_enabled') ) {
			return;
		}

		Translation::where('group', $group)->where('key', $key)->delete();

		return ['status' => 'ok'];
	}

	public function postImport(Request $request)
	{
		$replace = $request->get('replace', false);
		$counter = $this->manager->importTranslations($replace);

		return ['status' => 'ok', 'counter' => $counter];
	}

	public function postFind()
	{
		$numFound = $this->manager->findTranslations();

		return ['status' => 'ok', 'counter' => (int) $numFound];
	}

	public function postPublish($group, $sub_group = null)
	{
		if( $sub_group ) {
			$this->manager->exportTranslations($group . '/' . $sub_group);
		} else {
			$this->manager->exportTranslations($group);
		}

		return ['status' => 'ok'];
	}
}
