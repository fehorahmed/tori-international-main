const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const bodyParser = require('body-parser');
const cors = require('cors');
const Redis = require('ioredis');
const path = require('path');
require('dotenv').config({ path: path.join(__dirname, '../.env') });

// Import custom modules
const locationService = require('./services/locationService');

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
    cors: { origin: "*", methods: ["GET", "POST"] }
});

app.use(cors());
app.use(bodyParser.json());

/**
 * SOCKET.IO HANDLERS
 */
io.on('connection', (socket) => {
    console.log('[Socket] Client connected:', socket.id);

    socket.on('join-dashboard', (data) => {
        if (data && data.sister_concern_id) {
            const roomName = 'branch_' + data.sister_concern_id;
            socket.join(roomName);
            console.log(`[Socket] Dashboard client joined room: ${roomName}`);
        }
    });

    // Ultra-Fast Sync: Receive from client, broadcast instantly, save in background
    socket.on('client-update-location', async (data) => {
        const { user_id, latitude, longitude, name, sister_concern_id } = data;
        
        // 1. Instant Emit to Dashboards (Only to the specific branch room if available)
        if (sister_concern_id) {
            io.to('branch_' + sister_concern_id).emit('location-updated', { user_id, latitude, longitude, name });
            console.log(`[Real-Time] Forwarding update for: ${name} to branch_${sister_concern_id}`);
        } else {
            io.emit('location-updated', { user_id, latitude, longitude, name });
            console.log(`[Real-Time] Forwarding globally for: ${name} (Legacy App)`);
        }

        // 2. Background Save to Database (Non-blocking)
        locationService.saveLocationUpdate(data).catch(err => {
            console.error('[Service Error]', err.message);
        });
    });

    socket.on('disconnect', () => console.log('[Socket] Client disconnected'));
});

/**
 * HTTP ENDPOINTS
 */
app.post('/broadcast-location', (req, res) => {
    const data = req.body;
    console.log(`[HTTP] Legacy broadcast for: ${data.name}`);
    io.emit('location-updated', data); 
    res.json({ status: 'success' });
});

/**
 * REDIS BRIDGE (Optional Alternative)
 * Only attempts to connect if Redis is running or needed.
 */
let redis;
if (process.env.BROADCAST_DRIVER === 'redis') {
    redis = new Redis({
        host: process.env.REDIS_HOST || '127.0.0.1',
        port: process.env.REDIS_PORT || 6379,
        lazyConnect: true,
        maxRetriesPerRequest: 0 // Stop flooding errors
    });

    // Immediate error handler to prevent crashing
    redis.on('error', (err) => {
        // Silently catch connection errors
    });

    redis.subscribe('location-channel', (err) => {
        if (!err) {
            console.log('[Redis] Bridge Active: Listening for events...');
        } else {
            console.warn('[Redis] Warning: Could not subscribe to channel. Check if Redis is running.');
        }
    });

    redis.on('message', (channel, message) => {
        try {
            const payload = JSON.parse(message);
            const data = payload.data || payload;
            io.emit('location-updated', data);
        } catch (e) {
            console.error('[Redis Error]', e.message);
        }
    });

    redis.on('error', (err) => {
        // Silently handle connection errors unless needed
        if (err.code === 'ECONNREFUSED') {
            // console.warn('[Redis] Not connected: Server is offline.');
        } else {
            console.error('[Redis Error]', err.message);
        }
    });
} else {
    console.log('[Redis] Bridge Bypassed: Using Direct Socket/HTTP mode.');
}

/**
 * SERVER STARTUP
 */
const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`
🚀 REAL-TIME ENGINE STARTED
-----------------------------------
Port:      ${PORT}
State:     Active
Structure: Modular (Config/Services)
Bridges:   HTTP, Redis, Direct Socket
-----------------------------------
    `);
});

// Graceful shutdown handling
process.on('SIGTERM', () => {
    console.log('SIGTERM signal received: closing HTTP server');
    server.close(() => console.log('HTTP server closed'));
});
