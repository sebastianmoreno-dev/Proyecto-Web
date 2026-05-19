// index.js  –  Servidor principal EstateArch
require('dotenv').config();

const express = require('express');
const cors    = require('cors');
const app     = express();

// ─── Middlewares globales ────────────────────────────────────
app.use(cors());                        // Permite peticiones del frontend
app.use(express.json());                // Parsear JSON en el body
app.use(express.urlencoded({ extended: true }));

// ─── Rutas ───────────────────────────────────────────────────
app.use('/api/auth',         require('./routes/auth'));
app.use('/api/propiedades',  require('./routes/propiedades'));
app.use('/api/favoritos',    require('./routes/favoritos'));
app.use('/api/admin',        require('./routes/admin'));

// ─── Ruta raíz (salud del servidor) ─────────────────────────
app.get('/', (req, res) => {
    res.json({
        mensaje: '🏠 EstateArch API funcionando',
        version: '1.0.0',
        rutas: {
            auth:        '/api/auth',
            propiedades: '/api/propiedades',
            favoritos:   '/api/favoritos',
            admin:       '/api/admin'
        }
    });
});

// ─── Ruta no encontrada ──────────────────────────────────────
app.use((req, res) => {
    res.status(404).json({ mensaje: 'Ruta no encontrada.' });
});

// ─── Iniciar servidor ────────────────────────────────────────
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`🚀 Servidor corriendo en http://localhost:${PORT}`);
});
