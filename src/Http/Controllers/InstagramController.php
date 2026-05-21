<?php

namespace R124LEfendi\InstagramPublisher\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

use R124LEfendi\InstagramPublisher\Models\InstagramAccount;
use R124LEfendi\InstagramPublisher\Models\InstagramProfile;
use R124LEfendi\InstagramPublisher\Models\InstagramPost;
use R124LEfendi\InstagramPublisher\Services\InstagramService;

class InstagramController extends Controller
{
    protected InstagramService $igService;

    public function __construct(InstagramService $igService)
    {
        $this->igService = $igService;
    }

    /**
     * Display the Instagram publisher dashboard.
     */
    public function index()
    {
        $accounts = InstagramAccount::with('profiles')->get();
        $activeProfiles = InstagramProfile::where('is_active', true)->get();
        $recentPosts = InstagramPost::with('profile.account')->latest()->take(30)->get();

        return view('instagram-publisher::dashboard', compact('accounts', 'activeProfiles', 'recentPosts'));
    }

    /**
     * Connect an Instagram account using a Facebook User Access Token.
     */
    public function connectUserToken(Request $request)
    {
        $request->validate([
            'user_access_token' => 'required|string',
        ]);

        $token = $request->input('user_access_token');

        try {
            // Exchange for a long-lived user access token
            $longLivedToken = $this->igService->exchangeToLongLivedToken($token);

            // Fetch user profile details (the Facebook developer user)
            $profile = $this->igService->getUserProfile($longLivedToken);

            // Save Meta/Facebook Account
            $account = InstagramAccount::updateOrCreate(
                ['fb_user_id' => $profile['id']],
                [
                    'user_id' => auth()->id() ?: null,
                    'name' => $profile['name'],
                    'email' => $profile['email'] ?? null,
                    'access_token' => $longLivedToken,
                    'avatar' => $profile['avatar'] ?? null,
                ]
            );

            // Fetch and save Instagram Business Profiles linked to the Pages of this account
            $igProfiles = $this->igService->getInstagramProfiles($longLivedToken);
            $importedCount = 0;

            foreach ($igProfiles as $p) {
                InstagramProfile::updateOrCreate(
                    ['instagram_profile_id' => $p['instagram_profile_id']],
                    [
                        'instagram_account_id' => $account->id,
                        'instagram_username' => $p['instagram_username'],
                        'instagram_name' => $p['instagram_name'],
                        'fb_page_id' => $p['fb_page_id'],
                        'fb_page_access_token' => $p['fb_page_access_token'],
                        'avatar' => $p['avatar'],
                        'is_active' => true,
                    ]
                );
                $importedCount++;
            }

            return redirect()->route('instagram.dashboard')->with('success', "Connected account '{$account->name}' and imported {$importedCount} Instagram profiles successfully!");

        } catch (Exception $e) {
            return redirect()->route('instagram.dashboard')->with('error', "Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Connect a single Instagram Business Profile manually.
     */
    public function connectSingleProfile(Request $request)
    {
        $request->validate([
            'instagram_username' => 'required|string|max:255',
            'instagram_name' => 'nullable|string|max:255',
            'instagram_profile_id' => 'required|string|max:255',
            'fb_page_id' => 'required|string|max:255',
            'fb_page_access_token' => 'required|string',
        ]);

        try {
            // Validate the page access token before saving
            $pageAccessToken = $request->input('fb_page_access_token');
            $this->igService->validatePageAccessToken($pageAccessToken);

            // Create a virtual "Manual Connections" account to group manually entered pages
            $account = InstagramAccount::updateOrCreate(
                ['fb_user_id' => 'manual_connections'],
                [
                    'user_id' => auth()->id() ?: null,
                    'name' => 'Manual Connections',
                    'access_token' => 'manual',
                    'avatar' => 'https://www.facebook.com/images/assets_files/yis/gray_app_icon.png',
                ]
            );

            // Create/Update the specific Instagram profile
            $profile = InstagramProfile::updateOrCreate(
                ['instagram_profile_id' => $request->input('instagram_profile_id')],
                [
                    'instagram_account_id' => $account->id,
                    'instagram_username' => $request->input('instagram_username'),
                    'instagram_name' => $request->input('instagram_name') ?? $request->input('instagram_username'),
                    'fb_page_id' => $request->input('fb_page_id'),
                    'fb_page_access_token' => $request->input('fb_page_access_token'),
                    'avatar' => null,
                    'is_active' => true,
                ]
            );

            return redirect()->route('instagram.dashboard')->with('success', "Instagram profile '@{$profile->instagram_username}' connected successfully!");

        } catch (Exception $e) {
            return redirect()->route('instagram.dashboard')->with('error', "Manual connection failed: " . $e->getMessage());
        }
    }

    /**
     * Toggle active/inactive status of an Instagram Profile.
     */
    public function toggleProfile(InstagramProfile $profile)
    {
        $profile->is_active = !$profile->is_active;
        $profile->save();

        $status = $profile->is_active ? 'activated' : 'deactivated';
        return redirect()->route('instagram.dashboard')->with('success', "Profile '@{$profile->instagram_username}' has been {$status}!");
    }

    /**
     * Disconnect/delete a Meta Account and all its Instagram profiles.
     */
    public function deleteAccount(InstagramAccount $account)
    {
        $name = $account->name;
        $account->delete();

        return redirect()->route('instagram.dashboard')->with('success', "Disconnected Meta Account '{$name}' successfully.");
    }

    /**
     * Renew/update the access token of an Instagram/Meta Account and its pages.
     */
    public function renewToken(Request $request, InstagramAccount $account)
    {
        $request->validate([
            'new_access_token' => 'required|string',
        ]);

        $token = $request->input('new_access_token');

        try {
            // Exchange for a long-lived user access token
            $longLivedToken = $this->igService->exchangeToLongLivedToken($token);

            // Fetch user profile details to ensure the token belongs to the SAME user ID
            $profile = $this->igService->getUserProfile($longLivedToken);

            if ($profile['id'] !== $account->fb_user_id) {
                return redirect()->route('instagram.dashboard')->with('error', "Token mismatch: The new token belongs to '{$profile['name']}' (ID: {$profile['id']}), but this account is for '{$account->name}' (ID: {$account->fb_user_id}).");
            }

            // Update Account
            $account->update([
                'access_token' => $longLivedToken,
                'name' => $profile['name'],
                'email' => $profile['email'] ?? $account->email,
                'avatar' => $profile['avatar'] ?? $account->avatar,
            ]);

            // Fetch and update linked IG profiles for this account
            $igProfiles = $this->igService->getInstagramProfiles($longLivedToken);
            $updatedCount = 0;

            foreach ($igProfiles as $p) {
                $existingProfile = InstagramProfile::where('instagram_account_id', $account->id)
                    ->where('instagram_profile_id', $p['instagram_profile_id'])
                    ->first();

                if ($existingProfile) {
                    $existingProfile->update([
                        'fb_page_access_token' => $p['fb_page_access_token'],
                        'instagram_username' => $p['instagram_username'],
                        'instagram_name' => $p['instagram_name'],
                        'avatar' => $p['avatar'] ?? $existingProfile->avatar,
                    ]);
                    $updatedCount++;
                }
            }

            return redirect()->route('instagram.dashboard')->with('success', "Access token for '{$account->name}' and {$updatedCount} profiles successfully updated!");

        } catch (Exception $e) {
            return redirect()->route('instagram.dashboard')->with('error', "Failed to renew token: " . $e->getMessage());
        }
    }

    /**
     * Publish caption and image to selected Instagram Profiles.
     */

       public function forceImportPage(Request $request, InstagramAccount $account)
    {
        $request->validate([
            'fb_page_id' => 'required|string|max:255',
        ]);

        $pageId = $request->input('fb_page_id');
        $userAccessToken = $account->access_token;

        try {
            // 1. Fetch page access token and linked instagram account directly using the user access token
            $response = Http::get("https://graph.facebook.com/v20.0/{$pageId}", [
                'fields' => 'access_token,name,instagram_business_account{id,username,name,profile_picture_url}',
                'access_token' => $userAccessToken,
            ]);

            if ($response->failed()) {
                $errorMsg = $response->json()['error']['message'] ?? 'Failed to query the Facebook Page. Please ensure the Page ID is correct and you have admin rights.';
                throw new Exception($errorMsg);
            }

            $data = $response->json();
            $pageAccessToken = $data['access_token'] ?? null;
            $pageName = $data['name'] ?? $pageId;

            if (!$pageAccessToken) {
                throw new Exception("Could not retrieve access token for Page '{$pageName}'. Please make sure you are an admin of this Page.");
            }

            $igData = $data['instagram_business_account'] ?? null;
            if (!$igData) {
                throw new Exception("No Instagram Business or Creator Account is linked to Facebook Page '{$pageName}'. Please link it first under Facebook Page Settings > Linked Accounts.");
            }

            // 2. Create or Update the InstagramProfile row in database
            $profile = InstagramProfile::updateOrCreate(
                ['instagram_profile_id' => $igData['id']],
                [
                    'instagram_account_id' => $account->id,
                    'instagram_username' => $igData['username'],
                    'instagram_name' => $igData['name'] ?? $igData['username'],
                    'fb_page_id' => $pageId,
                    'fb_page_access_token' => $pageAccessToken,
                    'avatar' => $igData['profile_picture_url'] ?? null,
                    'is_active' => true,
                ]
            );

            return redirect()->route('instagram.dashboard')->with('success', "Successfully force-imported Instagram Profile @{$profile->instagram_username} linked to Page '{$pageName}'!");

        } catch (Exception $e) {
            return redirect()->route('instagram.dashboard')->with('error', "Import failed: " . $e->getMessage());
        }
    }

    /**
     * Automatically scan all Facebook pages for this account and import linked Instagram profiles.
     */
    public function autoScanPages(InstagramAccount $account)
    {
        $userAccessToken = $account->access_token;

        try {
            // 1. Fetch all Facebook Pages managed by this User
            $response = Http::get("https://graph.facebook.com/v20.0/me/accounts", [
                'fields' => 'id,name,access_token',
                'access_token' => $userAccessToken,
                'limit' => 200,
            ]);

            if ($response->failed()) {
                $errorMsg = $response->json()['error']['message'] ?? 'Failed to retrieve Facebook pages for this account.';
                throw new Exception($errorMsg);
            }

            $pages = $response->json()['data'] ?? [];

            if (empty($pages)) {
                return redirect()->route('instagram.dashboard')->with('warning', 'No Facebook pages were found linked to this Meta account. Make sure you are an administrator of the pages.');
            }

            $scannedCount = 0;
            $importedCount = 0;

            // 2. Loop through pages to bypass/query and import linked Instagram Business Accounts
            foreach ($pages as $page) {
                $scannedCount++;
                try {
                    $pageId = $page['id'];
                    $pageResponse = Http::get("https://graph.facebook.com/v20.0/{$pageId}", [
                        'fields' => 'access_token,name,instagram_business_account{id,username,name,profile_picture_url}',
                        'access_token' => $userAccessToken,
                    ]);

                    if ($pageResponse->successful()) {
                        $data = $pageResponse->json();
                        $pageAccessToken = $data['access_token'] ?? $page['access_token'] ?? null;
                        $pageName = $data['name'] ?? $page['name'] ?? $pageId;

                        if ($pageAccessToken && isset($data['instagram_business_account'])) {
                            $igData = $data['instagram_business_account'];

                            InstagramProfile::updateOrCreate(
                                ['instagram_profile_id' => $igData['id']],
                                [
                                    'instagram_account_id' => $account->id,
                                    'instagram_username' => $igData['username'],
                                    'instagram_name' => $igData['name'] ?? $igData['username'],
                                    'fb_page_id' => $pageId,
                                    'fb_page_access_token' => $pageAccessToken,
                                    'avatar' => $igData['profile_picture_url'] ?? null,
                                    'is_active' => true,
                                ]
                            );
                            $importedCount++;
                        }
                    }
                } catch (Exception $innerEx) {
                    Log::warning("Instagram AutoScan - Failed scanning page {$page['id']}: " . $innerEx->getMessage());
                }
            }

            if ($importedCount === 0) {
                return redirect()->route('instagram.dashboard')->with('info', "Scanned {$scannedCount} Facebook pages, but none of them had a linked Instagram Business Account. Please check your page settings or use 'Bypass Instan' if you have a specific Page ID.");
            }

            return redirect()->route('instagram.dashboard')->with('success', "Auto-scan completed! Successfully scanned {$scannedCount} Facebook pages and automatically imported/updated {$importedCount} Instagram profiles.");

        } catch (Exception $e) {
            return redirect()->route('instagram.dashboard')->with('error', "Auto-scan failed: " . $e->getMessage());
        }
    }

    public function post(Request $request)
    {
        $request->validate([
            'caption' => 'nullable|string',
            'image_url' => 'nullable|url',
            'image_file' => 'nullable|image|max:10240', // Max 10MB
            'profiles' => 'required|array|min:1',
            'profiles.*' => 'exists:instagram_profiles,id',
        ]);

        $caption = $request->input('caption');
        $inputUrl = $request->input('image_url');
        $profiles = $request->input('profiles');

        // Check that at least one image source was provided
        if (!$request->hasFile('image_file') && !$inputUrl) {
            return redirect()->route('instagram.dashboard')->with('error', 'You must provide either a remote image URL or upload an image file.');
        }

        $imageUrl = $inputUrl;
        $dbImagePath = $inputUrl; // Value to record in DB

        // If file uploaded, save locally
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', $file->getClientOriginalName());
            
            $uploadDir = public_path('uploads/instagram');
            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            $file->move($uploadDir, $fileName);
            
            // Build absolute URL for the Meta API, and relative path for database
            $imageUrl = url('uploads/instagram/' . $fileName);
            $dbImagePath = 'uploads/instagram/' . $fileName;
        }

        $successCount = 0;
        $failCount = 0;
        $errors = [];

        foreach ($profiles as $profileDbId) {
            $profile = InstagramProfile::find($profileDbId);
            if (!$profile || !$profile->is_active) {
                continue;
            }

            // Call the service to post to Instagram
            $result = $this->igService->postToInstagram(
                $profile->instagram_profile_id,
                $profile->fb_page_access_token,
                $imageUrl,
                $caption
            );

            if ($result['success']) {
                $successCount++;
                InstagramPost::create([
                    'user_id' => auth()->id() ?: null,
                    'instagram_profile_id' => $profile->id,
                    'caption' => $caption,
                    'image_url' => $dbImagePath,
                    'status' => 'success',
                    'ig_post_id' => $result['ig_post_id'],
                    'posted_at' => now(),
                ]);
            } else {
                $failCount++;
                $errors[] = "@{$profile->instagram_username}: {$result['error']}";
                InstagramPost::create([
                    'user_id' => auth()->id() ?: null,
                    'instagram_profile_id' => $profile->id,
                    'caption' => $caption,
                    'image_url' => $dbImagePath,
                    'status' => 'failed',
                    'error_message' => $result['error'],
                    'posted_at' => now(),
                ]);
            }
        }

        if ($failCount > 0) {
            $errMsg = "Published to {$successCount} accounts, but failed on {$failCount} accounts. Details: " . implode(' | ', $errors);
            return redirect()->route('instagram.dashboard')->with('warning', $errMsg);
        }

        return redirect()->route('instagram.dashboard')->with('success', "Successfully posted to all {$successCount} selected Instagram profiles!");
    }

    /**
     * Redirect the user to the Facebook authentication page.
     */
    public function redirectToFacebook()
    {
        $appId = config('services.facebook.app_id');
        $redirectUri = route('instagram.callback');
        
        if (!$appId) {
            return redirect()->route('instagram.dashboard')->with('error', 'Facebook App ID is not configured in services.php.');
        }

        // Build OAuth URL with Instagram-specific permissions
        $permissions = [
            'public_profile', 
            'email', 
            'pages_show_list', 
            'pages_read_engagement', 
            'instagram_basic', 
            'instagram_content_publish'
        ];

        $query = http_build_query([
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(',', $permissions),
            'response_type' => 'code',
            'state' => csrf_token(),
        ]);

        return redirect("https://www.facebook.com/v20.0/dialog/oauth?{$query}");
    }

    /**
     * Handle the callback from Meta authentication.
     */
    public function handleCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('instagram.dashboard')->with('error', 'Login cancelled or failed: ' . $request->input('error_description', 'Unknown error'));
        }

        $code = $request->input('code');
        
        if (!$code) {
            return redirect()->route('instagram.dashboard')->with('error', 'No authorization code returned.');
        }

        try {
            $appId = config('services.facebook.app_id');
            $appSecret = config('services.facebook.app_secret');
            $redirectUri = route('instagram.callback');

            // 1. Exchange authorization code for User Access Token
            $response = Http::get("https://graph.facebook.com/v20.0/oauth/access_token", [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            if ($response->failed()) {
                throw new Exception("Failed to exchange code: " . json_encode($response->json()));
            }

            $shortLivedToken = $response->json()['access_token'] ?? null;

            if (!$shortLivedToken) {
                throw new Exception("No access token returned in code exchange.");
            }

            // 2. Exchange short-lived token for long-lived token
            $longLivedToken = $this->igService->exchangeToLongLivedToken($shortLivedToken);

            // 3. Fetch user profile details
            $profile = $this->igService->getUserProfile($longLivedToken);

            // 4. Save/Update Account
            $account = InstagramAccount::updateOrCreate(
                ['fb_user_id' => $profile['id']],
                [
                    'user_id' => auth()->id() ?: null,
                    'name' => $profile['name'],
                    'email' => $profile['email'] ?? null,
                    'access_token' => $longLivedToken,
                    'avatar' => $profile['avatar'] ?? null,
                ]
            );

            // 5. Fetch and save Instagram Business Profiles linked to the Pages of this account
            $igProfiles = $this->igService->getInstagramProfiles($longLivedToken);
            $importedCount = 0;

            foreach ($igProfiles as $p) {
                InstagramProfile::updateOrCreate(
                    ['instagram_profile_id' => $p['instagram_profile_id']],
                    [
                        'instagram_account_id' => $account->id,
                        'instagram_username' => $p['instagram_username'],
                        'instagram_name' => $p['instagram_name'],
                        'fb_page_id' => $p['fb_page_id'],
                        'fb_page_access_token' => $p['fb_page_access_token'],
                        'avatar' => $p['avatar'],
                        'is_active' => true,
                    ]
                );
                $importedCount++;
            }

            return redirect()->route('instagram.dashboard')->with('success', "Connected account '{$account->name}' via Meta Login and imported {$importedCount} Instagram profiles successfully!");

        } catch (Exception $e) {
            Log::error("Instagram OAuth Callback Error: " . $e->getMessage());
            return redirect()->route('instagram.dashboard')->with('error', "Authentication failed: " . $e->getMessage());
        }
    }
}
