<?php

namespace horsefly\Console\Commands;

use Illuminate\Console\Command;
use horsefly\Library\UpgradeManager;

class UpgradeTranslation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update translation files to make those up-to-date with the default EN language';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $manager = new UpgradeManager();
        $files = ['auth', 'messages', 'pagination', 'passwords', 'validation'];
        foreach($files as $f) {
            $path = base_path("resources/lang/en/$f.php");
            $manager->upgradeLanguageFiles($path);
        }
    }
}
