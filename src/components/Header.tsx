import React from 'react';
import { 
  Home, 
  Moon, 
  Sun, 
  Smartphone, 
  PlusCircle, 
  RefreshCw, 
  ShoppingBag, 
  LayoutDashboard,
  Menu
} from 'lucide-react';

interface HeaderProps {
  currentTab: string;
  activeViewMode: 'admin' | 'checkout';
  setActiveViewMode: (mode: 'admin' | 'checkout') => void;
  isDarkMode: boolean;
  toggleDarkMode: () => void;
  onOpenTestWebhook: () => void;
  onOpenAddCard: () => void;
  onRefreshShaparak: () => void;
  isRefreshing: boolean;
  onToggleSidebar: () => void;
}

const tabTitles: Record<string, string> = {
  dashboard: 'داشبورد مالی و مانیتورینگ',
  orders: 'مدیریت سفارشات و رسیدهای بانکی',
  cards: 'مدیریت کارت‌ها و حساب‌های مقصد شتاب',
  devices: 'دستگاه‌های همگام‌ساز پیامک اندروید',
  settings: 'تنظیمات درگاه و پنی‌انجین',
  logs: 'لاگ‌ها و رویدادهای زنده سیستم',
  health: 'وضعیت سلامت و پایگاه داده ووکامرس',
  checkout: 'شبیه‌ساز پرداخت کارت‌به‌کارت خریدار',
};

export const Header: React.FC<HeaderProps> = ({
  currentTab,
  activeViewMode,
  setActiveViewMode,
  isDarkMode,
  toggleDarkMode,
  onOpenTestWebhook,
  onOpenAddCard,
  onRefreshShaparak,
  isRefreshing,
  onToggleSidebar,
}) => {
  return (
    <header className="sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 py-3.5 bg-white dark:bg-[#111c44] border-b border-slate-200 dark:border-slate-800 transition-colors shadow-xs">
      {/* Breadcrumb and mobile toggle */}
      <div className="flex items-center gap-3">
        <button
          type="button"
          onClick={onToggleSidebar}
          className="lg:hidden p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          title="منوی ناوبری"
        >
          <Menu className="w-5 h-5" />
        </button>

        <div className="flex items-center gap-2 text-xs sm:text-sm">
          <div className="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
            <Home className="w-4 h-4 text-slate-400" />
            <span className="hidden sm:inline font-medium">سامانه کارت‌به‌کارت</span>
          </div>
          <span className="text-slate-300 dark:text-slate-600">/</span>
          <span className="font-bold text-[#006194] dark:text-sky-400">
            {activeViewMode === 'checkout' ? tabTitles.checkout : (tabTitles[currentTab] || 'پیشخوان')}
          </span>
        </div>
      </div>

      {/* Actions */}
      <div className="flex items-center gap-2 sm:gap-2.5">
        {/* Toggle between Admin Dashboard and Checkout Simulator */}
        <div className="flex items-center p-0.5 rounded-lg bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
          <button
            type="button"
            onClick={() => setActiveViewMode('admin')}
            className={`flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-bold transition-all ${
              activeViewMode === 'admin'
                ? 'bg-white dark:bg-[#006194] text-[#006194] dark:text-white shadow-xs'
                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
            }`}
          >
            <LayoutDashboard className="w-3.5 h-3.5" />
            <span className="hidden md:inline">پیشخوان مدیریت</span>
          </button>
          <button
            type="button"
            onClick={() => setActiveViewMode('checkout')}
            className={`flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-bold transition-all ${
              activeViewMode === 'checkout'
                ? 'bg-white dark:bg-[#006194] text-[#006194] dark:text-white shadow-xs'
                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
            }`}
          >
            <ShoppingBag className="w-3.5 h-3.5 text-amber-500" />
            <span className="hidden md:inline">پرداخت خریدار</span>
          </button>
        </div>

        {/* Dark / Light Toggle */}
        <button
          type="button"
          onClick={toggleDarkMode}
          className="p-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-xs"
          title={isDarkMode ? 'حالت روشن' : 'حالت تیره'}
        >
          {isDarkMode ? <Sun className="w-4 h-4 text-amber-400" /> : <Moon className="w-4 h-4 text-slate-600" />}
        </button>

        {/* Webhook Simulator Modal Trigger */}
        <button
          type="button"
          onClick={onOpenTestWebhook}
          className="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors text-xs font-bold shadow-xs"
        >
          <Smartphone className="w-3.5 h-3.5 text-sky-500" />
          <span>تست وب‌هوک</span>
        </button>

        {/* Add Card Modal Trigger */}
        <button
          type="button"
          onClick={onOpenAddCard}
          className="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors text-xs font-bold shadow-xs"
        >
          <PlusCircle className="w-3.5 h-3.5 text-emerald-500" />
          <span>افزودن کارت</span>
        </button>

        {/* Live Shaparak Sync Refresh */}
        <button
          type="button"
          onClick={onRefreshShaparak}
          disabled={isRefreshing}
          className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#006194] hover:bg-[#004b73] text-white text-xs font-bold transition-all shadow-xs disabled:opacity-75"
        >
          <RefreshCw className={`w-3.5 h-3.5 ${isRefreshing ? 'animate-spin' : ''}`} />
          <span className="hidden sm:inline">همگام‌سازی شاپرک</span>
        </button>
      </div>
    </header>
  );
};
