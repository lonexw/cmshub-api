<?php

namespace App\Console\Commands;

use App\Models\Custom;
use App\Models\User;
use App\Services\SchemaService;
use Illuminate\Console\Command;

class UpdateCustGraphqlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:update_cust_graphql';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '批量更新graphql接口文件';

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
        $value = $this->ask('确定要更新吗？y/n');
        if ($value && ($value == 'n' || $value == 'no')) {
            $this->info('已停止');
            return;
        }
        $this->info('开始执行...');
        $schemaService = new SchemaService();
        Custom::query()
            ->chunk(100, function ($customs) use ($schemaService) {
            foreach ($customs as $custom) {
                $schemaService->generateRoute($custom);
            }
        });
    }
}
