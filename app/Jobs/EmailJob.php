<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // 验证码
    protected $code;
    // 收件人
    protected $to;
    // 标题
    protected $subject;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($code, $to, $subject)
    {
        $this->code = $code;
        $this->to = $to;
        $this->subject = $subject;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mail = config('mail');
        Mail::raw('验证码：' . $this->code, function ($message) use ($mail) {
            // 发件人（你自己的邮箱和名称）
            $message->from($mail['from']['address'], $mail['from']['name']);
            // 收件人的邮箱地址
            $message->to($this->to);
            // 邮件主题
            $message->subject($this->subject);
        });
    }
}
