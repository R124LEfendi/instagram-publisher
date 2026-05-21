# Meta Instagram Auto-Post Package for Laravel

A highly reusable, isolated, and premium multi-user and multi-profile Instagram auto-post package for Laravel.

This package is designed as a standalone, self-contained library so you can drop it into any Laravel project without risking routing, class, or dependency conflicts with your existing application code.

---

## ✨ Features

- **Multi-User Structure**: Multiple users can register and connect their own distinct Meta accounts.
- **Dynamic Instagram Profile Discovery**: Under each Meta account, automatically query and import all linked Instagram Business/Creator Profiles along with their parent Facebook Page permissions. Includes instant toggles to activate/deactivate profiles for autoposting.
- **Double Connection Flows**:
  1. **User Token Flow (Auto-import)**: Paste a Facebook User Access Token (with required Instagram scopes) to automatically fetch user profiles, exchange it for long-lived tokens, and query/import all connected Instagram accounts.
  2. **Profile Token Flow (Manual input)**: Manually connect a single Instagram Business profile with its direct ID, linked Page Access Token, and Page ID (perfect for local sandbox testing).
- **Multi-Profile Simultaneous Autoposting**: Write a single post, check multiple target Instagram profiles, and publish to all selected accounts at once.
- **Support for Uploads & Remote URLs**: Supports publishing images via file uploads (saved in public storage) as well as direct external image URLs (e.g. Unsplash, AWS S3) which is highly recommended for quick sandbox testing.
- **Artisan CLI Posting Command**: Includes an interactive terminal command `instagram:post-multi` with choices, progress bars, and validations.
- **Glassmorphic Premium Blade Dashboard**: Includes an out-of-the-box, fully responsive Blade dashboard styled with custom Vanilla CSS featuring gradient accents matching the Instagram brand.
- **Post Audit Logs**: Logs all success and failure payloads including direct links to published posts on Instagram.

---

## 🚀 Installation & Integration Guide

### Step 1: Register Package Repository in `composer.json`

To install this package, open your main Laravel application's `composer.json` and add the local path or the git repository to the `repositories` block:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/R124LEfendi/instagram-publisher"
    }
],
```

---

### Step 2: Install Package via Composer

Require the package in your host application:

```bash
composer require r124lefendi/instagram-publisher:dev-main
```

_Note: Since Laravel supports package auto-discovery, it will automatically register the `R124LEfendi\InstagramPublisher\InstagramPublisherServiceProvider` service provider._

### Step 3: Run Database Migrations

Run your application migrations to create the required `instagram_accounts`, `instagram_profiles`, and `instagram_posts` tables:

```bash
php artisan migrate
```

---

## 🛠️ Configuration

Add your Facebook App ID and App Secret credentials inside `config/services.php` (same as Facebook package):

```php
'facebook' => [
    'app_id' => env('META_APP_ID'),
    'app_secret' => env('META_SECRET_KEY'),
],
```

And define them in your `.env` file:

```env
META_APP_ID="your-app-id"
META_SECRET_KEY="your-app-secret"
```

### 🔑 Setting up Meta Login (OAuth 2.0)

To enable the seamless **Connect with Meta / Facebook** button in the dashboard:

1. Go to your **[Meta Developer Portal](https://developers.facebook.com/)** and select your App.
2. Under **Facebook Login for Business** settings, add your application's Callback URI:
   ```text
   https://your-domain.com/instagram/callback
   ```
3. Ensure your App has the following permissions:
   - `instagram_basic`
   - `instagram_content_publish`
   - `pages_show_list`
   - `pages_read_engagement`

---

## 💻 CLI Command Usage

The package registers an Artisan console command `instagram:post-multi` for CLI-based autoposting:

### **Publish with Caption and Public Image URL**

```bash
php artisan instagram:post-multi --caption="Beautiful sunset!" --image="https://images.unsplash.com/photo-1507525428034-b723cf961d3e"
```

> [!IMPORTANT]
> **Image Crawler Constraints:** The Meta API requires that the image to be published is hosted on a **public URL** accessible by Meta's servers. In local environments (`localhost`), local file uploads will fail to be crawled by Meta unless you use a tunnel like Ngrok. For local sandbox testing, always use a direct, public web image URL.

### **Interactive Selection Mode**

If you run the command without target profiles option, it will automatically display an interactive selection checklist for all your active Instagram profiles:

```bash
php artisan instagram:post-multi --caption="Interactive post!" --image="https://example.com/image.jpg"
```

---

## 🌐 Web Dashboard Usage

Go to:
🔗 `http://your-domain.local/instagram`

1. Paste a **Facebook User Access Token** in the dashboard form or click **Connect with Meta / Facebook**.
2. Click **Import Meta Account** to automatically register the account and import all its linked Instagram Business profiles.
3. Check the boxes of the target Instagram accounts, type your caption, provide an image source, and hit **Publish Instagram Post Now**!

---

## 🎨 Customizing & Publishing the UI

If you want to customize the layout, change the colors, or translate text, you can publish the package's Blade views directly into your Laravel application's resources directory:

```bash
php artisan vendor:publish --tag=instagram-publisher-views
```

This will copy the dashboard template to:
`resources/views/vendor/instagram-publisher/dashboard.blade.php`

Once published, Laravel will automatically prioritize and load your custom file instead of the default package layout, giving you 100% freedom to modify or style the view to match your application's design system!

## ⚠️ Troubleshooting & Sandbox Constraints

### Why is my connected Instagram profile not showing up?

If you completed the Meta Login flow but no Instagram profiles were imported, check the following:

1. **Instagram Account Type**: Meta's Graph API **only** supports **Instagram Business** or **Instagram Creator** accounts. Standard personal accounts will be ignored by the API.
2. **Business Portfolio Scoping**: If you created a new, empty Meta Business Portfolio during the Embedded Signup popup, the API will search inside that empty portfolio and return `0` pages.
   - _Fix 1_: Re-run the OAuth flow and select the correct Business Portfolio that owns your Facebook Pages.
   - _Fix 2_: Go to the **Single Profile Manual** tab on the dashboard, and manually paste your Facebook Page ID, Instagram Business Account ID, and Page Access Token.
