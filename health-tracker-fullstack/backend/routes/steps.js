const express = require('express');
const router = express.Router();
const { addSteps, getSteps, getStepsHistory } = require('../controllers/stepsController');
const { protect } = require('../middleware/auth');

router.post('/', protect, addSteps);
router.get('/', protect, getSteps);
router.get('/history', protect, getStepsHistory);

module.exports = router;