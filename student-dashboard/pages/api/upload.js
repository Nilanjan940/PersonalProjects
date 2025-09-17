import nextConnect from 'next-connect';
import multer from 'multer';
import XLSX from 'xlsx';
import connect from '../../lib/mongodb';
import StudentData from '../../lib/StudentData';

const upload = multer({ storage: multer.memoryStorage() });

const apiRoute = nextConnect({
  onError(error, req, res) {
    res.status(501).json({ error: `Something went wrong! ${error.message}` });
  },
  onNoMatch(req, res) {
    res.status(405).json({ error: `Method not allowed` });
  },
});

apiRoute.use(upload.single('file'));

apiRoute.post(async (req, res) => {
  try {
    const buffer = req.file.buffer;
    const workbook = XLSX.read(buffer, { type: 'buffer' });
    const sheetName = workbook.SheetNames[0];
    const worksheet = workbook.Sheets[sheetName];
    const jsonData = XLSX.utils.sheet_to_json(worksheet, { defval: '' });

    await connect();
    const newData = new StudentData({ data: jsonData });
    await newData.save();

    res.status(200).json({ message: 'File uploaded and saved!', data: jsonData });
  } catch (error) {
    res.status(500).json({ error: 'Failed to parse or save file' });
  }
});

export default apiRoute;
export const config = {
  api: {
    bodyParser: false,
  },
};
