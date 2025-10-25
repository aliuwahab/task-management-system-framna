#!/bin/bash

# Task Management System - Setup Script
# Automates installation and setup process

set -e  # Exit on error

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Task Management System Setup          ║${NC}"
echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo ""

# Step 1: Check PHP version
echo -e "${BLUE}[1/6]${NC} Checking PHP version..."
PHP_VERSION=$(php -r "echo PHP_VERSION;")
REQUIRED_VERSION="8.3"

if php -r "exit(version_compare(PHP_VERSION, '$REQUIRED_VERSION', '<') ? 1 : 0);"; then
    echo -e "${GREEN}✓${NC} PHP $PHP_VERSION detected"
else
    echo -e "${RED}✗${NC} PHP $REQUIRED_VERSION or higher is required (found $PHP_VERSION)"
    exit 1
fi

# Step 2: Copy environment file
echo -e "${BLUE}[2/6]${NC} Setting up environment configuration..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓${NC} Created .env file from .env.example"
else
    echo -e "${YELLOW}⚠${NC} .env file already exists, skipping..."
fi

# Step 3: Install dependencies
echo -e "${BLUE}[3/6]${NC} Installing dependencies..."
if ! command -v composer &> /dev/null; then
    echo -e "${RED}✗${NC} Composer not found. Please install composer first."
    exit 1
fi

composer install --no-interaction --prefer-dist --optimize-autoloader
echo -e "${GREEN}✓${NC} Dependencies installed"

# Step 4: Create database schema
echo -e "${BLUE}[4/6]${NC} Creating database schema..."

# Check if database exists
if [ -f var/data.db ]; then
    echo -e "${YELLOW}⚠${NC} Database already exists. Drop and recreate? (y/N)"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
        php bin/console doctrine:schema:drop --force --quiet
        php bin/console doctrine:schema:create --quiet
        echo -e "${GREEN}✓${NC} Database recreated"
    else
        echo -e "${YELLOW}⚠${NC} Skipping database creation"
    fi
else
    php bin/console doctrine:schema:create --quiet
    echo -e "${GREEN}✓${NC} Database schema created"
fi

# Step 5: Run tests
echo -e "${BLUE}[5/6]${NC} Running tests to verify installation..."
if vendor/bin/phpunit --stop-on-failure --no-coverage > /dev/null 2>&1; then
    TEST_COUNT=$(vendor/bin/phpunit --testdox 2>&1 | grep -E "^OK" | grep -oE "[0-9]+ tests")
    echo -e "${GREEN}✓${NC} All tests passed ($TEST_COUNT)"
else
    echo -e "${RED}✗${NC} Some tests failed. Please check the output above."
    exit 1
fi

# Step 6: Success message
echo ""
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✓ Setup completed successfully!       ║${NC}"
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo ""
echo -e "${BLUE}Next steps:${NC}"
echo ""
echo -e "  ${GREEN}1.${NC} Start the development server:"
echo -e "     ${YELLOW}php -S localhost:8000 -t public${NC}"
echo ""
echo -e "  ${GREEN}2.${NC} Open in your browser:"
echo -e "     ${YELLOW}http://localhost:8000/api/v1/docs${NC}"
echo ""
echo -e "  ${GREEN}3.${NC} Run tests:"
echo -e "     ${YELLOW}vendor/bin/phpunit --testdox${NC}"
echo ""
echo -e "${BLUE}Quick test:${NC}"
echo -e "  curl http://localhost:8000/api/v1/tasks"
echo ""

# Ask if user wants to start the server
echo -e "${BLUE}Start the development server now? (Y/n)${NC}"
read -r response
if [[ ! "$response" =~ ^([nN][oO]|[nN])$ ]]; then
    echo ""
    echo -e "${GREEN}Starting development server at http://localhost:8000${NC}"
    echo -e "${YELLOW}Press Ctrl+C to stop${NC}"
    echo ""
    php -S localhost:8000 -t public
fi
