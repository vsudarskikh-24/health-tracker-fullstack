const Goal = require('../models/Goal');

const createOrUpdateGoal = async (req, res) => {
  try {
    const { goalType, targetValue } = req.body;

    const goal = await Goal.findOneAndUpdate(
      { user: req.user._id, goalType },
      {
        user: req.user._id,
        goalType,
        targetValue
      },
      { upsert: true, new: true }
    );

    res.status(201).json(goal);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

const getGoals = async (req, res) => {
  try {
    const goals = await Goal.find({ user: req.user._id });
    res.json(goals);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

const deleteGoal = async (req, res) => {
  try {
    const goal = await Goal.findById(req.params.id);

    if (goal && goal.user.toString() === req.user._id.toString()) {
      await goal.deleteOne();
      res.json({ message: 'Goal removed' });
    } else {
      res.status(404).json({ message: 'Goal not found' });
    }
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

module.exports = { createOrUpdateGoal, getGoals, deleteGoal };