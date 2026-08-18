import React, { useState, useEffect } from 'react';

const API_URL = import.meta.env.VITE_AGENT_API_URL || 'http://localhost:8000/api';

function App() {
  const [token, setToken] = useState(localStorage.getItem('gateway_token') || '');
  const [pairingCode, setPairingCode] = useState('');
  const [command, setCommand] = useState('');
  const [status, setStatus] = useState('Disconnected');
  const [logs, setLogs] = useState([]);
  const [tasks, setTasks] = useState([]);
  const [approvals, setApprovals] = useState([]);
  const [devices, setDevices] = useState([]);

  const fetchState = async () => {
    if (!token) return;
    try {
      const headers = { Authorization: `Bearer ${token}` };
      const tasksRes = await fetch(`${API_URL}/tasks`, { headers });
      if (tasksRes.ok) setTasks(await tasksRes.json());

      const appRes = await fetch(`${API_URL}/approvals`, { headers });
      if (appRes.ok) setApprovals(await appRes.json());

      const logsRes = await fetch(`${API_URL}/logs`, { headers });
      if (logsRes.ok) setLogs(await logsRes.json());

      const devRes = await fetch(`${API_URL}/devices`, { headers });
      if (devRes.ok) setDevices(await devRes.json());
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

  // Pseudo-login for the dashboard user (web user, not the device)
  const loginDashboard = () => {
     // In a full production system, this would be a real user login (e.g., OAuth/JWT).
     // Here we just skip to giving them access to the pairing screen.
     const dummyUserToken = "web_user_token";
     setToken(dummyUserToken);
     localStorage.setItem('gateway_token', dummyUserToken);
  }

  const generatePairingCode = async () => {
    const res = await fetch(`${API_URL}/auth/generate-pairing`, { method: 'POST' });
    const data = await res.json();
    setPairingCode(data.pairing_code);
  };

  const sendCommand = async (e) => {
    e.preventDefault();
    if (!command.trim() || !token) return;
    try {
      await fetch(`${API_URL}/tasks?command=${encodeURIComponent(command)}`, {
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
    await fetch(`${API_URL}/tasks/stop`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` }
    });
    fetchState();
  };

  const handleApproval = async (id, approved) => {
    await fetch(`${API_URL}/approvals/${id}?approved=${approved}`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` }
    });
    fetchState();
  };

  if (!token) {
    return (
      <div className="min-h-screen bg-gray-900 text-white flex items-center justify-center flex-col gap-4">
         <h1 className="text-3xl font-bold">Chandan Agent Login</h1>
        <button onClick={loginDashboard} className="bg-blue-600 px-6 py-3 rounded text-xl font-bold hover:bg-blue-700">Enter Web Dashboard</button>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-900 text-white p-4 font-sans">
      <header className="flex justify-between items-center border-b border-gray-700 pb-4 mb-4">
        <h1 className="text-2xl font-bold">Chandan Agent Control Panel</h1>
        <div className="flex items-center gap-4">
          <button onClick={emergencyStop} className="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg text-lg uppercase shadow-lg shadow-red-900/50">EMERGENCY STOP</button>
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
          {approvals.length === 0 && <span className="text-sm text-gray-400">No pending approvals.</span>}
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

        <section className="flex flex-col gap-6">
          <div className="bg-gray-800 p-6 rounded-lg shadow-lg">
            <h2 className="text-xl font-semibold mb-4 flex justify-between">
                <span>Paired Devices</span>
                <button onClick={generatePairingCode} className="text-sm bg-blue-600 px-3 py-1 rounded">Add New Laptop</button>
            </h2>
            {pairingCode && (
                <div className="mb-4 bg-gray-700 p-4 rounded text-center">
                    <p className="text-sm mb-2">Enter this code in your Windows Agent CLI:</p>
                    <span className="text-3xl font-mono tracking-widest font-bold text-yellow-400">{pairingCode}</span>
                </div>
            )}
            {devices.map(d => {
                const isOnline = d.last_heartbeat && (new Date() - new Date(d.last_heartbeat)) < 30000;
                return (
                 <div key={d.device_id} className="border-b border-gray-700 py-2 text-sm flex justify-between items-center">
                    <div>
                        <span className="font-bold">{d.device_id}</span>
                        <div className="text-xs text-gray-400">OS: {d.os || '?'} | CPU: {d.cpu ? `${d.cpu}%` : '?'} | RAM: {d.ram ? `${d.ram}%` : '?'}</div>
                    </div>
                    <span className={`px-2 py-1 rounded text-xs font-bold ${isOnline ? 'bg-green-600' : 'bg-gray-600'}`}>
                        {isOnline ? 'ONLINE' : 'OFFLINE'}
                    </span>
                 </div>
            )})}
          </div>

          <div className="bg-gray-800 p-6 rounded-lg shadow-lg flex-1 flex flex-col">
            <h2 className="text-xl font-semibold mb-4">Task Queue</h2>
            <div className="max-h-48 overflow-y-auto font-mono text-sm">
                {tasks.map(t => (
                    <div key={t.id} className="border-b border-gray-700 py-2 flex justify-between">
                        <span className="truncate max-w-[70%]">{t.command}</span>
                        <span className="text-xs text-gray-400">{t.status}</span>
                    </div>
                ))}
            </div>
          </div>
        </section>
      </main>
    </div>
  );
}

export default App;
