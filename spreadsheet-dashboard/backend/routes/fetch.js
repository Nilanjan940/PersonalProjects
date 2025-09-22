const express = require('express');
const router = express.Router();
const StudentData = require('../models/StudentData');

router.get('/', async (req, res) => {
    try {
        const data = await StudentData.find();
        res.json({ students: data });
    } catch (error) {
        console.error(error);
        res.status(500).json({ message: 'Failed to fetch data' });
    }
});

module.exports = router;
