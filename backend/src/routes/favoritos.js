// routes/favoritos.js
const express = require('express');
const router  = express.Router();
const ctrl    = require('../controllers/favoritosController');
const { verificarToken, verificarRol } = require('../middlewares/auth');

// Todas requieren autenticación y rol comprador
// GET    /api/favoritos/:propiedad_id  → Ver mis favoritos
// POST   /api/favoritos/:propiedad_id  → Agregar favorito
// DELETE /api/favoritos/:propiedad_id  → Quitar favorito

router.get('/',
    verificarToken,
    verificarRol('comprador'),
    ctrl.verFavoritos
);

router.post('/:propiedad_id',
    verificarToken,
    verificarRol('comprador'),
    ctrl.agregar
);

router.delete('/:propiedad_id',
    verificarToken,
    verificarRol('comprador'),
    ctrl.quitar
);

module.exports = router;
