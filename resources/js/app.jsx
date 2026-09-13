import React from 'react';
import ReactDOM from 'react-dom/client';



const App = () => {
    return (
        <div>
            
        </div>
    );
};

// Only render if the element exists
const appElement = document.getElementById('app');
if (appElement) {
    ReactDOM.createRoot(appElement).render(
        <React.StrictMode>
            <App />
        </React.StrictMode>
    );
}

export default App;