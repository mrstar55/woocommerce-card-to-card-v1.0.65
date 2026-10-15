import React from 'react';
import { CheckCircle2, AlertTriangle, Info, XCircle, X } from 'lucide-react';

export interface ToastMessage {
  id: string;
  type: 'success' | 'warning' | 'error' | 'info';
  title?: string;
  message: string;
}

interface ToastProps {
  toasts: ToastMessage[];
  onDismiss: (id: string) => void;
}

export const Toast: React.FC<ToastProps> = ({ toasts, onDismiss }) => {
  if (toasts.length === 0) return null;

  return (
    <div className="fixed top-4 left-4 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none">
      {toasts.map((toast) => {
        const isSuccess = toast.type === 'success';
        const isWarning = toast.type === 'warning';
        const isError = toast.type === 'error';

        return (
          <div
            key={toast.id}
            className="pointer-events-auto flex items-start gap-3 p-3.5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xl transition-all animate-in slide-in-from-top-2"
          >
            <div className="mt-0.5">
              {isSuccess && <CheckCircle2 className="w-5 h-5 text-emerald-500" />}
              {isWarning && <AlertTriangle className="w-5 h-5 text-amber-500" />}
              {isError && <XCircle className="w-5 h-5 text-rose-500" />}
              {!isSuccess && !isWarning && !isError && <Info className="w-5 h-5 text-sky-500" />}
            </div>

            <div className="flex-1 text-right">
              {toast.title && (
                <div className="font-extrabold text-xs text-slate-900 dark:text-white mb-0.5">
                  {toast.title}
                </div>
              )}
              <div className="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                {toast.message}
              </div>
            </div>

            <button
              type="button"
              onClick={() => onDismiss(toast.id)}
              className="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white"
            >
              <X className="w-4 h-4" />
            </button>
          </div>
        );
      })}
    </div>
  );
};
