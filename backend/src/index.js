require('dotenv').config();

const express = require('express');
const cors    = require('cors');
const path    = require('path'); // 👈 1. IMPORTANTE: Agregamos este módulo nativo de Node
const app     = express();

// ─── Middlewares globales ────────────────────────────────────
app.use(cors());                        
app.use(express.json());                
app.use(express.urlencoded({ extended: true }));

// 👇 2. NUEVO: Servir archivos estáticos del frontend (CSS, JS del cliente, imágenes)
// Esto le dice a Express que todo lo que esté en 'frontend/src' está disponible públicamente
app.use(express.static(path.join(__dirname, '../../frontend/src')));

// ─── Rutas de la API ─────────────────────────────────────────
app.use('/api/auth',         require('./routes/auth'));
app.use('/api/propiedades',  require('./routes/propiedades'));
app.use('/api/favoritos',    require('./routes/favoritos'));
app.use('/api/admin',        require('./routes/admin'));

// ─── Ruta raíz (Ahora envía tu HTML) ─────────────────────────
app.get('/', (req, res) => {
    // 👇 3. MODIFICADO: Cambiamos el res.json por res.sendFile para enviar tu página web
    res.sendFile(path.join(__dirname, '../../frontend/src/index.html'));
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
