const Meal = require('../models/Meal');

const addMeal = async (req, res) => {
  try {
    const { date, mealType, foods, time } = req.body;

    const meal = await Meal.create({
      user: req.user._id,
      date: new Date(date),
      mealType,
      foods,
      time
    });

    res.status(201).json(meal);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

const getMeals = async (req, res) => {
  try {
    const { date } = req.query;
    const meals = await Meal.find({
      user: req.user._id,
      date: new Date(date)
    });

    res.json(meals);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

const deleteMeal = async (req, res) => {
  try {
    const meal = await Meal.findById(req.params.id);

    if (meal && meal.user.toString() === req.user._id.toString()) {
      await meal.deleteOne();
      res.json({ message: 'Meal removed' });
    } else {
      res.status(404).json({ message: 'Meal not found' });
    }
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

const getMealsHistory = async (req, res) => {
  try {
    const { startDate, endDate } = req.query;
    const query = { user: req.user._id };

    if (startDate && endDate) {
      query.date = {
        $gte: new Date(startDate),
        $lte: new Date(endDate)
      };
    }

    const mealsData = await Meal.find(query).sort({ date: -1 });
    res.json(mealsData);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

module.exports = { addMeal, getMeals, deleteMeal, getMealsHistory };