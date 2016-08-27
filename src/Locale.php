<?php

namespace Shemi\Translator;


use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;

class Locale
{

	public $isRtl;

	public $name;

	public $code;


	/**
	 * get locale
	 *
	 * @param $code
	 *
	 * @return $this
	 */
	public static function get($code)
	{
		return (new static)->set($code);
	}

	/**
	 * sets a locale
	 *
	 * @param $code
	 *
	 * @return $this
	 */
	private function set($code)
	{
		$locale = $this
			->getLocales()
			->get($code, ["code" => $code, "name" => $code, "rtl" => false]);

		$this->code = $locale['code'];
		$this->isRtl = $locale['rtl'];
		$this->name = $locale['name'];

		return $this;
	}

	public static function appLocales($json = false)
	{
		$locales = collect([]);
		$appLocal = config('app.locale');

		$langPath = resource_path('lang');

		$folders = (new Finder())
			->directories()
			->depth('== 0')
			->in($langPath)
			->sort(function($a, $b) use ($appLocal) {

				if($a->getFilename() == $appLocal || $b->getFilename() == $appLocal) {
					return $a->getFilename() == $appLocal ? 0 : 1;
				}

				return strcmp($a->getRealpath(), $b->getRealpath());
			});

		foreach($folders as $folder) {
			$locales->push(
				self::get($folder->getFilename())
			);
		}

		return $json ? json_encode($locales, JSON_UNESCAPED_UNICODE) : $locales;
	}

