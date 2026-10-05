const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const source = fs.readFileSync('js/atomboard.js', 'utf8');

function board(options = {}) {
  const selectors = [{ value: '' }, { value: '' }];
  const elements = new Map();
  const listeners = new Map();
  const requests = [];
  const alerts = [];
  const document = {
    cookie: options.cookie || '',
    documentElement: { dataset: { theme: 'Dark' } },
    body: {
      querySelectorAll: (selector) => selector === '.select-style' ? selectors : [],
      addEventListener: (name, fn) => listeners.set(name, fn),
    },
    querySelectorAll: (selector) => selector === '.select-style' ? selectors : [],
    getElementById: (id) => elements.get(id) || null,
    addEventListener: (name, fn) => listeners.set(name, fn),
  };
  const localStorage = {};
  if (options.savedSettings !== undefined) {
    localStorage.atomSettings = options.savedSettings;
  }
  function XMLHttpRequest() {
    this.open = (method, url) => { this.method = method; this.url = url; };
    this.send = () => requests.push(this);
  }
  XMLHttpRequest.DONE = 4;
  const context = vm.createContext({
    document, localStorage, XMLHttpRequest, alert: (message) => alerts.push(message),
    window: { location: { href: 'http://localhost/test/res/1.html' } },
    Date, encodeURIComponent, decodeURIComponent,
  });
  vm.runInContext(source, context, { filename: 'js/atomboard.js' });
  return { context, document, localStorage, listeners, selectors, elements, requests, alerts };
}

test('theme setting initializes, changes and persists across page loads', () => {
  const first = board();
  assert.equal(JSON.parse(first.localStorage.atomSettings).themeStyle, 'Dark');
  first.listeners.get('DOMContentLoaded')();
  assert.deepEqual(first.selectors.map((el) => el.value), ['Dark', 'Dark']);
  first.context.setThemeStyle({ value: 'Light' });
  assert.equal(first.document.documentElement.dataset.theme, 'Light');
  assert.equal(JSON.parse(first.localStorage.atomSettings).themeStyle, 'Light');
  const next = board({ savedSettings: first.localStorage.atomSettings });
  assert.equal(next.document.documentElement.dataset.theme, 'Light');
  next.listeners.get('DOMContentLoaded')();
  assert.deepEqual(next.selectors.map((el) => el.value), ['Light', 'Light']);
});

test('saved posting password fills both forms and changing it updates cookie', () => {
  const app = board({ cookie: 'atom_password=old-secret' });
  const posting = { value: '' };
  const deletion = { value: '' };
  app.elements.set('newpostpassword', posting);
  app.elements.set('deletepostpassword', deletion);
  app.listeners.get('DOMContentLoaded')();
  assert.equal(posting.value, 'old-secret');
  assert.equal(deletion.value, 'old-secret');
  posting.onchange({ target: { value: 'new secret' } });
  assert.equal(deletion.value, 'new secret');
  assert.match(app.document.cookie, /^atom_password=new%20secret;/);
});

test('passcode check hides captcha only after a valid server response', () => {
  const app = board({ cookie: 'passcode=1' });
  const captcha = { style: { display: '' } };
  const valid = { style: { display: 'none' } };
  app.elements.set('captchablock', captcha);
  app.elements.set('validcaptchablock', valid);
  app.listeners.get('DOMContentLoaded')();
  assert.equal(app.requests[0].url, 'http://localhost/test/imgboard.php?passcode&check');
  const request = app.requests[0];
  request.readyState = 4;
  request.responseText = 'OK';
  request.onreadystatechange({ target: request });
  assert.equal(captcha.style.display, 'none');
  assert.equal(valid.style.display, '');
});

test('like response updates the count and selected state', () => {
  const app = board();
  const classes = new Map();
  const count = { textContent: '' };
  const like = {
    classList: { toggle: (name, enabled) => classes.set(name, enabled) },
    nextElementSibling: count,
  };
  app.context.sendLike(like, 'test', 17);
  const request = app.requests[0];
  assert.equal(request.method, 'POST');
  assert.equal(request.url, '/test/imgboard.php?like=17');
  request.readyState = 4;
  request.status = 200;
  request.responseText = JSON.stringify({ result: 'ok', likes: 2 });
  request.onreadystatechange({ target: request });
  assert.equal(classes.get('like-enabled'), 2);
  assert.equal(count.textContent, 2);
});

test('media thumbnail expands and collapses an image', () => {
  const app = board();
  const expanded = { removed: false, remove() { this.removed = true; } };
  const thumbnail = {
    style: { display: '' },
    nextElementSibling: null,
    insertAdjacentHTML(position, html) {
      assert.equal(position, 'afterend');
      assert.match(html, /src="\/test\/src\/sample.png"/);
      this.nextElementSibling = expanded;
    },
  };
  const wrapper = {
    dataset: { width: '64', height: '48' },
    querySelector: () => thumbnail,
  };
  app.document.body.querySelector = () => wrapper;
  const link = { href: '/test/src/sample.png', parentNode: {
    parentNode: { querySelector: () => wrapper },
  } };
  assert.equal(app.context.expandFile({ which: 1 }, link, 'image'), false);
  assert.equal(thumbnail.style.display, 'none');
  assert.equal(app.context.expandFile({ which: 1 }, link, 'image'), false);
  assert.equal(thumbnail.style.display, '');
  assert.equal(expanded.removed, true);
});
