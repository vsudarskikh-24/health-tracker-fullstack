const express = require('express');
const router = express.Router();
const { addSleep, getSleep, getSleepHistory } = require('../controllers/sleepController');
const { protect } = require('../middleware/auth');

router.post('/', protect, addSleep);
router.get('/', protect, getSleep);
router.get('/history', protect, getSleepHistory);

module.exports = router;