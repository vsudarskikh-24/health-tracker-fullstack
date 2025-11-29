const express = require('express');
const router = express.Router();
const { addWater, getWater, getWaterHistory } = require('../controllers/waterController');
const { protect } = require('../middleware/auth');

router.post('/', protect, addWater);
router.get('/', protect, getWater);
router.get('/history', protect, getWaterHistory);

module.exports = router;