const mongoose = require('mongoose');

const sleepSchema = new mongoose.Schema({
  user: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    required: true
  },
  date: {
    type: Date,
    required: true
  },
  bedTime: {
    type: String,
    required: true
  },
  wakeTime: {
    type: String,
    required: true
  },
  duration: {
    type: Number
  },
  quality: {
    type: Number,
    min: 1,
    max: 5,
    required: true
  }
}, {
  timestamps: true
});

sleepSchema.index({ user: 1, date: 1 }, { unique: true });

module.exports = mongoose.model('Sleep', sleepSchema);