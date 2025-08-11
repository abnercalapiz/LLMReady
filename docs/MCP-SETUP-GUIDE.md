# LLM Ready MCP Setup Guide

This guide will walk you through setting up MCP (Model Context Protocol) to enable natural language queries for your WordPress sites with the LLM Ready plugin.

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Single Site Setup](#single-site-setup)
3. [Multiple Sites Setup](#multiple-sites-setup)
4. [Testing Your Setup](#testing-your-setup)
5. [Example Queries](#example-queries)
6. [Troubleshooting](#troubleshooting)

## Prerequisites

Before setting up MCP, ensure:
- ✅ LLM Ready plugin is installed and activated on your WordPress site(s)
- ✅ You have Claude Desktop app installed (or another MCP-compatible AI tool)
- ✅ Your WordPress site has HTTPS enabled
- ✅ REST API is accessible at `https://yoursite.com/wp-json/`

## Single Site Setup

### Step 1: Test Your WordPress MCP Endpoints

First, verify your endpoints are working by visiting these URLs in your browser:
```
https://yoursite.com/wp-json/llmr/mcp/v1/discovery
https://yoursite.com/wp-json/llmr/mcp/v1/business
```

You should see JSON responses with your site data.

### Step 2: Locate Claude Desktop Configuration

Find your Claude Desktop configuration file:

**Windows:**
```
%APPDATA%\Claude\claude_desktop_config.json
```

**macOS:**
```
~/Library/Application Support/Claude/claude_desktop_config.json
```

**Linux:**
```
~/.config/Claude/claude_desktop_config.json
```

### Step 3: Edit Configuration File

Open the configuration file and add your WordPress site:

```json
{
  "mcpServers": {
    "my-wordpress": {
      "command": "npx",
      "args": [
        "-y",
        "@modelcontextprotocol/server-fetch@latest"
      ],
      "env": {
        "FETCH_CONFIG": "{\"wordpress\":{\"baseUrl\":\"https://yoursite.com/wp-json/llmr/mcp/v1\",\"endpoints\":{\"discovery\":\"/discovery\",\"business\":\"/business\",\"contact\":\"/contact\",\"services\":\"/services\",\"search\":{\"path\":\"/search\",\"method\":\"POST\"}}}}"
      }
    }
  }
}
```

**Important:** Replace `https://yoursite.com` with your actual WordPress site URL.

### Step 4: Restart Claude Desktop

1. Completely quit Claude Desktop (not just close the window)
2. Restart Claude Desktop
3. You should see "my-wordpress" in the MCP connections

### Step 5: Start Using Natural Language

Now you can type queries like:
- "What services does my website offer?"
- "Show me posts about social media"
- "What are the business hours?"
- "Find articles about SEO"

## Multiple Sites Setup

For managing multiple WordPress sites (e.g., 100 sites), you'll need a custom MCP server.

### Option 1: Simple Multi-Site Configuration

Add multiple servers to your Claude configuration:

```json
{
  "mcpServers": {
    "site1": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-fetch@latest"],
      "env": {
        "FETCH_CONFIG": "{\"wordpress\":{\"baseUrl\":\"https://site1.com/wp-json/llmr/mcp/v1\",\"endpoints\":{\"search\":{\"path\":\"/search\",\"method\":\"POST\"},\"business\":\"/business\"}}}"
      }
    },
    "site2": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-fetch@latest"],
      "env": {
        "FETCH_CONFIG": "{\"wordpress\":{\"baseUrl\":\"https://site2.com/wp-json/llmr/mcp/v1\",\"endpoints\":{\"search\":{\"path\":\"/search\",\"method\":\"POST\"},\"business\":\"/business\"}}}"
      }
    }
  }
}
```

### Option 2: Custom Multi-Site MCP Server

1. **Install Node.js** (if not already installed)

2. **Create a project folder:**
```bash
mkdir wordpress-mcp-multisite
cd wordpress-mcp-multisite
npm init -y
npm install @modelcontextprotocol/sdk node-fetch
```

3. **Create the MCP server file** (`multisite-server.js`):
```javascript
#!/usr/bin/env node
import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import fetch from 'node-fetch';

// Add all your WordPress sites here
const SITES = {
  'site1': 'https://site1.com',
  'site2': 'https://site2.com',
  'newcastle': 'https://www.newcastleseo.com.au',
  // Add up to 100 sites...
};

const server = new Server({
  name: 'wordpress-multisite',
  version: '1.0.0',
}, {
  capabilities: { tools: {} },
});

// Search tool
server.setRequestHandler('tools/call', async (request) => {
  const { name, arguments: args } = request.params;
  
  if (name === 'search_all_sites') {
    const { query } = args;
    const results = {};
    
    for (const [name, url] of Object.entries(SITES)) {
      try {
        const response = await fetch(`${url}/wp-json/llmr/mcp/v1/search`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ query, per_page: 5 })
        });
        results[name] = await response.json();
      } catch (error) {
        results[name] = { error: error.message };
      }
    }
    
    return { toolResult: results };
  }
});

// Define available tools
server.setRequestHandler('tools/list', async () => {
  return {
    tools: [{
      name: 'search_all_sites',
      description: 'Search across all WordPress sites',
      inputSchema: {
        type: 'object',
        properties: {
          query: { type: 'string', description: 'Search query' }
        },
        required: ['query']
      }
    }]
  };
});

const transport = new StdioServerTransport();
await server.connect(transport);
```

4. **Configure Claude Desktop:**
```json
{
  "mcpServers": {
    "wordpress-multisite": {
      "command": "node",
      "args": ["/path/to/multisite-server.js"]
    }
  }
}
```

## Testing Your Setup

### 1. Check MCP Connection
After restarting Claude, look for the MCP icon or connection indicator showing your server is connected.

### 2. Test Basic Queries
Try these test queries:
- "What endpoints are available?"
- "Show me the business information"
- "Search for recent posts"

### 3. Verify Responses
You should get formatted responses with data from your WordPress site(s).

## Example Queries

### Single Site Queries:
- "What services does this business offer?"
- "Show me posts about WordPress"
- "Find articles published this month"
- "What are the contact details?"
- "Search for SEO tips"

### Multi-Site Queries:
- "Search all sites for social media content"
- "Find WordPress tutorials across all my sites"
- "Show me recent posts from newcastle site"
- "Compare services offered by site1 and site2"

## Troubleshooting

### Issue: "MCP server not connected"
**Solution:**
1. Check your configuration file syntax (valid JSON)
2. Ensure all quotes are properly escaped
3. Restart Claude Desktop completely

### Issue: "No results returned"
**Solution:**
1. Test your endpoints directly in browser
2. Check WordPress REST API is enabled
3. Verify LLM Ready plugin is activated
4. Check site URL includes https://

### Issue: "Authentication error"
**Solution:**
- The LLM Ready MCP endpoints are public by default
- Check if your hosting provider blocks REST API
- Verify .htaccess isn't blocking /wp-json/

### Issue: "Slow responses"
**Solution:**
1. Enable caching in WordPress
2. Use a CDN for your API endpoints
3. Limit search results with `per_page` parameter

### Debug Mode
To see detailed logs, run Claude Desktop from terminal:
```bash
# Windows
claude-desktop.exe --debug

# macOS/Linux
claude-desktop --debug
```

## Advanced Configuration

### Custom Headers
If your sites require authentication:
```json
{
  "env": {
    "FETCH_CONFIG": "{\"wordpress\":{\"baseUrl\":\"https://site.com/wp-json/llmr/mcp/v1\",\"headers\":{\"Authorization\":\"Bearer YOUR_TOKEN\"}}}"
  }
}
```

### Timeout Settings
For slower connections:
```json
{
  "env": {
    "FETCH_CONFIG": "{\"wordpress\":{\"baseUrl\":\"https://site.com/wp-json/llmr/mcp/v1\",\"timeout\":30000}}"
  }
}
```

## Best Practices

1. **Name your MCP servers clearly** - Use site names or descriptive labels
2. **Test endpoints first** - Always verify URLs work in browser
3. **Start with one site** - Get single site working before multi-site
4. **Use HTTPS** - MCP requires secure connections
5. **Monitor performance** - Check your site's API response times

## Getting Help

- **Plugin Issues:** Visit the WordPress plugin support forum
- **MCP Issues:** Check Claude Desktop documentation
- **API Issues:** Review your WordPress REST API settings

Remember: Once configured properly, you'll never need to type URLs or code - just natural language queries!