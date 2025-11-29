require('dotenv').config();
const express = require('express');
const cors = require('cors');
const connectDB = require('./config/db');

const authRoutes = require('./routes/auth');
const profileRoutes = require('./routes/profile');
const sleepRoutes = require('./routes/sleep');
const waterRoutes = require('./routes/water');
const stepsRoutes = require('./routes/steps');
const mealsRoutes = require('./routes/meals');
const goalsRoutes = require('./routes/goals');
const statisticsRoutes = require('./routes/statistics');

const app = express();

connectDB();

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

app.use('/api/auth', authRoutes);
app.use('/api/profile', profileRoutes);
app.use('/api/sleep', sleepRoutes);
app.use('/api/water', waterRoutes);
app.use('/api/steps', stepsRoutes);
app.use('/api/meals', mealsRoutes);
app.use('/api/goals', goalsRoutes);
app.use('/api/statistics', statisticsRoutes);

app.get('/', (req, res) => {
  res.json({ message: 'Health Tracker API is running' });
});

const PORT = process.env.PORT || 5000;

app.listen(PORT, () => {
  console.log(`Server is running on port ${PORT}`);
});

module.exports = app;