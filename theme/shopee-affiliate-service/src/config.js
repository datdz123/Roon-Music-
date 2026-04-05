const path = require('path');
const dotenv = require('dotenv');

dotenv.config({ path: path.resolve(process.cwd(), '.env') });

function toInt(value, fallback) {
  const parsed = Number.parseInt(value, 10);
  return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
}

module.exports = {
  port: toInt(process.env.PORT, 3018),
  nodeEnv: process.env.NODE_ENV || 'development',
  serviceToken: (process.env.SERVICE_TOKEN || '').trim(),
  maxConcurrency: Math.min(Math.max(toInt(process.env.MAX_CONCURRENCY, 3), 1), 5),
  resolveTimeoutMs: toInt(process.env.RESOLVE_TIMEOUT_MS, 20000),
  convertTimeoutMs: toInt(process.env.CONVERT_TIMEOUT_MS, 45000),
  cacheTtlMs: toInt(process.env.CACHE_TTL_MS, 12 * 60 * 60 * 1000),
  playwrightHeadless: (process.env.PLAYWRIGHT_HEADLESS || 'true') !== 'false',
  shopeeApiUrl: (process.env.SHOPEE_AFFILIATE_API_URL || '').trim(),
  shopeeApiKey: (process.env.SHOPEE_AFFILIATE_API_KEY || '').trim(),
  shopeeEmail: (process.env.SHOPEE_EMAIL || '').trim(),
  shopeePassword: (process.env.SHOPEE_PASSWORD || '').trim(),
  shopeeLoginUrl: (process.env.SHOPEE_AFFILIATE_LOGIN_URL || 'https://affiliate.shopee.vn/login').trim(),
  shopeeCustomLinkUrl: (process.env.SHOPEE_AFFILIATE_CUSTOM_LINK_URL || 'https://affiliate.shopee.vn/custom_link').trim(),
};
