<?php

namespace Shemi\Translator\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Shemi\Translator\Locale;
use Shemi\Translator\Models\Translation;

class TranslationsController extends Controller
{

	public function index($group, $sub_group = null)
	{
		/** @var Collection $allTranslations */
		$allTranslations = Translation::where('group', $group)
			->orderBy('key', 'asc')
			->get();
		
		$keyMap = $allTranslations
			->pluck('key')
			->unique()
			->values()
			->all();
		
		$translations = [];
		
		foreach( $allTranslations as $translation ) {
			$groupKey = $translation->key;
			$localKey = count(array_get($translations, $translation->key, []));
			$key = "{$groupKey}.{$localKey}";
			$translation->st_model_id = (string) $translation->st_model_id;
			
			array_set($translations, $key, $translation);
		}
		
		return $this->respond(compact('translations', 'keyMap'));
	}

	public function store(Request $request)
	{
		dd($request->all());
	}

	public function update(Request $request, $group, $sub_group = null)
	{
		if( ! ($request->has('value') && $request->has('key')) || $this->manager->isInBlackList($group) ) {
			return $this->respondBadRequest();
		}

		$translation = Translation::firstOrNew([
			'locale' => $request->input('locale'),
			'group'  => str_replace('.', '/', $group),
			'key'    => $request->input('key'),
		]);

		$translation->value = (string) $request->input('value') ?: null;
		$translation->status = Translation::STATUS_CHANGED;
		$translation->save();

		return $this->respond(compact('translation'));
	}

	public function delete(Request $request, $group)
	{
		$key = $request->input('key');
		$isGroup = $request->input('isGroup') == 'true' ? true : false;
		$operator = $isGroup ? 'LIKE' : '=';
		$search = $isGroup ? "{$key}%" : $key;
		
		$query = Translation::where('group', $group)
			->where('key', $operator, $search)
			->delete();
			
		
		return $this->respond(['count' => $query]);
	}
	
}