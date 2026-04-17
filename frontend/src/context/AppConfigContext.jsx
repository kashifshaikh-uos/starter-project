import { createContext, useContext, useState, useEffect } from 'react';
import api from '../api';

const AppConfigContext = createContext({
  name: 'Admin Panel',
  short_name: 'AP',
  icon: 'shield',
  primary_color: '#4f46e5',
});

export function AppConfigProvider({ children }) {
  const [config, setConfig] = useState({
    name: 'Admin Panel',
    short_name: 'AP',
    icon: 'shield',
    primary_color: '#4f46e5',
  });

  useEffect(() => {
    api.get('/app-config').then(({ data }) => {
      setConfig(data);
      document.title = data.name || 'Admin Panel';
    }).catch(() => {});
  }, []);

  return (
    <AppConfigContext.Provider value={config}>
      {children}
    </AppConfigContext.Provider>
  );
}

export function useAppConfig() {
  return useContext(AppConfigContext);
}
