/**
 * Rasterization pipeline for the article-cover image library.
 * Re-run after any template/data tweak to regenerate the whole set.
 * Usage: npm install && npm run render   (from covers/render/)
 *
 * See ../DEVELOPERS.md for the full explanation of what this does and why.
 */

const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');
const sharp = require('sharp');
const categories = require('./data');
const { renderCoverHTML } = require('./template');

const COVERS_DIR = path.join(__dirname, '..');

function altText(cat) {
	const headline = `${cat.headSans} ${cat.headSerif}`.replace(/,/g, '').toLowerCase();
	const subhead = cat.subhead.replace(/\.$/, '').toLowerCase();
	return `PowerData field guide: ${headline} — ${subhead}.`;
}

(async () => {
	const browser = await chromium.launch();
	const page = await browser.newPage({ viewport: { width: 1600, height: 900 }, deviceScaleFactor: 2 });

	const manifest = [];

	for (const cat of categories) {
		const html = renderCoverHTML(cat);
		const htmlPath = path.join(COVERS_DIR, `${cat.slug}.html`);
		fs.writeFileSync(htmlPath, html);

		await page.goto(`file://${htmlPath}`);
		await page.evaluate(() => document.fonts.ready);

		const cover = page.locator('#cover');

		// Hero — full state (kicker + subhead visible)
		const heroPng = await cover.screenshot();
		const heroFile = `${cat.slug}-01.webp`;
		await sharp(heroPng).resize(1600, 900).webp({ quality: 82 }).toFile(path.join(COVERS_DIR, heroFile));

		// Thumb — headline-only state
		await page.evaluate(() => document.getElementById('cover').classList.add('thumb'));
		const thumbPng = await cover.screenshot();
		const thumbFile = `${cat.slug}-01-thumb.webp`;
		await sharp(thumbPng).resize(800, 450).webp({ quality: 80 }).toFile(path.join(COVERS_DIR, thumbFile));

		manifest.push({
			file: heroFile,
			thumb: thumbFile,
			category: cat.slug,
			field: cat.field,
			alt: altText(cat),
			created: new Date().toISOString().slice(0, 10),
		});

		console.log(`✓ ${cat.slug} — hero + thumb rendered`);
	}

	fs.writeFileSync(path.join(COVERS_DIR, 'covers.json'), JSON.stringify(manifest, null, 2) + '\n');

	await browser.close();
	console.log(`\nDone. ${manifest.length} cover(s) rendered, manifest written to covers/covers.json.`);
})();
