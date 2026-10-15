import React, { useState, useEffect } from 'react';
import { X, PlusCircle, CreditCard, ShieldCheck } from 'lucide-react';
import { BankCard } from '../../types';

interface AddCardModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSaveCard: (card: Partial<BankCard>) => void;
  editingCard?: BankCard | null;
}

export const AddCardModal: React.FC<AddCardModalProps> = ({
  isOpen,
  onClose,
  onSaveCard,
  editingCard,
}) => {
  const [bankName, setBankName] = useState('');
  const [cardNumber, setCardNumber] = useState('');
  const [iban, setIban] = useState('');
  const [cardHolder, setCardHolder] = useState('');
  const [dailyLimit, setDailyLimit] = useState('100,000,000');
  const [dailyTxLimit, setDailyTxLimit] = useState(50);

  useEffect(() => {
    if (editingCard) {
      setBankName(editingCard.bankName);
      setCardNumber(editingCard.cardNumber.replace(/(\d{4})/g, '$1 ').trim());
      setIban(editingCard.iban);
      setCardHolder(editingCard.cardHolder);
      setDailyLimit(editingCard.dailyLimit.toLocaleString('en-US'));
      setDailyTxLimit(editingCard.dailyTxLimit);
    } else {
      setBankName('');
      setCardNumber('');
      setIban('');
      setCardHolder('');
      setDailyLimit('100,000,000');
      setDailyTxLimit(50);
    }
  }, [editingCard, isOpen]);

  if (!isOpen) return null;

  // Format card number with spaces every 4 digits
  const handleCardNumberChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const raw = e.target.value.replace(/\D/g, '').slice(0, 16);
    const formatted = raw.replace(/(\d{4})/g, '$1 ').trim();
    setCardNumber(formatted);
  };

  // Format IBAN (24 digits without IR)
  const handleIbanChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const raw = e.target.value.replace(/\D/g, '').slice(0, 24);
    setIban(raw);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const cleanCard = cardNumber.replace(/\D/g, '');
    const cleanLimit = Number(dailyLimit.replace(/,/g, '')) || 100000000;

    onSaveCard({
      id: editingCard ? editingCard.id : undefined,
      bankName,
      cardNumber: cleanCard,
      iban,
      cardHolder,
      dailyLimit: cleanLimit,
      dailyTxLimit,
      isActive: editingCard ? editingCard.isActive : true,
    });
    onClose();
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      {/* Backdrop */}
      <div 
        onClick={onClose}
        className="absolute inset-0 bg-slate-900/60 backdrop-blur-xs"
      />

      <div className="relative w-full max-w-lg bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl overflow-hidden">
        {/* Modal Header */}
        <div className="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <CreditCard className="w-5 h-5 text-[#006194] dark:text-sky-400" />
            <h3 className="font-extrabold text-sm sm:text-base text-slate-900 dark:text-white">
              {editingCard ? 'ویرایش کارت بانکی شتاب' : 'افزودن کارت بانکی جدید به سامانه'}
            </h3>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Form Body */}
        <form onSubmit={handleSubmit}>
          <div className="p-6 space-y-4">
            <div>
              <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                نام بانک صادرکننده:
              </label>
              <input
                type="text"
                value={bankName}
                onChange={(e) => setBankName(e.target.value)}
                placeholder="مثال: بانک ملت، بانک سامان، تجارت..."
                required
                className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-medium"
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                شماره کارت ۱۶ رقمی شتاب:
              </label>
              <input
                type="text"
                value={cardNumber}
                onChange={handleCardNumberChange}
                placeholder="xxxx xxxx xxxx xxxx"
                required
                maxLength={19}
                className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-mono font-bold tracking-widest"
              />
            </div>

            <div>
              <div className="flex items-center justify-between mb-1">
                <label className="text-xs font-bold text-slate-700 dark:text-slate-300">
                  شماره شبا (IBAN):
                </label>
                <span className="text-[11px] font-mono text-slate-400">
                  {iban.length} / 24 رقم
                </span>
              </div>
              <div className="relative flex items-center">
                <span className="absolute left-3 px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-700 font-mono text-xs font-bold text-slate-600 dark:text-slate-300 pointer-events-none">
                  IR
                </span>
                <input
                  type="text"
                  value={iban}
                  onChange={handleIbanChange}
                  placeholder="000000000000000000000000"
                  maxLength={24}
                  className="w-full pl-12 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-mono font-medium"
                />
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  نام صاحب حساب:
                </label>
                <input
                  type="text"
                  value={cardHolder}
                  onChange={(e) => setCardHolder(e.target.value)}
                  placeholder="مثال: مجتبی وفادار"
                  required
                  className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-medium"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  سقف پذیرش روزانه (تومان):
                </label>
                <input
                  type="text"
                  value={dailyLimit}
                  onChange={(e) => setDailyLimit(e.target.value)}
                  className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-mono font-bold"
                />
              </div>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                سقف تعداد تراکنش روزانه:
              </label>
              <input
                type="number"
                value={dailyTxLimit}
                onChange={(e) => setDailyTxLimit(Number(e.target.value))}
                min={1}
                className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-mono font-bold"
              />
            </div>
          </div>

          {/* Modal Footer */}
          <div className="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-end gap-2.5">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
            >
              انصراف
            </button>
            <button
              type="submit"
              className="px-5 py-2 rounded-xl bg-[#006194] hover:bg-[#004b73] text-white text-xs font-bold shadow-md shadow-[#006194]/20"
            >
              {editingCard ? 'ذخیره تغییرات' : 'ثبت و فعال‌سازی کارت'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
