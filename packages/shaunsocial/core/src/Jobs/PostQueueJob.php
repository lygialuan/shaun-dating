<?php


namespace Packages\ShaunSocial\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Packages\ShaunSocial\Core\Repositories\Api\PostRepository;

class PostQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $postQueue;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($postQueue)
    {
        $this->postQueue = $postQueue;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PostRepository $postRepository)
    {
        $postRepository->run_queue($this->postQueue);
    }
}
