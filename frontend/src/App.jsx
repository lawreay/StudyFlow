import { NavLink, Navigate, Route, Routes } from 'react-router-dom';
import { useAuth } from './context/useAuth';
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
import QuestionFormPage from './pages/QuestionFormPage';
import QuestionImportPage from './pages/QuestionImportPage';
import TopicSelectionPage from './pages/TopicSelectionPage';
import GameWorldPage from './pages/GameWorldPage';
import LearningCompanion from './components/LearningCompanion';
import LessonPage from './pages/LessonPage';
import CertificatesPage from './pages/CertificatesPage';
import VerifyCertificatePage from './pages/VerifyCertificatePage';
import ProfilePage from './pages/ProfilePage';
import AdminTopicsPage from './pages/AdminTopicsPage';
import AdminTopicLessonPage from './pages/AdminTopicLessonPage';
import ProjectChallengesPage from './pages/ProjectChallengesPage';
import ProjectChallengePage from './pages/ProjectChallengePage';
import AdminProjectChallengesPage from './pages/AdminProjectChallengesPage';
import AdminProjectSubmissionsPage from './pages/AdminProjectSubmissionsPage';
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
              <NavLink to="/world">Adventure</NavLink>
              <NavLink to="/projects">Projects</NavLink>
              <NavLink to="/certificates">Certificates</NavLink>
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
            <NavLink to="/profile" className="profile-link">{user?.name || 'Player'}</NavLink>
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
          <Route path="/verify/:certificateNumber" element={<VerifyCertificatePage />} />

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
          <Route path="/world" element={<ProtectedRoute><GameWorldPage /></ProtectedRoute>} />
          <Route path="/projects" element={<ProtectedRoute><ProjectChallengesPage /></ProtectedRoute>} />
          <Route path="/projects/:challengeId" element={<ProtectedRoute><ProjectChallengePage /></ProtectedRoute>} />
          <Route path="/certificates" element={<ProtectedRoute><CertificatesPage /></ProtectedRoute>} />
          <Route path="/profile" element={<ProtectedRoute><ProfilePage /></ProtectedRoute>} />
          <Route path="/topics/:topicId/lesson" element={<ProtectedRoute><LessonPage /></ProtectedRoute>} />
          <Route path="/topics/:topicId/quiz" element={<ProtectedRoute><QuizPage /></ProtectedRoute>} />
          <Route path="/attempts/:attemptId/results" element={<ProtectedRoute><ResultsPage /></ProtectedRoute>} />
          <Route path="/admin" element={<ProtectedRoute><AdminRoute><AdminDashboardPage /></AdminRoute></ProtectedRoute>} />
          <Route path="/admin/questions" element={<ProtectedRoute><AdminRoute><QuestionListPage /></AdminRoute></ProtectedRoute>} />
          <Route path="/admin/topics" element={<ProtectedRoute><AdminRoute><AdminTopicsPage /></AdminRoute></ProtectedRoute>} />
          <Route path="/admin/topics/:topicId/edit" element={<ProtectedRoute><AdminRoute><AdminTopicLessonPage /></AdminRoute></ProtectedRoute>} />
          <Route path="/admin/projects" element={<ProtectedRoute><AdminRoute><AdminProjectChallengesPage /></AdminRoute></ProtectedRoute>} />
          <Route path="/admin/projects/:challengeId/submissions" element={<ProtectedRoute><AdminRoute><AdminProjectSubmissionsPage /></AdminRoute></ProtectedRoute>} />
          <Route path="/admin/questions/new" element={<ProtectedRoute><AdminRoute><QuestionFormPage /></AdminRoute></ProtectedRoute>} />
          <Route path="/admin/questions/:questionId/edit" element={<ProtectedRoute><AdminRoute><QuestionFormPage /></AdminRoute></ProtectedRoute>} />
          <Route path="/admin/import" element={<ProtectedRoute><AdminRoute><QuestionImportPage /></AdminRoute></ProtectedRoute>} />

          <Route path="*" element={<NotFoundPage />} />
        </Routes>
      </main>
      {token && !user?.is_admin && <LearningCompanion />}
    </div>
  );
}

export default App;
