<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found | 404 Error</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333;
            text-align: center;
        }
        
        .container {
            max-width: 600px;
            width: 100%;
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .error-code {
            font-size: 120px;
            font-weight: 800;
            color: #4a6cf7;
            line-height: 1;
            margin-bottom: 10px;
            text-shadow: 3px 3px 0 rgba(74, 108, 247, 0.1);
        }
        
        h1 {
            font-size: 32px;
            margin-bottom: 15px;
            color: #333;
        }
        
        p {
            font-size: 18px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: #4a6cf7;
            color: white;
            border: 2px solid #4a6cf7;
        }
        
        .btn-primary:hover {
            background-color: #3a5ce5;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(74, 108, 247, 0.3);
        }
        
        .btn-secondary {
            background-color: transparent;
            color: #4a6cf7;
            border: 2px solid #4a6cf7;
        }
        
        .btn-secondary:hover {
            background-color: rgba(74, 108, 247, 0.1);
            transform: translateY(-3px);
        }
        
        .illustration {
            margin: 30px 0;
            max-width: 300px;
            height: 200px;
            position: relative;
            margin-left: auto;
            margin-right: auto;
        }
        
        .circle {
            position: absolute;
            border-radius: 50%;
            background-color: rgba(74, 108, 247, 0.1);
        }
        
        .circle-1 {
            width: 120px;
            height: 120px;
            top: 10px;
            left: 20px;
        }
        
        .circle-2 {
            width: 80px;
            height: 80px;
            bottom: 20px;
            right: 30px;
        }
        
        .lost-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 80px;
            color: #4a6cf7;
        }
        
        .search-box {
            display: flex;
            margin: 25px 0;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .search-input {
            flex-grow: 1;
            padding: 12px 20px;
            border: 2px solid #eaeaea;
            border-radius: 50px 0 0 50px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
        }
        
        .search-input:focus {
            border-color: #4a6cf7;
        }
        
        .search-btn {
            background-color: #4a6cf7;
            color: white;
            border: none;
            padding: 0 25px;
            border-radius: 0 50px 50px 0;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .search-btn:hover {
            background-color: #3a5ce5;
        }
        
        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }
            
            .error-code {
                font-size: 100px;
            }
            
            h1 {
                font-size: 26px;
            }
            
            .buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">404</div>
        <h1>Oops! Page Not Found</h1>
        <p>The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
        
        <div class="illustration">
            <div class="circle circle-1"></div>
            <div class="circle circle-2"></div>
            <div class="lost-icon">🔍</div>
        </div>
        
        <div class="search-box">
            <input type="text" class="search-input" placeholder="Search our website...">
            <button class="search-btn">Search</button>
        </div>
        
        <div class="buttons">
            <a href="#" class="btn btn-primary">Go Back Home</a>
            <a href="#" class="btn btn-secondary">Contact Support</a>
        </div>
    </div>
    
    <script>
        // Simple search functionality
        document.querySelector('.search-btn').addEventListener('click', function() {
            const searchTerm = document.querySelector('.search-input').value;
            if (searchTerm.trim() !== '') {
                alert(`Searching for: "${searchTerm}"\n(In a real implementation, this would redirect to search results)`);
            }
        });
        
        // Allow pressing Enter to search
        document.querySelector('.search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('.search-btn').click();
            }
        });
        
        // Go Home button functionality
        document.querySelector('.btn-primary').addEventListener('click', function() {
            alert('Redirecting to home page...');
            // In a real implementation: window.location.href = '/';
        });
        
        // Contact Support button functionality
        document.querySelector('.btn-secondary').addEventListener('click', function() {
            alert('Opening contact form...');
            // In a real implementation: window.location.href = '/contact';
        });
    </script>
</body>
</html>