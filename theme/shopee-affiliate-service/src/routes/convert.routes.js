const express = require('express');
const pLimit = require('p-limit');
const config = require('../config');
const { resolveUrl } = require('../services/resolver.service');
const { extractProductIds, normalizeShopeeUrl, stripTrackingParams } = require('../services/extractor.service');
const { generateAffiliateLink } = require('../services/shopee.service');
const { AppError } = require('../utils/errors');

const router = express.Router();
const limiter = pLimit(config.maxConcurrency);

function withTimeout(promise, timeoutMs, message) {
  return Promise.race([
    promise,
    new Promise((_, reject) => {
      setTimeout(() => reject(new AppError(message, 504)), timeoutMs);
    }),
  ]);
}

async function convertOne(url, subId) {
  if (typeof url !== 'string' || !url.trim()) {
    throw new AppError('invalid URL', 400);
  }

  let parsed;
  try {
    parsed = new URL(url.trim());
  } catch (error) {
    throw new AppError('invalid URL', 400);
  }

  const originalUrl = parsed.toString();

  const resolvedUrl = await withTimeout(resolveUrl(originalUrl), config.resolveTimeoutMs, 'cannot resolve redirect');
  if (!resolvedUrl) {
    throw new AppError('cannot resolve redirect', 422);
  }

  const extracted = extractProductIds(resolvedUrl);
  if (!extracted) {
    throw new AppError('cannot extract product ID', 422);
  }

  const cleanUrl = normalizeShopeeUrl(extracted.shopid, extracted.itemid);
  const normalizedInput = stripTrackingParams(cleanUrl);

  const affiliateLink = await withTimeout(
    generateAffiliateLink(normalizedInput, subId || ''),
    config.convertTimeoutMs,
    'Shopee conversion failed'
  );

  if (!affiliateLink) {
    throw new AppError('Shopee conversion failed', 502);
  }

  return {
    success: true,
    original_url: originalUrl,
    resolved_url: resolvedUrl,
    clean_url: normalizedInput,
    shopid: extracted.shopid,
    itemid: extracted.itemid,
    affiliate_link: affiliateLink,
    sub_id: subId || '',
  };
}

router.post('/convert', async (req, res) => {
  try {
    const { url, sub_id: subId = '' } = req.body || {};
    const data = await limiter(() => convertOne(url, subId));
    res.json(data);
  } catch (error) {
    res.status(error.statusCode || 500).json({
      success: false,
      error: error.message || 'Unexpected error',
    });
  }
});

router.post('/convert-batch', async (req, res) => {
  const { urls, sub_id: subId = '' } = req.body || {};

  if (!Array.isArray(urls) || urls.length === 0) {
    return res.status(400).json({
      success: false,
      error: 'urls is required and must be a non-empty array',
    });
  }

  const tasks = urls.map((url) =>
    limiter(async () => {
      try {
        return await convertOne(url, subId);
      } catch (error) {
        return {
          success: false,
          original_url: typeof url === 'string' ? url : '',
          error: error.message || 'Unexpected error',
          sub_id: subId || '',
        };
      }
    })
  );

  const results = await Promise.all(tasks);
  return res.json({
    success: true,
    count: results.length,
    results,
  });
});

module.exports = router;
