<?php

namespace Shemi\Translator\Console;

use Shemi\Translator\Manager;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class FindCommand extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'translations:find';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Find translations in php/twig files';

	/** @var  \Shemi\Translator\Manager */
	protected $manager;

	public function __construct(Manager $manager)
	{
		$this->manager = $manager;
		parent::__construct();
	}


	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$counter = $this->manager->findTranslations();
		$this->info('Done importing, processed ' . $counter . ' items!');

	}


}