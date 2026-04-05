const axios = require('axios');
const { chromium } = require('playwright');
const config = require('../config');

let browser;
let context;
let page;
let loggedIn = false;

async function ensureBrowser() {
  if (!browser) {
    browser = await chromium.launch({ headless: config.playwrightHeadless });
  }
  if (!context) {
    context = await browser.newContext();
  }
  if (!page) {
    page = await context.newPage();
  }
  return page;
}

async function loginIfNeeded() {
  if (loggedIn) {
    return;
  }
  if (!config.shopeeEmail || !config.shopeePassword) {
    throw new Error('Missing SHOPEE_EMAIL or SHOPEE_PASSWORD for dashboard fallback.');
  }

  const p = await ensureBrowser();
  await p.goto(config.shopeeLoginUrl, { waitUntil: 'domcontentloaded', timeout: config.convertTimeoutMs });

  const emailSelectors = ['input[type="email"]', 'input[name="email"]', 'input[placeholder*="Email"]', 'input[placeholder*="email"]'];
  const passwordSelectors = ['input[type="password"]', 'input[name="password"]'];

  let filledEmail = false;
  for (const selector of emailSelectors) {
    const element = p.locator(selector).first();
    if (await element.count()) {
      await element.fill(config.shopeeEmail);
      filledEmail = true;
      break;
    }
  }

  let filledPassword = false;
  for (const selector of passwordSelectors) {
    const element = p.locator(selector).first();
    if (await element.count()) {
      await element.fill(config.shopeePassword);
      filledPassword = true;
      break;
    }
  }

  if (!filledEmail || !filledPassword) {
    throw new Error('Cannot find login form elements on Shopee Affiliate dashboard.');
  }

  const loginButtons = ['button:has-text("Đăng nhập")', 'button:has-text("Login")', 'button[type="submit"]'];
  let clicked = false;
  for (const selector of loginButtons) {
    const element = p.locator(selector).first();
    if (await element.count()) {
      await element.click();
      clicked = true;
      break;
    }
  }

  if (!clicked) {
    throw new Error('Cannot find login button on Shopee Affiliate dashboard.');
  }

  await p.waitForLoadState('domcontentloaded', { timeout: config.convertTimeoutMs });
  await p.waitForTimeout(1200);
  loggedIn = true;
}

async function generateViaConfiguredApi(cleanUrl, subId) {
  if (!config.shopeeApiUrl) {
    return '';
  }

  const headers = {
    'Content-Type': 'application/json',
  };

  if (config.shopeeApiKey) {
    headers.Authorization = `Bearer ${config.shopeeApiKey}`;
  }

  const response = await axios.post(
    config.shopeeApiUrl,
    {
      url: cleanUrl,
      sub_id: subId || '',
    },
    {
      headers,
      timeout: config.convertTimeoutMs,
      validateStatus: () => true,
    }
  );

  if (response.status < 200 || response.status >= 300 || !response.data || typeof response.data !== 'object') {
    return '';
  }

  const payload = response.data;
  const candidates = [
    payload.affiliate_link,
    payload.link,
    payload.data && payload.data.affiliate_link,
    payload.data && payload.data.link,
  ];

  for (const candidate of candidates) {
    if (typeof candidate === 'string' && candidate.trim()) {
      return candidate.trim();
    }
  }

  return '';
}

async function generateViaDashboard(cleanUrl, subId) {
  await loginIfNeeded();
  const p = await ensureBrowser();

  await p.goto(config.shopeeCustomLinkUrl, { waitUntil: 'domcontentloaded', timeout: config.convertTimeoutMs });
  await p.waitForTimeout(800);

  const linkInputSelectors = ['textarea', 'input[placeholder*="https://"]', 'input[type="text"]'];
  let inputFilled = false;
  for (const selector of linkInputSelectors) {
    const element = p.locator(selector).first();
    if (await element.count()) {
      await element.fill(cleanUrl);
      inputFilled = true;
      break;
    }
  }

  if (!inputFilled) {
    throw new Error('Cannot find custom link input on Shopee Affiliate page.');
  }

  if (subId) {
    const subSelectors = ['input[name="sub_id1"]', 'input[placeholder*="Sub_id1"]', 'input[placeholder*="Sub ID"]'];
    for (const selector of subSelectors) {
      const element = p.locator(selector).first();
      if (await element.count()) {
        await element.fill(subId);
        break;
      }
    }
  }

  const actionButtons = ['button:has-text("Lấy link")', 'button:has-text("Get Link")', 'button:has-text("Lấy Link")'];
  let clicked = false;
  for (const selector of actionButtons) {
    const element = p.locator(selector).first();
    if (await element.count()) {
      await element.click();
      clicked = true;
      break;
    }
  }

  if (!clicked) {
    throw new Error('Cannot find get-link button on Shopee Affiliate page.');
  }

  await p.waitForTimeout(1500);

  const outputSelectors = ['textarea', 'input[type="text"]', '[class*="result"]', '[class*="link"]'];
  for (const selector of outputSelectors) {
    const element = p.locator(selector).first();
    if (!await element.count()) {
      continue;
    }

    const value = await element.inputValue().catch(() => '');
    if (value && value.includes('shopee')) {
      return value.trim();
    }

    const text = await element.textContent().catch(() => '');
    if (text && text.includes('shopee')) {
      return text.trim();
    }
  }

  throw new Error('Cannot extract affiliate link from Shopee dashboard response.');
}

async function generateAffiliateLink(cleanUrl, subId = '') {
  const fromApi = await generateViaConfiguredApi(cleanUrl, subId).catch(() => '');
  if (fromApi) {
    return fromApi;
  }

  return generateViaDashboard(cleanUrl, subId);
}

module.exports = {
  generateAffiliateLink,
};
