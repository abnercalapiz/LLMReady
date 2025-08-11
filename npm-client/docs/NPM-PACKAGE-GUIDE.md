# Creating an npm Package for WordPress MCP Client

This guide explains how to create a separate npm package that helps developers connect to WordPress sites using your LLM Ready plugin.

## Why Create an npm Package?

While your WordPress plugin provides the MCP endpoints, a JavaScript/TypeScript package can:
- Make it easier for developers to integrate with your MCP endpoints
- Provide type safety with TypeScript
- Handle authentication, error handling, and retries
- Work in Node.js, browsers, and AI agent environments
- Enable quick integration for MCP tool developers

## Package Structure

```
@jezweb/wordpress-mcp-client/
├── src/
│   ├── index.ts           # Main entry point
│   ├── client.ts          # Main client class
│   ├── types.ts           # TypeScript definitions
│   ├── endpoints/
│   │   ├── business.ts
│   │   ├── search.ts
│   │   ├── services.ts
│   │   └── contact.ts
│   └── utils/
│       ├── errors.ts
│       └── request.ts
├── dist/                  # Built files
├── examples/
│   ├── basic-usage.js
│   ├── mcp-server.js
│   └── multi-site.js
├── tests/
├── package.json
├── tsconfig.json
├── README.md
└── LICENSE
```

## Core Implementation

### 1. Main Client (`src/client.ts`)

```typescript
export interface WordPressMCPConfig {
  baseUrl: string;
  timeout?: number;
  headers?: Record<string, string>;
}

export class WordPressMCPClient {
  private config: WordPressMCPConfig;

  constructor(config: WordPressMCPConfig) {
    this.config = {
      timeout: 30000,
      ...config
    };
  }

  async discovery() {
    return this.request('/discovery');
  }

  async search(query: string, options?: SearchOptions) {
    return this.request('/search', {
      method: 'POST',
      body: JSON.stringify({ query, ...options })
    });
  }

  async business() {
    return this.request('/business');
  }

  async services() {
    return this.request('/services');
  }

  async contact() {
    return this.request('/contact');
  }

  private async request(endpoint: string, options?: RequestInit) {
    const url = `${this.config.baseUrl}${endpoint}`;
    
    try {
      const response = await fetch(url, {
        ...options,
        headers: {
          'Content-Type': 'application/json',
          ...this.config.headers,
          ...options?.headers
        }
      });

      if (!response.ok) {
        throw new MCPError(`Request failed: ${response.statusText}`, response.status);
      }

      return await response.json();
    } catch (error) {
      if (error instanceof MCPError) throw error;
      throw new MCPError(`Network error: ${error.message}`);
    }
  }
}
```

### 2. TypeScript Types (`src/types.ts`)

```typescript
export interface BusinessInfo {
  name: string;
  description: string;
  hours: {
    [day: string]: {
      open: string;
      close: string;
    };
  };
  timezone: string;
}

export interface SearchResult {
  id: number;
  title: string;
  excerpt: string;
  url: string;
  type: string;
  date: string;
}

export interface SearchOptions {
  per_page?: number;
  post_type?: string;
}

export interface Service {
  id: string;
  name: string;
  description: string;
  price?: string;
}

export interface ContactInfo {
  email?: string;
  phone?: string;
  address?: string;
  social?: {
    facebook?: string;
    twitter?: string;
    linkedin?: string;
    instagram?: string;
  };
}
```

### 3. Multi-Site Manager (`src/multi-site.ts`)

```typescript
export class WordPressMCPMultiSite {
  private clients: Map<string, WordPressMCPClient>;

  constructor(sites: Record<string, string>) {
    this.clients = new Map();
    
    for (const [name, url] of Object.entries(sites)) {
      this.clients.set(name, new WordPressMCPClient({
        baseUrl: `${url}/wp-json/llmr/mcp/v1`
      }));
    }
  }

  async searchAll(query: string, options?: SearchOptions) {
    const results = await Promise.allSettled(
      Array.from(this.clients.entries()).map(async ([name, client]) => ({
        site: name,
        results: await client.search(query, options)
      }))
    );

    return results.map((result, index) => {
      const siteName = Array.from(this.clients.keys())[index];
      if (result.status === 'fulfilled') {
        return result.value;
      }
      return {
        site: siteName,
        error: result.reason.message
      };
    });
  }

  getClient(siteName: string): WordPressMCPClient | undefined {
    return this.clients.get(siteName);
  }
}
```

### 4. MCP Server Integration (`examples/mcp-server.js`)

```javascript
import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import { WordPressMCPClient } from '@jezweb/wordpress-mcp-client';

const client = new WordPressMCPClient({
  baseUrl: 'https://example.com/wp-json/llmr/mcp/v1'
});

const server = new Server({
  name: 'wordpress-mcp',
  version: '1.0.0',
});

server.setRequestHandler('tools/call', async (request) => {
  const { name, arguments: args } = request.params;
  
  switch (name) {
    case 'search_wordpress':
      const results = await client.search(args.query, {
        per_page: args.limit || 10
      });
      return { toolResult: results };
      
    case 'get_business_info':
      const info = await client.business();
      return { toolResult: info };
      
    default:
      throw new Error(`Unknown tool: ${name}`);
  }
});

const transport = new StdioServerTransport();
await server.connect(transport);
```

