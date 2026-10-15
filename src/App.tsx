import React, { useState, useEffect } from 'react';
import { Header } from './components/Header';
import { Sidebar } from './components/Sidebar';
import { DashboardView } from './components/views/DashboardView';
import { OrdersView } from './components/views/OrdersView';
import { CardsView } from './components/views/CardsView';
import { DevicesView } from './components/views/DevicesView';
import { SettingsView } from './components/views/SettingsView';
import { LogsView } from './components/views/LogsView';
import { HealthView } from './components/views/HealthView';
import { CheckoutSimulatorView } from './components/views/CheckoutSimulatorView';
import { ReceiptDrawer } from './components/drawers/ReceiptDrawer';
import { AddCardModal } from './components/modals/AddCardModal';
import { TestWebhookModal } from './components/modals/TestWebhookModal';
import { ManualReceiptModal } from './components/modals/ManualReceiptModal';
import { Toast, ToastMessage } from './components/Toast';

import { 
  initialCards, 
  initialOrders, 
  initialDevices, 
  initialLogs, 
  initialSettings, 
  initialHealth 
} from './data/initialData';
import { BankCard, Order, Device, LogEntry, Settings, SystemHealth } from './types';

export const App: React.FC = () => {
  // Theme state
  const [isDarkMode, setIsDarkMode] = useState<boolean>(() => {
    return localStorage.getItem('wccc_theme') === 'dark';
  });

  useEffect(() => {
    if (isDarkMode) {
      document.documentElement.classList.add('dark');
      localStorage.setItem('wccc_theme', 'dark');
    } else {
      document.documentElement.classList.remove('dark');
      localStorage.setItem('wccc_theme', 'light');
    }
  }, [isDarkMode]);

  const toggleDarkMode = () => setIsDarkMode(prev => !prev);

  // Main state
  const [currentTab, setCurrentTab] = useState<string>('dashboard');
  const [activeViewMode, setActiveViewMode] = useState<'admin' | 'checkout'>('admin');
  const [sidebarOpen, setSidebarOpen] = useState<boolean>(false);

  const [cards, setCards] = useState<BankCard[]>(initialCards);
  const [orders, setOrders] = useState<Order[]>(initialOrders);
  const [devices, setDevices] = useState<Device[]>(initialDevices);
  const [logs, setLogs] = useState<LogEntry[]>(initialLogs);
  const [settings, setSettings] = useState<Settings>(initialSettings);
  const [health, setHealth] = useState<SystemHealth>(initialHealth);

  // Modals & Drawers state
  const [isTestWebhookOpen, setIsTestWebhookOpen] = useState<boolean>(false);
  const [isAddCardOpen, setIsAddCardOpen] = useState<boolean>(false);
  const [isManualReceiptOpen, setIsManualReceiptOpen] = useState<boolean>(false);
  const [editingCard, setEditingCard] = useState<BankCard | null>(null);
  const [inspectingOrder, setInspectingOrder] = useState<Order | null>(null);

  // UI state
  const [isRefreshingShaparak, setIsRefreshingShaparak] = useState<boolean>(false);
  const [isPingingDevices, setIsPingingDevices] = useState<boolean>(false);
  const [isRunningDiagnostics, setIsRunningDiagnostics] = useState<boolean>(false);
  const [copiedKey, setCopiedKey] = useState<string | null>(null);
  const [toasts, setToasts] = useState<ToastMessage[]>([]);

  // Add toast helper
  const addToast = (type: 'success' | 'warning' | 'error' | 'info', title: string, message: string) => {
    const id = Math.random().toString(36).substring(2, 9);
    setToasts(prev => [...prev, { id, type, title, message }]);
    setTimeout(() => {
      setToasts(prev => prev.filter(t => t.id !== id));
    }, 4000);
  };

  const removeToast = (id: string) => {
    setToasts(prev => prev.filter(t => t.id !== id));
  };

  // Add log helper
  const addLog = (level: LogEntry['level'], source: LogEntry['source'], message: string, metadata?: string) => {
    const newLog: LogEntry = {
      id: 'log_' + Date.now(),
      timestamp: new Date().toLocaleTimeString('fa-IR'),
      level,
      source,
      message,
      metadata,
    };
    setLogs(prev => [newLog, ...prev]);
  };

  // Copy helper
  const handleCopyText = (text: string, label: string) => {
    navigator.clipboard.writeText(text);
    setCopiedKey(label);
    addToast('info', 'کپی در کلیپ‌بورد', `${label} در حافظه موقت کپی شد.`);
    setTimeout(() => setCopiedKey(null), 2000);
  };

  // 1. Order actions
  const handleApproveOrder = (orderId: string) => {
    setOrders(prev => prev.map(o => {
      if (o.id === orderId) {
        return { ...o, status: 'completed' as const };
      }
      return o;
    }));
    const target = orders.find(o => o.id === orderId);
    addLog('SUCCESS', 'Admin', `سفارش #${target?.orderNumber || orderId} توسط مدیر تایید و تکمیل شد.`);
    addToast('success', 'تایید سفارش', `سفارش #${target?.orderNumber} با موفقیت تایید و تکمیل گردید.`);
    if (inspectingOrder?.id === orderId) {
      setInspectingOrder(null);
    }
  };

  const handleRejectOrder = (orderId: string) => {
    setOrders(prev => prev.map(o => {
      if (o.id === orderId) {
        return { ...o, status: 'rejected' as const };
      }
      return o;
    }));
    const target = orders.find(o => o.id === orderId);
    addLog('WARNING', 'Admin', `فیش سفارش #${target?.orderNumber || orderId} توسط مدیر رد شد.`);
    addToast('warning', 'رد فیش سفارش', `فیش سفارش #${target?.orderNumber} رد گردید.`);
    if (inspectingOrder?.id === orderId) {
      setInspectingOrder(null);
    }
  };

  const handleManualReceipt = (orderNumber: string, notes: string) => {
    let matched = false;
    setOrders(prev => prev.map(o => {
      if (o.orderNumber === orderNumber) {
        matched = true;
        return { ...o, status: 'completed' as const, notes };
      }
      return o;
    }));

    if (matched) {
      addLog('SUCCESS', 'Admin', `سفارش #${orderNumber} به صورت دستی تایید شد.`, notes);
      addToast('success', 'تایید دستی سفارش', `سفارش #${orderNumber} با یادداشت مدیر تایید شد.`);
    } else {
      // Create new manual order if not found
      const newOrder: Order = {
        id: 'ord_' + orderNumber,
        orderNumber,
        customerName: 'ثبت دستی مدیر',
        customerPhone: '---',
        baseAmount: 150000,
        pennyOffset: 0,
        totalAmount: 150000,
        destCardId: cards[0]?.id || '',
        destBankName: cards[0]?.bankName || 'بانک ملت',
        destCardNumber: cards[0]?.cardNumber || '',
        rrn: 'MANUAL-' + Date.now().toString().slice(-6),
        status: 'completed',
        date: 'هم‌اکنون',
        notes,
      };
      setOrders(prev => [newOrder, ...prev]);
      addLog('SUCCESS', 'Admin', `سفارش دستی جدید #${orderNumber} با موفقیت ایجاد و تایید شد.`);
      addToast('success', 'ایجاد سفارش دستی', `سفارش #${orderNumber} ایجاد و تایید شد.`);
    }
  };

  // 2. Webhook SMS Simulator
  const handleSimulateWebhook = (smsData: {
    rawSms: string;
    senderNumber: string;
    amount: number;
    rrn: string;
    cardLast4: string;
  }) => {
    addLog('INFO', 'Webhook', `دریافت پیامک از ${smsData.senderNumber}: مبلغ ${smsData.amount.toLocaleString('fa-IR')} تومان`, `امضای معتبر HMAC • کارت ${smsData.cardLast4}`);

    // Try matching order by total amount
    const matchedOrder = orders.find(o => 
      (o.status === 'pending_review' || o.status === 'pending_payment') && 
      (o.totalAmount === smsData.amount || Math.abs(o.totalAmount - smsData.amount) <= 10)
    );

    if (matchedOrder) {
      setOrders(prev => prev.map(o => {
        if (o.id === matchedOrder.id) {
          return { ...o, status: 'completed' as const, rrn: smsData.rrn };
        }
        return o;
      }));

      addLog('SUCCESS', 'PennyEngine', `تطبیق ۱۰۰٪ پنی‌انجین با سفارش #${matchedOrder.orderNumber}!`, `مبلغ: ${smsData.amount.toLocaleString('fa-IR')} ت • RRN: ${smsData.rrn}`);
      addToast('success', 'تطبیق هوشمند پنی‌انجین', `پیامک بانکی با سفارش #${matchedOrder.orderNumber} منطبق و سفارش تایید شد!`);
    } else {
      addLog('INFO', 'PennyEngine', `پیامک به مبلغ ${smsData.amount.toLocaleString('fa-IR')} تومان دریافت شد اما سفارش هم‌مبلغ بازیابی نشد. در جدول wccc_messages ذخیره شد.`);
      addToast('info', 'دریافت پیامک', `پیامک دریافت و در جدول پیام‌های شتابی ثبت شد.`);
    }
  };

  // 3. Card actions
  const handleSaveCard = (cardData: Partial<BankCard>) => {
    if (cardData.id) {
      // Edit existing
      setCards(prev => prev.map(c => c.id === cardData.id ? { ...c, ...cardData } as BankCard : c));
      addLog('INFO', 'Admin', `مشخصات کارت بانکی ${cardData.bankName} ویرایش شد.`);
      addToast('success', 'ویرایش کارت', `کارت ${cardData.bankName} با موفقیت بروزرسانی شد.`);
    } else {
      // Add new
      const newCard: BankCard = {
        id: 'card_' + Date.now(),
        bankName: cardData.bankName || 'بانک شتاب',
        cardNumber: cardData.cardNumber || '',
        iban: cardData.iban || '',
        cardHolder: cardData.cardHolder || '',
        dailyLimit: cardData.dailyLimit || 100000000,
        currentDailyTotal: 0,
        dailyTxLimit: cardData.dailyTxLimit || 50,
        currentDailyTx: 0,
        isActive: true,
        colorGradient: 'from-[#006194] via-[#0088CC] to-[#003B64]',
      };
      setCards(prev => [newCard, ...prev]);
      addLog('SUCCESS', 'Admin', `کارت بانکی جدید (${newCard.bankName}) به جدول اختصاصی wccc_cards افزوده شد.`);
      addToast('success', 'ثبت کارت جدید', `کارت ${newCard.bankName} با موفقیت اضافه شد.`);
    }
  };

  const handleDeleteCard = (cardId: string) => {
    const card = cards.find(c => c.id === cardId);
    setCards(prev => prev.filter(c => c.id !== cardId));
    addLog('WARNING', 'Admin', `کارت بانکی ${card?.bankName} (${card?.cardNumber.slice(-4)}) حذف شد.`);
    addToast('warning', 'حذف کارت', 'کارت بانکی با موفقیت حذف گردید.');
  };

  const handleToggleCardStatus = (cardId: string) => {
    setCards(prev => prev.map(c => {
      if (c.id === cardId) {
        const nextState = !c.isActive;
        addLog('INFO', 'Admin', `وضعیت کارت ${c.bankName} به ${nextState ? 'فعال' : 'غیرفعال'} تغییر یافت.`);
        addToast('info', 'تغییر وضعیت کارت', `کارت ${c.bankName} ${nextState ? 'فعال' : 'غیرفعال'} شد.`);
        return { ...c, isActive: nextState };
      }
      return c;
    }));
  };

  const handleResetLimits = () => {
    setCards(prev => prev.map(c => ({ ...c, currentDailyTotal: 0, currentDailyTx: 0 })));
    addLog('INFO', 'Admin', 'سقف‌های روزانه تمامی کارت‌های بانکی ریست شدند.');
    addToast('success', 'بازنشانی سقف‌ها', 'سقف‌های روزانه کلیه کارت‌ها با موفقیت ریست گردید.');
  };

  // 4. Shaparak & Network actions
  const handleRefreshShaparak = () => {
    setIsRefreshingShaparak(true);
    setTimeout(() => {
      setIsRefreshingShaparak(false);
      addLog('SUCCESS', 'ShaparakSync', 'صورت‌حساب شاپرک و پایا با موفقیت دریافت و همگام گردید.');
      addToast('success', 'همگام‌سازی شاپرک', 'شبکه بانکی متصل است و آخرین تراکنش‌ها استعلام شدند.');
    }, 1200);
  };

  // 5. Device pings
  const handlePingDevice = (deviceId: string) => {
    const device = devices.find(d => d.id === deviceId);
    addLog('INFO', 'Admin', `ارسال پینگ به گره ${device?.deviceName}...`);
    setTimeout(() => {
      addToast('success', 'پاسخ هارت‌بیت دستگاه', `دستگاه ${device?.deviceName} آنلاین است. تاخیر: ۱۸ میلی‌ثانیه`);
    }, 600);
  };

  const handlePingAllDevices = () => {
    setIsPingingDevices(true);
    addLog('INFO', 'Admin', 'ارسال پینگ همگانی به تمامی گره‌های پیامک‌خوان اندروید...');
    setTimeout(() => {
      setIsPingingDevices(false);
      addToast('success', 'ارسال پینگ', 'تمامی دستگاه‌های اندرویدی پاسخ دادند و آنلاین هستند.');
    }, 1000);
  };

  // 6. Diagnostics
  const handleRunDiagnostics = () => {
    setIsRunningDiagnostics(true);
    addLog('INFO', 'Admin', 'اجرای دیاگ جامع محیط، جداول و امنیت HMAC...');
    setTimeout(() => {
      setIsRunningDiagnostics(false);
      addToast('success', 'بررسی سلامت سیستم', 'تمامی تست‌های پایگاه داده، پوشه فیش‌ها و توکن HMAC با موفقیت تایید شدند.');
    }, 1500);
  };

  // 7. Checkout simulation completed
  const handleCompleteCheckout = (newOrderData: Partial<Order>) => {
    const createdOrder: Order = {
      id: 'ord_' + Date.now(),
      orderNumber: newOrderData.orderNumber || '1043',
      customerName: newOrderData.customerName || 'خریدار مهمان',
      customerPhone: newOrderData.customerPhone || '09120000000',
      baseAmount: newOrderData.baseAmount || 250000,
      pennyOffset: newOrderData.pennyOffset || 340,
      totalAmount: newOrderData.totalAmount || 250340,
      destCardId: newOrderData.destCardId || cards[0]?.id || '',
      destBankName: newOrderData.destBankName || cards[0]?.bankName || 'بانک ملت',
      destCardNumber: newOrderData.destCardNumber || cards[0]?.cardNumber || '',
      rrn: newOrderData.rrn || '---',
      status: 'pending_review',
      date: 'لحظاتی پیش',
      receiptUrl: newOrderData.receiptUrl,
      buyerCardMask: newOrderData.buyerCardMask,
      notes: newOrderData.notes,
    };

    setOrders(prev => [createdOrder, ...prev]);
    addLog('INFO', 'ReceiptUploader', `ثبت فاکتور جدید #${createdOrder.orderNumber} با مبلغ کل ${createdOrder.totalAmount.toLocaleString('fa-IR')} تومان در انتظار بررسی.`);
    addToast('success', 'ثبت فاکتور خریدار', `سفارش #${createdOrder.orderNumber} با موفقیت ثبت شد و در پیشخوان قرار گرفت.`);
  };

  // Pending count for sidebar badge
  const pendingCount = orders.filter(o => o.status === 'pending_review').length;

  return (
    <div className="min-h-screen bg-[#f8f9ff] dark:bg-[#0b132b] text-slate-800 dark:text-slate-100 flex flex-col font-sans transition-colors duration-200">
      <Toast toasts={toasts} onDismiss={removeToast} />

      <div className="flex flex-1 overflow-hidden">
        {/* Sidebar */}
        <Sidebar
          currentTab={currentTab}
          setCurrentTab={setCurrentTab}
          activeViewMode={activeViewMode}
          setActiveViewMode={setActiveViewMode}
          pendingOrdersCount={pendingCount}
          isOpen={sidebarOpen}
          onClose={() => setSidebarOpen(false)}
        />

        {/* Main Content Area */}
        <div className="flex-1 flex flex-col min-w-0 overflow-y-auto">
          {/* Header */}
          <Header
            currentTab={currentTab}
            activeViewMode={activeViewMode}
            setActiveViewMode={setActiveViewMode}
            isDarkMode={isDarkMode}
            toggleDarkMode={toggleDarkMode}
            onOpenTestWebhook={() => setIsTestWebhookOpen(true)}
            onOpenAddCard={() => {
              setEditingCard(null);
              setIsAddCardOpen(true);
            }}
            onRefreshShaparak={handleRefreshShaparak}
            isRefreshing={isRefreshingShaparak}
            onToggleSidebar={() => setSidebarOpen(prev => !prev)}
          />

          {/* Body Views */}
          <main className="p-4 sm:p-6 lg:p-8 flex-1">
            {activeViewMode === 'checkout' ? (
              <CheckoutSimulatorView
                cards={cards}
                onCompleteCheckout={handleCompleteCheckout}
                onBackToAdmin={() => setActiveViewMode('admin')}
              />
            ) : (
              <>
                {currentTab === 'dashboard' && (
                  <DashboardView
                    cards={cards}
                    orders={orders}
                    devices={devices}
                    onOpenTestWebhook={() => setIsTestWebhookOpen(true)}
                    onOpenAddCard={() => {
                      setEditingCard(null);
                      setIsAddCardOpen(true);
                    }}
                    onSelectTab={(tab) => setCurrentTab(tab)}
                    onInspectOrder={(order) => setInspectingOrder(order)}
                    onApproveOrder={handleApproveOrder}
                  />
                )}

                {currentTab === 'orders' && (
                  <OrdersView
                    orders={orders}
                    onInspectOrder={(order) => setInspectingOrder(order)}
                    onApproveOrder={handleApproveOrder}
                    onRejectOrder={handleRejectOrder}
                    onOpenManualReceipt={() => setIsManualReceiptOpen(true)}
                  />
                )}

                {currentTab === 'cards' && (
                  <CardsView
                    cards={cards}
                    onOpenAddCard={() => {
                      setEditingCard(null);
                      setIsAddCardOpen(true);
                    }}
                    onEditCard={(card) => {
                      setEditingCard(card);
                      setIsAddCardOpen(true);
                    }}
                    onDeleteCard={handleDeleteCard}
                    onToggleCardStatus={handleToggleCardStatus}
                    onResetLimits={handleResetLimits}
                    onCopyText={handleCopyText}
                    copiedKey={copiedKey}
                  />
                )}

                {currentTab === 'devices' && (
                  <DevicesView
                    devices={devices}
                    onPingDevice={handlePingDevice}
                    onPingAllDevices={handlePingAllDevices}
                    isPinging={isPingingDevices}
                    onCopyText={handleCopyText}
                    copiedKey={copiedKey}
                    hmacSecret={settings.hmacSecret}
                    onRegenerateSecret={() => {
                      const newSecret = 'wccc_sec_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                      setSettings(prev => ({ ...prev, hmacSecret: newSecret }));
                      addToast('success', 'تولید کلید جدید', 'کلید امنیتی جدید HMAC با موفقیت ایجاد شد.');
                    }}
                    webhookUrl={health.webhookUrl}
                  />
                )}

                {currentTab === 'settings' && (
                  <SettingsView
                    settings={settings}
                    onSaveSettings={(newSettings) => {
                      setSettings(newSettings);
                      addLog('INFO', 'Admin', 'تنظیمات درگاه و پنی‌انجین با موفقیت بروزرسانی شد.');
                      addToast('success', 'ذخیره تنظیمات', 'پیکربندی پنی‌انجین در پایگاه‌داده ذخیره شد.');
                    }}
                  />
                )}

                {currentTab === 'logs' && (
                  <LogsView
                    logs={logs}
                    onClearLogs={() => {
                      setLogs([]);
                      addToast('info', 'پاکسازی لاگ‌ها', 'تمام رویدادها از حافظه موقت پاک شدند.');
                    }}
                  />
                )}

                {currentTab === 'health' && (
                  <HealthView
                    health={health}
                    onRunDiagnostics={handleRunDiagnostics}
                    isRunningDiagnostics={isRunningDiagnostics}
                  />
                )}
              </>
            )}
          </main>
        </div>
      </div>

      {/* Slide-over Receipt Drawer */}
      <ReceiptDrawer
        order={inspectingOrder}
        isOpen={Boolean(inspectingOrder)}
        onClose={() => setInspectingOrder(null)}
        onApprove={handleApproveOrder}
        onReject={handleRejectOrder}
      />

      {/* Add / Edit Bank Card Modal */}
      <AddCardModal
        isOpen={isAddCardOpen}
        onClose={() => setIsAddCardOpen(false)}
        onSaveCard={handleSaveCard}
        editingCard={editingCard}
      />

      {/* Webhook Test Simulator Modal */}
      <TestWebhookModal
        isOpen={isTestWebhookOpen}
        onClose={() => setIsTestWebhookOpen(false)}
        onSimulateSms={handleSimulateWebhook}
      />

      {/* Manual Receipt Modal */}
      <ManualReceiptModal
        isOpen={isManualReceiptOpen}
        onClose={() => setIsManualReceiptOpen(false)}
        onSubmitManualReceipt={handleManualReceipt}
      />
    </div>
  );
};
export default App;
