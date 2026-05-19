// routes/admin.js
const express = require('express');
const router  = express.Router();
const ctrl    = require('../controllers/adminController');
const { verificarToken, verificarRol } = require('../middlewares/auth');

// Todas las rutas de admin requieren token + rol admin
const soloAdmin = [verificarToken, verificarRol('admin')];

// GET    /api/admin/stats                         → Estadísticas generales
// GET    /api/admin/usuarios                      → Listar todos los usuarios
// PUT    /api/admin/usuarios/:id/rol              → Cambiar rol de usuario
// DELETE /api/admin/usuarios/:id                  → Eliminar usuario
// GET    /api/admin/propiedades                   → Ver todas las propiedades
// PUT    /api/admin/propiedades/:id/estado        → Cambiar estado de propiedad

router.get('/stats',                          ...soloAdmin, ctrl.estadisticas);
router.get('/usuarios',                       ...soloAdmin, ctrl.listarUsuarios);
router.put('/usuarios/:id/rol',               ...soloAdmin, ctrl.cambiarRol);
router.delete('/usuarios/:id',                ...soloAdmin, ctrl.eliminarUsuario);
router.get('/propiedades',                    ...soloAdmin, ctrl.listarPropiedades);
router.put('/propiedades/:id/estado',         ...soloAdmin, ctrl.cambiarEstadoPropiedad);

module.exports = router;
