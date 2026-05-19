// controllers/favoritosController.js
// Solo compradores pueden guardar/quitar favoritos

const db = require('../config/db');

// ─── VER FAVORITOS del comprador ────────────────────────────
// GET /api/favoritos
const verFavoritos = async (req, res) => {
    try {
        const [rows] = await db.query(`
            SELECT p.*, f.creado_en AS guardado_en
            FROM favoritos f
            JOIN propiedades p ON f.propiedad_id = p.id
            WHERE f.comprador_id = ?
            ORDER BY f.creado_en DESC
        `, [req.usuario.id]);
        res.json(rows);
    } catch (err) {
        res.status(500).json({ mensaje: 'Error al obtener favoritos.' });
    }
};

// ─── AGREGAR A FAVORITOS ─────────────────────────────────────
// POST /api/favoritos/:propiedad_id
const agregar = async (req, res) => {
    try {
        const { propiedad_id } = req.params;

        // Verificar que la propiedad existe
        const [prop] = await db.query('SELECT id FROM propiedades WHERE id = ?', [propiedad_id]);
        if (prop.length === 0) return res.status(404).json({ mensaje: 'Propiedad no encontrada.' });

        await db.query(
            'INSERT IGNORE INTO favoritos (comprador_id, propiedad_id) VALUES (?, ?)',
            [req.usuario.id, propiedad_id]
        );

        res.status(201).json({ mensaje: 'Propiedad guardada en favoritos.' });
    } catch (err) {
        res.status(500).json({ mensaje: 'Error al agregar favorito.' });
    }
};

// ─── QUITAR DE FAVORITOS ─────────────────────────────────────
// DELETE /api/favoritos/:propiedad_id
const quitar = async (req, res) => {
    try {
        await db.query(
            'DELETE FROM favoritos WHERE comprador_id = ? AND propiedad_id = ?',
            [req.usuario.id, req.params.propiedad_id]
        );
        res.json({ mensaje: 'Propiedad quitada de favoritos.' });
    } catch (err) {
        res.status(500).json({ mensaje: 'Error al quitar favorito.' });
    }
};

module.exports = { verFavoritos, agregar, quitar };
