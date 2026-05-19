<?php


namespace Packages\ShaunSocial\Story\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Story\Models\StoryItem;
use Packages\ShaunSocial\Story\Repositories\Api\StoryRepository;

class StoryItemRemove extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_story:story_item_remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove old story item task.';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    protected $storyRepository = null;

    public function __construct(StoryRepository $storyRepository)
    {
        $this->storyRepository = $storyRepository;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $items = StoryItem::where('created_at', '<', now()->subHours(setting('story.time_delete_story')))->where('story_id', '!=', 0)->select(DB::raw('min(id) as id'))->groupBy('story_id')->limit(setting('feature.item_per_page'))->get();
        $items->each(function($item){
            $this->storyRepository->delete($item->id, true);
        });
    }
}