	/**
	 * get all available locales as collection
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public static function all()
	{
		return (new static)
			->getLocales()
			->values()
			->sortBy('code');
	}


	/**
	 * all available locales
	 *
	 * @return Collection
	 */
	private static function getLocales()
	{
		return collect([
			"af"  => ["code" => "af", "name" => "Afrikaans", "rtl" => false],
			"ar"  => ["code" => "ar", "name" => "العربية", "rtl" => true],
			"az"  => ["code" => "az", "name" => "Azərbaycan", "rtl" => false],
			"be"  => ["code" => "be", "name" => "Беларуская мова", "rtl" => false],
			"bg"  => ["code" => "bg", "name" => "български", "rtl" => false],
			"bs"  => ["code" => "bs", "name" => "Bosanski", "rtl" => false],
			"ca"  => ["code" => "ca", "name" => "Català", "rtl" => false],
			"cs"  => ["code" => "cs", "name" => "Čeština", "rtl" => false],
			"cy"  => ["code" => "cy", "name" => "Cymraeg", "rtl" => false],
			"da"  => ["code" => "da", "name" => "Dansk", "rtl" => false],
			"de"  => ["code" => "de", "name" => "Deutsch", "rtl" => false],
			"el"  => ["code" => "el", "name" => "Ελληνικά", "rtl" => false],
			"en"  => ["code" => "en", "name" => "English", "rtl" => false],
			"eo"  => ["code" => "eo", "name" => "Esperanto", "rtl" => false],
			"es"  => ["code" => "es", "name" => "Español", "rtl" => false],
			"et"  => ["code" => "et", "name" => "Eesti", "rtl" => false],
			"eu"  => ["code" => "eu", "name" => "Euskara", "rtl" => false],
			"fa"  => ["code" => "fa", "name" => "فارسی", "rtl" => true],
			"fi"  => ["code" => "fi", "name" => "Suomi", "rtl" => false],
			"fo"  => ["code" => "fo", "name" => "Føroyskt", "rtl" => false],
			"fr"  => ["code" => "fr", "name" => "Français", "rtl" => false],
			"fy"  => ["code" => "fy", "name" => "Frysk", "rtl" => false],
			"gd"  => ["code" => "gd", "name" => "Gàidhlig", "rtl" => false],
			"gl"  => ["code" => "gl", "name" => "Galego", "rtl" => false],
			"haz" => ["code" => "haz", "name" => "هزاره گی", "rtl" => true],
			"he"  => ["code" => "he", "name" => "עברית", "rtl" => true],
			"hi"  => ["code" => "hi", "name" => "हिन्दी", "rtl" => false],
			"hr"  => ["code" => "hr", "name" => "Hrvatski", "rtl" => false],
			"hu"  => ["code" => "hu", "name" => "Magyar", "rtl" => false],
			"id"  => ["code" => "id", "name" => "Bahasa Indonesia", "rtl" => false],
			"is"  => ["code" => "is", "name" => "Íslenska", "rtl" => false],
			"it"  => ["code" => "it", "name" => "Italiano", "rtl" => false],
			"ja"  => ["code" => "ja", "name" => "日本語", "rtl" => false],
			"jv"  => ["code" => "jv", "name" => "Basa Jawa", "rtl" => false],
			"ka"  => ["code" => "ka", "name" => "ქართული", "rtl" => false],
			"kk"  => ["code" => "kk", "name" => "Қазақ тілі", "rtl" => false],
			"ko"  => ["code" => "ko", "name" => "한국어", "rtl" => false],
			"ku"  => ["code" => "ku", "name" => "کوردی", "rtl" => true],
			"lo"  => ["code" => "lo", "name" => "ພາສາລາວ", "rtl" => false],
			"lt"  => ["code" => "lt", "name" => "Lietuviškai", "rtl" => false],
			"lv"  => ["code" => "lv", "name" => "Latviešu valoda", "rtl" => false],
			"mk"  => ["code" => "mk", "name" => "македонски јазик", "rtl" => false],
			"mn"  => ["code" => "mn", "name" => "Монгол хэл", "rtl" => false],
			"ms"  => ["code" => "ms", "name" => "Bahasa Melayu", "rtl" => false],
			"my"  => ["code" => "my", "name" => "ဗမာစာ", "rtl" => false],
			"nb"  => ["code" => "nb", "name" => "Norsk Bokmål", "rtl" => false],
			"ne"  => ["code" => "ne", "name" => "नेपाली", "rtl" => false],
			"nl"  => ["code" => "nl", "name" => "Nederlands", "rtl" => false],
			"nn"  => ["code" => "nn", "name" => "Norsk Nynorsk", "rtl" => false],
			"pl"  => ["code" => "pl", "name" => "Polski", "rtl" => false],
			"pt"  => ["code" => "pt", "name" => "Português", "rtl" => false],
			"ro"  => ["code" => "ro", "name" => "Română", "rtl" => false],
			"ru"  => ["code" => "ru", "name" => "Русский", "rtl" => false],
			"si"  => ["code" => "si", "name" => "සිංහල", "rtl" => false],
			"sk"  => ["code" => "sk", "name" => "Slovenčina", "rtl" => false],
			"sl"  => ["code" => "sl", "name" => "Slovenščina", "rtl" => false],
			"so"  => ["code" => "so", "name" => "Af-Soomaali", "rtl" => false],
			"sq"  => ["code" => "sq", "name" => "Shqip", "rtl" => false],
			"sr"  => ["code" => "sr", "name" => "Српски језик", "rtl" => false],
			"su"  => ["code" => "su", "name" => "Basa Sunda", "rtl" => false],
			"sv"  => ["code" => "sv", "name" => "Svenska", "rtl" => false],
			"ta"  => ["code" => "ta", "name" => "தமிழ்", "rtl" => false],
			"th"  => ["code" => "th", "name" => "ไทย", "rtl" => false],
			"tr"  => ["code" => "tr", "name" => "Türkçe", "rtl" => false],
			"ug"  => ["code" => "ug", "name" => "Uyƣurqə", "rtl" => false],
			"uk"  => ["code" => "uk", "name" => "Українська", "rtl" => false],
			"ur"  => ["code" => "ur", "name" => "اردو", "rtl" => true],
			"uz"  => ["code" => "uz", "name" => "Oʻzbek", "rtl" => false],
			"vec" => ["code" => "vec", "name" => "Vèneto", "rtl" => false],
			"vi"  => ["code" => "vi", "name" => "Tiếng Việt", "rtl" => false],
			"zh"  => ["code" => "zh", "name" => "中文 (中国)", "rtl" => false],
		]);
	}

}