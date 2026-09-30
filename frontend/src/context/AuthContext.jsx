import { createContext, useContext, useMemo, useState } from 'react';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(() => JSON.parse(localStorage.getItem('studyflow_user') || 'null'));
  const [token, setToken] = useState(() => localStorage.getItem('studyflow_token') || '');

  const login = (userData, authToken) => {
    setUser(userData);
    setToken(authToken);
    localStorage.setItem('studyflow_user', JSON.stringify(userData));
    localStorage.setItem('studyflow_token', authToken);
  };

  const logout = () => {
    setUser(null);
    setToken('');
    localStorage.removeItem('studyflow_user');
    localStorage.removeItem('studyflow_token');
  };

  const value = useMemo(() => ({ user, token, login, logout }), [user, token]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);

  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }

  return context;
}
