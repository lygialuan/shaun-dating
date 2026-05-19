<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;
use Packages\ShaunSocial\Core\Models\MailRecipient;
use Packages\ShaunSocial\Core\Support\Facades\Mail;

class MailSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:mail_send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mail send task.';

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
        $recipients = MailRecipient::orderBy('id')->limit(setting('mail.count'))->get();
        foreach ($recipients as $recipient) {
            Mail::sendRow($recipient->type, $recipient->to, json_decode($recipient->params, true));
            $recipient->delete();
        }
    }
}
