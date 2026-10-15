import React, { useState } from 'react';
import { X, Smartphone, Zap, ShieldCheck } from 'lucide-react';

interface TestWebhookModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSimulateSms: (smsData: {
    rawSms: string;
    senderNumber: string;
    amount: number;
    rrn: string;
    cardLast4: string;
  }) => void;
}

const presetTemplates = [
  {
    name: 'بانک سامان (سفارش #1042)',
    sms: `بانک سامان
واریز: ۲۵۰,۳۴۰ تومان
به کارت: ۵۴۱۲
کد پیگیری: ۹۸۲۳۰۴۸۱۲۹۵
۱۴:۳۲:۱۰`,
    sender: '20000189',
    amount: 250340,
    rrn: '98230481295',
    cardLast4: '5412',
  },
  {
    name: 'بانک ملت (سفارش ۱,۸۵۰,۵۲۰ ت)',
    sms: `بانک ملت
واریز: ۱,۸۵۰,۵۲۰ ریال/تومان
به حساب: ۳۴۵۶
شماره پیگیری: ۸۷۴۶۱۹۲۰۳۱۱
۱۵:۱۰:۴۴`,
    sender: '200033',
    amount: 1850520,
    rrn: '87461920311',
    cardLast4: '3456',
  },
  {
    name: 'بانک تجارت (سفارش ۶۴۰,۱۹۰ ت)',
    sms: `بانک تجارت
مبلغ ۶۴۰,۱۹۰ تومان واریز شد
به کارت: ۹۹۸۱
کد ارجاع: ۷۷۶۵۱۲۰۳۴۹۸`,
    sender: '200070',
    amount: 640190,
    rrn: '77651203498',
    cardLast4: '9981',
  },
];

export const TestWebhookModal: React.FC<TestWebhookModalProps> = ({
  isOpen,
  onClose,
  onSimulateSms,
}) => {
  const [selectedPreset, setSelectedPreset] = useState(0);
  const [rawSms, setRawSms] = useState(presetTemplates[0].sms);
  const [senderNumber, setSenderNumber] = useState(presetTemplates[0].sender);
  const [amount, setAmount] = useState<number>(presetTemplates[0].amount);
  const [rrn, setRrn] = useState(presetTemplates[0].rrn);
  const [cardLast4, setCardLast4] = useState(presetTemplates[0].cardLast4);

  if (!isOpen) return null;

  const handleSelectPreset = (index: number) => {
    setSelectedPreset(index);
    const p = presetTemplates[index];
    setRawSms(p.sms);
    setSenderNumber(p.sender);
    setAmount(p.amount);
    setRrn(p.rrn);
    setCardLast4(p.cardLast4);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSimulateSms({
      rawSms,
      senderNumber,
      amount,
      rrn,
      cardLast4,
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
            <Smartphone className="w-5 h-5 text-sky-500" />
            <h3 className="font-extrabold text-sm sm:text-base text-slate-900 dark:text-white">
              شبیه‌ساز تزریق پیامک شتابی به وب‌هوک (Webhook Tester)
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

        {/* Modal Form */}
        <form onSubmit={handleSubmit}>
          <div className="p-6 space-y-4 text-xs">
            {/* Quick Preset Selector */}
            <div>
              <label className="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                قالب‌های آماده پیامک بانکی:
              </label>
              <div className="flex items-center gap-2 flex-wrap">
                {presetTemplates.map((preset, idx) => (
                  <button
                    key={preset.name}
                    type="button"
                    onClick={() => handleSelectPreset(idx)}
                    className={`px-3 py-1.5 rounded-xl font-bold transition-all ${
                      selectedPreset === idx
                        ? 'bg-[#006194] text-white shadow-xs'
                        : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'
                    }`}
                  >
                    {preset.name}
                  </button>
                ))}
              </div>
            </div>

            {/* Raw SMS Textarea */}
            <div>
              <label className="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                متن خام پیامک واریز بانکی:
              </label>
              <textarea
                rows={4}
                value={rawSms}
                onChange={(e) => setRawSms(e.target.value)}
                required
                className="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono text-xs leading-relaxed"
              />
            </div>

            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                  سرشماره فرستنده:
                </label>
                <input
                  type="text"
                  value={senderNumber}
                  onChange={(e) => setSenderNumber(e.target.value)}
                  className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono font-bold"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                  مبلغ استخراجی (تومان):
                </label>
                <input
                  type="number"
                  value={amount}
                  onChange={(e) => setAmount(Number(e.target.value))}
                  required
                  className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono font-bold"
                />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                  شماره ارجاع / RRN:
                </label>
                <input
                  type="text"
                  value={rrn}
                  onChange={(e) => setRrn(e.target.value)}
                  className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono font-bold"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                  ۴ رقم آخر کارت مقصد:
                </label>
                <input
                  type="text"
                  value={cardLast4}
                  onChange={(e) => setCardLast4(e.target.value)}
                  className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono font-bold"
                />
              </div>
            </div>

            <div className="p-3 rounded-xl bg-sky-50 dark:bg-sky-950/60 border border-sky-200 dark:border-sky-800 text-[11px] text-sky-800 dark:text-sky-300 flex items-center gap-2">
              <Zap className="w-4 h-4 text-sky-500 shrink-0" />
              <span>
                پیامک با امضای هش HMAC-SHA256 شبیه‌سازی شده و در صورت تطبیق مبلغ با سفارش، فاکتور فورا پرداخت شده می‌شود.
              </span>
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
              className="px-5 py-2 rounded-xl bg-[#006194] hover:bg-[#004b73] text-white text-xs font-bold shadow-md shadow-[#006194]/20 flex items-center gap-1.5"
            >
              <Zap className="w-4 h-4" />
              <span>ارسال و تست وب‌هوک</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
