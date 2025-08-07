# LLM Ready MCP Server Integration Guide

## Overview

The LLM Ready plugin transforms your WordPress website into an MCP (Model Context Protocol) server, allowing AI agents like Claude, ChatGPT, and others to directly query your website for information.

## How It Works

When the LLM Ready plugin is activated, your WordPress site automatically provides MCP endpoints that AI agents can discover and interact with.

### Discovery

AI agents can discover your MCP server through:

1. **HTTP Headers**: Your site sends `X-MCP-Server` headers
2. **Meta Tags**: HTML meta tags indicate MCP availability
3. **Direct Discovery**: The discovery endpoint at `/wp-json/llmr/mcp/v1/discovery`

## Example AI Agent Interactions

### Example 1: Business Information Query

**User to AI**: "What are the business hours for example.com?"

**AI Agent Process**:
1. Discovers MCP server at example.com
2. Queries `/wp-json/llmr/mcp/v1/business`
3. Receives business hours data
4. Responds: "Example Business is open Monday-Friday 9AM-5PM"

### Example 2: Contact Information

**User to AI**: "How can I contact example.com?"

**AI Agent Process**:
1. Queries `/wp-json/llmr/mcp/v1/contact`
2. Receives contact details
3. Responds: "You can contact them at contact@example.com or call (555) 123-4567. They typically respond within 24 hours."

### Example 3: Service Search

**User to AI**: "What services does example.com offer?"

**AI Agent Process**:
1. Queries `/wp-json/llmr/mcp/v1/services`
2. Receives list of services
3. Responds with formatted service list

## Configuration

### 1. Basic Setup

After activating the plugin:
1. Go to **LLM Ready > MCP Server**
2. Fill in your business information
3. Add contact details
4. Configure services

### 2. Testing Your MCP Server

Use the built-in testing tools:
1. Navigate to the MCP Server settings page
2. Click the test buttons for each endpoint
3. Verify the JSON responses contain your information

### 3. Advanced Configuration

#### Custom Services
```json
{
  "name": "Web Development",
  "description": "Custom WordPress development services",
  "price": "Starting at $1000"
}
```

#### Business Hours Format
```json
{
  "monday": "9:00 AM - 5:00 PM",
  "tuesday": "9:00 AM - 5:00 PM",
  "wednesday": "9:00 AM - 5:00 PM",
  "thursday": "9:00 AM - 5:00 PM",
  "friday": "9:00 AM - 5:00 PM",
  "saturday": "Closed",
  "sunday": "Closed"
}
```

## Future Integrations

The MCP server is designed to be extensible. Future versions will support:

### Booking System Integration
- Connect with Calendly, Acuity, or custom booking systems
- Allow AI agents to check availability
- Enable appointment scheduling through AI assistants

### E-commerce Integration
- Product catalog access
- Inventory checking
- Order status queries

### Customer Support Integration
- FAQ access
- Support ticket creation
- Knowledge base queries

## API Reference

### Discovery Endpoint
```
GET /wp-json/llmr/mcp/v1/discovery
```

Returns available endpoints and capabilities.

### Business Information
```
GET /wp-json/llmr/mcp/v1/business
```

Returns business details, hours, and social media links.

### Contact Information
```
GET /wp-json/llmr/mcp/v1/contact
```

Returns contact details and preferred communication methods.

### Services Listing
```
GET /wp-json/llmr/mcp/v1/services
```

Returns available services or products.

### Content Search
```
POST /wp-json/llmr/mcp/v1/search
Body: { "query": "search term", "limit": 10 }
```

Searches website content and returns relevant results.

### Booking Information
```
GET /wp-json/llmr/mcp/v1/booking
```

Returns booking availability and system information.

## Security Considerations

1. **Rate Limiting**: The MCP server implements rate limiting (60 requests/minute)
2. **Authentication**: Optional API key support for higher rate limits
3. **Data Privacy**: Only publicly configured information is exposed
4. **CORS**: Properly configured for cross-origin requests

## Troubleshooting

### MCP Server Not Responding
1. Check if REST API is enabled in WordPress
2. Verify permalinks are set to anything except "Plain"
3. Check for conflicting plugins

### Missing Information
1. Ensure all fields are filled in the MCP Server settings
2. Save settings after making changes
3. Clear any caching plugins

### AI Agents Not Discovering Server
1. Check HTTP headers using browser developer tools
2. Verify meta tags are present in HTML
3. Test discovery endpoint directly

## Best Practices

1. **Keep Information Updated**: Regularly update business hours and contact info
2. **Add Detailed Services**: The more detailed your services, the better AI agents can help users
3. **Monitor Usage**: Check your server logs for MCP endpoint usage
4. **Test Regularly**: Use the built-in testing tools to ensure endpoints work correctly

---

Made with ❤️ by [Jezweb](https://www.jezweb.com.au/)