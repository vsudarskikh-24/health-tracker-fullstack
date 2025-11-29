const Steps = require('../models/Steps');

const addSteps = async (req, res) => {
  try {
    const { date, count } = req.body;

    const steps = await Steps.findOneAndUpdate(
      { user: req.user._id, date: new Date(date) },
      {
        user: req.user._id,
        date: new Date(date),
        count
      },
      { upsert: true, new: true }
    );

    res.status(201).json(steps);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

const getSteps = async (req, res) => {
  try {
    const { date } = req.query;
    const steps = await Steps.findOne({
      user: req.user._id,
      date: new Date(date)
    });

    res.json(steps || { count: 0, goal: 10000 });
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

const getStepsHistory = async (req, res) => {
  try {
    const { startDate, endDate } = req.query;
    const query = { user: req.user._id };

    if (startDate && endDate) {
      query.date = {
        $gte: new Date(startDate),
        $lte: new Date(endDate)
      };
    }

    const stepsData = await Steps.find(query).sort({ date: -1 });
    res.json(stepsData);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

module.exports = { addSteps, getSteps, getStepsHistory };