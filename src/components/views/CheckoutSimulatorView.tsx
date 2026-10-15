import React, { useState, useEffect } from 'react';
import { 
  ShoppingBag, 
  CreditCard, 
  Clock, 
  Copy, 
  Check, 
  UploadCloud, 
  AlertCircle, 
  CheckCircle2,
  ArrowRight
} from 'lucide-react';
import { BankCard, Order } from '../../types';

interface CheckoutSimulatorViewProps {
  cards: BankCard[];
  onCompleteCheckout: (newOrder: Partial<Order>) => void;
  onBackToAdmin: () => void;
}

export const CheckoutSimulatorView: React.FC<CheckoutSimulatorViewProps> = ({
  cards,
  onCompleteCheckout,
  onBackToAdmin,
}) => {
  const activeCards = cards.filter(c => c.isActive);
  const [selectedCardId, setSelectedCardId] = useState<string>(activeCards[0]?.id || '');
  const [baseAmount, setBaseAmount] = useState<number>(350000);
  const [pennyOffset] = useState<number>(420);
  const totalAmount = baseAmount + pennyOffset;

  // 15-minute countdown timer
  const [timeLeft, setTimeLeft] = useState<number>(15 * 60);
  useEffect(() => {
    const timer = setInterval(() => {
      setTimeLeft(prev => (prev > 0 ? prev - 1 : 0));
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  const minutes = Math.floor(timeLeft / 60);
  const seconds = timeLeft % 60;

  // Form states
  const [customerName, setCustomerName] = useState('امیرحسین رضایی');
  const [customerPhone, setCustomerPhone] = useState('09121234567');
  const [buyerCardMask, setBuyerCardMask] = useState('6037 •••• 5112');
  const [trackingCode, setTrackingCode] = useState('98230481295');
  const [copiedKey, setCopiedKey] = useState<string | null>(null);
  const [receiptUploaded, setReceiptUploaded] = useState(true);
  const [isSubmitted, setIsSubmitted] = useState(false);

  const selectedCard = cards.find(c => c.id === selectedCardId) || activeCards[0];

  const handleCopy = (text: string, key: string) => {
    navigator.clipboard.writeText(text);
    setCopiedKey(key);
    setTimeout(() => setCopiedKey(null), 2000);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const newOrder: Partial<Order> = {
      orderNumber: (Math.floor(Math.random() * 800) + 1050).toString(),
      customerName,
      customerPhone,
      baseAmount,
      pennyOffset,
      totalAmount,
      destCardId: selectedCard?.id,
      destBankName: selectedCard?.bankName || 'بانک ملت',
      destCardNumber: selectedCard?.cardNumber || '',
      rrn: trackingCode,
      status: 'pending_review',
      date: 'لحظاتی پیش',
      receiptUrl: 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&q=80&w=800',
      buyerCardMask,
      notes: 'ثبت شده توسط خریدار در فرم پرداخت شبیه‌ساز کارت به کارت.',
    };

    onCompleteCheckout(newOrder);
    setIsSubmitted(true);
  };

  if (isSubmitted) {
    return (
      <div className="max-w-xl mx-auto my-8 p-8 rounded-3xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xl text-center space-y-5">
        <div className="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center">
          <CheckCircle2 className="w-10 h-10" />
        </div>

        <h3 className="text-xl font-extrabold text-slate-900 dark:text-white">
          اطلاعات پرداخت شما با موفقیت ثبت گردید!
        </h3>

        <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed max-w-md mx-auto">
          فیش پرداخت و شماره پیگیری به شناسه سفارش شما پیوست شد. به محض دریافت پیامک شتابی توسط پنی‌انجین یا بررسی حسابداری، وضعیت سفارش تکمیل خواهد شد.
        </p>

        <div className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs text-right space-y-2">
          <div className="flex justify-between">
            <span className="text-slate-500">مبلغ پرداخت شده:</span>
            <span className="font-mono font-bold">{totalAmount.toLocaleString('fa-IR')} تومان</span>
          </div>
          <div className="flex justify-between">
            <span className="text-slate-500">شماره ارجاع / RRN:</span>
            <span className="font-mono font-bold">{trackingCode}</span>
          </div>
          <div className="flex justify-between">
            <span className="text-slate-500">بانک مقصد:</span>
            <span className="font-bold">{selectedCard?.bankName}</span>
          </div>
        </div>

        <div className="pt-3 flex items-center justify-center gap-3">
          <button
            type="button"
            onClick={onBackToAdmin}
            className="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#006194] hover:bg-[#004b73] text-white text-xs font-bold transition-all shadow-md shadow-[#006194]/20"
          >
            <span>مشاهده سفارش در پیشخوان مدیریت</span>
            <ArrowRight className="w-4 h-4" />
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      {/* Simulation Banner */}
      <div className="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-900 dark:text-amber-200 flex items-center justify-between text-xs font-bold">
        <div className="flex items-center gap-2">
          <ShoppingBag className="w-4 h-4 text-amber-600 dark:text-amber-400" />
          <span>شبیه‌ساز صفحه پرداخت کارت‌به‌کارت ووکامرس (نمای خریدار نهایی)</span>
        </div>
        <button
          type="button"
          onClick={onBackToAdmin}
          className="underline text-amber-700 dark:text-amber-300 hover:opacity-80"
        >
          بازگشت به پیشخوان
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {/* Left Column: Card Details & Penny Timer */}
        <div className="md:col-span-1 space-y-4">
          {/* Reservation Countdown Timer */}
          <div className="p-4 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs text-center space-y-2">
            <div className="flex items-center justify-center gap-1.5 text-xs font-bold text-slate-500 dark:text-slate-400">
              <Clock className="w-4 h-4 text-amber-500" />
              <span>مهلت واریز دقیق مبلغ پنی‌دار:</span>
            </div>
            <div className="font-mono text-3xl font-black text-[#006194] dark:text-sky-400">
              {String(minutes).padStart(2, '0')}:{String(seconds).padStart(2, '0')}
            </div>
            <div className="text-[11px] text-slate-400">
              پنی‌انجین مبلغ زیر را به مدت ۱۵ دقیقه برای شما رزرو نگه می‌دارد.
            </div>
          </div>

          {/* Amount Breakdown */}
          <div className="p-4 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs space-y-3 text-xs">
            <div className="flex items-center justify-between text-slate-500">
              <span>مبلغ فاکتور خرید:</span>
              <span className="font-mono">{baseAmount.toLocaleString('fa-IR')} ت</span>
            </div>
            <div className="flex items-center justify-between text-[#006194] dark:text-sky-400 font-bold">
              <span>کارمزد انحصاری پنی:</span>
              <span className="font-mono">+{pennyOffset.toLocaleString('fa-IR')} ت</span>
            </div>
            <div className="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-sm font-black text-slate-900 dark:text-white">
              <span>کل مبلغ قابل واریز:</span>
              <span className="font-mono text-base text-emerald-600 dark:text-emerald-400">
                {totalAmount.toLocaleString('fa-IR')} تومان
              </span>
            </div>
          </div>

          {/* Active Bank Card Preview */}
          {selectedCard && (
            <div className={`p-5 rounded-2xl bg-gradient-to-br ${selectedCard.colorGradient || 'from-slate-800 to-slate-950'} text-white shadow-lg space-y-3`}>
              <div className="flex items-start justify-between">
                <div>
                  <div className="text-xs text-slate-300 font-medium">{selectedCard.bankName}</div>
                  <div className="text-sm font-extrabold mt-0.5">{selectedCard.cardHolder}</div>
                </div>
                <CreditCard className="w-6 h-6 text-sky-400" />
              </div>

              <div className="pt-2">
                <div className="text-[10px] text-slate-400 mb-0.5">شماره کارت:</div>
                <div className="flex items-center justify-between">
                  <span className="font-mono font-bold tracking-widest text-base">
                    {selectedCard.cardNumber.replace(/(\d{4})/g, '$1 ').trim()}
                  </span>
                  <button
                    type="button"
                    onClick={() => handleCopy(selectedCard.cardNumber, 'checkout_card')}
                    className="p-1 rounded bg-white/10 hover:bg-white/20 transition-colors"
                  >
                    {copiedKey === 'checkout_card' ? <Check className="w-3.5 h-3.5 text-emerald-300" /> : <Copy className="w-3.5 h-3.5" />}
                  </button>
                </div>
              </div>

              <div>
                <div className="text-[10px] text-slate-400 mb-0.5">شماره شبا:</div>
                <div className="flex items-center justify-between text-xs font-mono">
                  <span>IR{selectedCard.iban}</span>
                  <button
                    type="button"
                    onClick={() => handleCopy(`IR${selectedCard.iban}`, 'checkout_iban')}
                    className="p-1 rounded hover:bg-white/10 transition-colors"
                  >
                    {copiedKey === 'checkout_iban' ? <Check className="w-3 h-3 text-emerald-300" /> : <Copy className="w-3 h-3 text-slate-400" />}
                  </button>
                </div>
              </div>
            </div>
          )}
        </div>

        {/* Right 2 Columns: Payment Entry Form */}
        <div className="md:col-span-2">
          <form onSubmit={handleSubmit} className="p-6 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs space-y-5">
            <div>
              <h4 className="font-extrabold text-sm sm:text-base text-slate-900 dark:text-white mb-1">
                ثبت مشخصات و فیش واریز کارت به کارت
              </h4>
              <p className="text-xs text-slate-500 dark:text-slate-400">
                لطفاً پس از انتقال وجه به شماره کارت بالا، اطلاعات رسید را در کادرهای زیر وارد نمایید.
              </p>
            </div>

            {/* Select Destination Card */}
            <div>
              <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                انتخاب حساب بانکی مقصد واریز:
              </label>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                {activeCards.map(card => (
                  <button
                    key={card.id}
                    type="button"
                    onClick={() => setSelectedCardId(card.id)}
                    className={`p-3 rounded-xl border text-right transition-all flex items-center justify-between ${
                      selectedCardId === card.id
                        ? 'border-[#006194] bg-sky-50/50 dark:bg-sky-950/40 ring-2 ring-[#006194]/20'
                        : 'border-slate-200 dark:border-slate-700'
                    }`}
                  >
                    <div>
                      <div className="text-xs font-bold text-slate-900 dark:text-white">{card.bankName}</div>
                      <div className="text-[11px] font-mono text-slate-400">•••• {card.cardNumber.slice(-4)}</div>
                    </div>
                    {selectedCardId === card.id && <CheckCircle2 className="w-4 h-4 text-[#006194] dark:text-sky-400" />}
                  </button>
                ))}
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  نام و نام خانوادگی واریزکننده:
                </label>
                <input
                  type="text"
                  value={customerName}
                  onChange={(e) => setCustomerName(e.target.value)}
                  required
                  className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-medium"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  شماره موبایل خریدار:
                </label>
                <input
                  type="text"
                  value={customerPhone}
                  onChange={(e) => setCustomerPhone(e.target.value)}
                  required
                  className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-mono"
                />
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  شماره کارت مبدا (یا ۴ رقم آخر):
                </label>
                <input
                  type="text"
                  value={buyerCardMask}
                  onChange={(e) => setBuyerCardMask(e.target.value)}
                  placeholder="مثال: ۶۰۳۷ •••• ۵۱۱۲"
                  required
                  className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-mono font-bold"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  شماره پیگیری / ارجاع بانکی (RRN):
                </label>
                <input
                  type="text"
                  value={trackingCode}
                  onChange={(e) => setTrackingCode(e.target.value)}
                  placeholder="مثال: ۹۸۲۳۰۴۸۱۲۹۵"
                  required
                  className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-mono font-bold"
                />
              </div>
            </div>

            {/* Receipt File Upload */}
            <div>
              <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                بارگذاری تصویر رسید / اسکرین‌شات واریزی:
              </label>
              <div className="p-4 rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 flex flex-col items-center justify-center text-center cursor-pointer hover:border-[#006194] transition-colors">
                <UploadCloud className="w-8 h-8 text-[#006194] dark:text-sky-400 mb-1" />
                <div className="text-xs font-bold text-slate-800 dark:text-slate-200">
                  {receiptUploaded ? 'فیش واریزی پیوست شد (receipt_1042.jpg)' : 'برای انتخاب فایل کلیک کنید یا تصویر را به اینجا بکشید'}
                </div>
                <div className="text-[11px] text-slate-400 mt-1">
                  فرمت‌های مجاز: JPG, PNG, WebP (حداکثر ۵ مگابایت)
                </div>
              </div>
            </div>

            <div className="pt-2">
              <button
                type="submit"
                className="w-full py-3 rounded-xl bg-gradient-to-r from-[#006194] to-sky-600 hover:from-[#004b73] hover:to-sky-700 text-white text-xs sm:text-sm font-extrabold transition-all shadow-lg shadow-[#006194]/25"
              >
                ثبت اطلاعات و تایید نهایی پرداخت
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};
