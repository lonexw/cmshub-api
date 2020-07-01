<?php

namespace App\Console\Commands;

use App\Models\Custom;
use App\Models\Project;
use App\Models\Token;
use Illuminate\Console\Command;

class AddTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:add_token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '添加token';

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
        $projectId = $this->askValue('请输入项目ID');
        if (!$projectId) {
            $this->warn('请输入项目ID');
            return;
        }
        $project = Project::query()
            ->find($projectId);
        if (!$project) {
            $this->warn('项目不存在');
            return;
        }

        $customIds = Custom::query()
            ->where('project_id', $project->id)
            ->pluck('id')
            ->toArray();
        if (count($customIds) == 0) {
            $this->warn('项目中尚未添加表结构');
            return;
        }
        $description = $this->ask('请输入token描述(可不填)');
        $scopes = ['query'];
        $tokenStr = (string)$projectId . (string)time();
        $token = new Token();
        $token->project_id = $projectId;
        $token->token = $tokenStr;
        $token->custom_ids = $customIds;
        $token->scopes = $scopes;
        $token->description = $description;
        $token->save();
        $token->customs()->sync($customIds);
        $this->info('token:' . $tokenStr);
    }

    public function askValue($message)
    {
        $value = $this->ask($message);
        if (!$value) {
            return $this->askValue($message);
        }
        return $value;
    }
}
