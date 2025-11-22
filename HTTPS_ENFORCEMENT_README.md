# HTTPS Enforcement Implementation

## Overview
This implementation enforces HTTPS in production environments while allowing HTTP on localhost for local development and demos.

## How It Works

### 1. **Localhost Detection**
The system automatically detects if you're running on localhost by checking:
- `localhost`
- `127.0.0.1`
- `::1` (IPv6 localhost)
- Private IP ranges (192.168.x.x, 10.0.x.x, 172.16-31.x.x)

### 2. **HTTPS Enforcement**
- **On Localhost**: HTTP is allowed (no redirect to HTTPS)
- **In Production**: HTTP requests are automatically redirected to HTTPS (301 redirect)

### 3. **Security Headers**
The following security headers are automatically set:
- **X-Frame-Options**: Prevents clickjacking
- **X-Content-Type-Options**: Prevents MIME type sniffing
- **X-XSS-Protection**: Enables XSS filter
- **Referrer-Policy**: Controls referrer information
- **Content-Security-Policy**: Basic CSP for XSS protection
- **Permissions-Policy**: Controls browser features
- **HSTS (HTTP Strict Transport Security)**: Only in production with HTTPS
- **Secure Session Cookies**: Only in production with HTTPS

## Files Created/Modified

### New Files:
1. **`include/security_headers.php`**
   - Main security headers implementation
   - Handles localhost detection
   - Enforces HTTPS in production
   - Sets all security headers

2. **`.htaccess`**
   - Apache/XAMPP configuration
   - HTTPS redirect rules (localhost-aware)
   - Additional security headers at server level
   - Directory protection

### Modified Files:
- `include/config.php` - Added environment detection constants
- `func.php` - Added security headers include
- `func1.php` - Added security headers include
- `func3.php` - Added security headers include
- `index1.php` - Added security headers include
- `admin-panel.php` - Added security headers include
- `doctor-panel.php` - Added security headers include
- `admin-panel1.php` - Added security headers include

## Testing on Localhost

### Option 1: HTTP (Simplest - No Setup Required)
1. **Access via HTTP**: `http://localhost/Hospital-Management-System-master/`
   - ✅ Works normally (no HTTPS redirect)
   - ✅ All security headers are set
   - ✅ Session cookies work correctly
   - ✅ **Recommended for quick demos**

### Option 2: HTTPS with Trusted Certificate (For Testing SSL)
If you want to test HTTPS functionality on localhost:

1. **Set up SSL certificate** (see `SETUP_SSL_LOCALHOST.md` for detailed instructions)
   - Use **mkcert** (recommended - easiest method)
   - Or create a self-signed certificate and trust it

2. **Access via HTTPS**: `https://localhost/Hospital-Management-System-master/`
   - ✅ Works with trusted certificate (no warnings)
   - ✅ HSTS header is set (1 hour max-age for localhost)
   - ✅ Secure session cookies enabled
   - ✅ Full HTTPS testing capabilities

### Why HTTPS Shows Certificate Error by Default?
- XAMPP uses a **self-signed SSL certificate** for HTTPS
- Browsers don't trust self-signed certificates by default
- **Solution**: Set up a trusted certificate using mkcert (see `SETUP_SSL_LOCALHOST.md`)

### Quick SSL Setup (mkcert - Recommended)
```powershell
# 1. Install mkcert
choco install mkcert  # or download from GitHub

# 2. Install local CA
mkcert -install

# 3. Generate certificate
cd C:\xampp\apache\conf
mkcert localhost 127.0.0.1 ::1

# 4. Configure Apache (see SETUP_SSL_LOCALHOST.md)
# 5. Restart Apache
```

After setup, `https://localhost/` will work with a green padlock! 🔒

## Production Deployment

When deploying to production:

1. **Ensure SSL Certificate is installed**
2. **The system will automatically:**
   - Redirect all HTTP traffic to HTTPS
   - Set HSTS headers
   - Enable secure session cookies
   - Apply all security headers

3. **No configuration changes needed** - the system detects production automatically

## Verification

### Check Security Headers:
You can verify headers are being set by:
1. Opening browser Developer Tools (F12)
2. Go to Network tab
3. Reload the page
4. Click on any request
5. Check "Response Headers" section

You should see:
- `X-Frame-Options: SAMEORIGIN`
- `X-Content-Type-Options: nosniff`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`
- And more...

### Check HTTPS Enforcement:
- On localhost: HTTP works (no redirect)
- On production: HTTP redirects to HTTPS (301 redirect)

## Troubleshooting

### Issue: "Your connection is not private" / Certificate Error on HTTPS
- **Cause**: You're accessing `https://localhost/...` with a self-signed certificate
- **Solution**: **Use HTTP instead of HTTPS for localhost**
  - Access: `http://localhost/Hospital-Management-System-master/`
  - This is the correct way for local development
  - Security headers still work on HTTP for localhost
  - No certificate warnings

### Issue: Headers not appearing
- **Solution**: Make sure `mod_headers` is enabled in Apache
- Check Apache error logs

### Issue: HTTPS redirect not working
- **Solution**: Make sure `mod_rewrite` is enabled in Apache
- Check `.htaccess` file is in the root directory
- Verify Apache `AllowOverride` is set to `All` or at least `FileInfo`

### Issue: Session cookies not secure
- **Solution**: This is expected on localhost (HTTP)
- In production with HTTPS, cookies will automatically be secure

## Apache Module Requirements

Make sure these Apache modules are enabled in XAMPP:
- `mod_rewrite` (for HTTPS redirects)
- `mod_headers` (for security headers)

To enable in XAMPP:
1. Open `httpd.conf`
2. Uncomment: `LoadModule rewrite_module modules/mod_rewrite.so`
3. Uncomment: `LoadModule headers_module modules/mod_headers.so`
4. Restart Apache

## Notes

- The Content-Security-Policy (CSP) is set to allow common CDNs used by the application
- If you add new external resources, you may need to update the CSP in `include/security_headers.php`
- HSTS is only set in production with HTTPS to avoid issues during development
- Session cookie security is automatically adjusted based on environment

