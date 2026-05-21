<?php

namespace R124LEfendi\InstagramPublisher\Console\Commands;

use Illuminate\Console\Command;
use R124LEfendi\InstagramPublisher\Models\InstagramProfile;
use R124LEfendi\InstagramPublisher\Models\InstagramPost;
use R124LEfendi\InstagramPublisher\Services\InstagramService;

class InstagramPostCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagram:post-multi
                            {--caption= : The caption text of the post}
                            {--image= : A publicly accessible URL of the image to attach (mandatory)}
                            {--profiles= : Comma-separated list of Instagram Profile IDs, or "all" to post to all active profiles}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post an image and caption to multiple Instagram accounts simultaneously';

    protected InstagramService $igService;

    public function __construct(InstagramService $igService)
    {
        parent::__construct();
        $this->igService = $igService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $caption = $this->option('caption');
        $image = $this->option('image');
        $profilesOption = $this->option('profiles');

        if (!$image) {
            $this->error("Error: Instagram Graph API requires an image. Please provide a publicly accessible --image URL.");
            return 1;
        }

        // Get active profiles from database
        $activeProfiles = InstagramProfile::where('is_active', true)->get();

        if ($activeProfiles->isEmpty()) {
            $this->error("No active Instagram profiles found in the database. Connect an account first!");
            return 1;
        }

        $selectedProfiles = collect();

        // Parse profile options
        if ($profilesOption === 'all') {
            $selectedProfiles = $activeProfiles;
        } elseif ($profilesOption) {
            $profileIds = explode(',', $profilesOption);
            $selectedProfiles = $activeProfiles->filter(function ($profile) use ($profileIds) {
                return in_array($profile->instagram_profile_id, $profileIds);
            });

            if ($selectedProfiles->isEmpty()) {
                $this->error("None of the specified Instagram Profile IDs match the active profiles in the database.");
                return 1;
            }
        } else {
            // Interactive Mode
            $this->info("No target profiles specified. Entering interactive selection mode...");
            
            $choices = [];
            foreach ($activeProfiles as $profile) {
                $choices[$profile->id] = "@{$profile->instagram_username} (ID: {$profile->instagram_profile_id})";
            }

            $selectedChoices = $this->choice(
                'Select which Instagram profiles to publish to (multiple answers allowed, comma-separated)',
                $choices,
                null,
                null,
                true // Multi-select
            );

            // Fetch the selected profiles
            $selectedUsernames = array_map(function ($choice) {
                // Extracts username from "@username (ID: ...)"
                return substr(explode(' (ID:', $choice)[0], 1);
            }, $selectedChoices);

            $selectedProfiles = $activeProfiles->filter(function ($profile) use ($selectedUsernames) {
                return in_array($profile->instagram_username, $selectedUsernames);
            });
        }

        $this->info("Preparing to post to " . $selectedProfiles->count() . " Instagram profile(s)...");
        $this->line(" - Caption: " . ($caption ?: '[None]'));
        $this->line(" - Image URL: " . $image);
        $this->newLine();

        // Warning if the image does not seem to be a public web URL
        if (!str_starts_with($image, 'http://') && !str_starts_with($image, 'https://')) {
            $this->warn("⚠️ Warning: Instagram Graph API requires a PUBLICLY accessible HTTP/HTTPS URL. Local paths will fail.");
        } elseif (str_contains($image, 'localhost') || str_contains($image, '127.0.0.1')) {
            $this->warn("⚠️ Warning: Your image URL points to localhost. Meta's servers will not be able to crawl this. You must use a public URL or ngrok.");
        }

        $bar = $this->output->createProgressBar($selectedProfiles->count());
        $bar->start();

        $successCount = 0;
        $failCount = 0;
        $results = [];

        foreach ($selectedProfiles as $profile) {
            $result = $this->igService->postToInstagram(
                $profile->instagram_profile_id,
                $profile->fb_page_access_token,
                $image,
                $caption
            );

            if ($result['success']) {
                $successCount++;
                $results[] = [
                    'profile' => '@' . $profile->instagram_username,
                    'status' => 'Success ✅',
                    'id' => $result['ig_post_id'],
                ];

                InstagramPost::create([
                    'user_id' => null,
                    'instagram_profile_id' => $profile->id,
                    'caption' => $caption,
                    'image_url' => $image,
                    'status' => 'success',
                    'ig_post_id' => $result['ig_post_id'],
                    'posted_at' => now(),
                ]);
            } else {
                $failCount++;
                $results[] = [
                    'profile' => '@' . $profile->instagram_username,
                    'status' => 'Failed ❌',
                    'id' => 'Error: ' . $result['error'],
                ];

                InstagramPost::create([
                    'user_id' => null,
                    'instagram_profile_id' => $profile->id,
                    'caption' => $caption,
                    'image_url' => $image,
                    'status' => 'failed',
                    'error_message' => $result['error'],
                    'posted_at' => now(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Display results table
        $this->table(['Instagram Profile', 'Status', 'Instagram Post ID / Error'], $results);

        $this->newLine();
        if ($failCount === 0) {
            $this->info("Completed successfully! Posted to all {$successCount} Instagram profiles.");
        } else {
            $this->warn("Completed with errors. Posted successfully to {$successCount} profiles, failed on {$failCount} profiles.");
        }

        return $failCount === 0 ? 0 : 1;
    }
}
