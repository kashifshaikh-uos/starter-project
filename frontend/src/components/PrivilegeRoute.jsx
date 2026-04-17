import { Navigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function PrivilegeRoute({ slug, children }) {
  const { hasPrv } = useAuth();

  if (!hasPrv(slug)) {
    return (
      <div className="flex flex-col items-center justify-center py-20">
        <div className="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
          <svg className="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h2 className="text-xl font-bold text-gray-900 mb-1">Access Denied</h2>
        <p className="text-sm text-gray-500">You do not have permission to access this page.</p>
      </div>
    );
  }

  return children;
}
