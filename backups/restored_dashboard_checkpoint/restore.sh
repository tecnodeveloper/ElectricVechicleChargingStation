#!/bin/bash
echo "Ì¥Ñ Restoring Admin Dashboard Checkpoint..."
echo "Ì≥Ö Created: October 1, 2025"
echo ""

# Navigate to project root
cd /z/evc

# Create backups of current files
echo "Ì≥¶ Creating backup of current files..."
cp app/Http/Controllers/AdminController.php app/Http/Controllers/AdminController.php.backup 2>/dev/null
cp resources/views/user/adminPanel.blade.php resources/views/user/adminPanel.blade.php.backup 2>/dev/null

# Restore checkpoint files
echo "‚ôªÔ∏è  Restoring checkpoint files..."
cp backups/restored_dashboard_checkpoint/AdminController.php app/Http/Controllers/
cp backups/restored_dashboard_checkpoint/adminPanel.blade.php resources/views/user/

# Clear cache
echo "Ì∑π Clearing Laravel cache..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear

echo ""
echo "‚úÖ Checkpoint restored successfully!"
echo "Ìºê Test at: http://localhost:8000/admin/dashboard"
echo ""
echo "Ì≥ã What was restored:"
echo "   - Full admin dashboard with stats cards"
echo "   - Recent Users table"
echo "   - Database integration"
echo "   - Alpine.js functionality"
echo "   - No Google Maps (clean setup)"
echo ""
