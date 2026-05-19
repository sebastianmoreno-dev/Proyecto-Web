// routes/propiedades.js
const express = require('express');
const router  = express.Router();
const ctrl    = require('../controllers/propiedadesController');
const { verificarToken, verificarRol } = require('../middlewares/auth');

// ── PÚBLICAS ─────────────────────────────────────────────────
// GET  /api/propiedades           → Listar todas (con filtros opcionales)
// GET  /api/propiedades/:id       → Ver una propiedad

router.get('/',    ctrl.listar);
router.get('/:id', ctrl.verUna);

// ── VENDEDOR / ADMIN ─────────────────────────────────────────
// GET  /api/propiedades/mis-propiedades → Ver propiedades propias (vendedor)
// POST /api/propiedades                 → Crear propiedad
// PUT  /api/propiedades/:id             → Editar propiedad
// DELETE /api/propiedades/:id           → Eliminar propiedad

router.get('/mis-propiedades',
    verificarToken,
    verificarRol('vendedor', 'admin'),
    ctrl.misPropiedades
);

router.post('/',
    verificarToken,
    verificarRol('vendedor', 'admin'),
    ctrl.crear
);

router.put('/:id',
    verificarToken,
    verificarRol('vendedor', 'admin'),
    ctrl.actualizar
);

router.delete('/:id',
    verificarToken,
    verificarRol('vendedor', 'admin'),
    ctrl.eliminar
);

module.exports = router;
