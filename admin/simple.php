<?php
// SUPER SIMPLE ADMIN - NO REDIRECTS, NO SESSIONS
echo "<!DOCTYPE html>
<html>
<head>
    <title>Simple Admin - TechFlix</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #1a1a2e; color: white; padding: 20px; }
        .header { background: #16213e; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: #0f3460; padding: 20px; border-radius: 10px; }
        .btn { background: #6a5af9; color: white; padding: 10px 15px; border: none; border-radius: 5px; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #5b4af0; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>🚀 TechFlix Simple Admin</h1>
        <p>Page Management Dashboard</p>
    </div>

    <div class='stats'>
        <div class='stat-card'>
            <h3>🏠 Home Page</h3>
            <p>Manage home.php content</p>
            <a href='../home.php' target='_blank' class='btn'>View Page</a>
            <a href='#edit-home' class='btn'>Edit</a>
        </div>
        
        <div class='stat-card'>
            <h3>🔍 Browser Page</h3>
            <p>Manage browser.php content</p>
            <a href='../browser.php' target='_blank' class='btn'>View Page</a>
            <a href='#edit-browser' class='btn'>Edit</a>
        </div>
        
        <div class='stat-card'>
            <h3>📺 TV Shows</h3>
            <p>Manage tv_show.php content</p>
            <a href='../tv_show.php' target='_blank' class='btn'>View Page</a>
            <a href='#edit-tv' class='btn'>Edit</a>
        </div>
        
        <div class='stat-card'>
            <h3>👶 Kids Content</h3>
            <p>Manage kids.php content</p>
            <a href='../kids.php' target='_blank' class='btn'>View Page</a>
            <a href='#edit-kids' class='btn'>Edit</a>
        </div>
        
        <div class='stat-card'>
            <h3>👤 Profile</h3>
            <p>Manage profile.php content</p>
            <a href='../profile.php' target='_blank' class='btn'>View Page</a>
            <a href='#edit-profile' class='btn'>Edit</a>
        </div>
    </div>

    <div style='background: #0f3460; padding: 20px; border-radius: 10px;'>
        <h2>Quick Actions</h2>
        <div style='margin-top: 15px;'>
            <a href='content.php' class='btn'>📹 Manage Content</a>
            <a href='users.php' class='btn'>👥 Manage Users</a>
            <a href='media.php' class='btn'>🖼️ Media Library</a>
            <a href='settings.php' class='btn'>⚙️ Settings</a>
        </div>
    </div>

    <div style='margin-top: 20px; padding: 15px; background: #2ed573; color: #000; border-radius: 5px;'>
        <strong>✅ SUCCESS!</strong> This simple admin page is working without redirects.
    </div>
</body>
</html>";
?>