export type OrderStatus = 'pending_review' | 'completed' | 'rejected' | 'pending_payment';

export interface BankCard {
  id: string;
  bankName: string;
  cardNumber: string; // 16 digits
  iban: string; // 24 digits (without IR)
  cardHolder: string;
  dailyLimit: number; // in Tomans
  currentDailyTotal: number;
  dailyTxLimit: number;
  currentDailyTx: number;
  isActive: boolean;
  colorGradient?: string;
}

export interface Order {
  id: string;
  orderNumber: string;
  customerName: string;
  customerPhone: string;
  baseAmount: number; // in Tomans
  pennyOffset: number; // in Tomans
  totalAmount: number; // baseAmount + pennyOffset
  destCardId: string;
  destBankName: string;
  destCardNumber: string;
  rrn: string; // Reference Retrieval Number / tracking code
  status: OrderStatus;
  date: string;
  receiptUrl?: string;
  notes?: string;
  buyerCardMask?: string;
}

export interface Device {
  id: string;
  deviceName: string;
  model: string;
  batteryLevel: number;
  isCharging: boolean;
  pingLatency: number; // in ms
  status: 'online' | 'offline';
  lastHeartbeat: string;
  receivedSmsToday: number;
  hmacSecret: string;
}

export interface LogEntry {
  id: string;
  timestamp: string;
  level: 'INFO' | 'SUCCESS' | 'WARNING' | 'ERROR';
  source: 'PennyEngine' | 'Webhook' | 'Admin' | 'ReceiptUploader' | 'ShaparakSync';
  message: string;
  metadata?: string;
}

export interface Settings {
  pennyEngineEnabled: boolean;
  minOffset: number;
  maxOffset: number;
  windowTimeout: number; // in minutes
  matchingDigits: number;
  maxFileSize: number; // in MB
  hmacSecret: string;
  autoRefresh: number;
  allowedFormats: string[];
  layoutMode: 'horizontal' | 'sidebar_compact' | 'sidebar_classic';
  stickyHeader: boolean;
  fontSource: 'vazirmatn' | 'system';
  gatewayTitle: string;
  gatewayDescription: string;
  orderInstructions: string;
  pendingReviewMessage: string;
  instantPayNotice: string;
  cardTheme: 'modern_gold' | 'sleek_dark' | 'banking_blue' | 'emerald';
  showBankLogo: boolean;
  enableCopyButtons: boolean;
  showShaba: boolean;
}

export interface SystemHealth {
  environment: string;
  nodeVersion: string;
  hposActive: boolean;
  webhookUrl: string;
  hmacStatus: boolean;
  tables: {
    key: string;
    name: string;
    recordsCount: number;
    status: 'healthy' | 'warning' | 'error';
  }[];
  uploadsWritable: boolean;
  syncLatencyMs: number;
}
