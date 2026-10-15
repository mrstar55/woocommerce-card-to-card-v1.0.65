import React, { useState } from 'react';
import { 
  Sliders, 
  MessageSquare, 
  Palette, 
  Save, 
  RotateCcw, 
  ShieldAlert, 
  CheckCircle2,
  FileUp,
  Cpu
} from 'lucide-react';
import { Settings } from '../../types';

interface SettingsViewProps {
  settings: Settings;
  onSaveSettings: (newSettings: Settings) => void;
}

export const SettingsView: React.FC<SettingsViewProps> = ({
  settings,
  onSaveSettings,
}) => {
  const [activeSubtab, setActiveSubtab] = useState<'engine' | 'messages' | 'appearance'>('engine');
  const [formState, setFormState] = useState<Settings>({ ...settings });
  const [isSaved, setIsSaved] = useState(false);

  const hasChanges = JSON.stringify(formState) !== JSON.stringify(settings);

  const handleReset = () => {
    setFormState({ ...settings });
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    onSaveSettings(formState);
    setIsSaved(true);
    setTimeout(() => setIsSaved(false), 3000);
  };

  return (
    <div className="space-y-6 pb-20">
      {/* Top Header Banner */}
      <div className="p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs">
        <h3 className="font-extrabold text-sm sm:text-base text-slate-900 dark:text-white">
          تنظیمات درگاه و پنی‌انجین (WCCC Gateway & Penny Engine)
        </h3>
        <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
          پیکربندی هوشمند الگوریتم پنی، متن‌ها و پیام‌های پرداخت خریدار، و سفارشی‌سازی ظاهر پیشخوان.
        </p>
      </div>

      {/* Subnav Visual Tabs */}
      <div className="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2">
        <button
          type="button"
          onClick={() => setActiveSubtab('engine')}
          className={`flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all ${
            activeSubtab === 'engine'
              ? 'bg-[#006194] text-white shadow-xs'
              : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'
          }`}
        >
          <Cpu className="w-4 h-4" />
          <span>تنظیمات پایه و موتور</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveSubtab('messages')}
          className={`flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all ${
            activeSubtab === 'messages'
              ? 'bg-[#006194] text-white shadow-xs'
              : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'
          }`}
        >
          <MessageSquare className="w-4 h-4" />
          <span>متن‌ها و پیام‌ها</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveSubtab('appearance')}
          className={`flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all ${
            activeSubtab === 'appearance'
              ? 'bg-[#006194] text-white shadow-xs'
              : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'
          }`}
        >
          <Palette className="w-4 h-4" />
          <span>ظاهر و چیدمان</span>
        </button>
      </div>

      <form onSubmit={handleSave}>
        {/* TAB 1: Engine & Security */}
        {activeSubtab === 'engine' && (
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Penny Engine Configuration */}
            <div className="p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
              <div className="flex items-center gap-2 pb-3 border-b border-slate-200 dark:border-slate-800">
                <Sliders className="w-5 h-5 text-[#006194] dark:text-sky-400" />
                <h4 className="font-extrabold text-sm text-slate-900 dark:text-white">
                  موتور تفکیک انحصاری مبالغ (Penny Engine)
                </h4>
              </div>

              <div className="space-y-4">
                <label className="flex items-center gap-3 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={formState.pennyEngineEnabled}
                    onChange={(e) => setFormState({ ...formState, pennyEngineEnabled: e.target.checked })}
                    className="w-4 h-4 text-[#006194] rounded border-slate-300 focus:ring-[#006194]"
                  />
                  <div>
                    <div className="text-xs font-bold text-slate-900 dark:text-white">
                      فعال‌سازی الگوریتم انحصاری پنی‌انجین
                    </div>
                    <div className="text-[11px] text-slate-500 dark:text-slate-400">
                      افزودن مبالغ تصادفی خرد (پنی) جهت تطبیق بی‌درنگ فاکتورها با پیامک بانکی
                    </div>
                  </div>
                </label>

                <div className="grid grid-cols-2 gap-4 pt-2">
                  <div>
                    <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                      حداقل مقدار پنی (تومان)
                    </label>
                    <input
                      type="number"
                      value={formState.minOffset}
                      onChange={(e) => setFormState({ ...formState, minOffset: Number(e.target.value) })}
                      className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-mono font-bold"
                      min={10}
                      step={10}
                    />
                  </div>

                  <div>
                    <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                      حداکثر مقدار پنی (تومان)
                    </label>
                    <input
                      type="number"
                      value={formState.maxOffset}
                      onChange={(e) => setFormState({ ...formState, maxOffset: Number(e.target.value) })}
                      className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-mono font-bold"
                      min={100}
                      step={50}
                    />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                      مدت اعتبار رزرو پنی (دقیقه)
                    </label>
                    <input
                      type="number"
                      value={formState.windowTimeout}
                      onChange={(e) => setFormState({ ...formState, windowTimeout: Number(e.target.value) })}
                      className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-mono font-bold"
                      min={5}
                      max={120}
                    />
                  </div>

                  <div>
                    <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                      تعداد ارقام انحصاری پنی
                    </label>
                    <select
                      value={formState.matchingDigits}
                      onChange={(e) => setFormState({ ...formState, matchingDigits: Number(e.target.value) })}
                      className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-bold"
                    >
                      <option value={2}>۲ رقم انحصاری (۱۰ تا ۹۹ تومان)</option>
                      <option value={3}>۳ رقم انحصاری (۱۰۰ تا ۹۹۹ تومان)</option>
                      <option value={4}>۴ رقم انحصاری (۱,۰۰۰ تا ۹,۹۹۹ تومان)</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            {/* Receipt Upload Settings */}
            <div className="p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
              <div className="flex items-center gap-2 pb-3 border-b border-slate-200 dark:border-slate-800">
                <FileUp className="w-5 h-5 text-[#006194] dark:text-sky-400" />
                <h4 className="font-extrabold text-sm text-slate-900 dark:text-white">
                  تنظیمات آپلود فیش پرداختی خریدار
                </h4>
              </div>

              <div className="space-y-4">
                <div>
                  <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                    حداکثر حجم مجاز فایل فیش (مگابایت)
                  </label>
                  <input
                    type="number"
                    value={formState.maxFileSize}
                    onChange={(e) => setFormState({ ...formState, maxFileSize: Number(e.target.value) })}
                    className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-mono font-bold"
                    min={1}
                    max={20}
                  />
                </div>

                <div>
                  <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-2">
                    فرمت‌های مجاز تصویر رسید
                  </label>
                  <div className="flex items-center gap-3 flex-wrap">
                    {['jpg', 'png', 'webp', 'pdf'].map((fmt) => {
                      const isChecked = formState.allowedFormats.includes(fmt);
                      return (
                        <label key={fmt} className="flex items-center gap-1.5 text-xs font-mono cursor-pointer uppercase font-bold">
                          <input
                            type="checkbox"
                            checked={isChecked}
                            onChange={(e) => {
                              const newFmts = e.target.checked
                                ? [...formState.allowedFormats, fmt]
                                : formState.allowedFormats.filter(f => f !== fmt);
                              setFormState({ ...formState, allowedFormats: newFmts });
                            }}
                            className="w-3.5 h-3.5 text-[#006194] rounded border-slate-300 focus:ring-[#006194]"
                          />
                          <span>{fmt}</span>
                        </label>
                      );
                    })}
                  </div>
                </div>

                {/* HPOS Security Notice */}
                <div className="p-3 rounded-xl bg-sky-50 dark:bg-sky-950/60 border border-sky-200 dark:border-sky-800 text-xs text-sky-800 dark:text-sky-300 leading-relaxed">
                  فیش‌های ارسالی مستقیماً در دایرکتوری محافظت‌شده با متادیتاهای امنیتی و هش SHA-256 ذخیره شده و با سفارشات ووکامرس پیوند داده می‌شوند.
                </div>
              </div>
            </div>
          </div>
        )}

        {/* TAB 2: Customer Messages */}
        {activeSubtab === 'messages' && (
          <div className="p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs space-y-4 max-w-3xl">
            <div className="flex items-center gap-2 pb-3 border-b border-slate-200 dark:border-slate-800">
              <MessageSquare className="w-5 h-5 text-[#006194] dark:text-sky-400" />
              <h4 className="font-extrabold text-sm text-slate-900 dark:text-white">
                متون و راهنماهای نمایش داده شده به خریدار
              </h4>
            </div>

            <div className="space-y-4">
              <div>
                <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                  عنوان درگاه در صفحه پرداخت ووکامرس
                </label>
                <input
                  type="text"
                  value={formState.gatewayTitle}
                  onChange={(e) => setFormState({ ...formState, gatewayTitle: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-bold"
                />
              </div>

              <div>
                <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                  توضیحات درگاه برای مشتری
                </label>
                <textarea
                  rows={2}
                  value={formState.gatewayDescription}
                  onChange={(e) => setFormState({ ...formState, gatewayDescription: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs"
                />
              </div>

              <div>
                <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                  دستورالعمل واریز کارت به کارت
                </label>
                <textarea
                  rows={2}
                  value={formState.orderInstructions}
                  onChange={(e) => setFormState({ ...formState, orderInstructions: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs"
                />
              </div>

              <div>
                <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                  پیام ثبت موفق فیش در صف بررسی حسابداری
                </label>
                <textarea
                  rows={2}
                  value={formState.pendingReviewMessage}
                  onChange={(e) => setFormState({ ...formState, pendingReviewMessage: e.target.value })}
                  className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs"
                />
              </div>
            </div>
          </div>
        )}

        {/* TAB 3: Appearance & Theme */}
        {activeSubtab === 'appearance' && (
          <div className="p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs space-y-4 max-w-3xl">
            <div className="flex items-center gap-2 pb-3 border-b border-slate-200 dark:border-slate-800">
              <Palette className="w-5 h-5 text-[#006194] dark:text-sky-400" />
              <h4 className="font-extrabold text-sm text-slate-900 dark:text-white">
                سفارشی‌سازی ظاهر پیشخوان و کارت‌های بانکی
              </h4>
            </div>

            <div className="space-y-4">
              <div>
                <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-2">
                  تم ظاهری کارت‌های بانکی در فرم پرداخت خریدار
                </label>
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                  {[
                    { id: 'modern_gold', name: 'طلایی لوکس', bg: 'from-amber-600 to-amber-900' },
                    { id: 'sleek_dark', name: 'مشکی کربن', bg: 'from-slate-800 to-slate-950' },
                    { id: 'banking_blue', name: 'آبی شاپرک', bg: 'from-[#006194] to-sky-700' },
                    { id: 'emerald', name: 'سبز زمردی', bg: 'from-emerald-700 to-teal-950' },
                  ].map((theme) => (
                    <button
                      key={theme.id}
                      type="button"
                      onClick={() => setFormState({ ...formState, cardTheme: theme.id as any })}
                      className={`p-3 rounded-xl border text-center transition-all ${
                        formState.cardTheme === theme.id
                          ? 'border-[#006194] dark:border-sky-400 ring-2 ring-[#006194]/30'
                          : 'border-slate-200 dark:border-slate-700'
                      }`}
                    >
                      <div className={`w-full h-8 rounded-lg bg-gradient-to-r ${theme.bg} mb-2 shadow-xs`}></div>
                      <span className="text-xs font-bold text-slate-800 dark:text-slate-200">{theme.name}</span>
                    </button>
                  ))}
                </div>
              </div>

              <div className="space-y-3 pt-2">
                <label className="flex items-center gap-2.5 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={formState.enableCopyButtons}
                    onChange={(e) => setFormState({ ...formState, enableCopyButtons: e.target.checked })}
                    className="w-4 h-4 text-[#006194] rounded border-slate-300 focus:ring-[#006194]"
                  />
                  <span className="text-xs font-bold text-slate-800 dark:text-slate-200">
                    نمایش دکمه‌های کپی یک‌کلیکه شماره کارت و شبا
                  </span>
                </label>

                <label className="flex items-center gap-2.5 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={formState.showShaba}
                    onChange={(e) => setFormState({ ...formState, showShaba: e.target.checked })}
                    className="w-4 h-4 text-[#006194] rounded border-slate-300 focus:ring-[#006194]"
                  />
                  <span className="text-xs font-bold text-slate-800 dark:text-slate-200">
                    نمایش شماره شبا (IBAN) در کارت‌ها
                  </span>
                </label>
              </div>
            </div>
          </div>
        )}

        {/* Floating Bottom Save Bar */}
        <div className="fixed bottom-4 left-4 z-40 flex items-center gap-2.5 p-2 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-700 shadow-xl">
          {hasChanges && (
            <span className="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 animate-pulse">
              تغییرات ذخیره‌نشده
            </span>
          )}

          {isSaved && (
            <span className="flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
              <CheckCircle2 className="w-3 h-3" />
              <span>ذخیره شد!</span>
            </span>
          )}

          <button
            type="button"
            onClick={handleReset}
            disabled={!hasChanges}
            className="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-40 transition-colors"
          >
            <RotateCcw className="w-3.5 h-3.5" />
            <span>واگرد</span>
          </button>

          <button
            type="submit"
            disabled={!hasChanges}
            className="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#006194] hover:bg-[#004b73] text-white text-xs font-bold transition-all shadow-md shadow-[#006194]/20 disabled:opacity-40"
          >
            <Save className="w-4 h-4" />
            <span>ذخیره تنظیمات</span>
          </button>
        </div>
      </form>
    </div>
  );
};
