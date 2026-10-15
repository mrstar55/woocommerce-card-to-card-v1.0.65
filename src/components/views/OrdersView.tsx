import React, { useState } from 'react';
import { 
  Search, 
  FileText, 
  CheckCircle, 
  XCircle, 
  Eye, 
  Check, 
  X,
  CreditCard
} from 'lucide-react';
import { Order, OrderStatus } from '../../types';

interface OrdersViewProps {
  orders: Order[];
  onInspectOrder: (order: Order) => void;
  onApproveOrder: (orderId: string) => void;
  onRejectOrder: (orderId: string) => void;
  onOpenManualReceipt: () => void;
}

export const OrdersView: React.FC<OrdersViewProps> = ({
  orders,
  onInspectOrder,
  onApproveOrder,
  onRejectOrder,
  onOpenManualReceipt,
}) => {
  const [filterStatus, setFilterStatus] = useState<string>('all');
  const [searchTerm, setSearchTerm] = useState<string>('');

  const filteredOrders = orders.filter((order) => {
    // Status filter
    if (filterStatus === 'pending' && order.status !== 'pending_review') return false;
    if (filterStatus === 'matched' && order.status !== 'completed') return false;
    if (filterStatus === 'rejected' && order.status !== 'rejected') return false;

    // Search term
    if (searchTerm.trim()) {
      const term = searchTerm.toLowerCase();
      const matchNumber = order.orderNumber.toLowerCase().includes(term);
      const matchCustomer = order.customerName.toLowerCase().includes(term);
      const matchPhone = order.customerPhone.includes(term);
      const matchRrn = order.rrn.includes(term);
      return matchNumber || matchCustomer || matchPhone || matchRrn;
    }

    return true;
  });

  return (
    <div className="space-y-5">
      {/* Filters & Action Bar */}
      <div className="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        {/* Status Filter Pills */}
        <div className="flex items-center gap-2 flex-wrap">
          <button
            type="button"
            onClick={() => setFilterStatus('all')}
            className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
              filterStatus === 'all'
                ? 'bg-[#006194] text-white shadow-xs'
                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'
            }`}
          >
            همه وضعیت‌ها ({orders.length})
          </button>
          <button
            type="button"
            onClick={() => setFilterStatus('pending')}
            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
              filterStatus === 'pending'
                ? 'bg-amber-500 text-white shadow-xs'
                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'
            }`}
          >
            <span className="w-2 h-2 rounded-full bg-amber-400"></span>
            <span>در انتظار بررسی ({orders.filter(o => o.status === 'pending_review').length})</span>
          </button>
          <button
            type="button"
            onClick={() => setFilterStatus('matched')}
            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
              filterStatus === 'matched'
                ? 'bg-emerald-600 text-white shadow-xs'
                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'
            }`}
          >
            <span className="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span>تایید شده ({orders.filter(o => o.status === 'completed').length})</span>
          </button>
          <button
            type="button"
            onClick={() => setFilterStatus('rejected')}
            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
              filterStatus === 'rejected'
                ? 'bg-rose-600 text-white shadow-xs'
                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'
            }`}
          >
            <span className="w-2 h-2 rounded-full bg-rose-400"></span>
            <span>رد شده ({orders.filter(o => o.status === 'rejected').length})</span>
          </button>
        </div>

        {/* Search & Actions */}
        <div className="flex items-center gap-2.5 flex-1 max-w-md justify-end">
          <div className="relative w-full">
            <input
              type="text"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              placeholder="جستجوی سفارش، خریدار، RRN..."
              className="w-full pr-9 pl-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 text-xs focus:outline-hidden focus:ring-2 focus:ring-[#006194]"
            />
            <Search className="w-4 h-4 text-slate-400 absolute right-3 top-2.5 pointer-events-none" />
          </div>

          <button
            type="button"
            onClick={onOpenManualReceipt}
            className="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold whitespace-nowrap transition-colors"
          >
            <FileText className="w-3.5 h-3.5 text-sky-500" />
            <span>ثبت فیش دستی</span>
          </button>
        </div>
      </div>

      {/* Orders Table */}
      <div className="overflow-hidden rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs">
        <div className="overflow-x-auto">
          <table className="w-full text-right text-xs">
            <thead>
              <tr className="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                <th className="py-3.5 px-4 font-bold">سفارش</th>
                <th className="py-3.5 px-4 font-bold">مشتری</th>
                <th className="py-3.5 px-4 font-bold">حساب مقصد</th>
                <th className="py-3.5 px-4 font-bold">مبلغ پایه</th>
                <th className="py-3.5 px-4 font-bold">پنی انحصاری</th>
                <th className="py-3.5 px-4 font-bold">مبلغ کل واریزی</th>
                <th className="py-3.5 px-4 font-bold">کد رهگیری / RRN</th>
                <th className="py-3.5 px-4 font-bold">وضعیت فیش</th>
                <th className="py-3.5 px-4 font-bold">زمان</th>
                <th className="py-3.5 px-4 font-bold text-center">عملیات</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
              {filteredOrders.length === 0 ? (
                <tr>
                  <td colSpan={10} className="py-12 text-center text-slate-400">
                    هیچ سفارشی مطابق جستجو یا فیلتر انتخابی یافت نشد.
                  </td>
                </tr>
              ) : (
                filteredOrders.map((order) => {
                  const isPending = order.status === 'pending_review';
                  const isCompleted = order.status === 'completed';
                  const isRejected = order.status === 'rejected';

                  return (
                    <tr key={order.id} className="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                      <td className="py-3.5 px-4 font-mono font-bold text-[#006194] dark:text-sky-400">
                        #{order.orderNumber}
                      </td>
                      <td className="py-3.5 px-4">
                        <div className="font-bold text-slate-900 dark:text-white">{order.customerName}</div>
                        <div className="text-[11px] font-mono text-slate-400">{order.customerPhone}</div>
                      </td>
                      <td className="py-3.5 px-4">
                        <div className="font-medium">{order.destBankName}</div>
                        <div className="text-[10px] font-mono text-slate-400">
                          •••• {order.destCardNumber.slice(-4)}
                        </div>
                      </td>
                      <td className="py-3.5 px-4 font-mono font-medium text-slate-600 dark:text-slate-400">
                        {order.baseAmount.toLocaleString('fa-IR')} ت
                      </td>
                      <td className="py-3.5 px-4">
                        {order.pennyOffset > 0 ? (
                          <span className="font-mono font-extrabold text-[#006194] dark:text-sky-400 bg-sky-50 dark:bg-sky-950/60 px-2 py-0.5 rounded">
                            +{order.pennyOffset.toLocaleString('fa-IR')} ت
                          </span>
                        ) : (
                          <span className="text-slate-400">---</span>
                        )}
                      </td>
                      <td className="py-3.5 px-4 font-mono font-black text-slate-900 dark:text-white text-sm">
                        {order.totalAmount.toLocaleString('fa-IR')} ت
                      </td>
                      <td className="py-3.5 px-4 font-mono font-bold text-slate-600 dark:text-slate-400">
                        {order.rrn}
                      </td>
                      <td className="py-3.5 px-4">
                        {isPending && (
                          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                            <span className="w-1.5 h-1.5 rounded-full bg-amber-500 pulse-dot-green"></span>
                            <span>در انتظار بررسی فیش</span>
                          </span>
                        )}
                        {isCompleted && (
                          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                            <CheckCircle className="w-3.5 h-3.5 text-emerald-600" />
                            <span>تایید شده (پنی / پیامک)</span>
                          </span>
                        )}
                        {isRejected && (
                          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                            <XCircle className="w-3.5 h-3.5 text-rose-600" />
                            <span>رد شده</span>
                          </span>
                        )}
                      </td>
                      <td className="py-3.5 px-4 text-slate-500 dark:text-slate-400 text-[11px]">
                        {order.date}
                      </td>
                      <td className="py-3.5 px-4 text-center">
                        <div className="flex items-center justify-center gap-1.5">
                          <button
                            type="button"
                            onClick={() => onInspectOrder(order)}
                            className="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors"
                            title="مشاهده فیش و جزئیات"
                          >
                            <Eye className="w-4 h-4" />
                          </button>
                          
                          {isPending && (
                            <>
                              <button
                                type="button"
                                onClick={() => onApproveOrder(order.id)}
                                className="p-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors"
                                title="تایید پرداخت فیش"
                              >
                                <Check className="w-4 h-4" />
                              </button>
                              <button
                                type="button"
                                onClick={() => onRejectOrder(order.id)}
                                className="p-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white transition-colors"
                                title="رد کردن فیش"
                              >
                                <X className="w-4 h-4" />
                              </button>
                            </>
                          )}
                        </div>
                      </td>
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};
