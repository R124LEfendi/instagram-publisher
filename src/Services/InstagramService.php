<?php

namespace R124LEfendi\InstagramPublisher\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class InstagramService
{
    protected string $apiVersion = 'v20.0';
    protected string $baseUrl = 'https://graph.facebook.com';

    /**
     * Get App ID from configuration.
     */
    protected function getAppId(): ?string
    {
        return config('services.facebook.app_id');
    }

    /**
     * Get App Secret from configuration.
     */
    protected function getAppSecret(): ?string
    {
        return config('services.facebook.app_secret');
    }

    /**
     * Exchange short-lived User Access Token for a long-lived one (valid for 60 days).
     */
    public function exchangeToLongLivedToken(string $shortLivedToken): string
    {
        $appId = $this->getAppId();
        $appSecret = $this->getAppSecret();

        if (!$appId || !$appSecret) {
            Log::warning("Facebook App ID or Secret is not configured. Returning original token.");
            return $shortLivedToken;
        }

        try {
            $response = Http::get("{$this->baseUrl}/{$this->apiVersion}/oauth/access_token", [
                'grant_type' => 'fb_exchange_token',
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'fb_exchange_token' => $shortLivedToken,
            ]);

            if ($response->failed()) {
                throw new Exception("Token exchange failed: " . json_encode($response->json()));
            }

            return $response->json()['access_token'] ?? $shortLivedToken;
        } catch (Exception $e) {
            Log::error("InstagramService - exchangeToLongLivedToken error: " . $e->getMessage());
            return $shortLivedToken;
        }
    }

    /**
     * Get Facebook user profile details using a User Access Token.
     */
    public function getUserProfile(string $userAccessToken): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/{$this->apiVersion}/me", [
                'fields' => 'id,name,email,picture.type(large)',
                'access_token' => $userAccessToken,
            ]);

            if ($response->failed()) {
                throw new Exception("Failed to fetch user profile: " . json_encode($response->json()));
            }

            $data = $response->json();
            return [
                'id' => $data['id'],
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'avatar' => $data['picture']['data']['url'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error("InstagramService - getUserProfile error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch Facebook Pages and look up their linked Instagram Business Accounts.
     */
    public function getInstagramProfiles(string $userAccessToken): array
    {
        try {
            // Step 1: Get all Facebook Pages managed by this User
            $response = Http::get("{$this->baseUrl}/{$this->apiVersion}/me/accounts", [
                'fields' => 'id,name,access_token,category,picture.type(large)',
                'access_token' => $userAccessToken,
                'limit' => 200,
            ]);

            if ($response->failed()) {
                throw new Exception("Failed to fetch user pages: " . json_encode($response->json()));
            }

            $pages = $response->json()['data'] ?? [];
            $instagramProfiles = [];

            // Step 2: Loop through pages to check if they have a linked Instagram Professional Account
            foreach ($pages as $page) {
                $pageId = $page['id'];
                $pageAccessToken = $page['access_token'];

                $igResponse = Http::get("{$this->baseUrl}/{$this->apiVersion}/{$pageId}", [
                    'fields' => 'instagram_business_account{id,username,name,profile_picture_url}',
                    'access_token' => $pageAccessToken,
                ]);

                if ($igResponse->successful()) {
                    $igData = $igResponse->json();
                    
                    if (isset($igData['instagram_business_account'])) {
                        $igAccount = $igData['instagram_business_account'];
                        
                        $instagramProfiles[] = [
                            'instagram_profile_id' => $igAccount['id'],
                            'instagram_username' => $igAccount['username'],
                            'instagram_name' => $igAccount['name'] ?? $igAccount['username'],
                            'fb_page_id' => $pageId,
                            'fb_page_access_token' => $pageAccessToken, // Permanent Page Token needed to act on the IG account
                            'avatar' => $igAccount['profile_picture_url'] ?? null,
                        ];
                    }
                }
            }

            return $instagramProfiles;
        } catch (Exception $e) {
            Log::error("InstagramService - getInstagramProfiles error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Post an image and caption to a specific Instagram Business Account.
     */
    public function postToInstagram(string $instagramProfileId, string $fbPageAccessToken, string $imageUrl, ?string $caption = null): array
    {
        try {
            // Step 1: Create Media Container
            $containerUrl = "{$this->baseUrl}/{$this->apiVersion}/{$instagramProfileId}/media";
            $containerParams = [
                'image_url' => $imageUrl,
                'access_token' => $fbPageAccessToken,
            ];

            if ($caption) {
                $containerParams['caption'] = $caption;
            }

            $containerResponse = Http::timeout(180)->post($containerUrl, $containerParams);
            $containerResult = $containerResponse->json();

            if ($containerResponse->failed()) {
                $errorMsg = $containerResult['error']['message'] ?? 'Failed to create media container.';
                Log::error("InstagramService - postToInstagram (Create Container Failed): " . json_encode($containerResult));
                return [
                    'success' => false,
                    'error' => $errorMsg,
                ];
            }

            $creationId = $containerResult['id'] ?? null;
            if (!$creationId) {
                return [
                    'success' => false,
                    'error' => 'Media container creation returned empty ID.',
                ];
            }

            // Step 2: Publish Media Container
            $publishUrl = "{$this->baseUrl}/{$this->apiVersion}/{$instagramProfileId}/media_publish";
            $publishResponse = Http::timeout(180)->post($publishUrl, [
                'creation_id' => $creationId,
                'access_token' => $fbPageAccessToken,
            ]);
            $publishResult = $publishResponse->json();

            if ($publishResponse->failed()) {
                $errorMsg = $publishResult['error']['message'] ?? 'Failed to publish media container.';
                Log::error("InstagramService - postToInstagram (Publish Container Failed): " . json_encode($publishResult));
                return [
                    'success' => false,
                    'error' => $errorMsg,
                ];
            }

            $igPostId = $publishResult['id'] ?? null;

            // Step 3: Fetch post permalink for convenience
            $permalink = null;
            if ($igPostId) {
                $mediaResponse = Http::get("{$this->baseUrl}/{$this->apiVersion}/{$igPostId}", [
                    'fields' => 'permalink',
                    'access_token' => $fbPageAccessToken,
                ]);
                if ($mediaResponse->successful()) {
                    $permalink = $mediaResponse->json()['permalink'] ?? null;
                }
            }

            return [
                'success' => true,
                'ig_post_id' => $igPostId,
                'permalink' => $permalink,
            ];

        } catch (Exception $e) {
            Log::error("InstagramService - postToInstagram exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
