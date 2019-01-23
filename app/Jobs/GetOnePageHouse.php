<?php

namespace App\Jobs;

use App\Service\House;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class GetOnePageHouse implements ShouldQueue
{
    use Queueable,Dispatchable;
    protected $page;
    protected $url;
    public function __construct($page,$url)
    {
        $this->page = $page;
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('执行第 '.$this->page . '次任务，执行时间：' . date('Y-m-d H:i:s'));
        House::getOnePageHouseMultiThread(['page' => $this->page,'url' => $this->url]);
    }
}
