# LLM Ready - WordPress AI Visibility Suite

A comprehensive solution for making WordPress sites discoverable and queryable by AI agents like ChatGPT, Claude, and Perplexity.

## 🚀 Project Components

### 1. WordPress Plugin (`/wordpress-plugin`)
The core WordPress plugin that:
- Generates `llms.txt` files for AI discoverability
- Provides MCP (Model Context Protocol) REST API endpoints
- Integrates with popular SEO plugins
- Tracks AI agent interactions

[View WordPress Plugin README](wordpress-plugin/README.md)

### 2. npm Client Package (`/npm-client`)
JavaScript/TypeScript client for developers:
- Connect to WordPress MCP endpoints
- Manage multiple WordPress sites
- Full TypeScript support
- Published as `@abnerjezweb/wordpress-mcp-client`

[View npm Client README](npm-client/README.md)

### 3. Documentation (`/docs`)
- MCP Setup Guide
- Multi-site Management Guide
- API Reference
- Security Documentation

### 4. Examples (`/examples`)
- Integration examples
- Multi-site configurations
- MCP server implementations

## 🎯 Quick Start

### For WordPress Site Owners
1. Install the WordPress plugin from `/wordpress-plugin`
2. Configure your site information
3. Enable MCP endpoints
4. Your site is now AI-ready!

### For Developers
```bash
npm install @abnerjezweb/wordpress-mcp-client
```

```javascript
import { WordPressMCPClient } from '@abnerjezweb/wordpress-mcp-client';

const client = new WordPressMCPClient({
  baseUrl: 'https://yoursite.com/wp-json/llmr/mcp/v1'
});

const results = await client.search('content');
```

## 📦 Repository Structure

```
LLMReady/
├── wordpress-plugin/     # WordPress plugin code
│   ├── llmready.php     # Main plugin file
│   ├── admin/           # Admin interface
│   ├── includes/        # Core functionality
│   └── README.md        # Plugin documentation
├── npm-client/          # npm package (@abnerjezweb/wordpress-mcp-client)
│   ├── src/             # TypeScript source
│   ├── examples/        # Usage examples
│   └── README.md        # Package documentation
├── docs/                # Comprehensive documentation
│   ├── MCP-SETUP-GUIDE.md
│   ├── MCP-MULTI-SITE-MANAGEMENT.md
│   └── ...
├── examples/            # Integration examples
└── README.md           # This file
```

## 🔗 Links

- **WordPress Plugin**: [WordPress.org](#) (coming soon)
- **npm Package**: [npmjs.com/package/@abnerjezweb/wordpress-mcp-client](https://www.npmjs.com/package/@abnerjezweb/wordpress-mcp-client)
- **GitHub**: [github.com/abnercalapiz/LLMReady](https://github.com/abnercalapiz/LLMReady)
- **npm Client Repo**: [github.com/abnercalapiz/wordpress-mcp-client](https://github.com/abnercalapiz/wordpress-mcp-client)

## 🤝 Contributing

We welcome contributions! Please see:
- [WordPress Plugin Development](wordpress-plugin/README.md#contributing)
- [npm Client Development](npm-client/README.md#contributing)

## 📄 License

- WordPress Plugin: GPL v2 or later
- npm Client: MIT

## 🌟 Features

- ✅ Automatic `llms.txt` generation
- ✅ MCP REST API endpoints
- ✅ Multi-site management
- ✅ TypeScript client library
- ✅ SEO plugin integration
- ✅ Usage statistics
- ✅ Natural language queries
- ✅ Enterprise-ready

## 💡 Use Cases

1. **Single Site Owners**: Make your WordPress site AI-discoverable
2. **Agencies**: Manage multiple client sites with AI integration
3. **Developers**: Build AI-powered applications using WordPress content
4. **Enterprises**: Connect 100+ WordPress sites to AI agents

---

Made with ❤️ by [Jezweb](https://www.jezweb.com.au)