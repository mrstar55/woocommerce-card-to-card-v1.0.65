import React from 'react';
import { 
  TrendingUp, 
  Zap, 
  CreditCard, 
  Clock, 
  Smartphone, 
  PlusCircle, 
  CheckCircle, 
  ExternalLink,
  ShieldCheck,
  Eye
} from 'lucide-react';
import { BankCard, Order, Device } from '../../types';

interface DashboardViewProps {
  cards: BankCard[];
  orders: Order[];
  devices: Device[];
  onOpenTestWebhook: () => void;
  onOpenAddCard: () => void;
  onSelectTab: (tab: string) => void;
  onInspectOrder: (order: Order) => void;
  onApproveOrder: (orderId: string) => void;
}

export const DashboardView: React.FC<DashboardViewProps> = ({
  cards,
  orders,
  devices,
  onOpenTestWebhook,
  onOpenAddCard,
  onSelectTab,
  onInspectOrder,
  onApproveOrder,
}) => {
  const activeCards = cards.filter(c => c.isActive);
  const pendingOrders = orders.filter(o => o.status === 'pending_review');
  const completedOrders = orders.filter(o => o.status === 'completed');
  
  // Calculate total today volume
  const todayVolume = completedOrders.reduce((sum, o) => sum + o.totalAmount, 0) + 18500000;
  const totalLimit = activeCards.reduce((sum, c) => sum + c.dailyLimit, 0);

  return (
    <div className="space-y-6">
      {/* Hero Banner */}
      <div className="relative overflow-hidden p-6 rounded-2xl bg-gradient-to-r from-white via-slate-50 to-sky-50 dark:from-[#111c44] dark:via-[#162456] dark:to-[#1a2d6b] border border-slate-200 dark:border-slate-800 shadow-xs">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div className="flex items-center gap-4">
            <div className="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#006194] to-sky-500 text-white flex items-center justify-center shadow-lg shadow-sky-500/30">
              <Zap className="w-7 h-7" />
            </div>
            <div>
              <h2 className="text-lg font-extrabold text-slate-900 dark:text-white">
                مرکز مانیتورینگ هوشمند کارت‌به‌کارت
              </h2>
              <div className="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-1">
                <span className="flex items-center gap-1.5">
                  موتور پنی‌انجین: <strong className="text-emerald-600 dark:text-emerald-400">فعال و پایدار</strong>
                </span>
                <span>•</span>
                <span className="flex items-center gap-1.5">
                  ذخیره‌ساز: <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">HPOS سازگار</span>
                </span>
                <span>•</span>
                <span>تطبیق خودکار لحظه‌ای</span>
              </div>
            </div>
          </div>

          <div className="flex items-center gap-2.5">
            <button
              type="button"
              onClick={onOpenTestWebhook}
              className="flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-bold transition-all shadow-xs"
            >
              <Smartphone className="w-4 h-4 text-sky-500" />
              <span>تست وب‌هوک پیامک</span>
            </button>
            <button
              type="button"
              onClick={onOpenAddCard}
              className="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#006194] hover:bg-[#004b73] text-white text-xs font-bold transition-all shadow-sm shadow-[#006194]/20"
            >
              <PlusCircle className="w-4 h-4" />
              <span>افزودن کارت بانکی</span>
            </button>
          </div>
        </div>
      </div>

      {/* 4 KPI Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {/* KPI 1: Today Volume */}
        <div className="p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs">
          <div className="flex items-center justify-between mb-3">
            <span className="text-xs font-bold text-slate-500 dark:text-slate-400">گردش مالی امروز</span>
            <div className="w-9 h-9 rounded-xl bg-[#006194]/10 text-[#006194] dark:text-sky-400 flex items-center justify-center">
              <TrendingUp className="w-5 h-5" />
            </div>
          </div>
          <div className="flex items-baseline gap-1.5 mb-2">
            <span className="text-2xl font-black font-mono text-slate-900 dark:text-white">
              {todayVolume.toLocaleString('fa-IR')}
            </span>
            <span className="text-xs text-slate-500 dark:text-slate-400 font-medium">تومان</span>
          </div>
          <div className="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
            <TrendingUp className="w-3.5 h-3.5" />
            <span>۱۸٪ افزایش نسبت به دیروز</span>
          </div>
        </div>

        {/* KPI 2: Penny Auto Match */}
        <div className="p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs">
          <div className="flex items-center justify-between mb-3">
            <span className="text-xs font-bold text-slate-500 dark:text-slate-400">تطبیق خودکار پنی‌انجین</span>
            <div className="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
              <Zap className="w-5 h-5" />
            </div>
          </div>
          <div className="flex items-baseline gap-1.5 mb-2">
            <span className="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400">
              ۹۸.۴٪
            </span>
            <span className="text-xs text-slate-500 dark:text-slate-400 font-medium">(۴۶ سفارش)</span>
          </div>
          <div className="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
            <span className="w-2 h-2 rounded-full bg-emerald-500 pulse-dot-green"></span>
            <span>بدون دخالت دست و تایید در لحظه</span>
          </div>
        </div>

        {/* KPI 3: Active Bank Cards */}
        <div className="p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs">
          <div className="flex items-center justify-between mb-3">
            <span className="text-xs font-bold text-slate-500 dark:text-slate-400">حساب‌های شتاب فعال</span>
            <div className="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
              <CreditCard className="w-5 h-5" />
            </div>
          </div>
          <div className="flex items-baseline gap-1.5 mb-2">
            <span className="text-2xl font-black font-mono text-slate-900 dark:text-white">
              {activeCards.length}
            </span>
            <span className="text-xs text-slate-500 dark:text-slate-400 font-medium">از {cards.length} کارت</span>
          </div>
          <div className="text-xs text-slate-500 dark:text-slate-400 font-mono">
            سقف مجاز: {(totalLimit / 1000000).toLocaleString('fa-IR')} میلیون تومان
          </div>
        </div>

        {/* KPI 4: Pending Receipts */}
        <div className="p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs">
          <div className="flex items-center justify-between mb-3">
            <span className="text-xs font-bold text-slate-500 dark:text-slate-400">فیش‌های در انتظار بررسی</span>
            <div className="w-9 h-9 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center">
              <Clock className="w-5 h-5" />
            </div>
          </div>
          <div className="flex items-baseline gap-1.5 mb-2">
            <span className="text-2xl font-black font-mono text-purple-600 dark:text-purple-400">
              {pendingOrders.length}
            </span>
            <span className="text-xs text-slate-500 dark:text-slate-400 font-medium">سفارش</span>
          </div>
          <button
            type="button"
            onClick={() => onSelectTab('orders')}
            className="flex items-center gap-1 text-xs text-[#006194] dark:text-sky-400 font-bold hover:underline"
          >
            <span>مشاهده و تایید فیش‌ها</span>
            <ExternalLink className="w-3 h-3" />
          </button>
        </div>
      </div>

      {/* 2 Column Section: Metallic Card Carousel + Recent Orders */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left Column: Metallic Cards Showcase */}
        <div className="lg:col-span-1 space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="font-extrabold text-sm text-slate-900 dark:text-white flex items-center gap-2">
              <CreditCard className="w-4 h-4 text-[#006194]" />
              <span>کارت‌های مقصد فعال</span>
            </h3>
            <button
              type="button"
              onClick={() => onSelectTab('cards')}
              className="text-xs text-[#006194] dark:text-sky-400 font-bold hover:underline"
            >
              مدیریت کارت‌ها
            </button>
          </div>

          <div className="space-y-3.5">
            {activeCards.slice(0, 2).map((card) => {
              const formattedCard = card.cardNumber.replace(/(\d{4})/g, '$1 ').trim();
              return (
                <div
                  key={card.id}
                  className={`p-4 rounded-2xl bg-gradient-to-br ${card.colorGradient || 'from-slate-800 to-slate-950'} text-white shadow-md relative overflow-hidden border border-white/10`}
                >
                  <div className="flex items-start justify-between mb-4">
                    <div>
                      <div className="text-xs font-medium text-slate-300">{card.bankName}</div>
                      <div className="text-sm font-extrabold text-white mt-0.5">{card.cardHolder}</div>
                    </div>
                    <div className="w-7 h-6 rounded bg-amber-400/80 border border-amber-300 shadow-xs flex items-center justify-center">
                      <div className="w-4 h-3 border border-amber-600/50 rounded-xs"></div>
                    </div>
                  </div>

                  <div className="text-center font-mono font-bold tracking-widest text-base sm:text-lg mb-4 text-slate-100">
                    {formattedCard}
                  </div>

                  <div className="flex items-center justify-between text-[11px] pt-2 border-t border-white/15">
                    <div>
                      <span className="text-slate-400 text-[10px]">گردش امروز:</span>{' '}
                      <span className="font-mono font-bold">{(card.currentDailyTotal / 1000).toLocaleString('fa-IR')} ه.ت</span>
                    </div>
                    <div className="font-mono text-slate-300">
                      IR{card.iban.slice(0, 4)}...{card.iban.slice(-4)}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>

          {/* Quick Node Status */}
          <div className="p-4 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs">
            <div className="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
              <span className="flex items-center gap-1.5">
                <Smartphone className="w-4 h-4 text-sky-500" />
                <span>گره‌های همگام‌ساز پیامک</span>
              </span>
              <span className="text-emerald-600 dark:text-emerald-400 font-mono text-[11px]">
                {devices.filter(d => d.status === 'online').length} آنلاین
              </span>
            </div>
            <div className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
              دستگاه‌های متصل پیامک‌های تراکنش شتابی را بی‌درنگ رمزگشایی و با فاکتورهای منتظر تطبیق می‌دهند.
            </div>
          </div>
        </div>

        {/* Right Column: Recent Orders Table */}
        <div className="lg:col-span-2 space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="font-extrabold text-sm text-slate-900 dark:text-white flex items-center gap-2">
              <Clock className="w-4 h-4 text-[#006194]" />
              <span>آخرین تراکنش‌ها و سفارشات</span>
            </h3>
            <button
              type="button"
              onClick={() => onSelectTab('orders')}
              className="text-xs text-[#006194] dark:text-sky-400 font-bold hover:underline"
            >
              مشاهده همه سفارشات
            </button>
          </div>

          <div className="overflow-hidden rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs">
            <div className="overflow-x-auto">
              <table className="w-full text-right text-xs">
                <thead>
                  <tr className="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                    <th className="py-3 px-4 font-bold">سفارش</th>
                    <th className="py-3 px-4 font-bold">مشتری</th>
                    <th className="py-3 px-4 font-bold">بانک مقصد</th>
                    <th className="py-3 px-4 font-bold">مبلغ کل (تومان)</th>
                    <th className="py-3 px-4 font-bold">وضعیت</th>
                    <th className="py-3 px-4 font-bold text-center">عملیات</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                  {orders.slice(0, 5).map((order) => {
                    const isPending = order.status === 'pending_review';
                    const isCompleted = order.status === 'completed';
                    const isRejected = order.status === 'rejected';

                    return (
                      <tr key={order.id} className="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                        <td className="py-3.5 px-4 font-mono font-bold text-[#006194] dark:text-sky-400">
                          #{order.orderNumber}
                        </td>
                        <td className="py-3.5 px-4">
                          <div className="font-bold text-slate-900 dark:text-white">{order.customerName}</div>
                          <div className="text-[11px] font-mono text-slate-400">{order.customerPhone}</div>
                        </td>
                        <td className="py-3.5 px-4">
                          <span className="font-medium">{order.destBankName}</span>
                        </td>
                        <td className="py-3.5 px-4">
                          <div className="font-mono font-bold text-slate-900 dark:text-white">
                            {order.totalAmount.toLocaleString('fa-IR')}
                          </div>
                          {order.pennyOffset > 0 && (
                            <div className="text-[10px] text-sky-600 dark:text-sky-400 font-mono">
                              +{order.pennyOffset} پنی
                            </div>
                          )}
                        </td>
                        <td className="py-3.5 px-4">
                          {isPending && (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                              <span className="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                              <span>بررسی فیش</span>
                            </span>
                          )}
                          {isCompleted && (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                              <CheckCircle className="w-3 h-3 text-emerald-600" />
                              <span>پرداخت شده</span>
                            </span>
                          )}
                          {isRejected && (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                              <span>رد شده</span>
                            </span>
                          )}
                        </td>
                        <td className="py-3.5 px-4 text-center">
                          <div className="flex items-center justify-center gap-1.5">
                            <button
                              type="button"
                              onClick={() => onInspectOrder(order)}
                              className="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors"
                              title="بررسی رسید"
                            >
                              <Eye className="w-3.5 h-3.5" />
                            </button>
                            {isPending && (
                              <button
                                type="button"
                                onClick={() => onApproveOrder(order.id)}
                                className="px-2 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition-colors"
                              >
                                تایید
                              </button>
                            )}
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
