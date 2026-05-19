// routes/auth.js
const express = require('express');
const router  = express.Router();
const { registro, login, perfil } = require('../controllers/authController');
const { verificarToken } = require('../middlewares/auth');

// POST /api/auth/registro  → Crear cuenta (comprador o vendedor)
router.post('/registro', registro);

// POST /api/auth/login     → Iniciar sesión → devuelve token JWT
router.post('/login', login);

// GET  /api/auth/perfil    → Ver perfil propio (requiere token)
router.get('/perfil', verificarToken, perfil);

module.exports = router;
