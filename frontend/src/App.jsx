import { NavLink, Navigate, Route, Routes } from 'react-router-dom';
import { useAuth } from './context/AuthContext';
import DashboardPage from './pages/DashboardPage';
import AdminDashboardPage from './pages/AdminDashboardPage';
import HomePage from './pages/HomePage';
import LoginPage from './pages/LoginPage';
import NotFoundPage from './pages/NotFoundPage';
import QuizPage from './pages/QuizPage';
import RegisterPage from './pages/RegisterPage';
import ResultsPage from './pages/ResultsPage';
import SubjectSelectionPage from './pages/SubjectSelectionPage';
import QuestionListPage from './pages/QuestionListPage';
import TopicSelectionPage from './pages/TopicSelectionPage';
import './App.css';

function ProtectedRoute({ children }) {
  const { token } = useAuth();

  if (!token) {
    return <Navigate to="/login" replace />;
  }

  return children;
}

function AdminRoute({ children }) {
  const { user } = useAuth();

  if (!user?.is_admin) return <Navigate to="/dashboard" replace />;

  return children;
}

function App() {
  const { user, token, logout } = useAuth();

  return (
    <div className="app-shell">
      <header className="topbar">
        <div className="brand-block">
          <span className="brand-mark">S</span>
          <span>StudyFlow</span>
        </div>

        <nav className="main-nav">
          <NavLink to="/">Home</NavLink>
          {token ? (
            <>
              <NavLink to="/dashboard">Dashboard</NavLink>
              <NavLink to="/subjects">Study</NavLink>
              {user?.is_admin && <NavLink to="/admin">Admin</NavLink>}
            </>
          ) : (
            <>
              <NavLink to="/login">Login</NavLink>
              <NavLink to="/register">Register</NavLink>
            </>
          )}
        </nav>

        {token && (
          <div className="user-controls">
            <span>{user?.name || 'Player'}</span>
            <button type="button" onClick={logout} className="text-button">
              Logout
            </button>
          </div>
        )}
      </header>

      <main className="page-content">
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/register" element={<RegisterPage />} />

          <Route
            path="/dashboard"
            element={
              <ProtectedRoute>
                <DashboardPage />
              </ProtectedRoute>
            }
          />

          <Route path="/subjects" element={<ProtectedRoute><SubjectSelectionPage /></ProtectedRoute>} />
          <Route path="/subjects/:subjectId/topics" element={<ProtectedRoute><TopicSelectionPage /></ProtectedRoute>} />
          <Route path="/topics/:topicId/quiz" element={<ProtectedRoute><QuizPage /></ProtectedRoute>} />
          <Route path="/attempts/:attemptId/results" element={<ProtectedRoute><ResultsPage /></ProtectedRoute>} />
          <Route path="/admin" element={<ProtectedRoute><AdminRoute><AdminDashboardPage /></AdminRoute></ProtectedRoute>} />
          <Route path="/admin/questions" element={<ProtectedRoute><AdminRoute><QuestionListPage /></AdminRoute></ProtectedRoute>} />

          <Route path="*" element={<NotFoundPage />} />
        </Routes>
      </main>
    </div>
  );
}

export default App;
