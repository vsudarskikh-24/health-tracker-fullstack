const express = require('express');
const router = express.Router();
const { addMeal, getMeals, deleteMeal, getMealsHistory } = require('../controllers/mealsController');
const { protect } = require('../middleware/auth');

router.post('/', protect, addMeal);
router.get('/', protect, getMeals);
router.get('/history', protect, getMealsHistory);
router.delete('/:id', protect, deleteMeal);

module.exports = router;