#!/usr/bin/env node
import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import fetch from 'node-fetch';

// Configure your WordPress sites here
const WORDPRESS_SITES = {
  'newcastle-seo': 'https://www.newcastleseo.com.au',
  'site2': 'https://site2.com',
  'site3': 'https://site3.com',
  // Add all 100 sites here
};

const server = new Server(
  {
    name: 'wordpress-multisite',
    version: '1.0.0',
  },
  {
    capabilities: {
      tools: {},
    },
  }
);

// Search posts across sites
server.setRequestHandler('tools/call', async (request) => {
  const { name, arguments: args } = request.params;
  
  if (name === 'search_posts') {
    const { query, site = 'all' } = args;
    
    // Search specific site or all sites
    const sitesToSearch = site === 'all' 
      ? Object.entries(WORDPRESS_SITES)
      : [[site, WORDPRESS_SITES[site]]];
    
    const results = [];
    
    for (const [siteName, siteUrl] of sitesToSearch) {
      try {
        const response = await fetch(`${siteUrl}/wp-json/llmr/mcp/v1/search`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ query, per_page: 10 })
        });
        
        const data = await response.json();
        results.push({
          site: siteName,
          results: data
        });
      } catch (error) {
        console.error(`Error searching ${siteName}:`, error);
      }
    }
    
    return { toolResult: results };
  }
  
  // Add other tools like get_business_info, etc.
});

// List available tools
server.setRequestHandler('tools/list', async () => {
  return {
    tools: [
      {
        name: 'search_posts',
        description: 'Search posts across WordPress sites',
        inputSchema: {
          type: 'object',
          properties: {
            query: {
              type: 'string',
              description: 'Search query'
            },
            site: {
              type: 'string',
              description: 'Site name or "all" for all sites',
              default: 'all'
            }
          },
          required: ['query']
        }
      }
    ]
  };
});

const transport = new StdioServerTransport();
await server.connect(transport);