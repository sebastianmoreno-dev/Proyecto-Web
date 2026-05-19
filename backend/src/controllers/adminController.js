// controllers/adminController.js
// Rutas exclusivas del Administrador

const db   = require('../config/db');
const bcrypt = require('bcryptjs');

// ─── LISTAR TODOS LOS USUARIOS ───────────────────────────────
// GET /api/admin/usuarios
const listarUsuarios = async (req, res) => {
    try {
        const [rows] = await db.query(
            'SELECT id, nombre, apellido, correo, rol, creado_en FROM usuarios ORDER BY creado_en DESC'
        );
        res.json(rows);
    } catch (err) {
        res.status(500).json({ mensaje: 'Error al obtener usuarios.' });
    }
};

// ─── CAMBIAR ROL DE UN USUARIO ───────────────────────────────
// PUT /api/admin/usuarios/:id/rol
// Body: { rol: 'comprador' | 'vendedor' | 'admin' }
const cambiarRol = async (req, res) => {
    try {
        const { id } = req.params;
        const { rol } = req.body;

        if (!['comprador', 'vendedor', 'admin'].includes(rol)) {
            return res.status(400).json({ mensaje: 'Rol no válido.' });
        }

        const [rows] = await db.query('SELECT id FROM usuarios WHERE id = ?', [id]);
        if (rows.length === 0) return res.status(404).json({ mensaje: 'Usuario no encontrado.' });

        await db.query('UPDATE usuarios SET rol = ? WHERE id = ?', [rol, id]);
        res.json({ mensaje: `Rol actualizado a "${rol}" correctamente.` });

    } catch (err) {
        res.status(500).json({ mensaje: 'Error al cambiar rol.' });
    }
};

// ─── ELIMINAR USUARIO ────────────────────────────────────────
// DELETE /api/admin/usuarios/:id
const eliminarUsuario = async (req, res) => {
    try {
        const { id } = req.params;

        // No se puede eliminar a sí mismo
        if (parseInt(id) === req.usuario.id) {
            return res.status(400).json({ mensaje: 'No puedes eliminar tu propia cuenta.' });
        }

        const [rows] = await db.query('SELECT id FROM usuarios WHERE id = ?', [id]);
        if (rows.length === 0) return res.status(404).json({ mensaje: 'Usuario no encontrado.' });

        await db.query('DELETE FROM usuarios WHERE id = ?', [id]);
        res.json({ mensaje: 'Usuario eliminado.' });

    } catch (err) {
        res.status(500).json({ mensaje: 'Error al eliminar usuario.' });
    }
};

// ─── LISTAR TODAS LAS PROPIEDADES (incluso inactivas) ────────
// GET /api/admin/propiedades
const listarPropiedades = async (req, res) => {
    try {
        const [rows] = await db.query(`
            SELECT p.*, u.nombre AS vendedor_nombre, u.correo AS vendedor_correo
            FROM propiedades p
            JOIN usuarios u ON p.vendedor_id = u.id
            ORDER BY p.creado_en DESC
        `);
        res.json(rows);
    } catch (err) {
        res.status(500).json({ mensaje: 'Error al obtener propiedades.' });
    }
};

// ─── CAMBIAR ESTADO DE PROPIEDAD ────────────────────────────
// PUT /api/admin/propiedades/:id/estado
// Body: { estado: 'activa' | 'inactiva' | 'vendida' }
const cambiarEstadoPropiedad = async (req, res) => {
    try {
        const { id } = req.params;
        const { estado } = req.body;

        if (!['activa', 'inactiva', 'vendida'].includes(estado)) {
            return res.status(400).json({ mensaje: 'Estado no válido.' });
        }

        await db.query('UPDATE propiedades SET estado = ? WHERE id = ?', [estado, id]);
        res.json({ mensaje: `Estado cambiado a "${estado}".` });

    } catch (err) {
        res.status(500).json({ mensaje: 'Error al cambiar estado.' });
    }
};

// ─── ESTADÍSTICAS GENERALES ─────────────────────────────────
// GET /api/admin/stats
const estadisticas = async (req, res) => {
    try {
        const [[{ total_usuarios }]]    = await db.query('SELECT COUNT(*) AS total_usuarios FROM usuarios');
        const [[{ total_propiedades }]] = await db.query('SELECT COUNT(*) AS total_propiedades FROM propiedades');
        const [[{ total_activas }]]     = await db.query("SELECT COUNT(*) AS total_activas FROM propiedades WHERE estado = 'activa'");
        const [[{ total_vendidas }]]    = await db.query("SELECT COUNT(*) AS total_vendidas FROM propiedades WHERE estado = 'vendida'");
        const [[{ total_compradores }]] = await db.query("SELECT COUNT(*) AS total_compradores FROM usuarios WHERE rol = 'comprador'");
        const [[{ total_vendedores }]]  = await db.query("SELECT COUNT(*) AS total_vendedores FROM usuarios WHERE rol = 'vendedor'");

        res.json({
            total_usuarios,
            total_propiedades,
            total_activas,
            total_vendidas,
            total_compradores,
            total_vendedores
        });
    } catch (err) {
        res.status(500).json({ mensaje: 'Error al obtener estadísticas.' });
    }
};

module.exports = { listarUsuarios, cambiarRol, eliminarUsuario, listarPropiedades, cambiarEstadoPropiedad, estadisticas };
