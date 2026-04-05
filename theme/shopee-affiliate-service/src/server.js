const express = require('express');
const config = require('./config');
const convertRoutes = require('./routes/convert.routes');

const app = express();

app.use(express.json({ limit: '1mb' }));

app.use((req, res, next) => {
  if (!config.serviceToken) {
    return next();
  }

  const auth = req.headers.authorization || '';
  const expected = `Bearer ${config.serviceToken}`;
  if (auth !== expected) {
    return res.status(401).json({
      success: false,
      error: 'Unauthorized',
    });
  }

  return next();
});

app.get('/health', (_req, res) => {
  res.json({
    success: true,
    service: 'shopee-affiliate-service',
    env: config.nodeEnv,
  });
});

app.use('/', convertRoutes);

app.use((err, _req, res, _next) => {
  res.status(500).json({
    success: false,
    error: err && err.message ? err.message : 'Internal server error',
  });
});

app.listen(config.port, () => {
  console.log(`Shopee affiliate service running on port ${config.port}`);
});
