const http = require('http');
const fs = require('fs');
const path = require('path');
const url = require('url');
const PORT = 3000;

const server = http.createServer((req, res) => {
  // Parse URL to ignore query strings
  const parsedUrl = url.parse(req.url);
  // Sanitize path to prevent directory traversal
  let sanitizePath = path.normalize(parsedUrl.pathname).replace(/^(\.\.[\/\\])+/, '');

  if (sanitizePath === '/' || sanitizePath === '\\') {
      sanitizePath = '/index.html';
  }

  // Resolve absolute path and ensure it's still within __dirname
  let filePath = path.join(__dirname, sanitizePath);

  if (!filePath.startsWith(__dirname)) {
      res.writeHead(403);
      res.end('Forbidden');
      return;
  }

  let extname = path.extname(filePath);
  let contentType = 'text/html';

  switch (extname) {
    case '.js':
      contentType = 'text/javascript';
      break;
    case '.css':
      contentType = 'text/css';
      break;
    case '.json':
      contentType = 'application/json';
      break;
    case '.png':
      contentType = 'image/png';
      break;
    case '.jpg':
      contentType = 'image/jpg';
      break;
  }

  fs.readFile(filePath, (err, content) => {
    if (err) {
      if(err.code == 'ENOENT'){
        res.writeHead(404);
        res.end('Not Found');
      } else {
        res.writeHead(500);
        res.end('Server Error');
      }
    } else {
      res.writeHead(200, { 'Content-Type': contentType });
      res.end(content, 'utf-8');
    }
  });
});

server.listen(PORT, () => {
  console.log(`Server running at http://localhost:${PORT}/`);
});