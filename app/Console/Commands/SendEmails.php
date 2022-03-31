<?php

namespace App\Console\Commands;

use App\Models\Log;
use App\Models\Post;
use App\Models\User;
use App\Mail\PostMail;
use App\Models\Subscriber;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:send {post_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email to the subscribers';

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
        $post = Post::findOrFail($this->argument('post_id'));
        $subscribers_id = Subscriber::where('website_id', $post->website_id)->pluck('user_id')->toArray();
        $users = User::whereIn('id',$subscribers_id)->select('id','email')->get()->toArray();
        
        foreach($users as $user) {
            //Email :
            Mail::to($user['email'])->send(new PostMail($post));
            //Save the log : 
            $log = Log::create([
                'post_id' => $post->id,
                'user_id' => $user['id']
            ]);
        }

        
        $this->info('The emails are sent successfully!');
    }
}
