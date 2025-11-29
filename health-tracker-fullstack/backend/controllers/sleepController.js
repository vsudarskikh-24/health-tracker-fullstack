const Sleep = require('../models/Sleep');

const addSleep = async (req, res) => {
  try {
    const { date, bedTime, wakeTime, quality } = req.body;

    const sleep = await Sleep.findOneAndUpdate(
      { user: req.user._id, date: new Date(date) },
      {
        user: req.user._id,
        date: new Date(date),
        bedTime,
        wakeTime,
        quality
      },
      { upsert: true, new: true }
    );

    res.status(201).json(sleep);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

const getSleep = async (req, res) => {
  try {
    const { date } = req.query;
    const sleep = await Sleep.findOne({
      user: req.user._id,
      date: new Date(date)
    });

    res.json(sleep || {});
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

const getSleepHistory = async (req, res) => {
  try {
    const { startDate, endDate } = req.query;
    const query = { user: req.user._id };

    if (startDate && endDate) {
      query.date = {
        $gte: new Date(startDate),
        $lte: new Date(endDate)
      };
    }

    const sleepData = await Sleep.find(query).sort({ date: -1 });
    res.json(sleepData);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

module.exports = { addSleep, getSleep, getSleepHistory };