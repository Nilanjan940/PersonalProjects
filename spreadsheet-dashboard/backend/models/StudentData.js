const mongoose = require('mongoose');

const StudentDataSchema = new mongoose.Schema({
    name: String,
    attendance: Number,
    testScore: Number,
    feePaid: Number,
    date: { type: Date, default: Date.now }
});

module.exports = mongoose.model('StudentData', StudentDataSchema);
