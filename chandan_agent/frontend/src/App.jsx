import React, { useState, useEffect } from 'react';

function App() {
  const [token, setToken] = useState(localStorage.getItem('agent_token') || '');
  const [command, setCommand] = useState('');
  const [status, setStatus] = useState('Disconnected');
  const [logs, setLogs] = useState([]);
  const [tasks, setTasks] = useState([]);
  const [approvals, setApprovals] = useState([]);
  const [ws, setWs] = useState(null);

  const fetchState = async () => {
    if (!token) return;
    try {
      const headers = { Authorization: `Bearer ${token}` };
      const tasksRes = await fetch('http://localhost:8000/api/tasks', { headers });
      if (tasksRes.ok) setTasks(await tasksRes.json());

      const appRes = await fetch('http://localhost:8000/api/approvals', { headers });
      if (appRes.ok) setApprovals(await appRes.json());

      const logsRes = await fetch('http://localhost:8000/api/logs', { headers });
      if (logsRes.ok) setLogs(await logsRes.json());
    } catch (e) {
      console.error(e);
    }
  };

  useEffect(() => {
    if (!token) return;
    fetchState();
    const interval = setInterval(fetchState, 3000);
    return () => clearInterval(interval);
  }, [token]);

  useEffect(() => {
    if (!token) return;
    const socket = new WebSocket('ws://localhost:8000/ws');

    socket.onopen = () => {
      setStatus('Connected');
      setWs(socket);
    };
    socket.onclose = () => setStatus('Disconnected');
    return () => socket.close();
  }, [token]);

  const pairDevice = async () => {
    const res = await fetch(`http://localhost:8000/api/auth/pair?device_id=browser_${Date.now()}`, { method: 'POST' });
    const data = await res.json();
    setToken(data.token);
    localStorage.setItem('agent_token', data.token);
  };

  const sendCommand = async (e) => {
    e.preventDefault();
    if (!command.trim() || !token) return;
    try {
      await fetch(`http://localhost:8000/api/command?command=${encodeURIComponent(command)}`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` }
      });
      fetchState();
    } catch (err) {
      console.error(err);
    }
    setCommand('');
  };

  const emergencyStop = async () => {
    if (!token) return;
    await fetch('http://localhost:8000/api/stop', {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` }
    });
    fetchState();
  };

  const handleApproval = async (id, approved) => {
    await fetch(`http://localhost:8000/api/approvals/${id}?approved=${approved}`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` }
    });
    fetchState();
  };

  if (!token) {
    return (
      <div className="min-h-screen bg-gray-900 text-white flex items-center justify-center">
        <button onClick={pairDevice} className="bg-blue-600 px-6 py-3 rounded text-xl font-bold hover:bg-blue-700">Pair Device</button>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-900 text-white p-4 font-sans">
      <header className="flex justify-between items-center border-b border-gray-700 pb-4 mb-4">
        <h1 className="text-2xl font-bold">Chandan Agent Control Panel</h1>
        <div className="flex items-center gap-4">
          <span className={`px-3 py-1 rounded-full text-sm ${status === 'Connected' ? 'bg-green-600' : 'bg-red-600'}`}>
            {status}
          </span>
          <button onClick={emergencyStop} className="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">EMERGENCY STOP</button>
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
            <button type="submit" className="bg-blue-600 hover:bg-blue-700 font-semibold py-2 px-4 rounded transition-colors">Send Command</button>
          </form>

          <h3 className="text-lg font-semibold mt-6 mb-2">Pending Approvals</h3>
          {approvals.map(app => (
            <div key={app.id} className="bg-yellow-900 p-3 rounded mb-2 flex justify-between items-center">
              <div>
                <strong>{app.action}</strong>
                <pre className="text-xs text-gray-300">{JSON.stringify(app.details)}</pre>
              </div>
              <div className="flex gap-2">
                <button onClick={() => handleApproval(app.id, true)} className="bg-green-600 px-3 py-1 rounded text-sm">Approve</button>
                <button onClick={() => handleApproval(app.id, false)} className="bg-red-600 px-3 py-1 rounded text-sm">Reject</button>
              </div>
            </div>
          ))}
        </section>

        <section className="bg-gray-800 p-6 rounded-lg shadow-lg flex flex-col">
          <h2 className="text-xl font-semibold mb-4">Task Queue</h2>
          <div className="mb-4 max-h-48 overflow-y-auto">
             {tasks.map(t => (
                 <div key={t.id} className="border-b border-gray-700 py-2">
                    <div className="flex justify-between">
                        <span className="font-mono text-sm">{t.command}</span>
                        <span className="text-xs bg-gray-700 px-2 py-1 rounded">{t.status}</span>
                    </div>
                 </div>
             ))}
          </div>

          <h2 className="text-xl font-semibold mb-4">Audit Logs</h2>
          <div className="flex-1 bg-black p-4 rounded overflow-y-auto max-h-96 font-mono text-xs text-green-400">
            {logs.map((log, i) => (
                <div key={i} className="mb-2 border-b border-gray-800 pb-1">
                  [{new Date(log.timestamp).toLocaleTimeString()}] {log.action} {JSON.stringify(log.details)}
                </div>
            ))}
          </div>
        </section>
      </main>
    </div>
  );
}

export default App;
