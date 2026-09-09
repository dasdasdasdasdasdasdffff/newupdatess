import express from 'express';
import { spawn, ChildProcess } from 'child_process';
import { createProxyMiddleware } from 'http-proxy-middleware';
import path from 'path';

const app = express();
const PORT = 3000;
const PHP_PORT = 8000;

// Ensure database tables and initial seed data exist
try {
  const seedInit = spawn('php', ['database/seed.php'], { stdio: 'inherit' });
  seedInit.on('error', (err) => console.error('Failed to run seed script:', err));
} catch (e) {
  console.error('Seed execution error:', e);
}

// Spawn PHP built-in web server with document root at /public and routing via public/index.php
let phpProcess: ChildProcess | null = null;

function startPhpServer() {
  phpProcess = spawn('php', ['-S', `127.0.0.1:${PHP_PORT}`, '-t', 'public', 'public/index.php'], {
    stdio: 'inherit',
    env: { ...process.env, DB_CONNECTION: process.env.DB_CONNECTION || 'mysql' }
  });

  phpProcess.on('exit', (code, signal) => {
    console.log(`PHP server exited with code ${code}, signal ${signal}. Restarting in 1s...`);
    setTimeout(startPhpServer, 1000);
  });

  phpProcess.on('error', (err) => {
    console.error('PHP process spawn error:', err);
  });
}

startPhpServer();

// Health check for platform container diagnostics
app.get('/api/health', (req, res) => {
  res.json({ status: 'ok', engine: 'PHP 8.2 + PDO MySQL/SQLite MVC' });
});

// Proxy all web and API requests to the PHP 8.2 backend server
const phpProxy = createProxyMiddleware({
  target: `http://127.0.0.1:${PHP_PORT}`,
  changeOrigin: true,
  ws: false,
  onError: (err, req, res) => {
    console.error('Proxy error connecting to PHP server:', err.message);
    if (!res.headersSent) {
      res.status(502).send(`
        <!DOCTYPE html>
        <html>
        <head><title>Starting CapitalNest Services</title><meta http-equiv="refresh" content="2"></head>
        <body style="font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; background: #F8F9FA;">
          <div style="text-align: center; padding: 2rem; background: white; border: 1px solid #E5E7EB; border-radius: 1rem;">
            <h2>Starting CapitalNest Financial Engine...</h2>
            <p style="color: #6B7280;">Connecting to PHP runtime and database. Please wait...</p>
          </div>
        </body>
        </html>
      `);
    }
  }
});

app.use('/', phpProxy);

const server = app.listen(PORT, '0.0.0.0', () => {
  console.log(`CapitalNest Node-to-PHP Gateway running on http://0.0.0.0:${PORT}`);
});

process.on('SIGTERM', () => {
  if (phpProcess) phpProcess.kill('SIGTERM');
  server.close();
});

process.on('SIGINT', () => {
  if (phpProcess) phpProcess.kill('SIGINT');
  server.close();
});
