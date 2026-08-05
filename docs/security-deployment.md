# Security Deployment & Server Configuration Guide

## Nginx Public Upload Protection

To block script execution (PHP, PHTML, PHAR, etc.) inside the public upload directory on Nginx servers, add the following location block to your site configuration:

```nginx
# Deny execution of PHP/script files inside public storage upload folder
location ~* ^/storage/.*\.(php|phtml|phar|php[0-9]|phps)$ {
    deny all;
    return 404;
}
```

## Apache Upload Protection
Apache protection is handled directly by `.htaccess` rules in:
- `storage/app/public/.htaccess`
- `public/.htaccess`

These prevent script handling and CGI execution inside the `/storage/` directory.
