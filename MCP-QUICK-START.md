# LLM Ready MCP Quick Start Guide

Get your AI assistant connected to WordPress in 5 minutes!

## 🚀 Quick Setup

### 1. Enable MCP in WordPress

1. Go to **WordPress Admin → LLM Ready → MCP Settings**
2. Toggle **Enable MCP Server** ON
3. Click **Generate API Key**
4. Copy and save your API key

### 2. Configure Your AI Assistant

#### For Claude Desktop

Add to `claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "my-wordpress": {
      "command": "node",
      "args": ["/path/to/mcp-client.js"],
      "env": {
        "WORDPRESS_URL": "https://yoursite.com",
        "API_KEY": "your-api-key-here"
      }
    }
  }
}
```

#### For Custom Integration

```bash
# Test your connection
curl -X POST https://yoursite.com/wp-json/llmr-mcp/v1/query \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"action": "search", "parameters": {"query": "test"}}'
```

## 📋 Common Commands

### Search Content
```json
{
  "action": "search",
  "parameters": {
    "query": "your search terms",
    "limit": 10
  }
}
```

### Get a Post
```json
{
  "action": "get_post",
  "parameters": {
    "post_id": 123,
    "format": "markdown"
  }
}
```

### List Recent Posts
```json
{
  "action": "list_posts",
  "parameters": {
    "post_type": "post",
    "limit": 20
  }
}
```

## 🎯 Example Prompts for AI

Once connected, try these with your AI assistant:

- "Search my WordPress site for articles about SEO"
- "Get the latest 5 blog posts"
- "Find all pages with 'contact' in the title"
- "Show me the content of post ID 123"

## ⚡ Quick Testing

1. **In WordPress Admin:**
   - Go to **LLM Ready → MCP Settings**
   - Click **Test Connection**

2. **Via Command Line:**
   ```bash
   # Replace with your values
   curl https://yoursite.com/wp-json/llmr-mcp/v1/ \
     -H "Authorization: Bearer YOUR_API_KEY"
   ```

## 🔧 Troubleshooting

### Can't Connect?
- ✅ Check MCP is enabled
- ✅ Verify API key is correct
- ✅ Ensure HTTPS is working
- ✅ Check WordPress REST API is accessible

### No Results?
- ✅ Ensure content is published
- ✅ Check post type in query
- ✅ Verify search terms

## 📚 Next Steps

- Read the [full MCP usage guide](MCP-USAGE-GUIDE.md)
- Explore [advanced configurations](MCP-INTEGRATION-GUIDE.md)
- Join our [community forum](https://github.com/abnercalapiz/LLMReady/discussions)

---

Need help? Create an issue on [GitHub](https://github.com/abnercalapiz/LLMReady/issues)