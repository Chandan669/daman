import React, { useState, useEffect } from 'react';

function App() {
  const [command, setCommand] = useState('');
  const [status, setStatus] = useState('Disconnected');
  const [logs, setLogs] = useState([]);
  const [ws, setWs] = useState(null);

  useEffect(() => {
    const socket = new WebSocket('ws://localhost:8000/ws');

    socket.onopen = () => {
      setStatus('Connected');
      setWs(socket);
    };

    socket.onmessage = (event) => {
      const data = JSON.parse(event.data);
      setLogs((prevLogs) => [...prevLogs, data.data]);
    };

    socket.onclose = () => {
      setStatus('Disconnected');
    };

    return () => socket.close();
  }, []);

  const sendCommand = async (e) => {
    e.preventDefault();
    if (!command.trim()) return;

    if (ws && ws.readyState === WebSocket.OPEN) {
      ws.send(command);
    }

    try {
      const res = await fetch(`http://localhost:8000/api/command?command=${encodeURIComponent(command)}`, {
        method: 'POST'
      });
      const data = await res.json();
      setLogs(prev => [...prev, `API Response: ${JSON.stringify(data)}`]);
    } catch (err) {
      console.error(err);
    }
    setCommand('');
  };

  const emergencyStop = async () => {
    try {
      const res = await fetch('http://localhost:8000/api/stop', { method: 'POST' });
      const data = await res.json();
      setLogs(prev => [...prev, `EMERGENCY STOP: ${data.message}`]);
    } catch (err) {
      console.error(err);
    }
  };

  return (
    <div className="min-h-screen bg-gray-900 text-white p-4 font-sans">
      <header className="flex justify-between items-center border-b border-gray-700 pb-4 mb-4">
        <h1 className="text-2xl font-bold">Chandan Agent Control Panel</h1>
        <div className="flex items-center gap-4">
          <span className={`px-3 py-1 rounded-full text-sm ${status === 'Connected' ? 'bg-green-600' : 'bg-red-600'}`}>
            {status}
          </span>
          <button
            onClick={emergencyStop}
            className="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
          >
            EMERGENCY STOP
          </button>
        </div>
      </header>

      <main className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <section className="bg-gray-800 p-6 rounded-lg shadow-lg">
          <h2 className="text-xl font-semibold mb-4">Command Center</h2>
          <form onSubmit={sendCommand} className="flex flex-col gap-4">
            <textarea
              className="w-full h-32 p-3 bg-gray-700 rounded text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Tell the agent what to do..."
              value={command}
              onChange={(e) => setCommand(e.target.value)}
            />
            <button
              type="submit"
              className="bg-blue-600 hover:bg-blue-700 font-semibold py-2 px-4 rounded transition-colors"
            >
              Send Command
            </button>
          </form>
        </section>

        <section className="bg-gray-800 p-6 rounded-lg shadow-lg flex flex-col">
          <h2 className="text-xl font-semibold mb-4">Agent Logs</h2>
          <div className="flex-1 bg-black p-4 rounded overflow-y-auto max-h-96 font-mono text-sm text-green-400">
            {logs.length === 0 ? (
              <span className="text-gray-500">No logs yet...</span>
            ) : (
              logs.map((log, i) => (
                <div key={i} className="mb-2 border-b border-gray-800 pb-1">
                  &gt; {log}
                </div>
              ))
            )}
          </div>
        </section>
      </main>
    </div>
  );
}

export default App;
