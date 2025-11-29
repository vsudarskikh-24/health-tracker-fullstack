const mongoose = require('mongoose');

const waterSchema = new mongoose.Schema({
  user: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    required: true
  },
  date: {
    type: Date,
    required: true
  },
  amount: {
    type: Number,
    required: true,
    default: 0
  },
  goal: {
    type: Number,
    default: 2500
  }
}, {
  timestamps: true
});

waterSchema.index({ user: 1, date: 1 }, { unique: true });

module.exports = mongoose.model('Water', waterSchema);