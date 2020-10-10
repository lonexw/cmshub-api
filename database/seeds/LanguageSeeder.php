<?php
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Language::query()->firstOrCreate([
            'code' => 'CN',
            'name' => '中文(简)',
            'is_default' => 1,
        ]);
        \App\Models\Language::query()->firstOrCreate([
            'code' => 'EN',
            'name' => '英文',
            'is_default' => 0,
        ]);
    }
}
