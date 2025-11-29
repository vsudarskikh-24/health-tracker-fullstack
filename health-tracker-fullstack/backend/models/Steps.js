const mongoose = require('mongoose');

const stepsSchema = new mongoose.Schema({
  user: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    required: true
  },
  date: {
    type: Date,
    required: true
  },
  count: {
    type: Number,
    required: true,
    default: 0
  },
  goal: {
    type: Number,
    default: 10000
  }
}, {
  timestamps: true
});

stepsSchema.index({ user: 1, date: 1 }, { unique: true });

module.exports = mongoose.model('Steps', stepsSchema);