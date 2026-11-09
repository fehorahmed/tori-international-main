const pool = require('../config/database');

/**
 * Calculates distance between two coordinates in meters using Haversine formula
 */
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // Earth radius in meters
    const rad = Math.PI / 180;
    const dLat = (lat2 - lat1) * rad;
    const dLon = (lon2 - lon1) * rad;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * rad) * Math.cos(lat2 * rad) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

/**
 * Handles saving location data to both 'users' and 'locations' tables.
 * This function handles duplicate checks and JSON history management.
 */
async function saveLocationUpdate(data) {
    const { user_id, latitude, longitude } = data;
    const currentDate = new Date().toISOString().slice(0, 10); // YYYY-MM-DD

    try {
        // 1. Update User Record (Latest Position & Online Status)
        await pool.execute(
            'UPDATE users SET latest_latitude = ?, latest_longitude = ?, is_online = 1 WHERE id = ?',
            [latitude, longitude, user_id]
        );

        // 2. Manage History in 'locations' table
        const [rows] = await pool.execute(
            'SELECT history FROM locations WHERE user_id = ? AND date = ?',
            [user_id, currentDate]
        );

        let history = [];
        const newPoint = {
            latitude: parseFloat(latitude),
            longitude: parseFloat(longitude),
            order_status: 0,
            order_no: ""
        };

        if (rows.length > 0) {
            // Check for identical duplicates to prevent redundant data
            history = typeof rows[0].history === 'string' ? JSON.parse(rows[0].history) : rows[0].history;
            if (history && history.length > 0) {
                const lastPoint = history[history.length - 1];
                
                // Calculate distance in meters
                const distance = calculateDistance(lastPoint.latitude, lastPoint.longitude, newPoint.latitude, newPoint.longitude);
                
                // Drop if distance is less than 20 meters (GPS Jitter filter)
                if (distance < 20) {
                    return { status: 'skipped', message: `Distance is only ${distance.toFixed(2)}m (less than 20m), dropping.` };
                }
            }
            
            history.push(newPoint);
            await pool.execute(
                'UPDATE locations SET history = ?, updated_at = NOW() WHERE user_id = ? AND date = ?',
                [JSON.stringify(history), user_id, currentDate]
            );
        } else {
            // Create new entry for the day
            history = [newPoint];
            await pool.execute(
                'INSERT INTO locations (user_id, date, history, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())',
                [user_id, currentDate, JSON.stringify(history)]
            );
        }
        return { status: 'success' };
    } catch (err) {
        throw new Error(`Database Update failed: ${err.message}`);
    }
}

module.exports = {
    saveLocationUpdate
};
