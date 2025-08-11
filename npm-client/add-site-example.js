// Example: How to dynamically add WordPress sites to your MCP configuration

// Method 1: Using configuration file
async function addSiteToConfig(siteId, siteName, siteUrl, tags = []) {
  const fs = require('fs').promises;
  const path = require('path');
  
  const configPath = path.join(process.env.HOME, '.wordpress-mcp', 'sites-config.json');
  
  // Read existing config
  let config = { sites: {} };
  try {
    const data = await fs.readFile(configPath, 'utf8');
    config = JSON.parse(data);
  } catch (error) {
    // Config doesn't exist yet
  }
  
  // Add new site
  config.sites[siteId] = {
    name: siteName,
    url: siteUrl,
    tags: tags
  };
  
  // Save config
  await fs.mkdir(path.dirname(configPath), { recursive: true });
  await fs.writeFile(configPath, JSON.stringify(config, null, 2));
  
  console.log(`Added site: ${siteName} (${siteUrl})`);
}

// Method 2: Using API call to running MCP server
async function addSiteViaAPI(siteId, siteName, siteUrl, tags = []) {
  // This assumes your MCP server exposes an HTTP endpoint
  const response = await fetch('http://localhost:3000/add-site', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      id: siteId,
      name: siteName,
      url: siteUrl,
      tags: tags
    })
  });
  
  if (response.ok) {
    console.log('Site added successfully');
  }
}

// Method 3: Direct database insertion (for 100+ sites)
function addSiteToDatabase(db, siteId, siteName, siteUrl, tags = []) {
  const stmt = db.prepare(`
    INSERT OR REPLACE INTO wordpress_sites (id, name, url, tags, added_date)
    VALUES (?, ?, ?, ?, datetime('now'))
  `);
  
  stmt.run(siteId, siteName, siteUrl, JSON.stringify(tags));
  console.log(`Added to database: ${siteName}`);
}

// Example usage:
async function main() {
  // Add a single site
  await addSiteToConfig(
    'client-abc',
    'ABC Company Website',
    'https://abccompany.com',
    ['corporate', 'services']
  );
  
  // Bulk add sites from CSV
  const csv = `
id,name,url,tags
tech-blog,Tech Blog,https://techblog.com,blog;technology
shop-001,Online Shop 1,https://shop1.com,ecommerce;retail
agency-site,Marketing Agency,https://agency.com,services;marketing
  `.trim();
  
  const lines = csv.split('\n').slice(1); // Skip header
  for (const line of lines) {
    const [id, name, url, tagString] = line.split(',');
    const tags = tagString ? tagString.split(';') : [];
    
    await addSiteToConfig(id, name, url, tags);
  }
  
  console.log('All sites added!');
}

// Run the example
main().catch(console.error);

// For use with Claude Desktop after configuration:
// "Add tech startup website at https://startup.com with tags startup and technology"
// "Register new client site https://newclient.com as New Client Corp"