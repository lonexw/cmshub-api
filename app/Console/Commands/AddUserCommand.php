<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AddUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:add_user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '添加用户';

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
        $name = $this->ask('请输入用户姓名（可不填）');
        $email = $this->askValue('请输入用户邮箱（必填）');
        if (!$email) {
            $this->warn('请输入用户邮箱');
        }
        $user = User::where('email', $email)->first();
        if ($user) {
            $this->warn('邮箱已存在，请更换');
            return;
        }
        $password = $this->askSecretValue('请输入密码（必填）');
        if (!$password) {
            $this->warn('请输入密码');
            return;
        }
        $confirmPassword = $this->askSecretValue('请输入确认密码（必填）');
        if (!$confirmPassword) {
            $this->warn('请输入确认密码');
            return;
        }
        if ($password != $confirmPassword) {
            $this->warn('两次密码输入不一致');
            return;
        }
        $user = new User();
        $user->name = $name ?? '';
        $user->email = $email;
        $user->password = bcrypt($password);
        $user->save();
    }

    public function askValue($message)
    {
        $value = $this->ask($message);
        if (!$value) {
            return $this->askValue($message);
        }
        return $value;
    }

    public function askSecretValue($message)
    {
        $value = $this->secret($message);
        if (!$value) {
            return $this->askSecretValue($message);
        }
        return $value;
    }
}
