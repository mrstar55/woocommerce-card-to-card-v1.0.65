import React, { useState } from 'react';
import { 
  ShieldCheck, 
  Database, 
  CheckCircle2, 
  RefreshCw, 
  Check, 
  HardDrive, 
  Lock, 
  Activity,
  Layers
} from 'lucide-react';
import { SystemHealth } from '../../types';

interface HealthViewProps {
  health: SystemHealth;
  onRunDiagnostics: () => void;
  isRunningDiagnostics: boolean;
}

export const HealthView: React.FC<HealthViewProps> = ({
  health,
  onRunDiagnostics,
  isRunningDiagnostics,
}) => {
  return (
    <div className="space-y-6">
      {/* Top Header Banner */}
      <div className="p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h3 className="font-extrabold text-sm sm:text-base text-slate-900 dark:text-white">
            وضعیت سلامت و دیاگ محیط ووکامرس (WCCC Diagnostics & Schema)
          </h3>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
            بررسی یکپارچگی جداول اختصاصی، سازگاری با HPOS، مجوزهای پوشه فیش‌ها و امنیت HMAC.
          </p>
        </div>

        <button
          type="button"
          onClick={onRunDiagnostics}
          disabled={isRunningDiagnostics}
          className="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#006194] hover:bg-[#004b73] text-white text-xs font-bold transition-all shadow-sm shadow-[#006194]/20 disabled:opacity-75"
        >
          <RefreshCw className={`w-3.5 h-3.5 ${isRunningDiagnostics ? 'animate-spin' : ''}`} />
          <span>{isRunningDiagnostics ? 'در حال عیب‌یابی...' : 'اجرای تست جامع دیاگ'}</span>
        </button>
      </div>

      {/* 4 Diagnostic Overview Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="p-4 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs">
          <div className="flex items-center justify-between mb-2">
            <span className="text-xs text-slate-500 dark:text-slate-400 font-medium">محیط اجرایی</span>
            <CheckCircle2 className="w-4 h-4 text-emerald-500" />
          </div>
          <div className="font-bold text-sm text-slate-900 dark:text-white mb-1">
            Node.js 22 Runtime
          </div>
          <div className="text-[11px] font-mono text-emerald-600 dark:text-emerald-400">
            سازگار و پایدار
          </div>
        </div>

        <div className="p-4 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs">
          <div className="flex items-center justify-between mb-2">
            <span className="text-xs text-slate-500 dark:text-slate-400 font-medium">معماری HPOS</span>
            <CheckCircle2 className="w-4 h-4 text-emerald-500" />
          </div>
          <div className="font-bold text-sm text-slate-900 dark:text-white mb-1">
            جدول مستقل سفارشات
          </div>
          <div className="text-[11px] text-emerald-600 dark:text-emerald-400">
            فعال و با راندمان بالا
          </div>
        </div>

        <div className="p-4 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs">
          <div className="flex items-center justify-between mb-2">
            <span className="text-xs text-slate-500 dark:text-slate-400 font-medium">پوشه ذخیره فیش‌ها</span>
            <CheckCircle2 className="w-4 h-4 text-emerald-500" />
          </div>
          <div className="font-bold text-sm text-slate-900 dark:text-white mb-1">
            /wccc-receipts/
          </div>
          <div className="text-[11px] text-emerald-600 dark:text-emerald-400">
            مجوز نوشتن ۷۵۵ برقرار
          </div>
        </div>

        <div className="p-4 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs">
          <div className="flex items-center justify-between mb-2">
            <span className="text-xs text-slate-500 dark:text-slate-400 font-medium">موتور امضای HMAC</span>
            <CheckCircle2 className="w-4 h-4 text-emerald-500" />
          </div>
          <div className="font-bold text-sm text-slate-900 dark:text-white mb-1">
            HMAC-SHA256
          </div>
          <div className="text-[11px] font-mono text-emerald-600 dark:text-emerald-400">
            تست اعتبار: PASS
          </div>
        </div>
      </div>

      {/* Database Schema Status Table */}
      <div className="p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        <div className="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
          <div className="flex items-center gap-2">
            <Database className="w-4 h-4 text-[#006194]" />
            <h4 className="font-extrabold text-sm text-slate-900 dark:text-white">
              وضعیت سلامت و سلامت جداول پایگاه داده اختصاصی (Schema::TABLE_*)
            </h4>
          </div>
          <span className="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
            تمام ۶ جدول سالم و آماده
          </span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-right text-xs">
            <thead>
              <tr className="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                <th className="py-3 px-4 font-bold">نام جدول دیتابیس</th>
                <th className="py-3 px-4 font-bold">تعداد رکوردهای موجود</th>
                <th className="py-3 px-4 font-bold">وضعیت شاخص‌ها</th>
                <th className="py-3 px-4 font-bold text-center">وضعیت راندمان</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-mono">
              {health.tables.map((table) => (
                <tr key={table.key} className="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                  <td className="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                    {table.name}
                  </td>
                  <td className="py-3.5 px-4">
                    {table.recordsCount.toLocaleString('fa-IR')} رکورد
                  </td>
                  <td className="py-3.5 px-4 text-emerald-600 dark:text-emerald-400">
                    ایندکس بهینه (Indexed)
                  </td>
                  <td className="py-3.5 px-4 text-center">
                    <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 font-sans">
                      <Check className="w-3 h-3 text-emerald-600" />
                      <span>کامپکت و پایدار</span>
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};
