import React, { useState } from 'react';
import { 
  History, 
  Search, 
  Trash2, 
  Download, 
  CheckCircle, 
  AlertTriangle, 
  Info, 
  XCircle,
  FileCode
} from 'lucide-react';
import { LogEntry } from '../../types';

interface LogsViewProps {
  logs: LogEntry[];
  onClearLogs: () => void;
}

export const LogsView: React.FC<LogsViewProps> = ({ logs, onClearLogs }) => {
  const [levelFilter, setLevelFilter] = useState<string>('all');
  const [searchTerm, setSearchTerm] = useState<string>('');

  const filteredLogs = logs.filter((log) => {
    if (levelFilter !== 'all' && log.level !== levelFilter) return false;
    if (searchTerm.trim()) {
      const term = searchTerm.toLowerCase();
      const matchMsg = log.message.toLowerCase().includes(term);
      const matchSource = log.source.toLowerCase().includes(term);
      const matchMeta = log.metadata?.toLowerCase().includes(term);
      return matchMsg || matchSource || matchMeta;
    }
    return true;
  });

  const handleExportJson = () => {
    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(logs, null, 2));
    const downloadAnchor = document.createElement('a');
    downloadAnchor.setAttribute("href", dataStr);
    downloadAnchor.setAttribute("download", `wccc-audit-logs-${new Date().toISOString().slice(0, 10)}.json`);
    document.body.appendChild(downloadAnchor);
    downloadAnchor.click();
    downloadAnchor.remove();
  };

  return (
    <div className="space-y-5">
      {/* Filters & Action Bar */}
      <div className="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        {/* Level Filters */}
        <div className="flex items-center gap-2 flex-wrap">
          <button
            type="button"
            onClick={() => setLevelFilter('all')}
            className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
              levelFilter === 'all'
                ? 'bg-[#006194] text-white shadow-xs'
                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'
            }`}
          >
            همه لاگ‌ها ({logs.length})
          </button>
          <button
            type="button"
            onClick={() => setLevelFilter('SUCCESS')}
            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
              levelFilter === 'SUCCESS'
                ? 'bg-emerald-600 text-white shadow-xs'
                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'
            }`}
          >
            <CheckCircle className="w-3.5 h-3.5 text-emerald-500" />
            <span>موفقیت ({logs.filter(l => l.level === 'SUCCESS').length})</span>
          </button>
          <button
            type="button"
            onClick={() => setLevelFilter('INFO')}
            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
              levelFilter === 'INFO'
                ? 'bg-sky-600 text-white shadow-xs'
                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'
            }`}
          >
            <Info className="w-3.5 h-3.5 text-sky-500" />
            <span>اطلاعات ({logs.filter(l => l.level === 'INFO').length})</span>
          </button>
          <button
            type="button"
            onClick={() => setLevelFilter('WARNING')}
            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
              levelFilter === 'WARNING'
                ? 'bg-amber-600 text-white shadow-xs'
                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'
            }`}
          >
            <AlertTriangle className="w-3.5 h-3.5 text-amber-500" />
            <span>هشدار ({logs.filter(l => l.level === 'WARNING').length})</span>
          </button>
          <button
            type="button"
            onClick={() => setLevelFilter('ERROR')}
            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
              levelFilter === 'ERROR'
                ? 'bg-rose-600 text-white shadow-xs'
                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'
            }`}
          >
            <XCircle className="w-3.5 h-3.5 text-rose-500" />
            <span>خطا ({logs.filter(l => l.level === 'ERROR').length})</span>
          </button>
        </div>

        {/* Search & Actions */}
        <div className="flex items-center gap-2.5">
          <div className="relative">
            <input
              type="text"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              placeholder="جستجو در متن لاگ..."
              className="pr-8 pl-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs w-48 sm:w-60 focus:outline-hidden"
            />
            <Search className="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-2 pointer-events-none" />
          </div>

          <button
            type="button"
            onClick={handleExportJson}
            className="p-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300"
            title="خروجی JSON"
          >
            <Download className="w-4 h-4" />
          </button>

          <button
            type="button"
            onClick={onClearLogs}
            className="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 text-xs font-bold hover:bg-rose-100 transition-colors"
          >
            <Trash2 className="w-3.5 h-3.5" />
            <span>پاکسازی لاگ‌ها</span>
          </button>
        </div>
      </div>

      {/* Logs List Card */}
      <div className="overflow-hidden rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs">
        <div className="divide-y divide-slate-100 dark:divide-slate-800">
          {filteredLogs.length === 0 ? (
            <div className="p-12 text-center text-slate-400 text-xs">
              هیچ رویدادی مطابق با فیلتر انتخابی موجود نیست.
            </div>
          ) : (
            filteredLogs.map((log) => {
              const isSuccess = log.level === 'SUCCESS';
              const isWarning = log.level === 'WARNING';
              const isError = log.level === 'ERROR';

              return (
                <div key={log.id} className="p-4 hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors flex items-start justify-between gap-4">
                  <div className="flex items-start gap-3">
                    <div className="mt-0.5">
                      {isSuccess && <CheckCircle className="w-4 h-4 text-emerald-500" />}
                      {isWarning && <AlertTriangle className="w-4 h-4 text-amber-500" />}
                      {isError && <XCircle className="w-4 h-4 text-rose-500" />}
                      {!isSuccess && !isWarning && !isError && <Info className="w-4 h-4 text-sky-500" />}
                    </div>

                    <div>
                      <div className="flex items-center gap-2 mb-1">
                        <span className="font-mono text-[10px] font-bold text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">
                          {log.timestamp}
                        </span>
                        <span className="text-[10px] font-mono font-bold text-[#006194] dark:text-sky-400 bg-sky-50 dark:bg-sky-950/60 px-2 py-0.5 rounded">
                          {log.source}
                        </span>
                      </div>
                      <div className="text-xs font-bold text-slate-900 dark:text-white leading-relaxed">
                        {log.message}
                      </div>
                      {log.metadata && (
                        <div className="text-[11px] font-mono text-slate-500 dark:text-slate-400 mt-1">
                          {log.metadata}
                        </div>
                      )}
                    </div>
                  </div>

                  <span className={`px-2 py-0.5 rounded-full text-[10px] font-mono font-bold ${
                    isSuccess ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' :
                    isWarning ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' :
                    isError ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' :
                    'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
                  }`}>
                    {log.level}
                  </span>
                </div>
              );
            })
          )}
        </div>
      </div>
    </div>
  );
};
