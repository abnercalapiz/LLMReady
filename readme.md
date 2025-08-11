# LLM Ready

**Contributors:** Jezweb  
**Tags:** seo, ai, llms, chatgpt, perplexity, claude, visibility  
**Requires at least:** WordPress 5.2  
**Tested up to:** WordPress 6.4  
**Stable tag:** 1.0.6  
**Requires PHP:** 7.2  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

Improve your website's visibility and ranking in AI tools like ChatGPT, Perplexity, and Claude by automatically generating and managing an llms.txt file.

## Description

LLM Ready helps your WordPress website gain better visibility in AI-powered tools and search engines by automatically generating and maintaining an llms.txt file at the root of your site. This file helps AI models understand and index your content more effectively.

### Key Features

* **Automatic llms.txt Generation**: Automatically creates and updates an llms.txt file containing all your published content
* **MCP Server Integration** 🆕: Turn your WordPress site into an MCP (Model Context Protocol) server that AI agents can directly query
* **SEO Plugin Integration**: Detects and integrates with popular SEO plugins (Yoast SEO, Rank Math, All in One SEO)
* **Smart Content Filtering**: Excludes pages marked as "noindex" in your SEO plugin
* **Flexible Post Type Selection**: Choose which post types to include (posts, pages, custom post types)
* **URL Management**: Manually include or exclude specific URLs
* **Metadata Control**: Include author, license, and canonical URL information
* **Auto-Regeneration**: Automatically updates llms.txt when content is published or updated
* **Dashboard Widget**: Monitor your LLM inclusion stats directly from the WordPress dashboard
* **One-Click Regeneration**: Manually regenerate the file anytime from the admin panel

### How It Works

1. Install and activate the plugin
2. Configure your settings (post types, metadata options, URLs)
3. The plugin automatically generates an llms.txt file at your site root
4. AI tools can now better discover and understand your content
5. The file updates automatically as you publish new content

### SEO Plugin Compatibility

LLM Ready seamlessly integrates with:
- Yoast SEO
- Rank Math
- All in One SEO (AIOSEO)

Content marked as "noindex" in these plugins is automatically excluded from the llms.txt file.

### MCP Server Feature (NEW!)

LLM Ready now includes a built-in MCP (Model Context Protocol) server that allows AI agents to directly interact with your WordPress site. This revolutionary feature transforms your website into an AI-queryable resource.

#### MCP Server Endpoints:

* **Discovery**: `/wp-json/llmr/mcp/v1/discovery` - Lists all available endpoints and capabilities
* **Business Info**: `/wp-json/llmr/mcp/v1/business` - Returns business information, hours, and social media
* **Contact**: `/wp-json/llmr/mcp/v1/contact` - Provides contact details and preferred communication methods
* **Services**: `/wp-json/llmr/mcp/v1/services` - Lists services/products offered
* **Search**: `/wp-json/llmr/mcp/v1/search` - Allows AI agents to search your content
* **Booking**: `/wp-json/llmr/mcp/v1/booking` - Future integration for appointment scheduling

#### Benefits:

* AI agents can answer questions about your business directly
* Provide real-time information to users through AI assistants
* Future-proof your website for AI-driven interactions
* Ready for integration with booking systems and other services

#### MCP Configuration for AI Tools

Connect your WordPress site to AI tools like Claude Desktop for natural language queries. Once configured, you can simply type questions like "Show me posts about social media" without any code or commands.

📖 **[View the complete MCP Setup Guide](docs/MCP-SETUP-GUIDE.md)** for step-by-step instructions, including single site and multi-site configurations.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/llmr` directory, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to the LLM Ready menu item to configure the plugin settings
4. Click "Regenerate llms.txt Now" to create your initial file

## Frequently Asked Questions

### What is an llms.txt file?

An llms.txt file is a standardized format that helps AI language models understand which content on your website they should index and how to properly attribute it. It's similar to robots.txt but designed specifically for AI tools.

### Will this improve my rankings in ChatGPT/Perplexity/Claude?

While we can't guarantee specific ranking improvements, providing a well-structured llms.txt file helps these AI tools better understand and index your content, potentially improving your visibility when users ask questions related to your content.

### Does this work with custom post types?

Yes! The plugin allows you to select any public post type registered on your WordPress site.

### Can I manually add URLs that aren't WordPress posts?

Yes, you can manually add any URLs through the "Additional URLs" field in the settings.

### How often is the llms.txt file updated?

By default, the file is automatically regenerated whenever you publish, update, or delete content. You can disable auto-regeneration and update manually if preferred.

## JavaScript/TypeScript Client

For developers who want to integrate with the MCP endpoints, we provide an npm package:

```bash
npm install @abnerjezweb/wordpress-mcp-client
```

- **npm Package**: [@abnerjezweb/wordpress-mcp-client](https://www.npmjs.com/package/@abnerjezweb/wordpress-mcp-client)
- **GitHub**: [wordpress-mcp-client](https://github.com/abnercalapiz/wordpress-mcp-client)

## Screenshots

1. Main settings page with all configuration options
2. Dashboard widget showing LLM inclusion statistics
3. Sample llms.txt file output
4. SEO plugin detection and integration

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.

## Links

- **GitHub Repository**: [github.com/abnercalapiz/LLMReady](https://github.com/abnercalapiz/LLMReady)
- **Documentation**: [View all docs](docs/)
- **Developer Client**: [wordpress-mcp-client](https://github.com/abnercalapiz/wordpress-mcp-client)

---

Made with ❤️ by [Jezweb](https://www.jezweb.com.au/)