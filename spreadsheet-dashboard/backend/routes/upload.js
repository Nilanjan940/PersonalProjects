const express = require('express');
const router = express.Router();
const XLSX = require('xlsx');
const multer = require('multer');
const StudentData = require('../models/StudentData');

const upload = multer({ storage: multer.memoryStorage() });

function parseExcel(buffer) {
    const workbook = XLSX.read(buffer, { type: 'buffer' });
    const sheetName = workbook.SheetNames[0];
    const sheet = workbook.Sheets[sheetName];
    return XLSX.utils.sheet_to_json(sheet);
}

function mergeData(attendance, scores, financial) {
    return attendance.map(att => {
        const score = scores.find(s => s.name === att.name) || {};
        const finance = financial.find(f => f.name === att.name) || {};
        return {
            name: att.name || "Unknown",
            attendance: att.attendance || 0,
            testScore: score.testScore || 0,
            feePaid: finance.feePaid || 0
        };
    });
}

router.post('/', upload.fields([
    { name: 'attendance', maxCount: 1 },
    { name: 'scores', maxCount: 1 },
    { name: 'financial', maxCount: 1 }
]), async (req, res) => {
    try {
        const attendanceFile = req.files['attendance']?.[0];
        const scoresFile = req.files['scores']?.[0];
        const financialFile = req.files['financial']?.[0];

        if (!attendanceFile || !scoresFile || !financialFile) {
            return res.status(400).json({ message: 'All three files are required.' });
        }

        const attendanceData = parseExcel(attendanceFile.buffer);
        const scoresData = parseExcel(scoresFile.buffer);
        const financialData = parseExcel(financialFile.buffer);

        const merged = mergeData(attendanceData, scoresData, financialData);

        await StudentData.insertMany(merged);

        res.json({ message: 'Data uploaded successfully' });
    } catch (error) {
        console.error('Error during file processing:', error);
        res.status(500).json({ message: 'Upload failed', error: error.message });
    }
});

module.exports = router;
