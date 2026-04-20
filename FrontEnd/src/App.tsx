import { BrowserRouter } from 'react-router-dom'
import { AuthProvider } from './shared/AuthContext'
import { NotificationProvider } from './shared/Notification'
import { ThemeProvider } from './shared/ThemeContext'
import AppRouter from './router/AppRouter'

export default function App() {
  return (
    <ThemeProvider>
      <BrowserRouter>
        <AuthProvider>
          <NotificationProvider>
            <AppRouter />
          </NotificationProvider>
        </AuthProvider>
      </BrowserRouter>
    </ThemeProvider>
  )
}
