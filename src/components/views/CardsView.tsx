import React from 'react';
import { 
  CreditCard, 
  RotateCcw, 
  PlusCircle, 
  Copy, 
  Check, 
  Edit3, 
  Trash2, 
  CheckCircle, 
  XCircle 
} from 'lucide-react';
import { BankCard } from '../../types';

interface CardsViewProps {
  cards: BankCard[];
  onOpenAddCard: () => void;
  onEditCard: (card: BankCard) => void;
  onDeleteCard: (cardId: string) => void;
  onToggleCardStatus: (cardId: string) => void;
  onResetLimits: () => void;
  onCopyText: (text: string, label: string) => void;
  copiedKey: string | null;
}

export const CardsView: React.FC<CardsViewProps> = ({
  cards,
  onOpenAddCard,
  onEditCard,
  onDeleteCard,
  onToggleCardStatus,
  onResetLimits,
  onCopyText,
  copiedKey,
}) => {
  return (
    <div className="space-y-6">
      {/* Top Action Banner */}
      <div className="p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h3 className="font-extrabold text-sm sm:text-base text-slate-900 dark:text-white">
            مدیریت کارت‌ها و حساب‌های مقصد شتاب (جدول اختصاصی wccc_cards)
          </h3>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
            شماره کارت‌ها و شباهای متصل به پنی‌انجین جهت تخصیص هوشمند به خریداران در هنگام تسویه حساب.
          </p>
        </div>

        <div className="flex items-center gap-2.5">
          <button
            type="button"
            onClick={onResetLimits}
            className="flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-bold transition-all shadow-xs"
          >
            <RotateCcw className="w-3.5 h-3.5 text-slate-400" />
            <span>بازنشانی سقف‌های روزانه</span>
          </button>

          <button
            type="button"
            onClick={onOpenAddCard}
            className="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#006194] hover:bg-[#004b73] text-white text-xs font-bold transition-all shadow-sm shadow-[#006194]/20"
          >
            <PlusCircle className="w-4 h-4" />
            <span>افزودن کارت بانکی جدید</span>
          </button>
        </div>
      </div>

      {/* Metallic Visual Cards Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        {cards.map((card) => {
          const formattedCard = card.cardNumber.replace(/(\d{4})/g, '$1 ').trim();
          const percentUsed = Math.min(100, Math.round((card.currentDailyTotal / (card.dailyLimit || 1)) * 100));

          return (
            <div
              key={card.id}
              className={`p-5 rounded-2xl bg-gradient-to-br ${card.colorGradient || 'from-slate-800 via-slate-900 to-slate-950'} text-white shadow-lg relative overflow-hidden border border-white/10 flex flex-col justify-between transition-transform hover:-translate-y-1`}
              style={{ minHeight: '210px' }}
            >
              {/* Card Top */}
              <div className="flex items-start justify-between">
                <div>
                  <div className="text-xs font-medium text-slate-300">{card.bankName}</div>
                  <div className="text-base font-extrabold text-white mt-0.5">{card.cardHolder}</div>
                </div>
                <div className="flex items-center gap-2">
                  <span className={`px-2 py-0.5 rounded-full text-[10px] font-bold ${
                    card.isActive ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-slate-700/60 text-slate-400'
                  }`}>
                    {card.isActive ? 'فعال در شتاب' : 'غیرفعال'}
                  </span>
                </div>
              </div>

              {/* Card Number */}
              <div className="my-4">
                <div className="flex items-center justify-between">
                  <div className="font-mono font-black tracking-widest text-lg sm:text-xl text-slate-100 drop-shadow-sm">
                    {formattedCard}
                  </div>
                  <button
                    type="button"
                    onClick={() => onCopyText(card.cardNumber, `card_${card.id}`)}
                    className="p-1 rounded bg-white/10 hover:bg-white/20 text-white transition-colors"
                    title="کپی شماره کارت"
                  >
                    {copiedKey === `card_${card.id}` ? (
                      <Check className="w-3.5 h-3.5 text-emerald-300" />
                    ) : (
                      <Copy className="w-3.5 h-3.5" />
                    )}
                  </button>
                </div>

                <div className="flex items-center justify-between text-xs font-mono text-slate-300 mt-1">
                  <span>IR{card.iban}</span>
                  <button
                    type="button"
                    onClick={() => onCopyText(`IR${card.iban}`, `iban_${card.id}`)}
                    className="p-1 rounded hover:bg-white/10 transition-colors"
                    title="کپی شماره شبا"
                  >
                    {copiedKey === `iban_${card.id}` ? (
                      <Check className="w-3 h-3 text-emerald-300" />
                    ) : (
                      <Copy className="w-3 h-3 text-slate-400" />
                    )}
                  </button>
                </div>
              </div>

              {/* Card Bottom: Limits & Progress */}
              <div className="pt-3 border-t border-white/15">
                <div className="flex items-center justify-between text-[11px] mb-1.5">
                  <span className="text-slate-300">گردش روزانه:</span>
                  <span className="font-mono font-bold">
                    {(card.currentDailyTotal / 1000).toLocaleString('fa-IR')} / {(card.dailyLimit / 1000).toLocaleString('fa-IR')} ه.ت
                  </span>
                </div>

                {/* Progress bar */}
                <div className="w-full h-1.5 rounded-full bg-black/30 overflow-hidden">
                  <div 
                    className="h-full bg-gradient-to-r from-emerald-400 to-sky-400 rounded-full"
                    style={{ width: `${percentUsed}%` }}
                  ></div>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {/* Full Cards Table Card */}
      <div className="overflow-hidden rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs">
        <div className="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <CreditCard className="w-4 h-4 text-[#006194]" />
            <h4 className="font-extrabold text-sm text-slate-900 dark:text-white">
              فهرست کامل حساب‌های بانکی سیستم
            </h4>
          </div>
          <span className="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 dark:bg-sky-950/60 text-[#006194] dark:text-sky-300">
            {cards.length} حساب ثبت شده
          </span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-right text-xs">
            <thead>
              <tr className="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                <th className="py-3.5 px-4 font-bold">نام بانک</th>
                <th className="py-3.5 px-4 font-bold">شماره کارت (۱۶ رقمی)</th>
                <th className="py-3.5 px-4 font-bold">شماره شبا (IBAN)</th>
                <th className="py-3.5 px-4 font-bold">صاحب حساب</th>
                <th className="py-3.5 px-4 font-bold">سقف گردش روزانه</th>
                <th className="py-3.5 px-4 font-bold">تراکنش امروز</th>
                <th className="py-3.5 px-4 font-bold text-center">وضعیت شتاب</th>
                <th className="py-3.5 px-4 font-bold text-center">عملیات</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
              {cards.map((card) => {
                const formattedCard = card.cardNumber.replace(/(\d{4})/g, '$1 ').trim();

                return (
                  <tr key={card.id} className="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                    <td className="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                      {card.bankName}
                    </td>
                    <td className="py-3.5 px-4">
                      <div className="flex items-center gap-2">
                        <span className="font-mono font-bold text-[#006194] dark:text-sky-400">
                          {formattedCard}
                        </span>
                        <button
                          type="button"
                          onClick={() => onCopyText(card.cardNumber, `table_card_${card.id}`)}
                          className="p-1 rounded text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"
                          title="کپی"
                        >
                          {copiedKey === `table_card_${card.id}` ? (
                            <Check className="w-3.5 h-3.5 text-emerald-500" />
                          ) : (
                            <Copy className="w-3.5 h-3.5" />
                          )}
                        </button>
                      </div>
                    </td>
                    <td className="py-3.5 px-4">
                      <div className="flex items-center gap-2">
                        <span className="font-mono text-slate-600 dark:text-slate-300">
                          IR{card.iban}
                        </span>
                        <button
                          type="button"
                          onClick={() => onCopyText(`IR${card.iban}`, `table_iban_${card.id}`)}
                          className="p-1 rounded text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"
                          title="کپی شبا"
                        >
                          {copiedKey === `table_iban_${card.id}` ? (
                            <Check className="w-3.5 h-3.5 text-emerald-500" />
                          ) : (
                            <Copy className="w-3.5 h-3.5" />
                          )}
                        </button>
                      </div>
                    </td>
                    <td className="py-3.5 px-4 font-medium text-slate-900 dark:text-white">
                      {card.cardHolder}
                    </td>
                    <td className="py-3.5 px-4 font-mono font-bold">
                      {card.dailyLimit.toLocaleString('fa-IR')} ت
                    </td>
                    <td className="py-3.5 px-4 font-mono">
                      <span className="font-bold text-slate-900 dark:text-white">
                        {card.currentDailyTx}
                      </span>{' '}
                      / {card.dailyTxLimit} تراکنش
                    </td>
                    <td className="py-3.5 px-4 text-center">
                      <button
                        type="button"
                        onClick={() => onToggleCardStatus(card.id)}
                        className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold transition-all ${
                          card.isActive 
                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' 
                            : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                        }`}
                      >
                        {card.isActive ? (
                          <>
                            <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 pulse-dot-green"></span>
                            <span>فعال</span>
                          </>
                        ) : (
                          <>
                            <span className="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            <span>غیرفعال</span>
                          </>
                        )}
                      </button>
                    </td>
                    <td className="py-3.5 px-4 text-center">
                      <div className="flex items-center justify-center gap-1.5">
                        <button
                          type="button"
                          onClick={() => onEditCard(card)}
                          className="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors"
                          title="ویرایش کارت"
                        >
                          <Edit3 className="w-3.5 h-3.5" />
                        </button>
                        <button
                          type="button"
                          onClick={() => onDeleteCard(card.id)}
                          className="p-1.5 rounded-lg border border-rose-200 dark:border-rose-900/60 hover:bg-rose-50 dark:hover:bg-rose-950/60 text-rose-600 dark:text-rose-400 transition-colors"
                          title="حذف کارت"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
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
  );
};
