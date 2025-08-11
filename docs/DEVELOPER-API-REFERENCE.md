# LLM Ready Developer API Reference

This document provides technical API reference for developers who want to test or integrate with the LLM Ready MCP endpoints.

## API Endpoints

The LLM Ready plugin exposes the following REST API endpoints:

### Base URL
```
https://yoursite.com/wp-json/llmr/mcp/v1
```

### Available Endpoints

#### 1. Discovery Endpoint
**GET** `/discovery`

Returns a list of all available endpoints and their capabilities.

```bash
curl https://yoursite.com/wp-json/llmr/mcp/v1/discovery
```

#### 2. Business Information
**GET** `/business`

Returns business information including name, description, and operating hours.

```bash
curl https://yoursite.com/wp-json/llmr/mcp/v1/business
```

#### 3. Contact Details
**GET** `/contact`

Returns contact information including phone, email, and address.

```bash
curl https://yoursite.com/wp-json/llmr/mcp/v1/contact
```

#### 4. Services/Products
**GET** `/services`

Returns a list of services or products offered.

```bash
curl https://yoursite.com/wp-json/llmr/mcp/v1/services
```

#### 5. Published Pages
**GET** `/pages`

Returns all published pages on the site.

```bash
curl https://yoursite.com/wp-json/llmr/mcp/v1/pages
```

#### 6. Content Search
**POST** `/search`

Search for content across the site.

```bash
# Basic search
curl -X POST https://yoursite.com/wp-json/llmr/mcp/v1/search \
  -H "Content-Type: application/json" \
  -d '{"query": "wordpress", "per_page": 5}'

# Search with specific post type
curl -X POST https://yoursite.com/wp-json/llmr/mcp/v1/search \
  -H "Content-Type: application/json" \
  -d '{"query": "services", "per_page": 10, "post_type": "page"}'
```

**Request Parameters:**
- `query` (string, required): Search query
- `per_page` (integer, optional): Number of results to return (default: 10)
- `post_type` (string, optional): Specific post type to search (default: all)

## Testing with Pretty Output

For better readability during testing, use `jq`:

```bash
# Install jq if needed
# Linux: sudo apt-get install jq
# Mac: brew install jq

# Use with any endpoint
curl https://yoursite.com/wp-json/llmr/mcp/v1/business | jq '.'
```

## Response Format

All endpoints return JSON responses with the following structure:

### Success Response
```json
{
  "success": true,
  "data": {
    // Response data here
  }
}
```

### Error Response
```json
{
  "success": false,
  "error": "Error message"
}
```

## Authentication

All LLM Ready MCP endpoints are public by default and do not require authentication. This is by design to allow AI agents to access the information freely.

## Rate Limiting

Rate limiting should be configured at the hosting or CDN level. The plugin does not implement rate limiting internally.

## CORS

The endpoints include appropriate CORS headers to allow browser-based access from AI tools.

## Integration Notes

1. **For AI Tool Developers**: These endpoints are designed to be consumed by AI agents. Use the MCP configuration approach for best results rather than direct API calls.

2. **For WordPress Developers**: You can extend these endpoints by using the WordPress REST API hooks and filters.

3. **For Testing**: Use the provided cURL examples to verify endpoints are working correctly before configuring MCP.

## Troubleshooting

### Common Issues

1. **404 Not Found**: Ensure the LLM Ready plugin is activated and permalinks are flushed.

2. **Empty Responses**: Check that the relevant data is configured in the plugin settings.

3. **CORS Errors**: Verify your hosting environment allows the necessary CORS headers.

### Debug Mode

Enable WordPress debug mode to see detailed error messages:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

## See Also

- [MCP Setup Guide](../MCP-SETUP-GUIDE.md) - For configuring AI agents to use these endpoints
- [Plugin Documentation](../README.md) - General plugin information