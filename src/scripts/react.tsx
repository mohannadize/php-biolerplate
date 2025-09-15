import { createRoot } from 'react-dom/client';

function App() {
	return <div>
		<div className="text-blue-400">Home</div>
	</div>
}

createRoot(document.getElementById('reactApp')!).render(<App />);

