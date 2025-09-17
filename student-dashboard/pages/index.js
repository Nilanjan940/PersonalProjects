import { useState, useEffect } from 'react';

export default function Home() {
  const [file, setFile] = useState(null);
  const [data, setData] = useState([]);
  const [headers, setHeaders] = useState([]);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    const res = await fetch('/api/fetch');
    const result = await res.json();
    if (!result.error) {
      const rows = result.data;
      setData(rows);
      if (rows.length > 0) {
        setHeaders(Object.keys(rows[0]));
      }
    }
  };

  const handleFileChange = (e) => {
    setFile(e.target.files[0]);
  };

  const handleUpload = async () => {
    if (!file) return alert("Please select a file");
    const formData = new FormData();
    formData.append('file', file);

    const res = await fetch('/api/upload', {
      method: 'POST',
      body: formData,
    });

    const result = await res.json();
    if (result.error) {
      alert(result.error);
    } else {
      alert(result.message);
      fetchData();
    }
  };

  const getCellStyle = (value) => {
    if (!value) return {};
    if (!isNaN(value)) {
      if (parseFloat(value) < 50) {
        return { backgroundColor: '#f8d7da' };
      } else if (parseFloat(value) < 75) {
        return { backgroundColor: '#fff3cd' };
      }
    }
    return {};
  };

  return (
    <div style={{ padding: '20px' }}>
      <h1>Spreadsheet Integration with MongoDB</h1>
      <input type="file" accept=".xlsx, .xls, .csv" onChange={handleFileChange} />
      <button onClick={handleUpload} style={{ marginLeft: '10px' }}>Upload</button>

      {data.length > 0 && (
        <table border="1" cellPadding="5" style={{ marginTop: '20px', borderCollapse: 'collapse' }}>
          <thead>
            <tr>
              {headers.map((head, index) => (
                <th key={index}>{head}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {data.map((row, rowIndex) => (
              <tr key={rowIndex}>
                {headers.map((head, index) => (
                  <td key={index} style={getCellStyle(row[head])}>
                    {row[head]}
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}
