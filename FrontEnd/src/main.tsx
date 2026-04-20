import React from 'react'
import ReactDOM from 'react-dom/client'
import './styles/tokens.css'
import './styles/global.css'
import App from './App'
import ApiErrorBoundary from './shared/ApiErrorBoundary'

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <ApiErrorBoundary>
      <App />
    </ApiErrorBoundary>
  </React.StrictMode>
)
