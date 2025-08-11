# MCP Multi-Site Management Guide

This guide shows you how to manage multiple WordPress sites (even 100+) with the LLM Ready plugin using MCP, including how to dynamically add new sites.

## Table of Contents
1. [Quick Start](#quick-start)
2. [Dynamic Site Management](#dynamic-site-management)
3. [Configuration Methods](#configuration-methods)
4. [Natural Language Usage](#natural-language-usage)
5. [Managing 100+ Sites](#managing-100-sites)
6. [Best Practices](#best-practices)

## Quick Start

### Step 1: Install LLM Ready Plugin on Each WordPress Site

Install and activate the LLM Ready plugin on each WordPress site you want to manage:
- Site 1: `https://client1.com` ✓
- Site 2: `https://client2.com` ✓
- Site 3: `https://client3.com` ✓
- ... up to 100+ sites

### Step 2: Verify Each Site's MCP Endpoints

Test each site by visiting:
```
https://client1.com/wp-json/llmr/mcp/v1/discovery
```

### Step 3: Create Your Multi-Site MCP Server

## Dynamic Site Management

### Option 1: Configuration File Approach (Recommended)

Create `sites-config.json`:
```json
{
  "sites": {
    "client1": {
      "name": "Client 1 Website",
      "url": "https://client1.com",
      "tags": ["ecommerce", "fashion"]
    },
    "client2": {
      "name": "Client 2 Blog",
      "url": "https://client2.com",
      "tags": ["blog", "tech"]
    },
    "newcastle-seo": {
      "name": "Newcastle SEO",
      "url": "https://www.newcastleseo.com.au",
      "tags": ["agency", "seo"]
    }
  }
}
```

Create `dynamic-mcp-server.js`:
```javascript
#!/usr/bin/env node
import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import fetch from 'node-fetch';
import fs from 'fs/promises';
import path from 'path';

class DynamicWordPressMCP {
  constructor() {
    this.configPath = path.join(process.env.HOME, '.wordpress-mcp', 'sites-config.json');
    this.sites = {};
    this.loadSites();
  }

  async loadSites() {
    try {
      const data = await fs.readFile(this.configPath, 'utf8');
      const config = JSON.parse(data);
      this.sites = config.sites;
    } catch (error) {
      console.error('Creating new config file...');
      await this.saveSites();
    }
  }

  async saveSites() {
    const dir = path.dirname(this.configPath);
    await fs.mkdir(dir, { recursive: true });
    await fs.writeFile(this.configPath, JSON.stringify({ sites: this.sites }, null, 2));
  }

  async addSite(id, name, url, tags = []) {
    this.sites[id] = { name, url, tags };
    await this.saveSites();
    return `Added site: ${name}`;
  }

  async removeSite(id) {
    const site = this.sites[id];
    if (site) {
      delete this.sites[id];
      await this.saveSites();
      return `Removed site: ${site.name}`;
    }
    return `Site not found: ${id}`;
  }

  async searchAllSites(query) {
    const results = [];
    
    for (const [id, site] of Object.entries(this.sites)) {
      try {
        const response = await fetch(`${site.url}/wp-json/llmr/mcp/v1/search`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ query, per_page: 5 })
        });
        
        const data = await response.json();
        results.push({
          site: id,
          name: site.name,
          results: data
        });
      } catch (error) {
        results.push({
          site: id,
          name: site.name,
          error: error.message
        });
      }
    }
    
    return results;
  }

  async searchByTag(query, tag) {
    const taggedSites = Object.entries(this.sites)
      .filter(([_, site]) => site.tags.includes(tag));
    
    const results = [];
    for (const [id, site] of taggedSites) {
      // Same search logic as above
    }
    
    return results;
  }

  listSites() {
    return Object.entries(this.sites).map(([id, site]) => ({
      id,
      ...site
    }));
  }
}

// Initialize MCP Server
const wpManager = new DynamicWordPressMCP();
const server = new Server({
  name: 'wordpress-multisite-manager',
  version: '1.0.0',
});

// Define available tools
server.setRequestHandler('tools/list', async () => {
  return {
    tools: [
      {
        name: 'add_site',
        description: 'Add a new WordPress site to manage',
        inputSchema: {
          type: 'object',
          properties: {
            id: { type: 'string', description: 'Unique identifier for the site' },
            name: { type: 'string', description: 'Human-readable site name' },
            url: { type: 'string', description: 'Site URL' },
            tags: { 
              type: 'array', 
              items: { type: 'string' },
              description: 'Tags for categorizing sites'
            }
          },
          required: ['id', 'name', 'url']
        }
      },
      {
        name: 'remove_site',
        description: 'Remove a WordPress site',
        inputSchema: {
          type: 'object',
          properties: {
            id: { type: 'string', description: 'Site identifier to remove' }
          },
          required: ['id']
        }
      },
      {
        name: 'list_sites',
        description: 'List all managed WordPress sites',
        inputSchema: { type: 'object', properties: {} }
      },
      {
        name: 'search_all',
        description: 'Search across all WordPress sites',
        inputSchema: {
          type: 'object',
          properties: {
            query: { type: 'string', description: 'Search query' }
          },
          required: ['query']
        }
      },
      {
        name: 'search_by_tag',
        description: 'Search sites with specific tag',
        inputSchema: {
          type: 'object',
          properties: {
            query: { type: 'string', description: 'Search query' },
            tag: { type: 'string', description: 'Tag to filter by' }
          },
          required: ['query', 'tag']
        }
      }
    ]
  };
});

// Handle tool calls
server.setRequestHandler('tools/call', async (request) => {
  const { name, arguments: args } = request.params;
  
  switch (name) {
    case 'add_site':
      return { 
        toolResult: await wpManager.addSite(args.id, args.name, args.url, args.tags || [])
      };
      
    case 'remove_site':
      return { 
        toolResult: await wpManager.removeSite(args.id)
      };
      
    case 'list_sites':
      return { 
        toolResult: wpManager.listSites()
      };
      
    case 'search_all':
      return { 
        toolResult: await wpManager.searchAllSites(args.query)
      };
      
    case 'search_by_tag':
      return { 
        toolResult: await wpManager.searchByTag(args.query, args.tag)
      };
      
    default:
      throw new Error(`Unknown tool: ${name}`);
  }
});

const transport = new StdioServerTransport();
await server.connect(transport);
```

### Option 2: Database-Driven Approach

For managing 100+ sites, use a SQLite database:

```javascript
import Database from 'better-sqlite3';

class WordPressSiteManager {
  constructor() {
    this.db = new Database('wordpress-sites.db');
    this.initDatabase();
  }

  initDatabase() {
    this.db.exec(`
      CREATE TABLE IF NOT EXISTS sites (
        id TEXT PRIMARY KEY,
        name TEXT NOT NULL,
        url TEXT NOT NULL,
        tags TEXT,
        added_date TEXT DEFAULT CURRENT_TIMESTAMP,
        last_checked TEXT,
        status TEXT DEFAULT 'active'
      )
    `);
  }

  addSite(id, name, url, tags = []) {
    const stmt = this.db.prepare(`
      INSERT OR REPLACE INTO sites (id, name, url, tags)
      VALUES (?, ?, ?, ?)
    `);
    stmt.run(id, name, url, JSON.stringify(tags));
  }

  getSites(filter = {}) {
    let query = 'SELECT * FROM sites WHERE status = "active"';
    const params = [];
    
    if (filter.tag) {
      query += ' AND tags LIKE ?';
      params.push(`%"${filter.tag}"%`);
    }
    
    const stmt = this.db.prepare(query);
    return stmt.all(...params);
  }
}
```

## Configuration Methods

### Method 1: Claude Desktop Configuration

Update your Claude Desktop config:

```json
{
  "mcpServers": {
    "wp-manager": {
      "command": "node",
      "args": ["/path/to/dynamic-mcp-server.js"]
    }
  }
}
```

### Method 2: Environment Variables

Create `.env` file for sensitive data:
```env
WP_SITES_CONFIG=/home/user/.wordpress-mcp/sites.json
WP_MCP_TIMEOUT=30000
```

## Natural Language Usage

Once configured, you can use natural language commands:

### Adding Sites
- "Add a new site called Tech Blog at techblog.com with tags blog and technology"
- "Register client5.com as Client 5 Website tagged with ecommerce"

### Searching
- "Search all sites for articles about SEO"
- "Find posts about WordPress in my blog sites"
- "Search ecommerce sites for product updates"

### Managing Sites
- "List all my WordPress sites"
- "Show me sites tagged with blog"
- "Remove site client3"

## Managing 100+ Sites

### Bulk Import Script

Create `bulk-import-sites.js`:
```javascript
import { readFileSync } from 'fs';
import { DynamicWordPressMCP } from './dynamic-mcp-server.js';

const wpManager = new DynamicWordPressMCP();

// Import from CSV
const csv = readFileSync('sites.csv', 'utf8');
const lines = csv.split('\n').slice(1); // Skip header

for (const line of lines) {
  const [id, name, url, tags] = line.split(',');
  await wpManager.addSite(
    id.trim(),
    name.trim(),
    url.trim(),
    tags ? tags.split(';').map(t => t.trim()) : []
  );
  console.log(`Added: ${name}`);
}
```

Example `sites.csv`:
```csv
id,name,url,tags
client001,Client 1 Corp,https://client1.com,corporate;services
client002,Client 2 Shop,https://client2.com,ecommerce;retail
client003,Client 3 Blog,https://client3.com,blog;lifestyle
```

### Performance Optimization

For 100+ sites, implement:

1. **Concurrent Searches**
```javascript
async searchAllSitesConcurrent(query, concurrency = 10) {
  const sites = Object.entries(this.sites);
  const results = [];
  
  for (let i = 0; i < sites.length; i += concurrency) {
    const batch = sites.slice(i, i + concurrency);
    const batchResults = await Promise.allSettled(
      batch.map(([id, site]) => this.searchSite(id, site, query))
    );
    results.push(...batchResults);
  }
  
  return results;
}
```

2. **Caching Layer**
```javascript
class CachedSearch {
  constructor(ttl = 300000) { // 5 minutes
    this.cache = new Map();
    this.ttl = ttl;
  }
  
  async search(siteId, query) {
    const key = `${siteId}:${query}`;
    const cached = this.cache.get(key);
    
    if (cached && Date.now() - cached.time < this.ttl) {
      return cached.data;
    }
    
    const result = await this.performSearch(siteId, query);
    this.cache.set(key, { data: result, time: Date.now() });
    return result;
  }
}
```

## Best Practices

### 1. Site Organization
- Use consistent naming: `client001`, `client002`, etc.
- Tag sites by type: `blog`, `ecommerce`, `corporate`
- Group by client or project

### 2. Health Monitoring
```javascript
async checkSiteHealth(id) {
  try {
    const response = await fetch(`${this.sites[id].url}/wp-json/llmr/mcp/v1/discovery`);
    return {
      id,
      status: response.ok ? 'healthy' : 'error',
      statusCode: response.status
    };
  } catch (error) {
    return { id, status: 'offline', error: error.message };
  }
}

async healthCheckAll() {
  const results = await Promise.allSettled(
    Object.keys(this.sites).map(id => this.checkSiteHealth(id))
  );
  return results;
}
```

### 3. Backup Configuration
```javascript
async exportConfiguration() {
  const config = {
    sites: this.sites,
    exportDate: new Date().toISOString(),
    version: '1.0'
  };
  
  await fs.writeFile(
    `backup-${Date.now()}.json`,
    JSON.stringify(config, null, 2)
  );
}
```

## Troubleshooting

### Common Issues

1. **Site Not Responding**
   - Check if LLM Ready plugin is activated
   - Verify REST API is accessible
   - Check HTTPS certificate

2. **Performance Issues**
   - Implement caching
   - Use concurrent requests with limits
   - Add timeout handling

3. **Configuration Problems**
   - Verify JSON syntax
   - Check file permissions
   - Validate URLs include https://

### Debug Mode

Add debug logging:
```javascript
if (process.env.DEBUG) {
  console.log(`Searching ${site.name}:`, query);
  console.time(`search-${id}`);
}
// ... perform search
if (process.env.DEBUG) {
  console.timeEnd(`search-${id}`);
}
```

## Example Workflows

### Daily Management
```
You: "Show me all my sites"
Assistant: Here are your 45 managed WordPress sites...

You: "Add newclient.com as New Client Site with tag prospect"
Assistant: Added site: New Client Site

You: "Search all prospect sites for pricing information"
Assistant: Searching 3 sites tagged 'prospect'...
```

### Bulk Operations
```
You: "Search all ecommerce sites for black friday"
Assistant: Searching 23 sites tagged 'ecommerce'...

You: "Check health of all sites"
Assistant: Checking 45 sites... 43 healthy, 2 offline
```

This setup allows you to manage hundreds of WordPress sites through natural language, making it perfect for agencies, consultants, or anyone managing multiple WordPress installations.