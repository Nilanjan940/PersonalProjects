import React, { useState, useEffect } from 'react';
import axios from 'axios';
import './App.css';

function App() {
    const [students, setStudents] = useState([]);
    const [files, setFiles] = useState({ attendance: null, scores: null, financial: null });

    const handleFileSelect = (input, type) => {
        setFiles(prev => ({ ...prev, [type]: input.files[0] }));
    };

    const processData = async () => {
        const formData = new FormData();
        formData.append('attendance', files.attendance);
        formData.append('scores', files.scores);
        formData.append('financial', files.financial);

        try {
            await axios.post(`${process.env.REACT_APP_API_URL}/upload`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            alert("Data processed successfully!");
            fetchStudents();
        } catch (error) {
            console.error(error);
            alert("Upload failed: " + (error.response?.data?.message || error.message));
        }
    };

    const fetchStudents = async () => {
        try {
            const res = await axios.get(`${process.env.REACT_APP_API_URL}/fetch`);
            setStudents(res.data.students || []);
        } catch (error) {
            console.error(error);
        }
    };

    useEffect(() => { fetchStudents(); }, []);

    return (
        <div className="container-fluid">
            <div className="row">
                <div className="col-lg-2 sidebar d-none d-lg-block">
                    <div className="d-flex flex-column p-3">
                        <span className="fs-4 text-white mb-3">Student Analytics</span>
                        <hr />
                        <ul className="nav nav-pills flex-column mb-auto">
                            <li className="nav-item"><span className="nav-link active">Dashboard</span></li>
                            <li><span className="nav-link">Students</span></li>
                            <li><span className="nav-link">Reports</span></li>
                            <li><span className="nav-link">Notifications</span></li>
                            <li><span className="nav-link">Settings</span></li>
                        </ul>
                        <hr />
                        <div>
                            <img src="https://via.placeholder.com/32" alt="user" className="rounded-circle me-2" />
                            <strong className="text-white">Admin User</strong>
                        </div>
                    </div>
                </div>

                <div className="col-lg-10 main-content">
                    <h1>Student Drop-out Prediction System</h1>
                    <div className="row mt-4">
                        {['attendance','scores','financial'].map((type,i) => (
                            <div key={i} className="col-md-4 mb-3">
                                <div className="upload-area" onClick={() => document.getElementById(type+'File').click()}>
                                    <input type="file" id={type+'File'} accept=".csv,.xlsx" style={{ display:'none' }}
                                        onChange={(e) => handleFileSelect(e.target, type)} />
                                    <h5>{type.charAt(0).toUpperCase() + type.slice(1)} Data</h5>
                                    {files[type] && <div className="text-success small mt-2">{files[type].name}</div>}
                                </div>
                            </div>
                        ))}
                    </div>
                    <button className="btn btn-primary mt-3" onClick={processData}>Process Data</button>

                    <div className="mt-4">
                        <h3>Student Records</h3>
                        <table className="table table-bordered">
                            <thead>
                                <tr><th>Name</th><th>Attendance</th><th>Test Score</th><th>Fee Paid</th></tr>
                            </thead>
                            <tbody>
                                {students.map((s,i) => (
                                    <tr key={i}>
                                        <td>{s.name}</td>
                                        <td>{s.attendance}</td>
                                        <td>{s.testScore}</td>
                                        <td>{s.feePaid}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default App;
