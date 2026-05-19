// controllers/propiedadesController.js
// CRUD de propiedades con lógica por rol

const db = require('../config/db');

// ─── LISTAR TODAS (público) ──────────────────────────────────
// GET /api/propiedades
const listar = async (req, res) => {
    try {
        const { ubicacion, tipo, precio_min, precio_max } = req.query;

        let sql = `
            SELECT p.*, u.nombre AS vendedor_nombre, u.apellido AS vendedor_apellido
            FROM propiedades p
            JOIN usuarios u ON p.vendedor_id = u.id
            WHERE p.estado = 'activa'
        `;
        const params = [];

        if (ubicacion) { sql += ' AND p.ubicacion LIKE ?'; params.push(`%${ubicacion}%`); }
        if (tipo)      { sql += ' AND p.tipo = ?';         params.push(tipo); }
        if (precio_min){ sql += ' AND p.precio >= ?';      params.push(precio_min); }
        if (precio_max){ sql += ' AND p.precio <= ?';      params.push(precio_max); }

        sql += ' ORDER BY p.creado_en DESC';

        const [propiedades] = await db.query(sql, params);
        res.json(propiedades);

    } catch (err) {
        console.error('Error al listar propiedades:', err);
        res.status(500).json({ mensaje: 'Error interno del servidor.' });
    }
};

// ─── VER UNA (público) ───────────────────────────────────────
// GET /api/propiedades/:id
const verUna = async (req, res) => {
    try {
        const [rows] = await db.query(`
            SELECT p.*, u.nombre AS vendedor_nombre, u.apellido AS vendedor_apellido, u.correo AS vendedor_correo
            FROM propiedades p
            JOIN usuarios u ON p.vendedor_id = u.id
            WHERE p.id = ?
        `, [req.params.id]);

        if (rows.length === 0) {
            return res.status(404).json({ mensaje: 'Propiedad no encontrada.' });
        }
        res.json(rows[0]);

    } catch (err) {
        res.status(500).json({ mensaje: 'Error interno del servidor.' });
    }
};

// ─── CREAR (solo vendedor y admin) ──────────────────────────
// POST /api/propiedades
const crear = async (req, res) => {
    try {
        const { titulo, descripcion, precio, ubicacion, tipo, habitaciones, banos, area_m2, imagen_url } = req.body;

        if (!titulo || !precio || !ubicacion) {
            return res.status(400).json({ mensaje: 'Título, precio y ubicación son requeridos.' });
        }

        const vendedor_id = req.usuario.id;

        const [resultado] = await db.query(
            `INSERT INTO propiedades 
            (titulo, descripcion, precio, ubicacion, tipo, habitaciones, banos, area_m2, imagen_url, vendedor_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
            [titulo, descripcion, precio, ubicacion, tipo || 'casa', habitaciones || 0, banos || 0, area_m2 || 0, imagen_url || '', vendedor_id]
        );

        res.status(201).json({
            mensaje: 'Propiedad creada con éxito.',
            propiedad_id: resultado.insertId
        });

    } catch (err) {
        console.error('Error al crear propiedad:', err);
        res.status(500).json({ mensaje: 'Error interno del servidor.' });
    }
};

// ─── ACTUALIZAR (dueño o admin) ──────────────────────────────
// PUT /api/propiedades/:id
const actualizar = async (req, res) => {
    try {
        const { id } = req.params;
        const { titulo, descripcion, precio, ubicacion, tipo, habitaciones, banos, area_m2, imagen_url, estado } = req.body;

        // Verificar que la propiedad existe
        const [rows] = await db.query('SELECT * FROM propiedades WHERE id = ?', [id]);
        if (rows.length === 0) return res.status(404).json({ mensaje: 'Propiedad no encontrada.' });

        const propiedad = rows[0];

        // Solo el dueño o un admin puede editar
        if (req.usuario.rol !== 'admin' && propiedad.vendedor_id !== req.usuario.id) {
            return res.status(403).json({ mensaje: 'No tienes permiso para editar esta propiedad.' });
        }

        await db.query(
            `UPDATE propiedades SET
                titulo        = COALESCE(?, titulo),
                descripcion   = COALESCE(?, descripcion),
                precio        = COALESCE(?, precio),
                ubicacion     = COALESCE(?, ubicacion),
                tipo          = COALESCE(?, tipo),
                habitaciones  = COALESCE(?, habitaciones),
                banos         = COALESCE(?, banos),
                area_m2       = COALESCE(?, area_m2),
                imagen_url    = COALESCE(?, imagen_url),
                estado        = COALESCE(?, estado)
            WHERE id = ?`,
            [titulo, descripcion, precio, ubicacion, tipo, habitaciones, banos, area_m2, imagen_url, estado, id]
        );

        res.json({ mensaje: 'Propiedad actualizada con éxito.' });

    } catch (err) {
        console.error('Error al actualizar:', err);
        res.status(500).json({ mensaje: 'Error interno del servidor.' });
    }
};

// ─── ELIMINAR (dueño o admin) ────────────────────────────────
// DELETE /api/propiedades/:id
const eliminar = async (req, res) => {
    try {
        const { id } = req.params;

        const [rows] = await db.query('SELECT * FROM propiedades WHERE id = ?', [id]);
        if (rows.length === 0) return res.status(404).json({ mensaje: 'Propiedad no encontrada.' });

        if (req.usuario.rol !== 'admin' && rows[0].vendedor_id !== req.usuario.id) {
            return res.status(403).json({ mensaje: 'No tienes permiso para eliminar esta propiedad.' });
        }

        await db.query('DELETE FROM propiedades WHERE id = ?', [id]);
        res.json({ mensaje: 'Propiedad eliminada.' });

    } catch (err) {
        res.status(500).json({ mensaje: 'Error interno del servidor.' });
    }
};

// ─── MIS PROPIEDADES (vendedor) ──────────────────────────────
// GET /api/propiedades/mis-propiedades
const misPropiedades = async (req, res) => {
    try {
        const [rows] = await db.query(
            'SELECT * FROM propiedades WHERE vendedor_id = ? ORDER BY creado_en DESC',
            [req.usuario.id]
        );
        res.json(rows);
    } catch (err) {
        res.status(500).json({ mensaje: 'Error interno del servidor.' });
    }
};

module.exports = { listar, verUna, crear, actualizar, eliminar, misPropiedades };
