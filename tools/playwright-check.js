const path = require('path');
const fs = require('fs');
const { chromium } = require('playwright');

(async () => {
  const htmlPath = path.resolve(__dirname, 'test-report-page.html');
  if (!fs.existsSync(htmlPath)) {
    console.error('Test HTML not found:', htmlPath);
    process.exit(1);
  }

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  const fileUrl = 'file://' + htmlPath.replace(/\\/g, '/');
  console.log('Opening', fileUrl);
  await page.goto(fileUrl, { waitUntil: 'domcontentloaded' });

  // Function to check a report type and capture screenshot + HTML
  async function checkReport(type) {
    console.log('\n=== Checking', type, '===');
    await page.selectOption('#report_type', type);
    // wait for filters to appear
    try {
      await page.waitForSelector('#filters-section select, #filters-section input', { timeout: 3000 });
    } catch (e) {
      console.error('Filters did not render for', type);
      return { type, ok: false };
    }

    const filtersHtml = await page.$eval('#filters-section', el => el.innerHTML);
    const ua = await page.evaluate(() => navigator.userAgent);
    const screenshotPath = path.resolve(__dirname, `${type.replace(/[^a-z0-9]/gi, '_')}_filters.png`);
    await page.screenshot({ path: screenshotPath, fullPage: true });
    console.log('User agent:', ua);
    console.log('Filters HTML snippet:', filtersHtml.slice(0, 400));
    console.log('Saved screenshot to', screenshotPath);
    return { type, ok: true, filtersHtml, ua, screenshotPath };
  }

  const typesToCheck = ['payroll_summary', 'deduction_summary', 'addition_summary'];
  const results = [];
  for (const t of typesToCheck) {
    // reload to reset state
    await page.reload({ waitUntil: 'domcontentloaded' });
    results.push(await checkReport(t));
  }

  console.log('\nAll results:', results.map(r => ({ type: r.type, ok: r.ok })));

  await browser.close();
})();