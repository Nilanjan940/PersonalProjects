import nextConnect from 'next-connect';
import connect from '../../lib/mongodb';
import StudentData from '../../lib/StudentData';

const apiRoute = nextConnect({
  onError(error, req, res) {
    res.status(501).json({ error: `Something went wrong! ${error.message}` });
  },
  onNoMatch(req, res) {
    res.status(405).json({ error: `Method not allowed` });
  },
});

apiRoute.get(async (req, res) => {
  try {
    await connect();
    const records = await StudentData.find().sort({ createdAt: -1 }).limit(1);
    if (records.length === 0) {
      return res.status(404).json({ error: 'No data found' });
    }
    res.status(200).json({ data: records[0].data });
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch data' });
  }
});

export default apiRoute;
