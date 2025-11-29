const Sleep = require('../models/Sleep');
const Water = require('../models/Water');
const Steps = require('../models/Steps');
const Meal = require('../models/Meal');

const getDashboard = async (req, res) => {
  try {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const sleep = await Sleep.findOne({
      user: req.user._id,
      date: today
    });

    const water = await Water.findOne({
      user: req.user._id,
      date: today
    });

    const steps = await Steps.findOne({
      user: req.user._id,
      date: today
    });

    const meals = await Meal.countDocuments({
      user: req.user._id,
      date: today
    });

    res.json({
      sleep: sleep || null,
      water: water ? water.amount : 0,
      waterGoal: 2500,
      steps: steps ? steps.count : 0,
      stepsGoal: 10000,
      meals: meals,
      mealsGoal: 4
    });
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

const getStatistics = async (req, res) => {
  try {
    const { startDate, endDate, type } = req.query;
    
    const query = {
      user: req.user._id,
      date: {
        $gte: new Date(startDate),
        $lte: new Date(endDate)
      }
    };

    let data = [];

    switch(type) {
      case 'sleep':
        data = await Sleep.find(query).sort({ date: 1 });
        break;
      case 'water':
        data = await Water.find(query).sort({ date: 1 });
        break;
      case 'steps':
        data = await Steps.find(query).sort({ date: 1 });
        break;
      case 'meals':
        const mealsData = await Meal.find(query).sort({ date: 1 });
        const groupedMeals = {};
        mealsData.forEach(meal => {
          const dateKey = meal.date.toISOString().split('T')[0];
          groupedMeals[dateKey] = (groupedMeals[dateKey] || 0) + 1;
        });
        data = Object.keys(groupedMeals).map(date => ({
          date,
          count: groupedMeals[date]
        }));
        break;
      default:
        return res.status(400).json({ message: 'Invalid type' });
    }

    res.json(data);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

module.exports = { getDashboard, getStatistics };