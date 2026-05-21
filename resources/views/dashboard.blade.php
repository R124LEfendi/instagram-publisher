<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Meta Instagram Publisher Dashboard</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Modern Premium CSS System */
        :root {
            --bg-primary: #0b0f19;
            --bg-secondary: #131a2e;
            --bg-tertiary: #1e294b;
            --accent-color: #e1306c; /* Instagram Magenta */
            --accent-purple: #833ab4;
            --accent-orange: #f77737;
            --accent-gradient: linear-gradient(135deg, #f56040 0%, #e1306c 50%, #833ab4 100%);
            --accent-success: #10b981;
            --accent-warning: #f59e0b;
            --accent-danger: #ef4444;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-bg: rgba(19, 26, 46, 0.75);
            --glass-border: rgba(255, 255, 255, 0.08);
            --card-shadow: 0 12px 40px -10px rgba(0, 0, 0, 0.6);
            --radius-lg: 16px;
            --radius-md: 10px;
            --radius-sm: 6px;
            --font-outfit: 'Outfit', sans-serif;
            --font-inter: 'Inter', sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            transition: all 0.25s ease;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-main);
            font-family: var(--font-inter);
            min-height: 100vh;
            line-height: 1.5;
            background-image: 
                radial-gradient(at 0% 0%, rgba(225, 48, 108, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(131, 58, 180, 0.15) 0px, transparent 50%);
            padding: 2rem 1rem;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
        }

        /* Header Styling */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-logo i {
            font-size: 2.5rem;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 0 8px rgba(225, 48, 108, 0.4));
        }

        .header-logo h1 {
            font-family: var(--font-outfit);
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-badge {
            background: rgba(225, 48, 108, 0.1);
            border: 1px solid rgba(225, 48, 108, 0.2);
            color: var(--accent-color);
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Alerts */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius-md);
            margin-bottom: 2rem;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: var(--card-shadow);
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        .alert-warning {
            background-color: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }

        /* Main Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 460px 1fr;
            }
        }

        /* Card System */
        .card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(16px);
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: transparent;
        }

        .card-accent-instagram::before {
            background: var(--accent-gradient);
        }

        .card-accent-emerald::before {
            background: linear-gradient(90deg, var(--accent-success), #34d399);
        }

        .card-title {
            font-family: var(--font-outfit);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-main);
        }

        .card-title i {
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-group {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .input-control {
            width: 100%;
            background-color: rgba(11, 15, 25, 0.6);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
            color: var(--text-main);
            font-family: var(--font-inter);
            font-size: 0.95rem;
        }

        .input-control:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(225, 48, 108, 0.25);
        }

        textarea.input-control {
            resize: vertical;
            min-height: 120px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            text-decoration: none;
            width: 100%;
            text-align: center;
        }

        .btn-primary {
            background: var(--accent-gradient);
            color: white;
            box-shadow: 0 4px 14px rgba(225, 48, 108, 0.3);
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-danger-outline {
            background: transparent;
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: var(--accent-danger);
        }

        .btn-danger-outline:hover {
            background-color: rgba(239, 68, 68, 0.1);
        }

        /* Switches */
        .token-mode-switch {
            display: flex;
            background: rgba(11, 15, 25, 0.4);
            border-radius: var(--radius-md);
            padding: 0.25rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--glass-border);
        }

        .tab-btn {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--text-muted);
            padding: 0.5rem;
            font-size: 0.825rem;
            font-weight: 600;
            border-radius: var(--radius-sm);
            cursor: pointer;
        }

        .tab-btn.active {
            background: var(--bg-tertiary);
            color: var(--text-main);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        /* Accounts List */
        .accounts-container {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .account-item {
            background: rgba(11, 15, 25, 0.4);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-md);
            padding: 1rem;
        }

        .account-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .account-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .account-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent-color);
        }

        .account-details h4 {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .account-details span {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .profiles-list {
            margin-top: 0.75rem;
            border-top: 1px dashed var(--glass-border);
            padding-top: 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .profile-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(30, 41, 59, 0.25);
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
        }

        .profile-name {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .profile-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            object-fit: cover;
            background-color: var(--bg-tertiary);
            border: 1px solid var(--glass-border);
        }

        .profile-status-badge {
            font-size: 0.725rem;
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
        }

        .profile-status-active {
            background-color: rgba(16, 185, 129, 0.15);
            color: #34d399;
        }

        .profile-status-inactive {
            background-color: rgba(239, 68, 68, 0.15);
            color: #f87171;
        }

        .switch-toggle {
            cursor: pointer;
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .switch-toggle:hover {
            color: var(--accent-color);
        }

        .profile-selector-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            max-height: 180px;
            overflow-y: auto;
            border: 1px solid var(--glass-border);
            padding: 0.75rem;
            border-radius: var(--radius-md);
            background: rgba(11, 15, 25, 0.4);
        }

        .profile-selector-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--bg-secondary);
            border: 1px solid var(--glass-border);
            padding: 0.6rem 0.75rem;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-size: 0.85rem;
        }

        .profile-selector-item:hover {
            border-color: var(--accent-color);
        }

        .profile-selector-item input {
            cursor: pointer;
            accent-color: var(--accent-color);
            width: 16px;
            height: 16px;
        }

        /* Image Source Switch UI */
        .image-source-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .image-source-btn {
            background: rgba(11, 15, 25, 0.6);
            border: 1px solid var(--glass-border);
            color: var(--text-muted);
            padding: 0.4rem 0.8rem;
            font-size: 0.775rem;
            font-weight: 600;
            border-radius: var(--radius-sm);
            cursor: pointer;
        }

        .image-source-btn.active {
            background-color: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }

        /* API Sandbox Alert */
        .sandbox-notice {
            background: rgba(245, 158, 11, 0.04);
            border: 1px solid rgba(245, 158, 11, 0.15);
            padding: 0.85rem 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.25rem;
            font-size: 0.8rem;
            color: #fbbf24;
            line-height: 1.4;
        }

        .sandbox-notice i {
            margin-right: 0.25rem;
        }

        /* Table */
        .recent-posts-card {
            margin-top: 2.5rem;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: var(--radius-md);
            border: 1px solid var(--glass-border);
            background: rgba(11, 15, 25, 0.4);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
        }

        th {
            background-color: rgba(19, 26, 46, 0.85);
            color: var(--text-muted);
            font-weight: 600;
            padding: 1rem;
            font-size: 0.775rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--glass-border);
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: rgba(255, 255, 255, 0.02);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success {
            background-color: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-danger {
            background-color: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .post-preview {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
            font-size: 0.85rem;
        }

        .post-link {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .post-link:hover {
            text-decoration: underline;
            color: #f472b6;
        }

        .empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 2.5rem;
            color: var(--text-muted);
            opacity: 0.4;
            margin-bottom: 1rem;
        }

        .img-thumbnail {
            width: 44px;
            height: 44px;
            border-radius: 4px;
            object-fit: cover;
            background-color: #000;
            border: 1px solid var(--glass-border);
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <header>
        <div class="header-logo">
            <i class="fab fa-instagram"></i>
            <div>
                <h1>Instagram Publisher</h1>
                <p style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">Multi-User & Multi-Profile Manager</p>
            </div>
        </div>
        <div class="header-badge">
            <i class="fas fa-plug"></i>
            <span>Instagram Graph API v20.0</span>
        </div>
    </header>

    <!-- Notifications -->
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    <!-- Main Grid -->
    <div class="dashboard-grid">
        
        <!-- Left Column -->
        <div class="section-group">
            
            <!-- Connection Card -->
            <div class="card card-accent-instagram">
                <h3 class="card-title"><i class="fas fa-link"></i> Connect Instagram Account</h3>
                
                <div class="token-mode-switch">
                    <button class="tab-btn active" onclick="switchTokenMode('oauth-mode', this)">Meta Login (Easy)</button>
                    <button class="tab-btn" onclick="switchTokenMode('user-mode', this)">User Token Explorer</button>
                    <button class="tab-btn" onclick="switchTokenMode('single-mode', this)">Single Profile Manual</button>
                </div>

                <!-- Meta OAuth Mode -->
                <div id="form-oauth-mode" style="text-align: center; padding: 1.25rem 0;">
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5;">
                        Fast and secure connection using standard Meta authentication. Instantly import your profile and all connected Instagram Business accounts.
                    </p>
                    <a href="{{ route('instagram.redirect') }}" class="btn btn-primary" style="background: var(--accent-gradient); font-weight: 700; font-size: 1rem; padding: 0.85rem 1.5rem; width: auto; display: inline-flex;">
                        <i class="fab fa-facebook-f" style="font-size: 1.15rem; margin-right: 0.5rem;"></i> Connect with Meta / Facebook
                    </a>
                </div>

                <!-- User Access Token Form -->
                <form id="form-user-mode" action="{{ route('instagram.connect.user-token') }}" method="POST" style="display: none;">
                    @csrf
                    <div class="form-group">
                        <label for="user_access_token">Facebook User Access Token</label>
                        <input type="text" id="user_access_token" name="user_access_token" class="input-control" placeholder="EAAwv1PIERk4..." required>
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.5rem;">
                            Must include <code>instagram_basic</code>, <code>instagram_content_publish</code>, <code>pages_show_list</code>, and <code>pages_read_engagement</code> permissions.
                        </p>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Import Meta Account
                    </button>
                </form>

                <!-- Single Profile Connection Form -->
                <form id="form-single-mode" action="{{ route('instagram.connect.single-profile') }}" method="POST" style="display: none;">
                    @csrf
                    <div class="form-group">
                        <label for="instagram_username">Instagram Username</label>
                        <input type="text" id="instagram_username" name="instagram_username" class="input-control" placeholder="e.g. my_business_shop" required>
                    </div>
                    <div class="form-group">
                        <label for="instagram_name">Instagram Display Name</label>
                        <input type="text" id="instagram_name" name="instagram_name" class="input-control" placeholder="e.g. My Business Shop">
                    </div>
                    <div class="form-group">
                        <label for="instagram_profile_id">Instagram Business Account ID</label>
                        <input type="text" id="instagram_profile_id" name="instagram_profile_id" class="input-control" placeholder="e.g. 17841405822304820" required>
                    </div>
                    <div class="form-group">
                        <label for="fb_page_id">Linked Facebook Page ID</label>
                        <input type="text" id="fb_page_id" name="fb_page_id" class="input-control" placeholder="e.g. 10459298379201" required>
                    </div>
                    <div class="form-group">
                        <label for="fb_page_access_token">Facebook Page Access Token</label>
                        <input type="text" id="fb_page_access_token" name="fb_page_access_token" class="input-control" placeholder="EAAwv..." required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Connect Single Profile
                    </button>
                </form>
            </div>

            <!-- Connected Accounts Card -->
            <div class="card">
                <h3 class="card-title"><i class="fas fa-users-cog"></i> Connected Accounts</h3>
                
                @if($accounts->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <p>No Meta Accounts linked. Connect via login or paste a token above to get started.</p>
                    </div>
                @else
                    <div class="accounts-container">
                        @foreach($accounts as $account)
                            <div class="account-item">
                                <div class="account-header">
                                    <div class="account-info">
                                        @if($account->avatar)
                                            <img src="{{ $account->avatar }}" alt="{{ $account->name }}" class="account-avatar">
                                        @else
                                            <div class="account-avatar" style="background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                                {{ strtoupper(substr($account->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="account-details">
                                            <h4>{{ $account->name }}</h4>
                                            <span>UID: {{ $account->fb_user_id }}</span>
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        @if($account->access_token !== 'manual')
                                            <button type="button" class="switch-toggle" style="color: var(--accent-warning); opacity: 0.9;" title="Renew / Update Access Token" onclick="toggleRenewForm('{{ $account->id }}')">
                                                <i class="fas fa-key"></i>
                                            </button>
                                        @endif
                                        
                                        <form action="{{ route('instagram.account.delete', $account->id) }}" method="POST" onsubmit="return confirm('Disconnect this account and all connected Instagram profiles?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="switch-toggle" style="color: var(--accent-danger); opacity: 0.8;" title="Disconnect Account">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Renew Token Form (Hidden by default) -->
                                <div id="renew-form-{{ $account->id }}" class="renew-token-form" style="display: none; margin-top: 1rem; padding: 0.75rem; background: rgba(245, 158, 11, 0.05); border: 1px dashed rgba(245, 158, 11, 0.3); border-radius: var(--radius-md); margin-bottom: 0.5rem;">
                                    <form action="{{ route('instagram.account.renew', $account->id) }}" method="POST">
                                        @csrf
                                        <div class="form-group" style="margin-bottom: 0.75rem;">
                                            <label style="color: var(--accent-warning); font-size: 0.8rem; font-weight: 600;">Enter New User Access Token:</label>
                                            <input type="text" name="new_access_token" class="input-control" placeholder="EAAwv1..." required style="padding: 0.5rem 0.75rem; font-size: 0.85rem;">
                                        </div>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <button type="submit" class="btn btn-primary" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; width: auto; background: var(--accent-warning); box-shadow: none; border: none; color: white; font-weight: 600; border-radius: var(--radius-md);">
                                                <i class="fas fa-sync-alt"></i> Update Token
                                            </button>
                                            <button type="button" class="btn btn-danger-outline" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; width: auto; border: 1px solid rgba(255,255,255,0.2); color: var(--text-muted); background: transparent; cursor: pointer; font-weight: 600; border-radius: var(--radius-md);" onclick="toggleRenewForm('{{ $account->id }}')">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Instagram Profiles List -->
                                <div class="profiles-list">
                                    <p style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Linked Instagram Profiles ({{ $account->profiles->count() }})</p>
                                    @if($account->profiles->isEmpty())
                                        <p style="font-size: 0.8rem; color: var(--text-muted);">No profiles found for this account.</p>
                                    @else
                                        @foreach($account->profiles as $profile)
                                            <div class="profile-item">
                                                <div class="profile-name">
                                                    @if($profile->avatar)
                                                        <img src="{{ $profile->avatar }}" alt="" class="profile-avatar">
                                                    @else
                                                        <div class="profile-avatar" style="background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; color: white; font-size: 0.6rem; font-weight: bold;">IG</div>
                                                    @endif
                                                    <span><strong>{{ $profile->instagram_name }}</strong> ({{ '@' . $profile->instagram_username }})</span>
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                    <span class="profile-status-badge {{ $profile->is_active ? 'profile-status-active' : 'profile-status-inactive' }}">
                                                        {{ $profile->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                    
                                                    <form action="{{ route('instagram.profile.toggle', $profile->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="switch-toggle" title="{{ $profile->is_active ? 'Deactivate Profile' : 'Activate Profile' }}">
                                                            <i class="fas {{ $profile->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}" style="color: {{ $profile->is_active ? 'var(--accent-success)' : 'var(--text-muted)' }}; font-size: 1.25rem;"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Autopost Form -->
        <div class="card card-accent-emerald" style="height: fit-content;">
            <h3 class="card-title"><i class="fas fa-paper-plane"></i> Publish to Instagram</h3>
            
            @if($activeProfiles->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle" style="color: var(--accent-warning);"></i>
                    <p style="margin-top: 1rem;">You must connect an account and activate at least one Instagram Profile before writing a post.</p>
                </div>
            @else
                <form action="{{ route('instagram.post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Profile Selector -->
                    <div class="form-group">
                        <label>Select Target Instagram Accounts (Multi-Selection Allowed)</label>
                        <div class="profile-selector-grid">
                            @foreach($activeProfiles as $profile)
                                <label class="profile-selector-item">
                                    <input type="checkbox" name="profiles[]" value="{{ $profile->id }}" checked>
                                    @if($profile->avatar)
                                        <img src="{{ $profile->avatar }}" alt="" style="width: 18px; height: 18px; border-radius: 50%;">
                                    @else
                                        <div style="width: 18px; height: 18px; border-radius: 50%; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-size: 0.4rem; color: white;">IG</div>
                                    @endif
                                    <span>{{ '@' . $profile->instagram_username }}</span>
                                    <span style="font-size: 0.65rem; color: var(--text-muted);">({{ $profile->account->name }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Caption input -->
                    <div class="form-group">
                        <label for="caption">Post Caption</label>
                        <textarea id="caption" name="caption" class="input-control" placeholder="Write your premium Instagram caption here... #autopost #laravel"></textarea>
                    </div>

                    <!-- Image Source Selector -->
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Image Attachment Source</label>
                        <div class="image-source-tabs">
                            <button type="button" id="tab-url-btn" class="image-source-btn active" onclick="switchImageSource('url')">Direct Image URL</button>
                            <button type="button" id="tab-file-btn" class="image-source-btn" onclick="switchImageSource('file')">Upload Image File</button>
                        </div>

                        <!-- Notice explaining Meta API crawler requirement -->
                        <div class="sandbox-notice">
                            <i class="fas fa-info-circle"></i>
                            <strong>Meta Server Constraint:</strong> Instagram requires image files to be publicly reachable by Meta's crawlers.
                            <span id="url-notice-text">Please provide a direct URL to an online image (e.g. from Unsplash or AWS S3).</span>
                            <span id="file-notice-text" style="display: none;">Uploaded files will be saved in public storage, but this will fail in a local <code>localhost</code> sandbox because Meta cannot query your local address. Use a tunnel (e.g. Ngrok) in local dev.</span>
                        </div>

                        <!-- Image URL input -->
                        <div id="image-url-group">
                            <input type="url" id="image_url" name="image_url" class="input-control" placeholder="https://images.unsplash.com/photo-...">
                        </div>

                        <!-- Image File input -->
                        <div id="image-file-group" style="display: none;">
                            <input type="file" id="image_file" name="image_file" class="input-control" accept="image/*">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fab fa-instagram"></i> Publish Instagram Post Now
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Recent Posts Log -->
    <div class="card recent-posts-card">
        <h3 class="card-title"><i class="fas fa-history"></i> Recent Publication History</h3>
        
        @if($recentPosts->isEmpty())
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <p>No recent posts recorded. Run a test publish above or from Artisan command line!</p>
            </div>
        @else
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Target Profile</th>
                            <th>Caption</th>
                            <th>Status</th>
                            <th>Link / Message</th>
                            <th>Posted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentPosts as $post)
                            <tr>
                                <td>
                                    @if($post->image_url)
                                        @if(str_starts_with($post->image_url, 'http://') || str_starts_with($post->image_url, 'https://'))
                                            <img src="{{ $post->image_url }}" alt="Post image" class="img-thumbnail" onerror="this.src='https://www.publicdomainpictures.net/pictures/30000/velka/plain-white-background.jpg'">
                                        @else
                                            <img src="{{ asset($post->image_url) }}" alt="Post image" class="img-thumbnail" onerror="this.src='https://www.publicdomainpictures.net/pictures/30000/velka/plain-white-background.jpg'">
                                        @endif
                                    @else
                                        <div class="img-thumbnail" style="display: flex; align-items: center; justify-content: center; background-color: var(--bg-tertiary); color: var(--text-muted); font-size: 0.65rem;">No Image</div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 600;">{{ $post->profile->instagram_name }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ '@' . $post->profile->instagram_username }}</div>
                                </td>
                                <td>
                                    <span class="post-preview" title="{{ $post->caption }}">{{ $post->caption ?: '[No Caption]' }}</span>
                                </td>
                                <td>
                                    @if($post->status === 'success')
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Success
                                        </span>
                                    @else
                                        <span class="badge badge-danger" title="{{ $post->error_message }}">
                                            <i class="fas fa-exclamation-circle"></i> Failed
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($post->status === 'success' && $post->ig_post_id)
                                        <!-- Get permanent link if returned from Instagram Service, otherwise search link -->
                                        @php
                                            // The permalink is returned via API if available, else we can fall back to standard instagram URL
                                            $linkUrl = $post->profile->posts()->where('ig_post_id', $post->ig_post_id)->whereNotNull('posted_at')->first()?->permalink ?? 'https://www.instagram.com/' . $post->profile->instagram_username;
                                        @endphp
                                        <a href="{{ $linkUrl }}" target="_blank" class="post-link">
                                            View on Instagram <i class="fas fa-external-link-alt" style="font-size: 0.75rem;"></i>
                                        </a>
                                    @elseif($post->error_message)
                                        <span style="font-size: 0.775rem; color: var(--accent-danger); font-family: monospace; display: block; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $post->error_message }}">
                                            {{ $post->error_message }}
                                        </span>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.8rem;">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">
                                        {{ $post->posted_at ? $post->posted_at->diffForHumans() : $post->created_at->diffForHumans() }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<script>
    // Tab switching for Connect forms
    function switchTokenMode(mode, btn) {
        // Toggle active class on buttons
        const tabButtons = document.querySelectorAll('.token-mode-switch .tab-btn');
        tabButtons.forEach(button => button.classList.remove('active'));
        btn.classList.add('active');

        // Hide all forms
        document.getElementById('form-oauth-mode').style.display = 'none';
        document.getElementById('form-user-mode').style.display = 'none';
        document.getElementById('form-single-mode').style.display = 'none';

        // Show the selected form
        document.getElementById('form-' + mode).style.display = mode === 'oauth-mode' ? 'block' : 'none';
        document.getElementById('form-' + mode).style.display = mode === 'user-mode' ? 'block' : 'none';
        document.getElementById('form-' + mode).style.display = mode === 'single-mode' ? 'block' : 'none';
        
        // Custom style fix for inline center layout on oauth block
        if(mode === 'oauth-mode') {
            document.getElementById('form-oauth-mode').style.display = 'block';
        } else if(mode === 'user-mode') {
            document.getElementById('form-user-mode').style.display = 'block';
        } else {
            document.getElementById('form-single-mode').style.display = 'block';
        }
    }

    // Toggle renew input form for specific accounts
    function toggleRenewForm(accountId) {
        const renewForm = document.getElementById('renew-form-' + accountId);
        if (renewForm.style.display === 'none') {
            renewForm.style.display = 'block';
        } else {
            renewForm.style.display = 'none';
        }
    }

    // Tab switching for image attachment sources
    function switchImageSource(source) {
        const urlBtn = document.getElementById('tab-url-btn');
        const fileBtn = document.getElementById('tab-file-btn');
        
        const urlGroup = document.getElementById('image-url-group');
        const fileGroup = document.getElementById('image-file-group');
        
        const urlNotice = document.getElementById('url-notice-text');
        const fileNotice = document.getElementById('file-notice-text');

        if (source === 'url') {
            urlBtn.classList.add('active');
            fileBtn.classList.remove('active');
            
            urlGroup.style.display = 'block';
            fileGroup.style.display = 'none';
            
            urlNotice.style.display = 'inline';
            fileNotice.style.display = 'none';
            
            document.getElementById('image_file').value = '';
            document.getElementById('image_url').setAttribute('required', 'required');
        } else {
            fileBtn.classList.add('active');
            urlBtn.classList.remove('active');
            
            fileGroup.style.display = 'block';
            urlGroup.style.display = 'none';
            
            fileNotice.style.display = 'inline';
            urlNotice.style.display = 'none';
            
            document.getElementById('image_url').value = '';
            document.getElementById('image_url').removeAttribute('required');
        }
    }
</script>

</body>
</html>
