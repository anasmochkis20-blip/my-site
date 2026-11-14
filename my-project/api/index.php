{
  "functions": {
    "api/index.php": {
      "runtime": "vercel-php@0.7.1"
    },
    "api/track/*.php": {
      "runtime": "vercel-php@0.7.1"
    }
  },
  "routes": [
    {
      "src": "/(.*\\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf))",
      "dest": "/$1"
    },
    {
      "src": "/track/(.*)",
      "dest": "/api/track/$1"
    },
    {
      "src": "/(.*)",
      "dest": "/api/index.php"
    }
  ]
}
