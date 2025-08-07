# LLM Ready MCP (Model Context Protocol) Usage Guide

## Table of Contents
1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Installation & Setup](#installation--setup)
4. [MCP Server Features](#mcp-server-features)
5. [Using MCP with AI Tools](#using-mcp-with-ai-tools)
6. [API Endpoints](#api-endpoints)
7. [Example Use Cases](#example-use-cases)
8. [Troubleshooting](#troubleshooting)
9. [Security Considerations](#security-considerations)

## Overview

The LLM Ready plugin includes a built-in MCP (Model Context Protocol) server that allows AI assistants to directly interact with your WordPress site. This enables AI tools to:

- Query and search your WordPress content
- Access post metadata and information
- Retrieve content in AI-optimized formats
- Perform administrative tasks (with proper authentication)

## Prerequisites

Before using the MCP features, ensure:

1. **WordPress Requirements**
   - WordPress 5.2 or higher
   - PHP 7.2 or higher
   - LLM Ready plugin activated

2. **MCP Client Requirements**
   - An MCP-compatible AI assistant (Claude, GPT-4, etc.)
   - API key for authentication (generated in plugin settings)

## Installation & Setup

### Step 1: Enable MCP Server

1. Navigate to **WordPress Admin → LLM Ready → MCP Settings**
2. Toggle **Enable MCP Server** to ON
3. Click **Save Changes**

### Step 2: Generate API Key

1. In MCP Settings, click **Generate New API Key**
2. Copy the generated key immediately (it won't be shown again)
3. Store the key securely

### Step 3: Configure Server Settings

```php
// Default MCP endpoint
https://yoursite.com/wp-json/llmr-mcp/v1/

// With custom permalink structure
https://yoursite.com/index.php?rest_route=/llmr-mcp/v1/
```

## MCP Server Features

### 1. Content Discovery
The MCP server provides intelligent content discovery:

```json
{
  "action": "search",
  "query": "wordpress security",
  "post_type": ["post", "page"],
  "limit": 10
}
```

### 2. Content Retrieval
Get content in AI-optimized formats:

```json
{
  "action": "get_post",
  "post_id": 123,
  "format": "markdown"
}
```

### 3. Metadata Access
Access rich metadata about your content:

```json
{
  "action": "get_metadata",
  "post_id": 123,
  "fields": ["author", "categories", "tags", "seo"]
}
```

## Using MCP with AI Tools

### Claude Desktop Configuration

1. Add to your Claude configuration file:

```json
{
  "mcpServers": {
    "wordpress-llmready": {
      "command": "curl",
      "args": [
        "-X", "POST",
        "https://yoursite.com/wp-json/llmr-mcp/v1/query",
        "-H", "Authorization: Bearer YOUR_API_KEY",
        "-H", "Content-Type: application/json"
      ]
    }
  }
}
```

### Using with Claude

Once configured, you can ask Claude:

- "Search my WordPress site for articles about SEO"
- "Get the content of post ID 123 in markdown format"
- "List all published pages on my site"
- "Find posts by author John Doe"

## API Endpoints

### Base Endpoint
```
https://yoursite.com/wp-json/llmr-mcp/v1/
```

### Available Endpoints

#### 1. Query Endpoint
```
POST /wp-json/llmr-mcp/v1/query
```

**Request Body:**
```json
{
  "action": "search|get_post|list_posts|get_metadata",
  "parameters": {
    // Action-specific parameters
  }
}
```

#### 2. Search Content
```json
{
  "action": "search",
  "parameters": {
    "query": "search terms",
    "post_type": ["post", "page"],
    "limit": 10,
    "orderby": "relevance|date|title",
    "order": "DESC|ASC"
  }
}
```

#### 3. Get Single Post
```json
{
  "action": "get_post",
  "parameters": {
    "post_id": 123,
    "format": "markdown|html|plain",
    "include_meta": true
  }
}
```

#### 4. List Posts
```json
{
  "action": "list_posts",
  "parameters": {
    "post_type": "post",
    "status": "publish",
    "limit": 20,
    "offset": 0,
    "orderby": "date",
    "order": "DESC"
  }
}
```

### Authentication

All requests must include the API key in the Authorization header:

```
Authorization: Bearer YOUR_API_KEY_HERE
```

## Example Use Cases

### Use Case 1: Content Research Assistant

An AI assistant helping with content research:

```python
# Python example
import requests

api_key = "your_api_key"
endpoint = "https://yoursite.com/wp-json/llmr-mcp/v1/query"

# Search for related content
response = requests.post(
    endpoint,
    headers={
        "Authorization": f"Bearer {api_key}",
        "Content-Type": "application/json"
    },
    json={
        "action": "search",
        "parameters": {
            "query": "artificial intelligence trends",
            "post_type": ["post"],
            "limit": 5
        }
    }
)

results = response.json()
```

### Use Case 2: Content Analysis

Analyze content structure and metadata:

```javascript
// JavaScript example
const analyzeContent = async (postId) => {
  const response = await fetch('https://yoursite.com/wp-json/llmr-mcp/v1/query', {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer YOUR_API_KEY',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      action: 'get_metadata',
      parameters: {
        post_id: postId,
        fields: ['title', 'excerpt', 'categories', 'tags', 'author', 'seo']
      }
    })
  });
  
  return await response.json();
};
```

### Use Case 3: Automated Content Summaries

Generate summaries of your content:

```bash
# cURL example
curl -X POST https://yoursite.com/wp-json/llmr-mcp/v1/query \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "get_post",
    "parameters": {
      "post_id": 123,
      "format": "markdown",
      "include_meta": true
    }
  }'
```

## Troubleshooting

### Common Issues

#### 1. Authentication Errors
```json
{
  "error": "Invalid API key"
}
```
**Solution:** Verify your API key is correct and hasn't expired.

#### 2. 404 Not Found
**Solution:** Check if:
- MCP server is enabled in settings
- Permalinks are properly configured
- REST API is not disabled

#### 3. Empty Results
**Solution:** Ensure:
- Content exists matching your query
- Post types are correctly specified
- Posts are published (not draft)

### Debug Mode

Enable debug mode in MCP settings to get detailed error messages:

1. Go to **LLM Ready → MCP Settings**
2. Enable **Debug Mode**
3. Check error logs in **Tools → Site Health → Info → LLM Ready MCP**

### Testing the MCP Server

Test your MCP server using the built-in tester:

1. Go to **LLM Ready → MCP Settings**
2. Click **Test MCP Connection**
3. Review the test results

## Security Considerations

### Best Practices

1. **API Key Management**
   - Rotate API keys regularly
   - Use different keys for different applications
   - Never commit API keys to version control

2. **Access Control**
   - Limit MCP access to specific IP addresses if possible
   - Use HTTPS only
   - Monitor API usage in logs

3. **Rate Limiting**
   - Default: 60 requests per minute
   - Configure in MCP Settings
   - Monitor for abuse

### Security Headers

The MCP server automatically includes security headers:

```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
```

### Audit Logging

All MCP requests are logged with:
- Timestamp
- IP address
- Requested action
- Response status

Access logs at: **Tools → Site Health → Info → LLM Ready MCP Logs**

## Advanced Configuration

### Custom Filters

Add custom filters to modify MCP responses:

```php
// Add to your theme's functions.php
add_filter('llmr_mcp_response', function($response, $action, $parameters) {
    // Modify response based on action
    if ($action === 'search') {
        // Add custom scoring or filtering
    }
    return $response;
}, 10, 3);
```

### Extending MCP Actions

Register custom MCP actions:

```php
add_filter('llmr_mcp_actions', function($actions) {
    $actions['custom_action'] = 'my_custom_handler';
    return $actions;
});

function my_custom_handler($parameters) {
    // Handle custom action
    return ['success' => true, 'data' => 'Custom response'];
}
```

## Support

For support and questions:

1. Check the [plugin documentation](https://github.com/abnercalapiz/LLMReady)
2. Submit issues on [GitHub](https://github.com/abnercalapiz/LLMReady/issues)
3. Contact [Jezweb support](https://www.jezweb.com.au/support)

---

**Note:** This guide is for LLM Ready v1.0.0. Features may vary in other versions.