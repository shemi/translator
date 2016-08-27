<?php

namespace Shemi\Translator\Console;

use Illuminate\Console\Command;
use Shemi\Translator\Manager;

class ResetCommand extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'translations:reset';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Delete all translations from the database';

	/** @var \Shemi\Translator\Manager */
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
		$this->manager->truncateTranslations();
		$this->info("All translations are deleted");
	}


}
