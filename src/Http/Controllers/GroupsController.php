<?php

namespace Shemi\Translator\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Shemi\Translator\Manager;
use Shemi\Translator\Models\Translation;

class GroupsController extends Controller
{

	public function index()
	{
		$excluded = $this->manager->getConfig('exclude_groups') ?: [];
		$changedStatus = Translation::STATUS_CHANGED;

		$groups =
			Translation::select(
				'group',
				'status',
				DB::raw("sum(case when `status` = {$changedStatus} and `value` != '' then 1 else 0 end) as `changed_count`")
			)
			->whereNotIn('group', $excluded)
			->groupBy('group', 'status')
			->get()

			->transform(function($group) {
				return [
					'changed_count' => (int) $group->changed_count,
					'key' => $group->group
				];
			});

		return $this->respond($groups, [], true);
	}

	public function import(Request $request)
	{
		$replace = $request->get('replace', false);
		$counter = $this->manager->importTranslations($replace);

		return $this->respond(compact('counter', 'replace'));
	}

	public function publish($group)
	{
		$updated = $this->manager->exportTranslations($group);

		return $this->respond(compact('updated'));
	}

	public function store(Request $request)
	{

		$group = $request->input('group');
		$sub_group = null;

		if( $sub_group ) {
			$group = $group . "/" . $sub_group;
		}

		$keys = Manager::stringToKeys($request->input('keys'));
		$created = $this->manager->createKeys($group, $keys);
		$count = $created->count();

		return $this->respond(compact('created', 'count'));
	}

	public function show($group)
	{
		return view('translator::group', compact('group'));
	}

}
