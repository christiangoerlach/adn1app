#!/bin/bash
echo "Starting local PHP development server..."
echo ""
echo "Server will be available at: http://localhost:8000"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""
cd public
php -S localhost:8000 router.php

