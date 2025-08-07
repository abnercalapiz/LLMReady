# Changelog

All notable changes to the LLM Ready WordPress Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
- Comprehensive MCP documentation:
  - MCP-USAGE-GUIDE.md - Complete MCP implementation guide
  - MCP-QUICK-START.md - Quick setup guide
  - MCP-ENDPOINTS-GUIDE.md - Detailed endpoint documentation
  - MCP-QUICK-REFERENCE.md - Quick reference for endpoints

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