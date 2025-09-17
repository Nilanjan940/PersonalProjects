import mongoose from 'mongoose';

const StudentDataSchema = new mongoose.Schema({
  data: { type: Array, required: true },
  createdAt: { type: Date, default: Date.now }
});

export default mongoose.models.StudentData || mongoose.model('StudentData', StudentDataSchema);
