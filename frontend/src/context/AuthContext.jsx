import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import api from '../api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [menu, setMenu] = useState([]);
  const [privileges, setPrivileges] = useState([]);
  const [loading, setLoading] = useState(true);

  const fetchUser = useCallback(async () => {
    try {
      const { data } = await api.get('/me');
      setUser(data.user);
      setMenu(data.menu);
      setPrivileges(data.privileges);
    } catch {
      localStorage.removeItem('token');
      setUser(null);
      setMenu([]);
      setPrivileges([]);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (token) {
      fetchUser();
    } else {
      setLoading(false);
    }
  }, [fetchUser]);

  const login = async (cnic_no, password) => {
    const { data } = await api.post('/login', { cnic_no, password });
    localStorage.setItem('token', data.access_token);
    setUser(data.user);
    await fetchUser();
    return data;
  };

  const logout = async () => {
    try {
      await api.post('/logout');
    } catch { /* ignore */ }
    localStorage.removeItem('token');
    setUser(null);
    setMenu([]);
    setPrivileges([]);
  };

  const hasPrv = (slug) => privileges.includes(slug);

  return (
    <AuthContext.Provider value={{ user, menu, privileges, loading, login, logout, hasPrv, fetchUser }}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => useContext(AuthContext);
