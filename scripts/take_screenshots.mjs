import puppeteer from 'puppeteer';
import fs from 'fs';

(async () => {
    // Ensure docs/screenshots directory exists
    const dir = './docs/screenshots';
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
    }

    const browser = await puppeteer.launch({
        headless: 'new',
        defaultViewport: { width: 1440, height: 900 }
    });

    const page = await browser.newPage();

    // Log in
    await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle0' });

    // Filament v3 specific login fields
    await page.waitForSelector('input[type="email"]', { timeout: 15000 });

    await page.type('input[type="email"]', 'admin@timesworld.com');
    await page.type('input[type="password"]', 'password');
    await page.click('button[type="submit"]');

    await page.waitForNavigation({ waitUntil: 'networkidle0' });

    console.log("Logged in successfully. Capturing Dashboard...");

    // 1. Dashboard
    await page.waitForTimeout(1000); // wait for widgets to load
    await page.screenshot({ path: `${dir}/dashboard.png`, fullPage: true });

    // 2. Projects list with filters active
    console.log("Navigating to Projects...");
    await page.goto('http://127.0.0.1:8000/admin/projects');
    await page.waitForSelector('table');
    // Open filter dropdown
    const filterBtn = await page.$$('button[title="Filter"]');
    if (filterBtn.length > 0) {
        await filterBtn[0].click();
        await page.waitForTimeout(1000); // Wait for dropdown animation
    }
    await page.screenshot({ path: `${dir}/projects_dashboard.png` });

    // 3. Project Overview tab
    console.log("Navigating to Project Details...");
    await page.goto('http://127.0.0.1:8000/admin/projects/1/edit');
    await page.waitForSelector('form');
    await page.screenshot({ path: `${dir}/project_overview.png` });

    // 4. Environments tab
    console.log("Capturing Environments Tab...");
    const envTab = await page.$x("//button[contains(., 'Environments')]");
    if (envTab.length > 0) {
        await envTab[0].click();
        await page.waitForTimeout(1000);
        await page.screenshot({ path: `${dir}/environments_tab.png` });

        const expandBtn = await page.$('button[title="Expand"]');
        if (expandBtn) {
            await expandBtn.click();
            await page.waitForTimeout(500);
            await page.screenshot({ path: `${dir}/integrations.png` });
        }
    }

    // 5. Access Summary tab
    console.log("Capturing Access Summary Tab...");
    const aclTab = await page.$x("//button[contains(., 'Access Summary')]");
    if (aclTab.length > 0) {
        await aclTab[0].click();
        await page.waitForTimeout(1000);
        await page.screenshot({ path: `${dir}/access_summary.png` });
    }

    // 6. Integration Types master data
    console.log("Capturing Integration Types...");
    await page.goto('http://127.0.0.1:8000/admin/integration-types');
    await page.waitForSelector('table');
    // Remove modals/notifications if any
    await page.evaluate(() => {
        let notifs = document.querySelectorAll('.fi-no-notification');
        notifs.forEach(n => n.remove());
    });
    await page.screenshot({ path: `${dir}/integration_types.png` });

    // 7. User access view
    console.log("Capturing User Access View...");
    await page.goto('http://127.0.0.1:8000/admin/users/1/edit');
    await page.waitForSelector('form');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: `${dir}/user_access.png` });

    console.log("All screenshots captured under docs/screenshots/");
    await browser.close();
})();
