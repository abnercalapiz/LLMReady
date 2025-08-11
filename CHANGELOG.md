# Changelog

All notable changes to the LLM Ready WordPress Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.5] - 2025-01-11

### Improved
- Removed cURL examples from main README for better user experience
- Cleaner documentation focused on end-user needs  
- Separated technical API details from user-facing documentation

### Added
- Developer API Reference documentation (`docs/DEVELOPER-API-REFERENCE.md`)
  - Complete API endpoint documentation
  - cURL examples for testing
  - Integration notes for developers
  - Troubleshooting guide

## [1.0.4] - 2025-01-11

### Added
- Comprehensive MCP Setup Guide (`MCP-SETUP-GUIDE.md`)
  - Step-by-step instructions for single site configuration
  - Multi-site setup guide for managing 100+ WordPress sites
  - Natural language query examples
  - Detailed troubleshooting section
  - Best practices for MCP implementation
- Multi-site MCP server example (`multi-wordpress-mcp.js`)
  - Custom Node.js server for routing to multiple WordPress sites
  - Unified interface for managing multiple installations
- Additional Claude Desktop configuration examples
  - `claude-multisite-config.json` for multi-site setups

### Improved
- Documentation structure with dedicated MCP setup guide
- Better guidance for enterprise users managing multiple WordPress sites
- Clear examples of natural language queries without code
- Enhanced configuration examples for various use cases

## [1.0.3] - 2025-01-09

### Added
- MCP (Model Context Protocol) configuration examples
  - Multiple MCP configuration file formats for different implementations
  - Examples for Claude Desktop integration
  - OpenAPI specification for MCP endpoints
  - HTTP-based and command-based MCP configurations
  - Python example for testing MCP search functionality
- MCP-REST API comparison documentation

### Improved
- Documentation for AI agent integration
- Better examples for connecting AI tools to WordPress sites

## [1.0.2] - 2025-01-07

### Added
- Comprehensive Statistics page with analytics dashboard
  - Overview cards showing key metrics
  - Content statistics by post type
  - MCP API usage tracking and visualization
  - Interactive charts for daily and hourly activity
  - Export statistics to CSV
  - Reset statistics functionality
- Automatic API call tracking for all MCP endpoints
- Real-time usage monitoring
- Chart.js integration for data visualization

### Improved
- Navigation menu now includes Stats submenu item
- Better insights into AI agent interactions with your site

## [1.0.1] - 2025-01-07

### Fixed
- Fixed PHP parse errors in admin class (missing semicolons)
- Fixed duplicate method declaration in generator class (`get_seo_detector`)
- Fixed missing field callback implementations for MCP settings:
  - Contact fields (phone, address, city, state, country, postal code, response time)
  - Social media fields (Facebook, Twitter, LinkedIn, Instagram URLs)
  - Removed duplicate field callbacks

### Added
- Business hours functionality in MCP settings
  - Added hours configuration for each day of the week
  - Added timezone selection
  - Hours data now included in MCP `/business` endpoint response
- MCP test tool (`mcp-test-tool.html`) for easy endpoint testing
- Quick search test functionality in admin panel

### Improved
- Enhanced MCP admin interface with inline search testing
- Updated sanitization to handle all new settings fields
- Better error handling in MCP endpoints
- Added helpful descriptions and examples for all input fields

### Security
- All MCP endpoints remain public by design (no authentication required)
- Proper data sanitization for all new fields
- Security best practices maintained throughout

## [1.0.0] - 2025-01-07

### Initial Release
- Automatic generation of llms.txt file for AI tool visibility
- SEO plugin integration (Yoast, Rank Math, AIOSEO)
- Admin interface with settings and dashboard widget
- Auto-regeneration on post updates
- URL inclusion/exclusion management
- MCP server integration for AI agents
- Business information management
- Contact details configuration
- Services/products listing
- Content search functionality for AI agents