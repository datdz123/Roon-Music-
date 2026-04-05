# Shopee Affiliate Service

Service to convert any Shopee-related URL into your own affiliate link.

## Features

- POST /convert
- POST /convert-batch
- Resolve short links and redirect links
- Extract shopid and itemid from Shopee URL
- Normalize clean URL
- Option A: call configured API endpoint first
- Option B: fallback to Shopee Affiliate dashboard automation with Playwright
- Concurrency limit (max 5)
- Timeout and structured error handling

## Setup

1. Install dependencies:

npm install
npx playwright install chromium

2. Create env file:

copy .env.example .env

3. Fill env values:

- SERVICE_TOKEN (optional but recommended)
- SHOPEE_AFFILIATE_API_URL (preferred, if you have one)
- SHOPEE_AFFILIATE_API_KEY (if needed)
- SHOPEE_EMAIL and SHOPEE_PASSWORD (fallback dashboard mode)

## Run

npm run dev

Default service URL:

http://127.0.0.1:3018

## API examples

POST /convert body:

{
  "url": "https://shope.ee/xxxx",
  "sub_id": "campaign_01"
}

POST /convert-batch body:

{
  "urls": ["https://shope.ee/1", "https://bit.ly/2"],
  "sub_id": "batch_01"
}
