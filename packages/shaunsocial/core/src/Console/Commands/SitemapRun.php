<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;
use Packages\ShaunSocial\Core\Enum\SitemapStep;
use Packages\ShaunSocial\Core\Models\Key;
use Packages\ShaunSocial\Core\Models\Page;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\Sitemap;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Group\Enum\GroupStatus;
use Packages\ShaunSocial\Group\Models\Group;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Models\StorageFile;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Symfony\Component\HttpFoundation\File\File as FileCore;

class SitemapRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:sitemap_run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Task run sitemap.';

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
        if (! setting('sitemap.enable')) {
            return;
        }
        $processTime = Key::getValue('sitemap_process_time');
        $processStep = Key::getValue('sitemap_process_step');
        $config = convertJsonFromString(Key::getValue('sitemap_process_config'));
        $processFiles = convertJsonFromString(Key::getValue('sitemap_process_files'));
        
        if (! $processTime) {
            $processTime = now()->timestamp;
            Key::setValue('sitemap_process_time', $processTime);
        }

        if ($processTime <= now()->timestamp && ! $processStep) {
            Key::setValue('sitemap_process_step', SitemapStep::GET_URL->value);
            Sitemap::truncate();
            Key::setValue('sitemap_process_config', '');
        }

        switch ($processStep) {
            case SitemapStep::GET_URL->value:
                $check = true;
                //pages
                if (empty($config['pages'])) {
                    $check = false;
                    $pageData = [
                        ['url' => route('web.home.index'), 'changefreq' => setting('sitemap.schedule')],
                        ['url' => route('web.discovery.index'), 'changefreq' => setting('sitemap.schedule')],
                        ['url' => route('web.watch.index'), 'changefreq' => setting('sitemap.schedule')],
                        ['url' => route('web.vibb.index'), 'changefreq' => setting('sitemap.schedule')],
                        ['url' => route('web.user_page.index'), 'changefreq' => setting('sitemap.schedule')],
                        ['url' => route('web.document.index'), 'changefreq' => setting('sitemap.schedule')],
                        ['url' => route('web.group.index'), 'changefreq' => setting('sitemap.schedule')],
                        ['url' => route('web.contact.create'), 'changefreq' => setting('sitemap.schedule')]
                    ];
                    
                    $pages = Page::all();
                    foreach ($pages as $page) {
                        $pageData[] = [
                            'url' => $page->getHref(),
                            'changefreq' => setting('sitemap.schedule')
                        ];
                    }

                    Sitemap::insert($pageData);
                    $config['pages'] = true;
                }

                //users
                if (empty($config['users'])) {
                    $check = false;
                    if (empty($config['user_id'])) {
                        $config['user_id'] = 0;
                    }
                    $users = User::where('is_active', true)->where('id', '>', $config['user_id'])->orderBy('id')->limit(config('shaun_core.core.number_sitemap_get'))->get();
                    if (count($users)) {
                        $userData = [];
                        $current = 0;
                        foreach ($users as $user) {
                            $current = $user->id;
                            $userData[] = [
                                'url' => $user->getHref(),
                                'changefreq' => setting('sitemap.schedule')
                            ];
                        }
                        $config['user_id'] = $current;

                        Sitemap::insert($userData);
                    } else {
                        $config['users'] = true;
                    }
                }

                //posts
                if (empty($config['posts'])) {
                    $check = false;
                    if (empty($config['post_id'])) {
                        $config['post_id'] = 0;
                    }
                    $posts = Post::where('id', '>', $config['post_id'])->where('show', true)->where('has_source', false)->orderBy('id')->limit(config('shaun_core.core.number_sitemap_get'))->get();
                    if (count($posts)) {
                        $postData = [];
                        $current = 0;
                        foreach ($posts as $post) {
                            $current = $post->id;
                            $postData[] = [
                                'url' => $post->getHref(),
                                'changefreq' => setting('sitemap.schedule')
                            ];
                        }
                        $config['post_id'] = $current;

                        Sitemap::insert($postData);
                    } else {
                        $config['posts'] = true;
                    }
                }

                //groups
                if (empty($config['groups'])) {
                    $check = false;
                    if (empty($config['group_id'])) {
                        $config['group_id'] = 0;
                    }
                    $groups = Group::where('id', '>', $config['group_id'])->where('status', GroupStatus::ACTIVE)->orderBy('id')->limit(config('shaun_core.core.number_sitemap_get'))->get();
                    if (count($groups)) {
                        $groupData = [];
                        $current = 0;
                        foreach ($groups as $group) {
                            $current = $group->id;
                            $groupData[] = [
                                'url' => $group->getHref(),
                                'changefreq' => setting('sitemap.schedule')
                            ];
                        }
                        $config['group_id'] = $current;

                        Sitemap::insert($groupData);
                    } else {
                        $config['groups'] = true;
                    }
                }

                Key::setValue('sitemap_process_config', json_encode($config));

                if ($check) {
                    Key::setValue('sitemap_process_step', SitemapStep::GENERATE_SUB_FILE->value);
                }
                break;

            case SitemapStep::GENERATE_SUB_FILE->value:
                $check = true;

                $sitemaps = Sitemap::orderBy('id')->limit(config('shaun_core.core.number_sitemap_per_file'))->get();
                $current = 0;
                if (count($sitemaps)) {
                    $current = $sitemaps->last()->id;
                    Sitemap::where('id', '<=', $current)->orderBy('id')->delete();
                    $content = view('shaun_core::sitemap.sub', ['sitemaps' => $sitemaps])->render();
                    $xmlPath = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.xml';
                    file_put_contents($xmlPath, $content);
                    $storeFile = File::store(new FileCore($xmlPath), [
                        'parent_type' => 'sitemap_process_file',
                        'parent_id' => 1
                    ]);
                    $processFiles[] = $storeFile->id;
                    Key::setValue('sitemap_process_files', json_encode($processFiles));
                } else {
                    Key::setValue('sitemap_process_step', SitemapStep::GENERATE_MAIN_FILE->value);
                }
                break;
            case SitemapStep::GENERATE_MAIN_FILE->value:
                $files = [];
                foreach ($processFiles as $fileId) {
                    $files[] = StorageFile::findByField('id', $fileId);
                }
                $content = view('shaun_core::sitemap.main', ['files' => $files])->render();
                $xmlPath = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.xml';
                file_put_contents($xmlPath, $content);
                $storeFile = File::store(new FileCore($xmlPath), [
                    'parent_type' => 'sitemap_process_file',
                    'parent_id' => 1
                ]);

                //remove old
                $siteId = Key::getValue('sitemap_id');
                if ($siteId) {
                    $file = StorageFile::findByField('id', $siteId);
                    if ($file) {
                        $file->delete();
                    }
                }
                $filesId = convertJsonFromString(Key::getValue('sitemap_files_id'));
                foreach ($filesId as $fileId) {
                    $file = StorageFile::findByField('id', $fileId);
                    if ($file) {
                        $file->delete();
                    }
                }

                Key::setValue('sitemap_id', $storeFile->id);
                Key::setValue('sitemap_files_id', json_encode($processFiles));
                Key::setValue('sitemap_process_files', '');

                //update
                Key::setValue('sitemap_process_step', '');
                $processTime = now()->timestamp;
                switch (setting('sitemap.schedule')) {
                    case 'daily':
                        $processTime = now()->addDay()->timestamp;
                        break;
                    case 'weekly':
                        $processTime = now()->addWeek()->timestamp;
                        break;
                    case 'monthly':
                        $processTime = now()->addMonth()->timestamp;
                        break;
                }
                
                Key::setValue('sitemap_process_time', $processTime);

                break;
        }
        
        return Command::SUCCESS;
    }
}
