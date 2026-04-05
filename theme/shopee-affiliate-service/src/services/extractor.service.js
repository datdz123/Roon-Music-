const TRACKING_KEYS = ['af_id', 'smtt', 'uls_trackid', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];

function extractProductIds(url) {
  const patterns = [
    /\/(?:product|item)\/(\d+)\/(\d+)/i,
    /\/-i\.(\d+)\.(\d+)/i,
  ];

  for (const pattern of patterns) {
    const match = pattern.exec(url);
    if (match) {
      return { shopid: match[1], itemid: match[2] };
    }
  }

  return null;
}

function normalizeShopeeUrl(shopid, itemid) {
  return `https://shopee.vn/product/${encodeURIComponent(shopid)}/${encodeURIComponent(itemid)}`;
}

function stripTrackingParams(url) {
  let parsed;
  try {
    parsed = new URL(url);
  } catch (error) {
    return url;
  }

  for (const key of [...parsed.searchParams.keys()]) {
    const lower = key.toLowerCase();
    if (lower.startsWith('utm_') || TRACKING_KEYS.includes(lower)) {
      parsed.searchParams.delete(key);
    }
  }

  return parsed.toString();
}

module.exports = {
  extractProductIds,
  normalizeShopeeUrl,
  stripTrackingParams,
};
