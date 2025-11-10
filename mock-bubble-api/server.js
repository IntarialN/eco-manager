const express = require('express');
const bodyParser = require('body-parser');
const fs = require('fs');
const path = require('path');
const { v4: uuid } = require('uuid');

const PORT = process.env.PORT || 4001;
const API_KEY = process.env.API_KEY || 'demo-key';
const DATA_FILE = path.join(__dirname, 'data.json');

function loadData() {
  const raw = fs.readFileSync(DATA_FILE, 'utf8');
  return JSON.parse(raw);
}

let data = loadData();

function saveData() {
  fs.writeFileSync(DATA_FILE, JSON.stringify(data, null, 2));
}

const app = express();
app.use(bodyParser.json());

app.use((req, res, next) => {
  if (!req.path.startsWith('/api')) {
    return next();
  }
  if (req.headers['x-api-key'] !== API_KEY) {
    return res.status(401).json({ error: 'Invalid API key' });
  }
  next();
});

app.get('/health', (req, res) => {
  res.json({ status: 'ok' });
});

app.get('/api/contracts', (req, res) => {
  const { client_external_id } = req.query;
  let contracts = data.contracts;
  if (client_external_id) {
    contracts = contracts.filter((c) => c.client_external_id === client_external_id);
  }
  res.json(contracts);
});

app.get('/api/contracts/:id', (req, res) => {
  const contract = data.contracts.find((c) => c.id === req.params.id);
  if (!contract) {
    return res.status(404).json({ error: 'Not found' });
  }
  res.json(contract);
});

app.patch('/api/contracts/:id', (req, res) => {
  const contract = data.contracts.find((c) => c.id === req.params.id);
  if (!contract) {
    return res.status(404).json({ error: 'Not found' });
  }
  Object.assign(contract, req.body, { updated_at: new Date().toISOString() });
  saveData();
  res.json(contract);
});

app.get('/api/contracts/:id/invoices', (req, res) => {
  const invoices = data.invoices.filter((inv) => inv.contract_id === req.params.id);
  res.json(invoices);
});

app.post('/api/contracts/:id/invoices', (req, res) => {
  const contract = data.contracts.find((c) => c.id === req.params.id);
  if (!contract) {
    return res.status(404).json({ error: 'Contract not found' });
  }
  const invoice = {
    id: uuid(),
    contract_id: contract.id,
    number: req.body.number || `INV-${Date.now()}`,
    issue_date: req.body.issue_date || new Date().toISOString().slice(0, 10),
    due_date: req.body.due_date || null,
    amount: req.body.amount || 0,
    currency: req.body.currency || 'RUB',
    status: req.body.status || 'issued',
    payment_date: null,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  };
  data.invoices.push(invoice);
  saveData();
  res.status(201).json(invoice);
});

app.get('/api/invoices/:id', (req, res) => {
  const invoice = data.invoices.find((inv) => inv.id === req.params.id);
  if (!invoice) {
    return res.status(404).json({ error: 'Not found' });
  }
  res.json(invoice);
});

app.patch('/api/invoices/:id', (req, res) => {
  const invoice = data.invoices.find((inv) => inv.id === req.params.id);
  if (!invoice) {
    return res.status(404).json({ error: 'Not found' });
  }
  Object.assign(invoice, req.body, { updated_at: new Date().toISOString() });
  saveData();
  res.json(invoice);
});

app.get('/api/contracts/:id/acts', (req, res) => {
  const acts = data.acts.filter((act) => act.contract_id === req.params.id);
  res.json(acts);
});

app.post('/api/contracts/:id/acts', (req, res) => {
  const contract = data.contracts.find((c) => c.id === req.params.id);
  if (!contract) {
    return res.status(404).json({ error: 'Contract not found' });
  }
  const act = {
    id: uuid(),
    contract_id: contract.id,
    invoice_id: req.body.invoice_id || null,
    number: req.body.number || `ACT-${Date.now()}`,
    issue_date: req.body.issue_date || new Date().toISOString().slice(0, 10),
    status: req.body.status || 'draft',
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  };
  data.acts.push(act);
  saveData();
  res.status(201).json(act);
});

app.get('/api/acts/:id', (req, res) => {
  const act = data.acts.find((a) => a.id === req.params.id);
  if (!act) {
    return res.status(404).json({ error: 'Not found' });
  }
  res.json(act);
});

app.patch('/api/acts/:id', (req, res) => {
  const act = data.acts.find((a) => a.id === req.params.id);
  if (!act) {
    return res.status(404).json({ error: 'Not found' });
  }
  Object.assign(act, req.body, { updated_at: new Date().toISOString() });
  saveData();
  res.json(act);
});

app.listen(PORT, () => {
  console.log(`Mock Bubble API listening on port ${PORT}`);
});
