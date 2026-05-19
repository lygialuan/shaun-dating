<?php


namespace Packages\ShaunSocial\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Packages\ShaunSocial\Core\Support\Facades\Mail;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type;

    protected $to;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($type, $to, $data)
    {
        $this->type = $type;
        $this->to = $to;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::sendRow($this->type, $this->to, $this->data);
    }
}
