// middlewares/auth.js
// Verifica el token JWT y el rol del usuario

const jwt = require('jsonwebtoken');
require('dotenv').config();

// ─── Verificar Token ─────────────────────────────────────────
const verificarToken = (req, res, next) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1]; // Formato: "Bearer <token>"

    if (!token) {
        return res.status(401).json({ mensaje: 'Acceso denegado. Token requerido.' });
    }

    try {
        const decoded = jwt.verify(token, process.env.JWT_SECRET);
        req.usuario = decoded; // { id, correo, rol }
        next();
    } catch (err) {
        return res.status(403).json({ mensaje: 'Token inválido o expirado.' });
    }
};

// ─── Verificar Rol ────────────────────────────────────────────
// Uso: verificarRol('admin') o verificarRol('admin', 'vendedor')
const verificarRol = (...rolesPermitidos) => {
    return (req, res, next) => {
        if (!req.usuario) {
            return res.status(401).json({ mensaje: 'No autenticado.' });
        }

        if (!rolesPermitidos.includes(req.usuario.rol)) {
            return res.status(403).json({
                mensaje: `Acceso denegado. Se requiere rol: ${rolesPermitidos.join(' o ')}.`
            });
        }

        next();
    };
};

module.exports = { verificarToken, verificarRol };
