const mongoose = require('mongoose');

const goalSchema = new mongoose.Schema({
  user: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    required: true
  },
  goalType: {
    type: String,
    enum: ['sleep', 'water', 'steps', 'meals'],
    required: true
  },
  targetValue: {
    type: Number,
    required: true
  },
  currentValue: {
    type: Number,
    default: 0
  }
}, {
  timestamps: true
});

goalSchema.index({ user: 1, goalType: 1 }, { unique: true });

module.exports = mongoose.model('Goal', goalSchema);