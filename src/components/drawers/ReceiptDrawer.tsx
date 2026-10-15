import React from 'react';
import { 
  X, 
  ExternalLink, 
  Check, 
  XCircle, 
  User, 
  CreditCard, 
  DollarSign, 
  FileText,
  Clock
} from 'lucide-react';
import { Order } from '../../types';

interface ReceiptDrawerProps {
  order: Order | null;
  isOpen: boolean;
  onClose: () => void;
  onApprove: (orderId: string) => void;
  onReject: (orderId: string) => void;
}

export const ReceiptDrawer: React.FC<ReceiptDrawerProps> = ({
  order,
  isOpen,
  onClose,
  onApprove,
  onReject,
}) => {
  if (!isOpen || !order) return null;

  const isPending = order.status === 'pending_review';

  return (
    <div className="fixed inset-0 z-50 overflow-hidden">
      {/* Backdrop */}
      <div 
        onClick={onClose}
        className="absolute inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
      />

      <div className="fixed inset-y-0 left-0 max-w-full flex pl-0 sm:pl-10">
        <div className="w-screen max-w-md bg-white dark:bg-[#111c44] border-r border-slate-200 dark:border-slate-800 shadow-2xl flex flex-col justify-between">
          
          {/* Drawer Header */}
          <div className="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
              <div className="flex items-center gap-2">
                <h3 className="font-extrabold text-base text-slate-900 dark:text-white">
                  بررسی و تایید فیش سفارش
                </h3>
                <span className="px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-[#006194]/10 text-[#006194] dark:text-sky-300">
                  #{order.orderNumber}
                </span>
              </div>
              <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                سفارش ووکامرس • ثبت شده {order.date}
              </p>
            </div>

            <button
              type="button"
              onClick={onClose}
              className="p-1.5 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            >
              <X className="w-5 h-5" />
            </button>
          </div>

          {/* Drawer Scrollable Body */}
          <div className="p-5 overflow-y-auto space-y-5 flex-1">
            {/* Receipt Image Preview */}
            <div className="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-center space-y-2">
              <img
                src={order.receiptUrl || 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&q=80&w=800'}
                alt="تصویر فیش واریزی"
                className="w-full max-h-64 object-contain rounded-xl shadow-xs mx-auto border border-slate-200/60 dark:border-slate-700"
              />
              <div className="pt-1 flex justify-center">
                <a
                  href={order.receiptUrl || 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&q=80&w=800'}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-800 transition-colors"
                >
                  <ExternalLink className="w-3.5 h-3.5" />
                  <span>مشاهده اندازه کامل فیش</span>
                </a>
              </div>
            </div>

            {/* Customer & Transaction Metadata Card */}
            <div className="p-4 rounded-2xl bg-white dark:bg-[#162456]/40 border border-slate-200 dark:border-slate-800 space-y-3 text-xs">
              <h4 className="font-extrabold text-slate-900 dark:text-white pb-2 border-b border-slate-200 dark:border-slate-700">
                اطلاعات پرداخت و مشخصات خریدار
              </h4>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <span className="text-slate-400 block text-[11px]">نام خریدار:</span>
                  <span className="font-bold text-slate-900 dark:text-white">{order.customerName}</span>
                </div>
                <div>
                  <span className="text-slate-400 block text-[11px]">شماره تماس:</span>
                  <span className="font-mono text-slate-700 dark:text-slate-300">{order.customerPhone}</span>
                </div>
                <div>
                  <span className="text-slate-400 block text-[11px]">کارت مبدا مشتری:</span>
                  <span className="font-mono font-bold text-slate-700 dark:text-slate-300">{order.buyerCardMask || '---'}</span>
                </div>
                <div>
                  <span className="text-slate-400 block text-[11px]">شماره ارجاع / RRN:</span>
                  <span className="font-mono font-bold text-slate-900 dark:text-white">{order.rrn}</span>
                </div>
                <div>
                  <span className="text-slate-400 block text-[11px]">مبلغ پایه سفارش:</span>
                  <span className="font-mono">{order.baseAmount.toLocaleString('fa-IR')} تومان</span>
                </div>
                <div>
                  <span className="text-slate-400 block text-[11px]">کارمزد پنی انحصاری:</span>
                  <span className="font-mono font-bold text-[#006194] dark:text-sky-400">+{order.pennyOffset} تومان</span>
                </div>
              </div>

              <div className="pt-2 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <span className="font-bold text-slate-700 dark:text-slate-300">کل مبلغ واریزی مورد انتظار:</span>
                <span className="font-mono text-base font-black text-emerald-600 dark:text-emerald-400">
                  {order.totalAmount.toLocaleString('fa-IR')} تومان
                </span>
              </div>

              {order.notes && (
                <div className="pt-2 text-[11px] text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-xl">
                  <strong>یادداشت:</strong> {order.notes}
                </div>
              )}
            </div>
          </div>

          {/* Sticky Drawer Footer Actions */}
          <div className="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-between gap-3">
            <button
              type="button"
              onClick={() => onReject(order.id)}
              className="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-colors"
            >
              <XCircle className="w-4 h-4" />
              <span>رد کردن فیش</span>
            </button>

            <button
              type="button"
              onClick={() => onApprove(order.id)}
              className="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors shadow-md shadow-emerald-600/20"
            >
              <Check className="w-4 h-4" />
              <span>تایید فیش و تکمیل سفارش</span>
            </button>
          </div>

        </div>
      </div>
    </div>
  );
};
