<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\PostStatistic;

class PostStatisticRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:post_statistic_run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post statistic run task.';

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
     * @return int
     */
    public function handle()
    {
        $statistics = PostStatistic::select(DB::raw('post_id'))->groupBy('post_id')->limit(setting('feature.item_per_page'))->get();
        if (count($statistics)) {
            foreach ($statistics as $statistic) {
                $results = PostStatistic::select(DB::raw('type, count(*) as total'))->where('post_id', $statistic->post_id)->groupBy('type')->get();
                PostStatistic::where('post_id', $statistic->post_id)->delete();
                $post = Post::findByField('id', $statistic->post_id);
                if ($post) {
                    foreach ($results as $result) {
                        switch ($result->type) {
                            case 'post_reach' :
                                $post->update([
                                    'view_count' => $post->view_count + $result->total
                                ]);
                                break;
                        }
                    }
                }
            }
        }
    }
}
