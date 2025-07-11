#!/bin/bash
# Fix common upload issues

echo "🔧 Fixing Instagram Clone Upload Issues..."

# Create directories if they don't exist
echo "📁 Creating upload directories..."
mkdir -p uploads/profiles
mkdir -p uploads/posts

# Set proper permissions
echo "🔐 Setting permissions..."
chmod 755 uploads/
chmod 755 uploads/profiles/
chmod 755 uploads/posts/

# Create .htaccess files for security
echo "🛡️ Creating security files..."
cat > uploads/.htaccess << 'EOF'
# Prevent PHP execution in uploads
<Files "*.php">
    Order Deny,Allow
    Deny from All
</Files>

# Only allow image files
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Order Allow,Deny
    Allow from All
</FilesMatch>
EOF

cp uploads/.htaccess uploads/profiles/
cp uploads/.htaccess uploads/posts/

# Create index.php files to prevent directory listing
echo "📄 Creating index files..."
cat > uploads/index.php << 'EOF'
<?php
header('HTTP/1.0 403 Forbidden');
exit('Directory access is forbidden.');
?>
EOF

cp uploads/index.php uploads/profiles/
cp uploads/index.php uploads/posts/

# Create default avatar if it doesn't exist
if [ ! -f "uploads/profiles/default-avatar.jpg" ]; then
    echo "👤 Creating default avatar..."
    # You'll need to add a default avatar image here
    echo "⚠️  Please add default-avatar.jpg to uploads/profiles/"
fi

echo "✅ Upload fix completed!"
echo ""
echo "📋 Next steps:"
echo "1. Run this script: bash fix-permissions.sh"
echo "2. Add default-avatar.jpg to uploads/profiles/"
echo "3. Check PHP settings (upload_max_filesize, post_max_size)"
echo "4. Test upload at: debug-upload.php"
