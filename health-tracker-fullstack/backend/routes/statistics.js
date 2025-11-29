const express = require('express');
const router = express.Router();
const { getDashboard, getStatistics } = require('../controllers/statisticsController');
const { protect } = require('../middleware/auth');

router.get('/dashboard', protect, getDashboard);
router.get('/', protect, getStatistics);

module.exports = router;