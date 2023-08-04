import {test, expect} from './tools/lib/test-utils.js';

test.useAdminLoggedIn();

test('Test navigation bar menu', async ({page, admin, requestUtils}) => {
  const testId = Math.floor(Math.random() * 10000); // NOSONAR
  const testPageTitle = `Navbar test ${testId}`;

  // Create a new page
  await requestUtils.createPage({
    title: testPageTitle,
    content: `
      <!-- wp:paragraph -->
        <p>The new page for the navigation bar test</p>
      <!-- /wp:paragraph -->
    `,
    status: 'publish',
  });

  // Go to Appearance > Menus
  await admin.visitAdminPage('nav-menus.php');

  // Select the Navigation bar menu
  await page.waitForSelector('#select-menu-to-edit');
  await page.selectOption('#select-menu-to-edit', {label: 'Navigation Bar Menu (Navigation Bar Menu)'});
  await page.getByText('Select', {exact: true}).click();

  // Add the newly created page to the menu
  const pageOptions = page.locator('#add-post-type-page');
  await pageOptions.getByRole('checkbox', {name: testPageTitle}).first().check();
  await pageOptions.getByRole('button', {name: 'Add to Menu'}).click();
  const newMenuItem = page.locator('.menu-item-depth-0', {hasText: testPageTitle});
  await expect(newMenuItem).toBeVisible();
  const newMenuItemLink = await newMenuItem.locator('.link-to-original a').getAttribute('href');
  await page.getByRole('button', {name: 'Save Menu'}).click();
  await expect(page.locator('.updated.notice')).toBeVisible();

  // Check in the frontend that the new menu item is correctly added
  await page.goto('./');
  await expect(page.getByRole('link', {name: testPageTitle})).toBeVisible();
  await expect(page.getByRole('link', {name: testPageTitle})).toHaveAttribute('href', newMenuItemLink);
});
