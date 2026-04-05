const axios = require('axios');
const { chromium } = require('playwright');
const config = require('../config');

const resolveCache = new Map();

function getCachedValue(key) {
  const value = resolveCache.get(key);
  if (!value) {
    return null;
  }
  if (Date.now() > value.expiredAt) {
    resolveCache.delete(key);
    return null;
  }
  return value.data;
}

function setCachedValue(key, data) {
  resolveCache.set(key, {
    data,
    expiredAt: Date.now() + config.cacheTtlMs,
  });
}

async function resolveWithAxios(url) {
  let finalUrl = url;

  const response = await axios.get(url, {
    timeout: config.resolveTimeoutMs,
    maxRedirects: 5,
    validateStatus: () => true,
    headers: {
      'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
    },
    beforeRedirect: (options) => {
      const protocol = options.protocol || 'https:';
      const host = options.hostname || '';
      const port = options.port ? `:${options.port}` : '';
      const path = options.path || '';
      finalUrl = `${protocol}//${host}${port}${path}`;
    },
  });

  if (response && response.request && response.request.res && response.request.res.responseUrl) {
    finalUrl = response.request.res.responseUrl;
  }

  return finalUrl;
}

async function resolveWithPlaywright(url) {
  const browser = await chromium.launch({ headless: config.playwrightHeadless });
  try {
    const context = await browser.newContext();
    const page = await context.newPage();
    await page.goto(url, {
      waitUntil: 'domcontentloaded',
      timeout: config.resolveTimeoutMs,
    });
    await page.waitForTimeout(1200);
    const finalUrl = page.url();
    await context.close();
    return finalUrl;
  } finally {
    await browser.close();
  }
}

async function resolveUrl(inputUrl) {
  const cached = getCachedValue(inputUrl);
  if (cached) {
    return cached;
  }

  let resolved = '';
  try {
    resolved = await resolveWithPlaywright(inputUrl);
  } catch (error) {
    resolved = await resolveWithAxios(inputUrl);
  }

  setCachedValue(inputUrl, resolved);
  return resolved;
}

module.exports = {
  resolveUrl,
};
