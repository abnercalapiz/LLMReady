# MCP (Model Context Protocol) Testing Guide

This guide will help you test the MCP functionality of your LLM Ready WordPress plugin to ensure AI agents can properly interact with your site.

## Prerequisites

1. **WordPress Site with LLM Ready Plugin**
   - Plugin installed and activated (version 1.0.3+)
   - MCP settings configured in WordPress admin
   - Public access to your site (no maintenance mode)

2. **Testing Tools**
   - Web browser
   - Command line with `curl` (or Postman/Insomnia)
   - Python 3.x (optional, for advanced testing)
   - Claude Desktop or other MCP-compatible AI tool (optional)

## Step 1: Configure Your WordPress Site

1. Log into WordPress admin
2. Navigate to **LLM Ready → MCP Settings**
3. Fill in the required information:
   - Business name and description
   - Contact details (email, phone, address)
   - Business hours
   - Services/products
4. Click **Save Changes**

## Step 2: Update Configuration Files

All MCP configuration files in this repository use `https://your-domain.com` as a placeholder. You need to replace this with your actual WordPress site URL.

### For Basic Testing

Edit `mcp-simple-config.json`:
```json
{
  "wordpress_mcp": {
    "base_url": "https://example.com/wp-json/llmr/mcp/v1",
    "endpoints": [...]
  }
}
```

Replace `https://your-domain.com` with your actual site URL (e.g., `https://example.com`).

### For Claude Desktop

Edit `claude-desktop-config.json`:
```json
{
  "mcpServers": {
    "wordpress": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-fetch"],
      "env": {
        "WORDPRESS_SITE_URL": "https://example.com",
        "WORDPRESS_API_BASE": "/wp-json/llmr/mcp/v1"
      }
    }
  }
}
```

## Step 3: Test MCP Endpoints

### Method 1: Using the Built-in Test Tool

1. Open `mcp-test-tool.html` in a web browser
2. Update the base URL to your WordPress site
3. Click each endpoint button to test

### Method 2: Using curl (Command Line)

Test each endpoint individually:

```bash
# Test Discovery endpoint
curl https://example.com/wp-json/llmr/mcp/v1/discovery

# Test Business Info endpoint
curl https://example.com/wp-json/llmr/mcp/v1/business

# Test Contact endpoint
curl https://example.com/wp-json/llmr/mcp/v1/contact

# Test Services endpoint
curl https://example.com/wp-json/llmr/mcp/v1/services

# Test Pages endpoint
curl https://example.com/wp-json/llmr/mcp/v1/pages

# Test Search endpoint (POST request)
curl -X POST https://example.com/wp-json/llmr/mcp/v1/search \
  -H "Content-Type: application/json" \
  -d '{"query": "your search term", "per_page": 5}'
```

### Method 3: Using Python Script

1. Edit `mcp-search-example.py` and update the URL:
```python
url = 'https://example.com/wp-json/llmr/mcp/v1/search'
```

2. Run the script:
```bash
python3 mcp-search-example.py
```

### Method 4: WordPress Admin Quick Test

1. Go to **LLM Ready → MCP Settings** in WordPress admin
2. Scroll to the "Test MCP Search" section
3. Enter a search query
4. Click "Test Search"
5. Results will appear below

## Step 4: Verify Responses

Each endpoint should return a JSON response with:
- `success: true`
- `data` object containing the relevant information

### Expected Response Examples

**Discovery Endpoint:**
```json
{
  "success": true,
  "data": {
    "version": "1.0",
    "endpoints": [...],
    "capabilities": [...]
  }
}
```

**Business Endpoint:**
```json
{
  "success": true,
  "data": {
    "name": "Your Business Name",
    "description": "Your business description",
    "hours": {...},
    "timezone": "America/New_York"
  }
}
```

**Search Endpoint:**
```json
{
  "success": true,
  "data": [
    {
      "title": "Post Title",
      "url": "https://example.com/post-slug",
      "excerpt": "Post excerpt...",
      "type": "post"
    }
  ]
}
```

## Step 5: Test with AI Tools

### Claude Desktop Integration

1. Copy the contents of `claude-desktop-config.json`
2. Add it to your Claude Desktop configuration
3. Restart Claude Desktop
4. Test by asking Claude about your website content

### Custom MCP Client

Use the configuration files as templates to integrate with other MCP-compatible tools.

## Troubleshooting

### Common Issues

1. **404 Not Found**
   - Check if plugin is activated
   - Verify permalinks are set (Settings → Permalinks → Save)
   - Ensure URL is correct: `/wp-json/llmr/mcp/v1/[endpoint]`

2. **Empty Responses**
   - Check if MCP settings are saved in WordPress admin
   - Verify you have published content (for search/pages endpoints)
   - Check PHP error logs

3. **CORS Errors (Browser Testing)**
   - MCP endpoints allow CORS by default
   - Check if security plugins are blocking requests
   - Try testing with curl instead

4. **Authentication Errors**
   - MCP endpoints are public by default
   - No authentication required
   - Check if security plugins are adding authentication

### Debug Mode

Enable WordPress debug mode to see detailed errors:
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check logs at: `wp-content/debug.log`

## Advanced Testing

### Load Testing
```bash
# Test endpoint performance
for i in {1..10}; do
  time curl -s https://example.com/wp-json/llmr/mcp/v1/discovery > /dev/null
done
```

### Monitoring API Usage
1. Go to **LLM Ready → Stats** in WordPress admin
2. View real-time API usage statistics
3. Export data as CSV for analysis

## Security Considerations

- MCP endpoints are public (no authentication required)
- Only public content is exposed
- No sensitive data should be added to MCP settings
- Rate limiting is recommended for production sites

## Next Steps

1. Configure your AI tools to use the MCP endpoints
2. Monitor usage through the Stats dashboard
3. Customize responses by modifying plugin settings
4. Consider implementing caching for high-traffic sites

## Support

- GitHub Issues: https://github.com/abnercalapiz/LLMReady/issues
- Documentation: Check the repository README
- WordPress Support: Via plugin page on WordPress.org (if published)