// config/db.js
// Conexión a MySQL usando mysql2

const mysql = require('mysql2/promise');
require('dotenv').config();

const pool = mysql.createPool({
    host:     process.env.DB_HOST,
    user:     process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
    waitForConnections: true,
    connectionLimit:    10,
});

// Verificar conexión al iniciar
pool.getConnection()
    .then(conn => {
        console.log('✅ Conectado a MySQL correctamente');
        conn.release();
    })
    .catch(err => {
        console.error('❌ Error al conectar a MySQL:', err.message);
        process.exit(1);
    });

module.exports = pool;