## Package.json Configuration

```json
{
  "name": "@jezweb/wordpress-mcp-client",
  "version": "1.0.0",
  "description": "JavaScript/TypeScript client for WordPress LLM Ready MCP endpoints",
  "main": "dist/index.js",
  "module": "dist/index.mjs",
  "types": "dist/index.d.ts",
  "files": [
    "dist",
    "README.md",
    "LICENSE"
  ],
  "scripts": {
    "build": "tsup src/index.ts --format cjs,esm --dts",
    "test": "jest",
    "prepublishOnly": "npm run build"
  },
  "keywords": [
    "wordpress",
    "mcp",
    "ai",
    "llm",
    "claude",
    "chatgpt",
    "api-client"
  ],
  "author": "Jezweb",
  "license": "MIT",
  "dependencies": {
    "node-fetch": "^3.3.0"
  },
  "devDependencies": {
    "@types/node": "^20.0.0",
    "tsup": "^8.0.0",
    "typescript": "^5.0.0",
    "jest": "^29.0.0"
  },
  "engines": {
    "node": ">=14.0.0"
  },
  "repository": {
    "type": "git",
    "url": "https://github.com/jezweb/wordpress-mcp-client"
  }
}
```

## Usage Examples

### Basic Usage

```javascript
import { WordPressMCPClient } from '@jezweb/wordpress-mcp-client';

const client = new WordPressMCPClient({
  baseUrl: 'https://example.com/wp-json/llmr/mcp/v1'
});

// Search for content
const searchResults = await client.search('wordpress tips');
console.log(searchResults);

// Get business information
const businessInfo = await client.business();
console.log(businessInfo);
```

### Multi-Site Usage

```javascript
import { WordPressMCPMultiSite } from '@jezweb/wordpress-mcp-client';

const multisite = new WordPressMCPMultiSite({
  'site1': 'https://site1.com',
  'site2': 'https://site2.com',
  'site3': 'https://site3.com'
});

// Search across all sites
const results = await multisite.searchAll('social media');
```

### Error Handling

```javascript
try {
  const results = await client.search('test');
} catch (error) {
  if (error.status === 404) {
    console.error('MCP endpoints not found. Is the plugin activated?');
  } else {
    console.error('Error:', error.message);
  }
}
```

## Publishing to npm

### 1. Create npm Account
```bash
npm adduser
```

### 2. Namespace Scoping
Reserve your namespace:
```bash
npm org create jezweb  # If you own the organization
```

### 3. Build and Test
```bash
npm run build
npm test
npm pack  # Creates a .tgz file to test locally
```

### 4. Publish
```bash
npm publish --access public
```

### 5. Version Management
```bash
npm version patch  # 1.0.0 -> 1.0.1
npm version minor  # 1.0.0 -> 1.1.0
npm version major  # 1.0.0 -> 2.0.0
```

## Benefits of This Approach

1. **Developer Experience**
   - Type-safe API calls
   - Auto-completion in IDEs
   - Clear documentation

2. **MCP Tool Developers**
   - Quick integration
   - No need to understand WordPress REST API
   - Built-in error handling

3. **Maintenance**
   - Separate versioning from WordPress plugin
   - Can update client without plugin updates
   - Better testing capabilities

4. **Use Cases**
   - Building MCP servers
   - Creating AI agents
   - Integration testing
   - Multi-site management tools

## Best Practices

1. **Keep it Lightweight**
   - Minimal dependencies
   - Tree-shakeable exports
   - Small bundle size

2. **Version Compatibility**
   - Document which plugin versions are supported
   - Use semantic versioning
   - Provide migration guides

3. **Documentation**
   - Include TypeScript examples
   - Provide MCP integration examples
   - Keep README focused on developers

4. **Testing**
   - Unit tests for all methods
   - Integration tests with mock server
   - Example implementations

## Example README for npm Package

```markdown
# WordPress MCP Client

Official JavaScript/TypeScript client for WordPress sites using the LLM Ready plugin.

## Installation

```bash
npm install @jezweb/wordpress-mcp-client
```

## Quick Start

```javascript
import { WordPressMCPClient } from '@jezweb/wordpress-mcp-client';

const client = new WordPressMCPClient({
  baseUrl: 'https://yoursite.com/wp-json/llmr/mcp/v1'
});

const results = await client.search('your query');
```

## Requirements

- WordPress site with LLM Ready plugin v1.0.0+
- Node.js 14+

## Documentation

See [full documentation](https://github.com/jezweb/wordpress-mcp-client) for more examples.
```

This separate npm package approach keeps your WordPress plugin focused on WordPress functionality while providing JavaScript developers with a proper SDK for integration.