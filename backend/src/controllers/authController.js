// controllers/authController.js
// Registro e inicio de sesión de usuarios

const bcrypt  = require('bcryptjs');
const jwt     = require('jsonwebtoken');
const db      = require('../config/db');
require('dotenv').config();

// ─── REGISTRO ────────────────────────────────────────────────
// POST /api/auth/registro
// Body: { nombre, apellido, correo, contrasena, rol }
// rol puede ser: 'comprador' o 'vendedor' (el admin se crea directo en DB)
const registro = async (req, res) => {
    try {
        const { nombre, apellido, correo, contrasena, rol } = req.body;

        // Validar campos obligatorios
        if (!nombre || !apellido || !correo || !contrasena) {
            return res.status(400).json({ mensaje: 'Todos los campos son requeridos.' });
        }

        // Solo se puede registrar como comprador o vendedor
        const rolValido = ['comprador', 'vendedor'].includes(rol) ? rol : 'comprador';

        // Verificar si el correo ya existe
        const [existe] = await db.query('SELECT id FROM usuarios WHERE correo = ?', [correo]);
        if (existe.length > 0) {
            return res.status(409).json({ mensaje: 'El correo ya está registrado.' });
        }

        // Encriptar contraseña
        const hash = await bcrypt.hash(contrasena, 10);

        // Insertar usuario
        const [resultado] = await db.query(
            'INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol) VALUES (?, ?, ?, ?, ?)',
            [nombre, apellido, correo, hash, rolValido]
        );

        res.status(201).json({
            mensaje: 'Usuario registrado con éxito.',
            usuario: { id: resultado.insertId, nombre, apellido, correo, rol: rolValido }
        });

    } catch (err) {
        console.error('Error en registro:', err);
        res.status(500).json({ mensaje: 'Error interno del servidor.' });
    }
};

// ─── INICIO DE SESIÓN ────────────────────────────────────────
// POST /api/auth/login
// Body: { correo, contrasena }
const login = async (req, res) => {
    try {
        const { correo, contrasena } = req.body;

        if (!correo || !contrasena) {
            return res.status(400).json({ mensaje: 'Correo y contraseña son requeridos.' });
        }

        // Buscar usuario
        const [usuarios] = await db.query('SELECT * FROM usuarios WHERE correo = ?', [correo]);
        if (usuarios.length === 0) {
            return res.status(401).json({ mensaje: 'Credenciales incorrectas.' });
        }

        const usuario = usuarios[0];

        // Verificar contraseña
        const contrasenaValida = await bcrypt.compare(contrasena, usuario.contrasena);
        if (!contrasenaValida) {
            return res.status(401).json({ mensaje: 'Credenciales incorrectas.' });
        }

        // Generar token JWT
        const token = jwt.sign(
            { id: usuario.id, correo: usuario.correo, rol: usuario.rol },
            process.env.JWT_SECRET,
            { expiresIn: process.env.JWT_EXPIRES_IN }
        );

        res.json({
            mensaje: 'Sesión iniciada con éxito.',
            token,
            usuario: {
                id:       usuario.id,
                nombre:   usuario.nombre,
                apellido: usuario.apellido,
                correo:   usuario.correo,
                rol:      usuario.rol
            }
        });

    } catch (err) {
        console.error('Error en login:', err);
        res.status(500).json({ mensaje: 'Error interno del servidor.' });
    }
};

// ─── PERFIL (usuario autenticado) ───────────────────────────
// GET /api/auth/perfil
const perfil = async (req, res) => {
    try {
        const [usuarios] = await db.query(
            'SELECT id, nombre, apellido, correo, rol, creado_en FROM usuarios WHERE id = ?',
            [req.usuario.id]
        );
        if (usuarios.length === 0) {
            return res.status(404).json({ mensaje: 'Usuario no encontrado.' });
        }
        res.json(usuarios[0]);
    } catch (err) {
        res.status(500).json({ mensaje: 'Error interno del servidor.' });
    }
};

module.exports = { registro, login, perfil };
