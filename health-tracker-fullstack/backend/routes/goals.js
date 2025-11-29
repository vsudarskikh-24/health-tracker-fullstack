const express = require('express');
const router = express.Router();
const { createOrUpdateGoal, getGoals, deleteGoal } = require('../controllers/goalsController');
const { protect } = require('../middleware/auth');

router.route('/')
  .post(protect, createOrUpdateGoal)
  .get(protect, getGoals);

router.delete('/:id', protect, deleteGoal);

module.exports = router;