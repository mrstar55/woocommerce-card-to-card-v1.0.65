import React from 'react';
import { 
  Smartphone, 
  BatteryCharging, 
  Battery, 
  Wifi, 
  Key, 
  Copy, 
  Check, 
  RefreshCw, 
  QrCode, 
  ShieldCheck,
  Radio
} from 'lucide-react';
import { Device } from '../../types';

interface DevicesViewProps {
  devices: Device[];
  onPingDevice: (deviceId: string) => void;
  onPingAllDevices: () => void;
  isPinging: boolean;
  onCopyText: (text: string, label: string) => void;
  copiedKey: string | null;
  hmacSecret: string;
  onRegenerateSecret: () => void;
  webhookUrl: string;
}

export const DevicesView: React.FC<DevicesViewProps> = ({
  devices,
  onPingDevice,
  onPingAllDevices,
  isPinging,
  onCopyText,
  copiedKey,
  hmacSecret,
  onRegenerateSecret,
  webhookUrl,
}) => {
  return (
    <div className="space-y-6">
      {/* Top Action Banner */}
      <div className="p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h3 className="font-extrabold text-sm sm:text-base text-slate-900 dark:text-white">
            دستگاه‌های متصل پیامک‌خوان اندروید (SMS Sync Nodes)
          </h3>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
            دریافت مستقیم و آنی پیامک‌های واریزی بانک از طریق اپلیکیشن اختصاصی با امضای رمزنگاری‌شده HMAC-SHA256.
          </p>
        </div>

        <button
          type="button"
          onClick={onPingAllDevices}
          disabled={isPinging}
          className="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#006194] hover:bg-[#004b73] text-white text-xs font-bold transition-all shadow-sm shadow-[#006194]/20 disabled:opacity-75"
        >
          <Radio className={`w-4 h-4 ${isPinging ? 'animate-pulse text-amber-300' : ''}`} />
          <span>{isPinging ? 'در حال ارسال پینگ...' : 'ارسال پینگ به تمامی دستگاه‌ها'}</span>
        </button>
      </div>

      {/* 2 Column Layout: Devices List + Pairing QR Box */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left 2 Cols: Connected Devices */}
        <div className="lg:col-span-2 space-y-4">
          <div className="flex items-center justify-between">
            <h4 className="font-extrabold text-sm text-slate-900 dark:text-white flex items-center gap-2">
              <Smartphone className="w-4 h-4 text-[#006194]" />
              <span>گره‌های متصل فعال ({devices.length} دستگاه)</span>
            </h4>
          </div>

          <div className="space-y-4">
            {devices.map((device) => {
              const isOnline = device.status === 'online';

              return (
                <div
                  key={device.id}
                  className="p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs space-y-4"
                >
                  <div className="flex items-start justify-between">
                    <div className="flex items-center gap-3">
                      <div className="w-11 h-11 rounded-xl bg-[#006194]/10 text-[#006194] dark:text-sky-400 flex items-center justify-center">
                        <Smartphone className="w-6 h-6" />
                      </div>
                      <div>
                        <div className="font-extrabold text-sm text-slate-900 dark:text-white">
                          {device.deviceName}
                        </div>
                        <div className="text-xs font-mono text-slate-400">
                          {device.model} • {device.id}
                        </div>
                      </div>
                    </div>

                    <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold ${
                      isOnline ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-600'
                    }`}>
                      {isOnline && <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 pulse-dot-green"></span>}
                      <span>{isOnline ? 'آنلاین و برخط' : 'آفلاین'}</span>
                    </span>
                  </div>

                  {/* 3 Telemetry Metrics */}
                  <div className="grid grid-cols-3 gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-xs">
                    <div>
                      <div className="text-[11px] text-slate-400 mb-0.5">میزان باتری</div>
                      <div className="flex items-center gap-1.5 font-bold text-slate-700 dark:text-slate-200">
                        {device.isCharging ? (
                          <BatteryCharging className="w-4 h-4 text-emerald-500" />
                        ) : (
                          <Battery className="w-4 h-4 text-amber-500" />
                        )}
                        <span className="font-mono">{device.batteryLevel}%</span>
                        {device.isCharging && <span className="text-[10px] text-emerald-600 font-normal">(در حال شارژ)</span>}
                      </div>
                    </div>

                    <div>
                      <div className="text-[11px] text-slate-400 mb-0.5">تاخیر پینگ</div>
                      <div className="flex items-center gap-1.5 font-bold font-mono text-[#006194] dark:text-sky-400">
                        <Wifi className="w-3.5 h-3.5" />
                        <span>{device.pingLatency} ms</span>
                      </div>
                    </div>

                    <div>
                      <div className="text-[11px] text-slate-400 mb-0.5">آخرین هارت‌بیت</div>
                      <div className="font-bold text-slate-700 dark:text-slate-200 text-[11px]">
                        {device.lastHeartbeat}
                      </div>
                    </div>
                  </div>

                  {/* Device Footer Actions */}
                  <div className="flex items-center justify-between pt-2">
                    <span className="text-xs text-slate-500 dark:text-slate-400">
                      پیامک‌های دریافتی امروز: <strong className="font-mono text-slate-900 dark:text-white">{device.receivedSmsToday} پیامک</strong>
                    </span>

                    <button
                      type="button"
                      onClick={() => onPingDevice(device.id)}
                      className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors"
                    >
                      <Radio className="w-3.5 h-3.5 text-sky-500" />
                      <span>ارسال پینگ</span>
                    </button>
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Right 1 Col: Pairing QR Box & Webhook Credentials */}
        <div className="space-y-4">
          <div className="p-5 rounded-2xl bg-white dark:bg-[#111c44] border border-slate-200 dark:border-slate-800 shadow-xs space-y-5">
            <div className="flex items-center gap-2 pb-3 border-b border-slate-200 dark:border-slate-800">
              <QrCode className="w-5 h-5 text-[#006194] dark:text-sky-400" />
              <h4 className="font-extrabold text-sm text-slate-900 dark:text-white">
                اتصال و جفت‌سازی دستگاه جدید
              </h4>
            </div>

            <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
              با اسکن کیوآرکد در اپلیکیشن پیامک‌خوان اندروید WCCC، آدرس وب‌هوک و کلید خصوصی رمزنگاری HMAC به صورت خودکار کانفیگ می‌شود.
            </p>

            {/* Visual QR Code Display */}
            <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col items-center justify-center text-center">
              <div className="w-36 h-36 bg-white p-2.5 rounded-xl border border-slate-200 shadow-xs flex items-center justify-center">
                <div className="w-full h-full bg-slate-900 rounded-lg p-2 flex flex-col items-center justify-center text-white">
                  <QrCode className="w-16 h-16 text-sky-400" />
                  <span className="text-[9px] font-mono mt-1 text-slate-300">WCCC-HMAC-SYNC</span>
                </div>
              </div>
              <span className="text-[11px] text-slate-400 mt-2">اسکن با دوربین اپلیکیشن اندروید</span>
            </div>

            {/* Webhook URL Copy */}
            <div>
              <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                آدرس وب‌هوک افزونه (Webhook URL)
              </label>
              <div className="flex items-center gap-1.5">
                <input
                  type="text"
                  readOnly
                  value={webhookUrl}
                  className="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-mono"
                />
                <button
                  type="button"
                  onClick={() => onCopyText(webhookUrl, 'webhook_url')}
                  className="p-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300"
                  title="کپی آدرس وب‌هوک"
                >
                  {copiedKey === 'webhook_url' ? <Check className="w-3.5 h-3.5 text-emerald-500" /> : <Copy className="w-3.5 h-3.5" />}
                </button>
              </div>
            </div>

            {/* Secret HMAC Token */}
            <div>
              <div className="flex items-center justify-between mb-1">
                <label className="text-[11px] font-bold text-slate-700 dark:text-slate-300">
                  کلید امنیتی اشتراکی (HMAC Secret)
                </label>
                <button
                  type="button"
                  onClick={onRegenerateSecret}
                  className="text-[10px] font-bold text-[#006194] dark:text-sky-400 hover:underline flex items-center gap-1"
                >
                  <RefreshCw className="w-2.5 h-2.5" />
                  <span>تولید کلید نو</span>
                </button>
              </div>
              <div className="flex items-center gap-1.5">
                <input
                  type="password"
                  readOnly
                  value={hmacSecret}
                  className="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-mono"
                />
                <button
                  type="button"
                  onClick={() => onCopyText(hmacSecret, 'hmac_secret')}
                  className="p-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300"
                  title="کپی کلید امنیتی"
                >
                  {copiedKey === 'hmac_secret' ? <Check className="w-3.5 h-3.5 text-emerald-500" /> : <Copy className="w-3.5 h-3.5" />}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
