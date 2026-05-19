<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;
use Packages\ShaunSocial\Core\Models\PostQueue;
use Packages\ShaunSocial\Core\Repositories\Api\PostRepository;

class PostQueueRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:post_queue_run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post queue run task.';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    protected $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (config('shaun_core.core.queue')) {
            return;
        }

        if (config('app.debug')) {
            return;
        }

        $limit = 1;
        $posts = PostQueue::limit($limit)->get();
        $posts->each(function($postQueue){
            $this->postRepository->run_queue($postQueue);
        });
    }
}
