import React from 'react';
import { 
  LayoutDashboard, 
  ClipboardList, 
  CreditCard, 
  Smartphone, 
  Settings as SettingsIcon, 
  History, 
  ShieldCheck, 
  ShoppingBag,
  CheckCircle2,
  X
} from 'lucide-react';

interface SidebarProps {
  currentTab: string;
  setCurrentTab: (tab: string) => void;
  activeViewMode: 'admin' | 'checkout';
  setActiveViewMode: (mode: 'admin' | 'checkout') => void;
  pendingOrdersCount: number;
  isOpen: boolean;
  onClose: () => void;
}

export const Sidebar: React.FC<SidebarProps> = ({
  currentTab,
  setCurrentTab,
  activeViewMode,
  setActiveViewMode,
  pendingOrdersCount,
  isOpen,
  onClose,
}) => {
  const navItems = [
    { id: 'dashboard', label: 'داشبورد مانیتورینگ', icon: LayoutDashboard },
    { id: 'orders', label: 'سفارشات و رسیدها', icon: ClipboardList, badge: pendingOrdersCount },
    { id: 'cards', label: 'حساب‌ها و کارت‌ها', icon: CreditCard },
    { id: 'devices', label: 'دستگاه‌های اندروید', icon: Smartphone },
    { id: 'settings', label: 'تنظیمات درگاه', icon: SettingsIcon },
    { id: 'logs', label: 'لاگ‌ها و رویدادها', icon: History },
    { id: 'health', label: 'وضعیت سلامت سیستم', icon: ShieldCheck },
  ];

  const handleSelectTab = (tabId: string) => {
    setCurrentTab(tabId);
    setActiveViewMode('admin');
    if (window.innerWidth < 1024) {
      onClose();
    }
  };

  const handleSelectCheckout = () => {
    setActiveViewMode('checkout');
    if (window.innerWidth < 1024) {
      onClose();
    }
  };

  return (
    <>
      {/* Mobile Backdrop */}
      {isOpen && (
        <div 
          onClick={onClose}
          className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 lg:hidden"
        />
      )}

      <aside className={`
        fixed lg:static top-0 bottom-0 right-0 z-50
        w-64 bg-[#1e1e2d] dark:bg-[#0d1633] text-slate-300
        flex flex-col justify-between border-l border-slate-800
        transition-transform duration-300 ease-in-out
        ${isOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'}
      `}>
        <div className="flex flex-col flex-1 overflow-y-auto">
          {/* Brand Header */}
          <div className="flex items-center justify-between p-4 border-b border-slate-800/80 bg-[#171723] dark:bg-[#091026]">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#006194] to-sky-500 flex items-center justify-center text-white shadow-md shadow-sky-500/20">
                <CreditCard className="w-5 h-5" />
              </div>
              <div>
                <div className="font-extrabold text-sm text-white tracking-wide">کارت به کارت هوشمند</div>
                <div className="text-[11px] font-mono text-sky-400">WCCC v1.0.65</div>
              </div>
            </div>

            <button 
              type="button"
              onClick={onClose}
              className="lg:hidden text-slate-400 hover:text-white p-1"
            >
              <X className="w-5 h-5" />
            </button>
          </div>

          {/* Online Banking Telemetry Status Box */}
          <div className="p-3 m-3 rounded-xl bg-slate-800/50 dark:bg-slate-900/60 border border-slate-700/60 flex items-center justify-between">
            <div className="flex items-center gap-2">
              <span className="w-2.5 h-2.5 rounded-full bg-emerald-500 pulse-dot-green"></span>
              <span className="text-[11px] font-bold text-slate-100">شبکه بانکی برخط</span>
            </div>
            <span className="text-[10px] font-mono text-slate-400 bg-slate-700/40 px-1.5 py-0.5 rounded">
              پینگ ۲۴ms
            </span>
          </div>

          {/* Navigation Items */}
          <nav className="px-3 py-2 space-y-1">
            <div className="text-[10px] font-bold text-slate-400 px-3 py-1.5 uppercase tracking-wider">
              مدیریت ووکامرس
            </div>

            {navItems.map((item) => {
              const Icon = item.icon;
              const isActive = activeViewMode === 'admin' && currentTab === item.id;

              return (
                <button
                  key={item.id}
                  type="button"
                  onClick={() => handleSelectTab(item.id)}
                  className={`
                    w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-medium transition-all
                    ${isActive 
                      ? 'bg-[#006194] text-white shadow-md shadow-[#006194]/30 font-bold' 
                      : 'text-slate-300 hover:bg-slate-800/60 hover:text-white'}
                  `}
                >
                  <div className="flex items-center gap-3">
                    <Icon className={`w-4 h-4 ${isActive ? 'text-white' : 'text-slate-400'}`} />
                    <span>{item.label}</span>
                  </div>

                  {Boolean(item.badge && item.badge > 0) && (
                    <span className="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-slate-950 font-mono">
                      {item.badge}
                    </span>
                  )}
                </button>
              );
            })}

            <div className="pt-3 pb-1 text-[10px] font-bold text-slate-400 px-3 uppercase tracking-wider">
              شبیه‌ساز و تست
            </div>

            <button
              type="button"
              onClick={handleSelectCheckout}
              className={`
                w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-medium transition-all
                ${activeViewMode === 'checkout'
                  ? 'bg-gradient-to-r from-amber-600 to-amber-700 text-white shadow-md shadow-amber-600/30 font-bold'
                  : 'text-amber-300 hover:bg-slate-800/60 hover:text-amber-200'}
              `}
            >
              <div className="flex items-center gap-3">
                <ShoppingBag className="w-4 h-4 text-amber-400" />
                <span>شبیه‌ساز پرداخت خریدار</span>
              </div>
              <span className="text-[10px] bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded">
                تست
              </span>
            </button>
          </nav>
        </div>

        {/* Sidebar Footer */}
        <div className="p-3.5 border-t border-slate-800/80 bg-[#171723]/60 dark:bg-[#091026]/80 m-3 rounded-xl">
          <div className="flex items-center justify-between">
            <div>
              <div className="text-[11px] font-bold text-slate-200">نسخه سازمانی WCCC</div>
              <div className="text-[10px] text-slate-400">منطبق بر HPOS ووکامرس</div>
            </div>
            <CheckCircle2 className="w-5 h-5 text-sky-400" />
          </div>
        </div>
      </aside>
    </>
  );
};
