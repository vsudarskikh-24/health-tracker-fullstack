const Water = require('../models/Water');

const addWater = async (req, res) => {
  try {
    const { date, amount } = req.body;

    let water = await Water.findOne({
      user: req.user._id,
      date: new Date(date)
    });

    if (water) {
      water.amount += amount;
      await water.save();
    } else {
      water = await Water.create({
        user: req.user._id,
        date: new Date(date),
        amount
      });
    }

    res.status(201).json(water);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

const getWater = async (req, res) => {
  try {
    const { date } = req.query;
    const water = await Water.findOne({
      user: req.user._id,
      date: new Date(date)
    });

    res.json(water || { amount: 0, goal: 2500 });
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

const getWaterHistory = async (req, res) => {
  try {
    const { startDate, endDate } = req.query;
    const query = { user: req.user._id };

    if (startDate && endDate) {
      query.date = {
        $gte: new Date(startDate),
        $lte: new Date(endDate)
      };
    }

    const waterData = await Water.find(query).sort({ date: -1 });
    res.json(waterData);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

module.exports = { addWater, getWater, getWaterHistory };