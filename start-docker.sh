#!/bin/bash
echo "Starting Docker containers (Azure-like environment)..."
echo ""
echo "This will start:"
echo "  - nginx (Port 8000)"
echo "  - PHP 8.2-FPM"
echo "  - SQL Server 2022 (Port 1433)"
echo ""
echo "Press Ctrl+C to stop"
echo ""

docker-compose up



