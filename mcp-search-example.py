import requests
import json

class WordPressMCP:
    def __init__(self, base_url):
        self.base_url = base_url.rstrip('/')
        
    def search(self, query, per_page=10, post_type='any'):
        """Search WordPress content via MCP endpoint"""
        endpoint = f"{self.base_url}/wp-json/llmr/mcp/v1/search"
        
        # POST request with JSON body
        payload = {
            "query": query,
            "per_page": per_page,
            "post_type": post_type
        }
        
        headers = {
            "Content-Type": "application/json"
        }
        
        response = requests.post(endpoint, json=payload, headers=headers)
        return response.json()
    
    def get_business_info(self):
        """Get business information via MCP endpoint"""
        endpoint = f"{self.base_url}/wp-json/llmr/mcp/v1/business"
        response = requests.get(endpoint)
        return response.json()

# Example usage
mcp = WordPressMCP("https://your-domain.com")

# Search using POST
results = mcp.search("AI tools", per_page=5)
print(json.dumps(results, indent=2))

# Get business info using GET
business = mcp.get_business_info()
print(json.dumps(business, indent=2))